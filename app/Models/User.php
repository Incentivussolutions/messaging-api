<?php

namespace App\Models;

use App\Models\User;
use App\Helpers\Date;
use App\Helpers\Common;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\AppAccessToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role_id',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'            => 'integer',
        'firstname'     => 'string',
        'lastname'      => 'string',
        'email'         => 'string',
        'password'      => 'string',
        'role_id'     => 'integer',
        'status'        => 'integer',
        'created_at'    => 'datetime:d-m-Y H:i:s',
    ];

    /**
     * The Fields
     */
    protected static $fields = [
        'users.id', 
        'users.firstname',
        'users.lastname',
        'users.email',
        'users.password',
        'users.role_id',
        'users.status',
        'users.created_at',
    ];
    /**
     * The Search Fields
     */
    protected static $search_fields = [
        'name'      => 'users.firstname',
        'name'      => 'users.lastname',
        'email'     => 'users.email'
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
        if (@$old_values['password'] && @$new_values['password']) {
            Arr::set($data, 'event',  'password_changed');
        }

        return $data;
    }

    public static function index($request) {
        $response = null;
        try {
            $qry = User::select(self::$fields);
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

    public static function get(Object $request) {
        $response = null;
        try {
            $qry = User::select(self::$fields);
            $qry = $qry->whereNull('users.deleted_at')
                        ->where('users.status', '=', 1);
            if (@$request->role_id) {
                $qry = $qry->where('users.role_id', '=', $request->role_id);
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
                $qry = User::select(self::$fields)
                            ->whereNull('users.deleted_at')
                            ->where('users.id', '=', $request->id);
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
                $user = User::find($request->id);
                $user->updated_user_id = $request->user['id'];
                $user->updated_at = Date::getDateTime();
            } else {
                $user = new User;
                $user->created_user_id = $request->user['id'];
                $user->password = Common::hashmake($request->password);
            }
            $user->firstname            = $request->firstname;
            $user->lastname             = $request->lastname;
            $user->email                = $request->email;
            $user->role_id              = @$request->role_id;
            $user->status               = @$request->status;
            // $user->is_archive   = $request->is_archive;
            // $user->is_active    = $request->is_active;
            // $user->is_admin     = $request->is_admin;
            if($user->save()) { 
                $response = self::first($user);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function login($request) {
        $response = null;
        try {
            $login_qry = User::whereNull('users.deleted_at')
                               ->where('status', '=', 1)
                               ->where('email', '=', $request->email);
            $login_response = $login_qry->first();
            if ($login_response) {
                $user_password = $login_response->password;
                if (Common::hashCheck($request->password, $user_password)) {
                    $response = self::first($login_response);
                }
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function status($request) {
        $response = null;
        try {
            $user = User::find($request->id);
            if ($user) {
                $user->is_active = $request->is_active;
                $response = $user->save();
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

    public static function getUserById($id) {
        $response = null;
        try {
            $qry = User::where('users.id', '=', $id);
            $response = $qry->first();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function changePassword($request) {
        $response = null;
        try {
            $user = User::find($request->id);
            if ($user) {
                $user->password = Common::hashmake($request->new_password);
                $user->updated_user_id = @$request->user->id;
                $response = $user->save();
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function logoutAllDevices($request) {
        try {
            $response = AppAccessToken::where('user_id', '=', $request->id)->delete();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return ApiResponse::send(500);
        }
    }
}
