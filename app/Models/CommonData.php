<?php

namespace App\Models;

use App\Models\User;
use App\Helpers\Date;
use App\Helpers\Common;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommonData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'value',
        'description',
        'reference',
        'sample',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type'          => 'string',
        'value'         => 'integer',
        'description'   => 'string',
        'reference'     => 'string',
        'sample'        => 'string',
        'created_at'    => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The Fields
     */
    protected static $fields = [
        'common_data.value as id',
        'common_data.description',
        'common_data.reference',
        'common_data.sample',
        'common_data.created_at',
    ];

    /**
     * The Search Fields
     */
    protected static $search_fields = [
        'type' => 'common_data.type'
    ];

    // AuditLog Functions

    public function transformAudit(array $data): array {
        if (@$this->created_user_id) {
            $user = User::find($this->created_user_id);
        }
        if (@$this->updated_user_id) {
            $user = User::find($this->updated_user_id);
        }
        if (@$this->deleted_user_id) {
            $user = User::find($this->deleted_user_id);
        }
        if(@$user) {
            Arr::set($data, 'user_type',  'App\Models\User');
            Arr::set($data, 'user_id',  $user->id);
        }
        
        $old_values = $data['old_values'];
        $new_values = $data['new_values'];

        return $data;
    }

    public static function index($request) {
        $response = null;
        try {
            $fields = [
                'common_data.id',
                'common_data.type',
                'common_data.value',
                'common_data.description',
                'common_data.reference',
                'common_data.sample',
                'common_data.created_at',
            ];
            $qry = CommonData::select($fields);
            $qry = Common::searchIndex($qry, $request, self::$search_fields);
            // $qry = $qry->whereNull('users.deleted_at');
            $qry = Common::orderIndex($request, $qry);
            $response = $qry->paginate(@$request->limit);
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function get($type = null) {
        $response = array();
        try {
            $qry = CommonData::select(self::$fields)
            ->whereNull('deleted_at');
            if ($type) {
                $qry->where('type', '=', $type);
            }
            $response = $qry->get();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function first(Object $request) {
        $response = null;
        try {
            if (@$request->id) {
                $qry = CommonData::select(self::$fields)
                            ->whereNull('common_data.deleted_at')
                            ->where('common_data.id', '=', $request->id);
                $response = $qry->first();
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function store($request) {
        $response = null;
        try {
            if ($request->id) {
                $common_data = CommonData::find($request->id);
                $common_data->updated_user_id = $request->user['id'];
                $common_data->updated_at = Date::getDateTime();
            } else {
                $common_data = new CommonData;
                $common_data->created_user_id = $request->user['id'];
            }
            $common_data->type          = $request->type;
            $common_data->value         = $request->value;
            $common_data->description   = $request->description;
            $common_data->reference     = @$request->reference;
            $common_data->sample        = @$request->sample;
            if($common_data->save()) { 
                $response = self::first($common_data);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function destroy($request) {
        $response = null;
        try {
            $qry = CommonData::where('common_data.id', '=', $request->id);
            $common_data = $qry->first();
            if ($common_data) {
                $c = CommonData::find($common_data->id);
                $c->deleted_user_id = $request->user['id'];
                $c->save();
                CommonData::where('common_data.id', $request->id)->delete();
            }
            return true;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }
}
