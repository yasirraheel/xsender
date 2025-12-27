<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class InsertDispatchLogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logs;

    /**
     * Create a new job instance.
     *
     * @param array $logs
     * @return void
     */
    public function __construct(array $logs)
    {
        $this->logs = $logs;
        $this->onQueue('dispatch-logs'); 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::transaction(function () {
            DB::table('dispatch_logs')->insert($this->logs);
        });
    }
}