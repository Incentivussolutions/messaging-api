<?php

namespace App\Jobs;

use App\Helpers\Date;
use App\Helpers\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Status implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $status;
    /**
     * Create a new job instance.
     */
    public function __construct($request)
    {
        $this->status = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Handling Status Job");
        $status = $this->status;
        Log::info($status);
        if (@$status['client_ref']) {
            $db_name = "messaging_".$status['client_ref'];
            $status_arr = array(
                'message_ref_id' => @$status['message_uuid'],
                'status'         => @$status['status'],
                'status_code'    => (@$status['error']) ? @$status['error']['title'] : null,
                'status_date'    => (@$status['timestamp']) ? Date::getDateTime($status['timestamp']) : null,
                'rejected_reason'=> (@$status['error']) ? @$status['error']['detail'] : null,
                'status_response'=> json_encode($status)
            );
            DB::transaction(function () use ($db_name, $status_arr) {
                DB::table($db_name.'.status_logs')->insert($status_arr);
            });
            Log::info("Status Job Completed");
        }
    }
}
