<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetQueue extends Model
{
    use HasFactory;

    public static $available_status = array(
        1 => 'submitted',
        2 => 'delivered',
        3 => 'rejected' ,
        4 => 'undeliverable',
        5 => 'read'
    );

    public function getStatusAttribute() {
        if (@$this->status_id) {
            return self::$available_status[$this->status_id];
        } else {
            return null;
        }
    }

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
