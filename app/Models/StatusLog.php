<?php

namespace App\Models;

use App\Helpers\Date;
use App\Helpers\Common;
use App\Models\TargetQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusLog extends Model
{
    use HasFactory;


    /**
     * The Search Fields
     */
    protected static $search_fields = [
        'status'      => 'status_logs.status',
        'message_id'  => 'status_logs.message_ref_id',
        'phone_no'    => 'response_logs.phone_no',
        'date'        => 'status_logs.status_date',
    ];
    public static $available_status = array(
        1 => 'submitted',
        2 => 'delivered',
        3 => 'rejected' ,
        4 => 'undeliverable',
        5 => 'read'
    );

    public static $fields = array(
        'response_logs.phone_no',
        'response_logs.ref_id',
        'status_logs.message_ref_id',
        'status_logs.status',
        'status_logs.status as status_id',
        'status_logs.status_code',
        'status_logs.status_date',
        'status_logs.rejected_reason',
    );

    public function getStatusAttribute() {
        return self::$available_status[$this->status];
    }

    public static function index($request) {
        $response = null;
        try {
            $qry = TargetQueue::select(static::$fields)
                                ->join('response_logs', 'response_logs.target_queue_id', '=', 'target_queues.id')
                                ->join('status_logs', 'status_logs.message_ref_id', '=', 'response_logs.message_ref_id');
            $qry = Common::searchIndex($qry, $request, self::$search_fields);
            $qry = Common::orderIndex($request, $qry);
            $qry->orderBy('created_at', 'desc');
            $response = $qry->paginate(@$request->limit);
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function getStatus($request) {
        try {
            $fields = array(
                'response_logs.phone_no',
                'response_logs.ref_id',
                'status_logs.message_ref_id',
                'status_logs.status',
                'status_logs.status_code',
                'status_logs.status_date',
                'status_logs.rejected_reason',
            );
            $qry = TargetQueue::select(static::$fields)
                                ->join('response_logs', 'response_logs.target_queue_id', '=', 'target_queues.id')
                                ->join('status_logs', 'status_logs.message_ref_id', '=', 'response_logs.message_ref_id')
                                ->where('target_queues.id', '=', $request->batch_id);
            if (@$request->status) {
                $qry->where('status_logs.status', '=', @$request->status);
            }
            if (@$request->phone_no) {
                $qry->where('response_logs.phone_no', '=', @$request->phone_no);
            }
            if (@$request->ref_id) {
                $qry->where('response_logs.ref_id', '=', @$request->ref_id);
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
