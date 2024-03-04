<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseLog extends Model
{
    use HasFactory;

    public static function store($log) {
        try {
            $response_log = new ResponseLog;
            $response_log->target_queue_id  = @$log->target_queue->id;
            $response_log->phone_no         = @$log->to_number;
            $response_log->response_data    = json_encode($log->response);
            $response_log->message_ref_id   = @$log->response['message_uuid'];
            return $response_log->save();
        }catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }

}
