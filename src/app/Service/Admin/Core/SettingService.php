<?php

namespace App\Service\Admin\Core;

use App\Enums\Common\Status;
use App\Enums\SettingKey;
use App\Enums\StatusEnum;
use App\Enums\System\ChannelTypeEnum;
use App\Enums\System\SessionStatusEnum;
use App\Models\AndroidApi;
use App\Models\AndroidSession;
use App\Models\Gateway;
use App\Models\PricingPlan;
use Illuminate\Support\Arr;
use App\Models\Setting;
use App\Rules\FileExtentionCheckRule;
use App\Service\Admin\Core\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class SettingService {

    public function getIndex($type) {


        $data = [

            "title" => translate(ucfirst($type)." Settings"),
        ];
        switch($type) {

            case "general":

                $type_data = [
                    "countries"     => json_decode(file_get_contents(resource_path('views/partials/country_file.json'))),
                    "timeLocations" => collect(timezone_identifiers_list())->groupBy(function($item) {
                        return explode('/', $item)[0];
                    })
                ];
                $data = array_merge($data, $type_data);
                break;

            case "member" :

                $type_data = [
                    
                    "plans"  => PricingPlan::active()
                                                ->select('id', 'name')
                                                ->latest()
                                                ->get(),

                    "sms_api_gateways"  => Gateway::whereNull('user_id')
                                                        ->where("channel", ChannelTypeEnum::SMS)
                                                        ->where('status', Status::ACTIVE)
                                                        ->orderBy('is_default', 'DESC')
                                                        ->get(),
                    "sms_android_gateways"  => AndroidSession::whereNull('user_id')
                                                                    ->where('status', SessionStatusEnum::CONNECTED)
                                                                    ->with(['androidSims'])
                                                                    ->orderBy('id', 'DESC')
                                                                    ->get(),
                    "mail_gateways" => Gateway::whereNull('user_id')
                                                    ->where("channel", ChannelTypeEnum::EMAIL)
                                                    ->where('status', Status::ACTIVE)
                                                    ->orderBy('is_default', 'DESC')
                                                    ->get(),
                ];
                $data = array_merge($data, $type_data);
                break;
            case "authentication":

                $data["title"] = translate("Authentication Page Setup");

                case "automation":
                    $phpPath = PHP_BINARY ?: '/usr/bin/php';
                    $queueConfig = site_settings('queue_connection_config', config('setting.site_settings.queue_connection_config', [
                            'driver' => 'database',
                            'connection' => [
                                'host' => null,
                                'port' => null,
                                'database' => null,
                                'username' => null,
                                'password' => null,
                            ],
                        ]));
                    if(gettype($queueConfig) == "string") $queueConfig = json_decode($queueConfig, true);

                    $type_data = [
                        "title" => translate("Automation Settings"),
                        "domain" => request()->getHost(),
                        "curl" => [
                            "all_queues_url" => route('queue.work'),
                            "cron_run_url" => route('cron.run'), 
                            "queues" => [
                                "import-contacts" => route('queue.work.import-contacts'),
                                "verify-email" => route('queue.work.verify-email'),
                                // "dispatch-logs" => route('queue.work.dispatch-logs'),
                                "regular-sms" => route('queue.work.regular-sms'),
                                "regular-email" => route('queue.work.regular-email'),
                                "regular-whatsapp" => route('queue.work.regular-whatsapp'),
                                "campaign-sms" => route('queue.work.campaign-sms'),
                                "campaign-email" => route('queue.work.campaign-email'),
                                "campaign-whatsapp" => route('queue.work.campaign-whatsapp'),
                            ],
                            "worker_trigger_command" => $phpPath . base_path('artisan') . " queue:work:worker-trigger",
                        ],
                        "command" => [
                            "commands" => [
                                "import-contacts" => $phpPath . " ". base_path('artisan') . " queue:work:import-contacts",
                                "verify-email" => $phpPath. " ". base_path('artisan') . " queue:work:verify-email",
                                // "dispatch-logs" => $phpPath . " ". base_path('artisan') . " queue:work:dispatch-logs",
                                "regular-sms" => $phpPath . " ". base_path('artisan') . " queue:work:regular-sms",
                                "regular-email" => $phpPath . " ". base_path('artisan') . " queue:work:regular-email",
                                "regular-whatsapp" => $phpPath . " ". base_path('artisan') . " queue:work:regular-whatsapp",
                                "campaign-sms" => $phpPath . " ". base_path('artisan') . " queue:work:campaign-sms",
                                "campaign-email" => $phpPath . " ". base_path('artisan') . " queue:work:campaign-email",
                                "campaign-whatsapp" => $phpPath . " ". base_path('artisan') . " queue:work:campaign-whatsapp",
                                "worker-trigger" => $phpPath . " ". base_path('artisan') . " queue:work:worker-trigger",
                            ],
                        ],
                        "supervisor" => [
                            "root_dir" => base_path(),
                            "user" => get_current_user() ?: 'www-data',
                            "group" => posix_getgrgid(posix_getegid())['name'] ?? 'www-data',
                            "artisan_path" => base_path('artisan'),
                            "php_binary" => $phpPath,
                        ],
                        "queue_info" => [
                            "priority_order" => [
                                // 'dispatch_logs',
                                "regular-email",
                                "regular-sms",
                                "regular-whatsapp",
                                "campaign-email",
                                "campaign-sms",
                                "campaign-whatsapp",
                                'import-contacts',
                                'verify-email',
                            ],
                            "no_auth_warning" => translate("cURL routes are publicly accessible."),
                        ],
                        "cron_path" => base_path('artisan'),
                        "connections" => [
                            "driver" => Arr::get($queueConfig, 'driver'),
                            "sync" => [],
                            "database" => [],
                            "beanstalkd" => [
                                "host" => [
                                    "label"         => translate("Beanstalkd Host"),
                                    "placeholder"   => translate("e.g., localhost"),
                                    "required"      => true,
                                    "value"         => Arr::get($queueConfig, 'connection.host'),
                                ],
                                "port" => [
                                    "label" => translate("Beanstalkd Port"),
                                    "placeholder" => translate("e.g., 11300"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.port'),
                                    
                                ],
                            ],
                            "sqs" => [
                                "key" => [
                                    "label" => translate("AWS Access Key ID"),
                                    "placeholder" => translate("e.g., AKIA..."),
                                    "required" => true,
                                    "value"         => Arr::get($queueConfig, 'connection.key'),
                                    
                                ],
                                "secret" => [
                                    "label" => translate("AWS Secret Access Key"),
                                    "placeholder" => translate("e.g., your_secret"),
                                    "required" => true,
                                    "value"         => Arr::get($queueConfig, 'connection.hsecretost'),
                                    
                                ],
                                "prefix" => [
                                    "label" => translate("SQS Prefix"),
                                    "placeholder" => translate("e.g., https://sqs.us-east-1.amazonaws.com/your-account-id"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.prefix'),
                                    
                                ],
                                "queue" => [
                                    "label" => translate("SQS Queue Name"),
                                    "placeholder" => translate("e.g., default"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.queue'),
                                    
                                ],
                                "region" => [
                                    "label" => translate("AWS Region"),
                                    "placeholder" => translate("e.g., us-east-1"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.region'),
                                    
                                ],
                            ],
                            "redis" => [
                                "host" => [
                                    "label" => translate("Redis Host"),
                                    "placeholder" => translate("e.g., 127.0.0.1"),
                                    "required" => true,
                                    "value"         => Arr::get($queueConfig, 'connection.host'),
                                    
                                ],
                                "port" => [
                                    "label" => translate("Redis Port"),
                                    "placeholder" => translate("e.g., 6379"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.port'),
                                    
                                ],
                                "database" => [
                                    "label" => translate("Redis Database"),
                                    "placeholder" => translate("e.g., 0"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.database'),
                                    
                                ],
                                "username" => [
                                    "label" => translate("Redis Username"),
                                    "placeholder" => translate("Optional"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.username'),
                                    
                                ],
                                "password" => [
                                    "label" => translate("Redis Password"),
                                    "placeholder" => translate("Optional"),
                                    "required" => false,
                                    "value"         => Arr::get($queueConfig, 'connection.password'),
                                    
                                ],
                            ],
                        ],
                    ];
                    $data = array_merge($data, $type_data);
                    break;
        }

        return $data;
    }
    
    /**
     * settings validations
     * 
     * @return array
     */
    public function validationRules(array $request_data ,string $key = 'site_settings') :array{

        $rules      = [];
        $message    = [];

        foreach ($request_data as $data_key => $data_value) {

            if ($data_value instanceof UploadedFile) {

                $rules[$key . "." . $data_key] = ['nullable', 'image', new FileExtentionCheckRule(json_decode(site_settings('mime_types'), true))];
            } else {
                
                $rules[$key . "." . $data_key] = ['nullable'];
                $messages[$key . "." . $data_key . '.nullable'] = ucfirst(str_replace('_', ' ', $data_key)) . ' ' . translate('Field is Required');
            }
        }
        return [
            'rules'   => $rules,
            'message' => $message
        ];
    }

    /**
     * updateSettings
     *
     * @param array $request_data
     * @param string|null|null $channel
     * 
     * @return void
     */
    public function updateSettings(array $request_data, string|null $channel = null): void {
        $json_keys = Arr::get(config('setting'), 'json_object', []);
        $fileService = new FileService();
        
        foreach ($request_data as $key => $value) {
            
            if ($value instanceof UploadedFile) {
                
                $filePath = config("setting.file_path.$key")['path'];
                $fileName = $fileService->uploadFile(file: $value, key: $key, file_path: $filePath);
                
                if ($fileName) {
                    
                    $value = $fileName;
                }
            } elseif (in_array($key, $json_keys)) {
                
                
                $value = $this->processNestedFiles($value, $key, $fileService);
                
                $existingSetting = Setting::where('key', $key)->first();
                
                $existingData = $existingSetting && $existingSetting->value ? json_decode($existingSetting->value, true) : [];
                
                $mergedData = array_merge($existingData, $value);
                $value = json_encode($mergedData);
                
                
            } 
            try {
                
                Setting::updateOrInsert(
                    [
                        'key'   => $key
                    ],
                    [
                        'channel' => $channel,
                        'value' => $value
                    ]
                );
                
                Cache::forget("site_settings");
            } catch (\Throwable $th){}
        }
    }

    /**
     * processNestedFiles
     *
     * @param array $data
     * @param string $key
     * @param FileService $fileService
     * 
     * @return array
     */
    private function processNestedFiles(array $data, string $key, FileService $fileService): array
    {
        foreach ($data as $index => $item) {
            if ($item instanceof UploadedFile) {
                
                $filePath = config("setting.file_path.$key")['path'];

                $fileName = $fileService->uploadFile(file: $item, key: $key, file_path: $filePath);

                if ($fileName) {
                    $data[$index] = $fileName;
                } else {
                    // If file upload fails, remove the field to avoid storing the UploadedFile object
                    unset($data[$index]);
                }
            } elseif (is_array($item)) {

                $data[$index] = $this->processNestedFiles($item, $key, $fileService);
            }
        }

        return $data;
    }

    public function prepData($request) {

        $is_default = null;
        $data['currencies'] = json_decode(site_settings("currencies"), true);
        if($request->has('old_code')) {
            
            $is_default = $data['currencies'][$request->input('old_code')]['is_default'];
            unset($data['currencies'][$request->input('old_code')]);
        }
        $data['currencies'][$request->input('code')] = [

            'name'       => $request->input('name'),
            'symbol'     => $request->input('symbol'),
            'rate'       => $request->input('rate'),
            'status'     => StatusEnum::TRUE->status(),
            'is_default' => $is_default == StatusEnum::TRUE->status() ? StatusEnum::TRUE->status() : StatusEnum::FALSE->status(),
        ];
        return $data;
    }

    public function statusUpdate($request) {

        $status  = true;
        $reload  = false;
        $column   = $request->input("column");
        $message  = $column != "is_default" ? translate('Currency status updated successfully') : translate("Default currency changed");
        $data['currencies'] = json_decode(site_settings('currencies'), true);
        
        if ($column != 'is_default' && $data['currencies'][$request->input('id')]['status'] == StatusEnum::TRUE->status() &&  $data['currencies'][$request->input('id')]['is_default'] != StatusEnum::TRUE->status()) {

            $data['currencies'][$request->input('id')]['status'] = StatusEnum::FALSE->status();
            
        } elseif ($column != 'is_default' && $data['currencies'][$request->input('id')]['status'] == StatusEnum::FALSE->status()) {

           $data['currencies'][$request->input('id')]['status'] = StatusEnum::TRUE->status();

        } elseif($column == 'is_default') {

            
            $reload = true;
            $data['currencies'] = array_map(function ($currency) {
                $currency['is_default'] = StatusEnum::FALSE->status();
                return $currency;
            }, $data['currencies']);
            $data['currencies'][$request->input('id')]['is_default'] = StatusEnum::TRUE->status();
            $data['currencies'][$request->input('id')]['status'] = StatusEnum::TRUE->status();

        } else {

            $status  = false;
            $reload  = true;
            $message = translate("Can not disable default currency status");
        }

        return [
            $status, 
            $reload, 
            $message,
            $data
        ];
    }
}
