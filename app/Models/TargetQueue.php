<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetQueue extends Model
{
    use HasFactory;

    public static function store($request) {
        try {
            $target_queue = new TargetQueue;
            $target_queue->auth_key = @$request->auth_key;
            $target_queue->phone_no = @$request->to_number;
            $target_queue->request_data = json_encode($request->all());
            $target_queue->send_status = 1;
            $target_queue->save();
            return $target_queue;
        }catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
