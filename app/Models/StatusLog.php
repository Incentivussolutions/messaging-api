<?php

namespace App\Models;

use App\Helpers\Date;
use App\Models\TargetQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusLog extends Model
{
    use HasFactory;

    public static function getStatus($request) {
        try {
            $fields = array(
                'response_logs.phone_no',
                'status_logs.message_ref_id',
                'status_logs.status',
                'status_logs.status_code',
                'status_logs.status_date',
                'status_logs.rejected_reason',
            );
            $qry = TargetQueue::select($fields)
                                ->join('response_logs', 'response_logs.target_queue_id', '=', 'target_queues.id')
                                ->join('status_logs', 'status_logs.message_ref_id', '=', 'response_logs.message_ref_id')
                                ->where('target_queues.id', '=', $request->batch_id);
            if (@$request->status) {
                $qry->where('status_logs.status', '=', @$request->status);
            }
            if (@$request->phone_no) {
                $qry->where('response_logs.phone_no', '=', @$request->phone_no);
            }
            if (@$request->date) {
                $qry->whereRaw("date(status_logs.status_date) = '".Date::getDate($request->date)."'");
            }
            $response = $qry->get();
            return $response;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
