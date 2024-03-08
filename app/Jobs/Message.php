<?php

namespace App\Jobs;

use App\Helpers\Date;
use App\Helpers\Vonage;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Message implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $client;
    protected $to_number;
    protected $parameters;
    protected $template;
    protected $config;
    protected $target_queue;
    /**
     * Create a new job instance.
     */
    public function __construct($client, $to_number, $parameters, $template, $config, $target_queue)
    {
        $this->client       = $client;
        $this->to_number    = $to_number;
        $this->parameters   = $parameters;
        $this->template     = $template;
        $this->config       = $config;
        $this->target_queue = $target_queue;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Handling Message Job");
        $client         = $this->client;
        $to_number      = $this->to_number;
        $parameters     = $this->parameters;
        $this->template['language'] = (Object) $this->template['language'];
        $template       = (Object) $this->template;
        $config         = (Object) $this->config;
        $target_queue   = (Object) $this->target_queue;
        $request  = array(
            'client' => $client,
            'to_number' => $to_number,
            'parameters' => $parameters
        );
        $response = Vonage::sendMessage((Object) $request, $config, $template, $target_queue, true);
        Log::info($response);
        $db_name = "messaging_".$client['unique_id'];
        $log_arr = array(
            'target_queue_id'=> @$target_queue->id,
            'phone_no'       => @$to_number,
            'response_data'  => json_encode($response),
            'message_ref_id' => @$response['message_uuid'],
            'created_at'     => Date::getDateTime()
        );
        DB::transaction(function () use ($db_name, $log_arr) {
            DB::table($db_name.'.response_logs')->insert($log_arr);
        });
        Log::info("Message Job Completed");
    }
}
