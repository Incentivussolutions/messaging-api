<?php

namespace App\Models;

use App\Helpers\Date;
use App\Helpers\Common;
use App\Helpers\Vonage;
use App\Models\CommonData;
use App\Helpers\AppStorage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\TemplateType;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemplateConfig extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_name',
        'template_type',
        'template_namespace',
        'template_id',
        'template_key',
        'whatspp_config_id',
        'language_id',
        'template_content',
        'header',
        'footer',
        'status',
        'created_at',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'            => 'integer',
        'template_name' => 'string',
        'template_type' => 'integer',
        'template_id'   => 'string',
        'template_namespace'   => 'string',
        'template_key'  => 'string',
        'whatspp_config_id' => 'integer',
        'language_id'   => 'integer',
        'template_content' => 'string',
        'header'        => 'array',
        'footer'        => 'array',
        'status'        => 'integer',
        'created_at'    => 'datetime:d-m-Y H:i:s',
    ];

    /**
     * The Fields
     */
    protected static $fields = [
        'template_configs.id', 
        'template_configs.template_name',
        'template_configs.template_type',
        'template_configs.template_namespace',
        'template_configs.template_id',
        'template_configs.template_key',
        'template_configs.whatspp_config_id',
        'template_configs.language_id',
        'template_configs.template_content',
        'template_configs.header',
        'template_configs.footer',
        'template_configs.status',
        'template_configs.created_at',
    ];

    /**
     * The Search Fields
     */
    protected static $search_fields = [
        'name'      => 'template_configs.template_name'
    ];

    
    protected $appends = [
    ];

    // AuditLog Functions

    public function transformAudit(array $data): array {
        if (@$this->created_user_id) {
            $user = TemplateConfig::find($this->created_user_id);
        }
        if (@$this->updated_user_id) {
            $user = TemplateConfig::find($this->updated_user_id);
        }
        if (@$this->deleted_user_id) {
            $user = TemplateConfig::find($this->deleted_user_id);
        }
        if(@$user) {
            Arr::set($data, 'user_type',  'App\Models\User');
            Arr::set($data, 'user_id',  $user->id);
        }
        return $data;
    }

    public function getHeaderAttribute($value) {
        return (@$value) ? json_decode(json_decode($value), true) : null;
    }

    public function getFooterAttribute($value) {
        return (@$value) ? json_decode(json_decode($value), true) : null;
    }

    public function language() {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

    public function whatsappConfig() {
        return $this->hasOne(WhatsappConfig::class, 'id', 'whatspp_config_id');
    }

    public function selectedTemplateType() {
        return $this->hasOne(TemplateType::class, 'id', 'template_type');
    }

    public static function index($request) {
        $response = null;
        try {
            $qry = TemplateConfig::select(self::$fields)
                                   ->with('language', 'whatsappConfig', 'selectedTemplateType');
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
            $qry = TemplateConfig::select(self::$fields);
            $qry = $qry->whereNull('template_configs.deleted_at')
                        ->where('template_configs.status', '=', 1);
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
                $qry = TemplateConfig::select(self::$fields)
                            ->whereNull('template_configs.deleted_at')
                            ->where('template_configs.id', '=', $request->id);
                $response = $qry->first();
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function getTemplateByTemplateID($template_id) {
        $response = null;
        try {
            if (@$template_id) {
                $qry = TemplateConfig::select(self::$fields)
                            ->with('language')
                            ->whereNull('template_configs.deleted_at')
                            ->where('template_configs.template_id', '=', $template_id);
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
                $template_config = TemplateConfig::find($request->id);
                $template_config->updated_user_id = $request->user['id'];
                $template_config->updated_at = Date::getDateTime();
            } else {
                $template_config = new TemplateConfig;
                $template_config->created_user_id = $request->user['id'];
                while(true) {
                    $uuid           = Common::getUUID();
                    $is_exists      = TemplateConfig::where('template_id', '=', $uuid)->first();
                    if (!$is_exists) {
                        break;
                    }
                }
                $template_config->template_id    = $uuid;
            }
            $template_config->template_name      = @$request->template_name;
            $template_config->template_type      = @$request->template_type;
            $template_config->template_namespace = @$request->template_namespace;
            $template_config->template_key       = @$request->template_key;
            $template_config->whatspp_config_id  = @$request->whatspp_config_id;
            $template_config->language_id        = @$request->language_id;
            $template_config->template_content   = @$request->template_content;
            $template_config->header             = (@$request->header) ? json_encode($request->header) : null;
            $template_config->footer             = (@$request->footer) ? json_encode($request->footer) : null;
            $template_config->status             = @$request->status;
            if($template_config->save()) { 
                if (@$request->file('header_file')) {
                    $path = AppStorage::$client_store_path.$request->client['id'].'/'.AppStorage::$template_store_path.$template_config->id.'/'.AppStorage::$header_store_path;
                    $file = $request->file('header_file');
                    $file_name = $file->getClientOriginalName();
                    AppStorage::deleteFolder($path);
                    $file_upload = AppStorage::storeFile($file, $path, $file_name, '');
                }
                if (@$request->file('preview_file')) {
                    $path = AppStorage::$client_store_path.$request->client['id'].'/'.AppStorage::$template_store_path.$template_config->id.'/'.AppStorage::$template_review_store_path;
                    $file = $request->file('preview_file');
                    $file_name = $file->getClientOriginalName();
                    AppStorage::deleteFolder($path);
                    $file_upload = AppStorage::storeFile($file, $path, $file_name, '');
                }
                if (@$request->file('custom_file')) {
                    $path = AppStorage::$client_store_path.$request->client['id'].'/'.AppStorage::$template_store_path.$template_config->id.'/'.AppStorage::$custom_store_path;
                    $file = $request->file('custom_file');
                    $file_name = $file->getClientOriginalName();
                    AppStorage::deleteFolder($path);
                    $file_upload = AppStorage::storeFile($file, $path, $file_name, '');
                }
                $response = self::first($template_config);
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

    
    public static function getHeaderUrl($client_id, $template_config) {
        $path = null;
        $header = $template_config->header;
        if (@$header['input_type'] == 2) {
            $path = AppStorage::getFolderFileUrl('HEADER', $client_id, $template_config->id);
        }
        return $path;
    }
    
    public static function getPreviewUrl($client_id, $template_config) {
        $path = AppStorage::getFolderFileUrl('PREVIEW', $client_id, $template_config->id);
        return $path;
    }
    
    // public function getCustomUrl($request, $template_config) {
    //     $header = $template_config->header;
    //     if (@$header['field_type'] == 2) {
    //         $path = AppStorage::$client_store_path.$request->client['id'].'/'.AppStorage::$template_store_path.$template_config->id.'/'.AppStorage::$header_store_path;
    //     }
    // }

    public static function downloadWhatsappTemplates($request) {
        try {
            $response = Vonage::getTemplatesFromWhatsapp($request->waba_id);
            if (@$response['templates']) {
                dd($response);
            }
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getClientTemplates($request) {
        try {
            $qry = TemplateConfig::with('language', 'selectedTemplateType')
                                   ->whereNull('deleted_at');
            $response = $qry->get();
            return $response;
        } catch (Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
