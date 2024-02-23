<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public static function get($type, $options=array()) {
        $response = array();
        try {
            $qry = CommonData::select(self::$fields)
                               ->where('type', '=', $type)
                               ->whereNull('deleted_at');
            $response = $qry->get();
            return $response;
        } catch(Exception $e) {
            Log::info($e);
            return $response;
        }
    }
}
