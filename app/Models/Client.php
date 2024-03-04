<?php

namespace App\Models;

use App\Helpers\Date;
use App\Helpers\Common;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    public static $fields = array(
        'clients.id',
        'clients.name',
        'clients.authkey',
        'clients.unique_id',
        'clients.status',
        'clients.is_external',
        'clients.created_at',
    );

    public static function store($request) {
        $response = null;
        try {
            if ($request->id) {
                $client = Client::find($request->id);
            } else {
                $client = new Client;
            }
            $client->name     = $request->name;
            $client->is_external   = (@$request->is_external) ? $request->is_external : 0;
            if ($client->save()) {
                $client->authkey        = Common::getClientAuthKey($client);
                while(true) {
                    $uuid               = Common::getRandomKey(8);
                    $is_exists          = Client::where('unique_id', '=', $uuid)->first();
                    if (!$is_exists) {
                        break;
                    }
                }
                $client->unique_id      = $uuid;
                $response = $client->save();
                Common::createClientDB($client);
            }
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }

    public static function search($request) {
        $response = null;
        try {
            $search_fields = array(
                'clients.name',
                'clients.unique_id'
            );
            $qry = Client::select($search_fields)
                           ->whereNull('deleted_at')
                           ->where('status', '=', 1)
                           ->where('name', 'like', '%'.$request->client_name.'%');
            $response = $qry->get();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }

    public static function getClientByAuthKey($auth_key) {
        $response = null;
        try {
            $qry = Client::select(static::$fields)
                           ->whereNull('deleted_at')
                           ->where('status', '=', 1)
                           ->where('authkey', '=', $auth_key);
            $response = $qry->first();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
