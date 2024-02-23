<?php

namespace App\Models;

use App\Models\User;
use App\Helpers\Date;
use App\Helpers\Common;
use App\Helpers\AppStorage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\AppAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappConfig extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_name',
        'provider',
        'sender_id',
        'application_id',
        'api_key',
        'api_secret',
        'key_file',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'key_file',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'            => 'integer',
        'account_name'  => 'string',
        'provider'      => 'integer',
        'sender_id'     => 'string',
        'application_id'=> 'string',
        'api_key'       => 'string',
        'api_secret'    => 'string',
        'key_file'      => 'string',
        'status'        => 'integer',
        'created_at'    => 'datetime:d-m-Y H:i:s',
    ];

    /**
     * The Fields
     */
    protected static $fields = [
        'whatsapp_configs.id', 
        'whatsapp_configs.account_name',
        'whatsapp_configs.provider',
        'whatsapp_configs.sender_id',
        'whatsapp_configs.application_id',
        'whatsapp_configs.api_key',
        'whatsapp_configs.api_secret',
        'whatsapp_configs.key_file',
        'whatsapp_configs.status',
        'whatsapp_configs.created_at',
    ];
    /**
     * The Search Fields
     */
    protected static $search_fields = [
        'name'      => 'whatsapp_configs.account_name'
    ];

    // AuditLog Functions

    public function transformAudit(array $data): array {
        if (@$this->created_user_id) {
            $user = WhatsappConfig::find($this->created_user_id);
        }
        if (@$this->updated_user_id) {
            $user = WhatsappConfig::find($this->updated_user_id);
        }
        if (@$this->deleted_user_id) {
            $user = WhatsappConfig::find($this->deleted_user_id);
        }
        if(@$user) {
            Arr::set($data, 'user_type',  'App\Models\User');
            Arr::set($data, 'user_id',  $user->id);
        }
        return $data;
    }

    public static function index($request) {
        $response = null;
        try {
            $qry = WhatsappConfig::select(self::$fields);
            $qry = Common::searchIndex($qry, $request, self::$search_fields);
            $qry = Common::orderIndex($request, $qry);
            $response = $qry->paginate(@$request->limit);
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function get(Object $request) {
        $response = null;
        try {
            $qry = WhatsappConfig::select(self::$fields);
            $qry = $qry->whereNull('whatsapp_configs.deleted_at')
                        ->where('whatsapp_configs.status', '=', 1);
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
                $qry = WhatsappConfig::select(self::$fields)
                            ->whereNull('whatsapp_configs.deleted_at')
                            ->where('whatsapp_configs.id', '=', $request->id);
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
                $whatsapp_config = WhatsappConfig::find($request->id);
                $whatsapp_config->updated_user_id = $request->user['id'];
                $whatsapp_config->updated_at = Date::getDateTime();
            } else {
                $whatsapp_config = new WhatsappConfig;
                $whatsapp_config->created_user_id = $request->user['id'];
            }
            $whatsapp_config->account_name      = $request->account_name;
            $whatsapp_config->provider          = $request->provider;
            $whatsapp_config->sender_id         = $request->sender_id;
            $whatsapp_config->application_id    = $request->application_id;
            $whatsapp_config->api_key           = @$request->api_key;
            $whatsapp_config->api_secret        = @$request->api_secret;
            $whatsapp_config->status            = @$request->status;
            if($whatsapp_config->save()) { 
                if (@$request->files && $request->file('file')) {
                    $path = AppStorage::$client_store_path.$request->client['id'].'/'.AppStorage::$key_store_path.$whatsapp_config->id.'/';
                    $file = $request->file('file');
                    $file_name = $file->getClientOriginalName();
                    AppStorage::deleteFile($file_name, $path);
                    $file_upload = AppStorage::storeFile($file, $path, $file_name, '');
                    $whatsapp_config->key_file = $path.$file_name;
                    $whatsapp_config->save();
                }
                $response = self::first($whatsapp_config);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function auditIndex($request) {
        $response = null;
        try {
            $form = self::first(@$request);
            if ($form) {
                $response = $form->audits()
                                   ->with('user')
                                   ->paginate(@$request->limit);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }
}
