<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public static function search($request) {
        $response = null;
        try {
            $search_fields = array(
                'clients.client_name',
                'clients.unique_id'
            );
            $qry = Client::select($search_fields)
                           ->whereNull('deleted_at')
                           ->where('client_status', '=', 1)
                           ->where('client_name', 'like', '%'.$request->client_name.'%');
            $response = $qry->get();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return null;
        }
    }
}
