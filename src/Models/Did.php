<?php
namespace Didbot\DidbotApi\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Did extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    |
    |
    */


    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans(Carbon::now(), TRUE, TRUE, 3);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * The tags that belong to the did.
     */
    public function tags()
    {
        return $this->belongsToMany('Didbot\DidbotApi\Models\Tag');
    }

    /**
     * The client that belongs to the did.
     */
    public function client()
    {
        return $this->belongsTo('Laravel\Passport\Client');
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

    public function scopeTagFilter($query, $tag_id)
    {
        if(!empty($tag_id)){
            return $query->whereHas('tags', function ($query) use ($tag_id) {
                $query->where('id', $tag_id);
            });
        }
    }

    public function scopeClientFilter($query, $client_id)
    {
        if(!empty($client_id)){
            return $query->where('client_id', $client_id);
        }
    }

    public function scopeCursorFilter($query, $cursor)
    {
        if(!empty($cursor)){
            return $query->where('id', '<', $cursor);
        }
    }
}