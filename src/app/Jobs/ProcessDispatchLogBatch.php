<?php

namespace App\Jobs;

use App\Http\Utility\SendWhatsapp;
use Carbon\Carbon;
use App\Models\Gateway;
use App\Models\DispatchLog;
use App\Models\DispatchUnit;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Http\Utility\SendSMS;
use App\Http\Utility\SendMail;
use Illuminate\Support\Facades\DB;
use App\Enums\System\ChannelTypeEnum;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\System\CommunicationStatusEnum;
use App\Enums\System\Gateway\WhatsAppGatewayTypeEnum;
use App\Models\User;
use App\Service\Admin\Core\CustomerService;
use App\Services\System\Communication\DispatchService;
use Exception;

class ProcessDispatchLogBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;
    protected $pipe;
    protected $isBulk;
    protected $channel;
    protected $sendSMS;
    protected $sendMail;
    protected $sendWhatsapp;
    protected $dispatchService;
    protected $customerService;

    /**
     * __construct
     *
     * @param array $ids
     * @param ChannelTypeEnum $channel
     * @param string $pipe
     * @param bool $isBulk
     */
    public function __construct(array $ids, ChannelTypeEnum $channel, string $pipe, bool $isBulk)
    {
        $this->ids              = $ids;
        $this->pipe             = $pipe;
        $this->isBulk           = $isBulk;
        $this->channel          = $channel;
        $this->sendSMS          = new SendSMS();
        $this->sendMail         = new SendMail();
        $this->sendWhatsapp     = new SendWhatsapp();
        $this->customerService  = new CustomerService();
        $this->dispatchService  = new DispatchService();
        $this->queue            = config("queue.pipes.{$pipe}.{$channel->value}");
    }

    /**
     * handle
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->isBulk) {
            DispatchUnit::whereIn('id', $this->ids)
                ->with([
                    'message',
                    'gateway',
                    'dispatchLogs' => function ($query) {
                        $query->where('status', CommunicationStatusEnum::PENDING)
                            ->with(['message', 'contact', 'gatewayable']);
                    }
                ])
                ->lazyById()
                ->each(function (DispatchUnit $unit) {
                    $logs = $unit->dispatchLogs;
                    if ($logs->isEmpty()) return;
                    $gateway = $unit->gateway;
                    if (!$gateway) {
                        $this->failUnit($unit, $logs, translate("Gateway could not be used"));
                        return;
                    }
                    try {
                        $this->processBulkUnit($unit, $logs, $gateway);
                    } catch (Exception $e) {
                        
                        $this->failUnit($unit, $logs, $e->getMessage());
                    }
                });
        } else {
            DispatchLog::whereIn('id', $this->ids)
                ->where('status', CommunicationStatusEnum::PENDING)
                ->with(['contact', 'message', 'gatewayable'])
                ->lazyById()
                ->each(function (DispatchLog $log) {
                    
                    try {
                        
                        $this->processSingleLog($log);
                    } catch (Exception $e) {
                        
                        $this->failLog($log, $e->getMessage());
                    }
                });
        }
    }

    /**
     * processBulkUnit
     *
     * @param DispatchUnit $unit
     * @param Collection $logs
     * @param Gateway $gateway
     * 
     * @return void
     */
    protected function processBulkUnit(DispatchUnit $unit, Collection $logs, Gateway $gateway): void
    {
        $message = $unit->message;
        $to = $logs->pluck("contact.{$this->channel->value}_contact", "id")->all();

        $this->updateAndApplyGatewayDelays($gateway, count($to));
        
        if ($this->channel === ChannelTypeEnum::SMS) {
            
            $this->sendSMS->send(
                strtolower($gateway->type),
                $to,
                $gateway,
                $logs,
                $message->message
            );
        } elseif ($this->channel === ChannelTypeEnum::EMAIL) {
            $this->sendMail->send(
                $gateway,
                $to,
                $message->subject,
                $message->main_body,
                $logs
            );
        } elseif ($this->channel === ChannelTypeEnum::WHATSAPP) {

            $this->sendWhatsapp->send(
                $gateway,
                $to,
                $logs,
                $message,
                $message->message
            );
        } else {
            throw new \Exception("Channel {$this->channel->value} not yet implemented for bulk dispatch.");
        }
        $unit->update([
            'status' => CommunicationStatusEnum::DELIVERED,
            'response_message' => translate('Bulk dispatch successful'),
        ]);
    }

    /**
     * processSingleLog
     *
     * @param DispatchLog $log
     * 
     * @return void
     */
    protected function processSingleLog(DispatchLog $log): void
    {
        $now        = Carbon::now();
        $message    = $log->message;
        $contact    = $log->contact;
        $gateway    = $log->gatewayable;
        $to         = $contact->{"{$this->channel->value}_contact"};
        
        if (!$message || !$contact || !$gateway || !$to) {
            $this->failLog($log, translate("Something went wrong during dispatch, please contact support"));
            return;
        }
        $this->updateAndApplyGatewayDelays($gateway, 1);

        $log->sent_at   = $now;
        $log->status    = CommunicationStatusEnum::PROCESSING;
        $log->save();
        
        if ($this->channel === ChannelTypeEnum::SMS) {

            $messageText = replaceContactVariables($contact, $message->message);
            
            $this->sendSMS->send(
                strtolower($gateway->type),
                $to,
                $gateway,
                $log,
                $messageText
            );
        } elseif ($this->channel === ChannelTypeEnum::EMAIL) {

            $subject    = replaceContactVariables($contact, $message->subject);
            $mainBody   = replaceContactVariables($contact, $message->main_body);
            
            $this->sendMail->send(
                $gateway,
                $to,
                $subject,
                $mainBody,
                $log
            );
        } elseif ($this->channel === ChannelTypeEnum::WHATSAPP) {

            $messageText = replaceContactVariables($contact, $message->message);

            $this->sendWhatsapp->send(
                $gateway,
                $to,
                $log,
                $message,
                $messageText
            );
        } else {
            throw new \Exception("Channel {$this->channel->value} not yet implemented for Gateway dispatch.");
        }
    }

    /**
     * updateAndApplyGatewayDelays
     *
     * @param Gateway $gateway
     * @param int $messagesSent
     * 
     * @return void
     */
    protected function updateAndApplyGatewayDelays(Gateway $gateway, int $messagesSent): void
    {
        $currentCount = $gateway->sent_message_count;
        $newCount = $currentCount + $messagesSent;

        if ($gateway->per_message_min_delay > 0 || $gateway->per_message_max_delay > 0) {
            $minDelay = max(0, $gateway->per_message_min_delay);
            $maxDelay = max($minDelay, $gateway->per_message_max_delay);

            $totalDelay = 0;
            for ($i = 0; $i < $messagesSent; $i++) {
                $delay = mt_rand((int)($minDelay * 1_000_000), (int)($maxDelay * 1_000_000));
                $totalDelay += $delay;
            }

            \Log::info("Applying total delay: $totalDelay microseconds");
            $start = microtime(true);

            $chunkDelay = 1_000_000;
            while ($totalDelay > 0) {
                $sleepTime = min($chunkDelay, $totalDelay);
                usleep($sleepTime);
                $totalDelay -= $sleepTime;
            }

            $end = microtime(true);
            \Log::info("Finished delay, actual duration: " . (($end - $start) * 1000000) . " microseconds");
        }

        if ($gateway->delay_after_count > 0 && $newCount >= $gateway->delay_after_count) {
            $cycles = floor($newCount / $gateway->delay_after_count) - floor($currentCount / $gateway->delay_after_count);

            if ($gateway->delay_after_duration > 0 && $cycles > 0) {
                $cycleDelay = (int)($gateway->delay_after_duration * 1_000_000 * $cycles);
                \Log::info("Applying cycle delay: $cycleDelay microseconds");
                $start = microtime(true);
                usleep($cycleDelay);
                $end = microtime(true);
                \Log::info("Finished cycle delay, actual duration: " . (($end - $start) * 1000000) . " microseconds");
            }
        }

        if ($gateway->reset_after_count > 0 && $newCount >= $gateway->reset_after_count) {
            $newCount = $newCount % $gateway->reset_after_count;
        }

        $gateway->sent_message_count = $newCount;
        $gateway->save();
    }


    /**
     * failUnit
     *
     * @param DispatchUnit $unit
     * @param mixed $logs
     * @param string $message
     * 
     * @return void
     */
    protected function failUnit(DispatchUnit $unit, $logs, string $message): void
    {
        $unit->update([
            'status' => CommunicationStatusEnum::FAIL,
            'response_message' => $message,
        ]);
        DispatchLog::where('dispatch_unit_id', $unit->id)->update([
            'status' => CommunicationStatusEnum::FAIL,
            'response_message' => $message,
            'retry_count' => DB::raw('retry_count + 1'),
        ]);
        if ($logs->isNotEmpty() && $logs->first()->user_id) {
            $user = User::find($logs->first()->user_id);
            
            if ($user) {
                $creditCount = $logs->count();
                $serviceType = $this->dispatchService->getServiceType($this->channel);
                $this->customerService->addedCreditLog(
                    $user,
                    $creditCount,
                    $serviceType->value,
                    false,
                    translate("Re-added {$creditCount} credits due to failed {$this->channel->name} bulk dispatch: {$message}")
                );
            }
        }
    }

    /**
     * failLog
     *
     * @param DispatchLog $log
     * @param string $message
     * 
     * @return void
     */
    protected function failLog(DispatchLog $log, string $message): void
    {
        $log->update([
            'status' => CommunicationStatusEnum::FAIL,
            'response_message' => $message,
            'retry_count' => $log->retry_count + 1,
        ]);

        if ($log->user_id) {
            $user = User::find($log->user_id);
            if ($user) {
                $serviceType = $this->dispatchService->getServiceType($this->channel);
                $creditCount = $this->channel === ChannelTypeEnum::WHATSAPP && !$log->whatsapp_template_id
                    ? count(str_split($log->message->message ?? '', site_settings('whatsapp_word_count')))
                    : 1;
                $this->customerService->addedCreditLog(
                    $user,
                    $creditCount,
                    $serviceType->value,
                    false,
                    translate("Re-added {$creditCount} credit due to failed {$this->channel->name} dispatch: {$message}")
                );
            }
        }
    }

}