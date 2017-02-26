<?php
namespace Didbot\DidbotApi\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Did extends Model
{
    protected $hidden = [
        'searchable',
    ];

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

    public function scopeFullTextSearchFilter($query, $q, $user_id)
    {

        // fall back to basic search if not using pgsql driver or $q is a single word
        if(DB::connection()->getDriverName() != 'pgsql' || !strpos($q, ' ')){
            return $this->scopeSearchFilter($query, $q);
        }

        if(!empty($q)){
            return $query->whereRaw('id IN (
            		SELECT id FROM dids 
            		WHERE searchable @@ plainto_tsquery(?) AND user_id = ?)',
                [$q, $user_id]
            );
        }
    }

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

    public function scopeDateFilter($query, $since, $until)
    {

        if(!empty($since))
        {
            $since = Carbon::parse($since);
            $query->where('created_at', '>=', $since->toDateTimeString());
        }

        if(!empty($until))
        {
            $until = Carbon::parse($until);
            $query->where('created_at', '<=', $until->toDateTimeString());
        }

        return $query;
    }
}