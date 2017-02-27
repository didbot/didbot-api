<?php

namespace Didbot\DidbotApi\Models;

use Illuminate\Database\Eloquent\Model;
use Didbot\DidbotApi\Traits\Uuids;

class Tag extends Model
{
    use Uuids;

    public $incrementing = false;

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    |
    |
    */

    //

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * The dids that belong to the tag.
     */
    public function dids()
    {
        return $this->belongsToMany('Didbot\DidbotApi\Models\Did');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    |
    |
    */

    public function scopeSearchFilter($query, $q)
    {
        if(!empty($q)){
            return $query->where(DB::raw('LOWER(text)'), 'LIKE', '%' . strtolower($q) . '%');
        }
    }

    public function scopeCursorFilter($query, $cursor)
    {
        if(!empty($cursor)){
            return $query->where('id', '<', $cursor);
        }
    }

}
