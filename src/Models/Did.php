<?php
namespace Didbot\DidbotApi\Models;
use Illuminate\Database\Eloquent\Model;
use Didbot\DidbotApi\Traits\Uuids;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Carbon\Carbon;
use DB;

class Did extends Model
{
    use Uuids, PostgisTrait;

    protected $postgisFields = [
        'geo',
    ];

    protected $hidden = [
        'searchable',
    ];

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
     * The tags that belong to the did.
     */
    public function tags()
    {
        return $this->belongsToMany('Didbot\DidbotApi\Models\Tag');
    }

    /**
     * The source the did belongs to.
     */
    public function source()
    {
        return $this->belongsTo('Didbot\DidbotApi\Models\Source');
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
        if(!empty($q)){

            // fall back to basic search if $q is a single word
            if(!strpos($q, ' ')){
                return $this->scopeSearchFilter($query, $q);
            }


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

    public function scopeSourceFilter($query, $source_id)
    {
        if(!empty($source_id)){
            return $query->where('source_id', $source_id);
        }
    }

    public function scopeCursorFilter($query, $cursor)
    {
        if(!empty($cursor)){
            return $query->whereRaw('uuid_v1_timestamp(id) < uuid_v1_timestamp(?)', [$cursor]);
        }
    }

    public function scopePrevFilter($query, $cursor)
    {
        if(!empty($cursor)){
            return $query->whereRaw('uuid_v1_timestamp(id) > uuid_v1_timestamp(?)', [$cursor]);
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