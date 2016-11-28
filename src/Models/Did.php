<?php
namespace Didbot\DidbotApi\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Did extends Model
{
    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans(Carbon::now(), TRUE, TRUE, 3);
    }

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

    /**
     * Prepares a did eloquent model.
     * @param integer $user_id
     * @param integer $tag_id
     * @param integer $client_id
     * @param integer $cursor
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getDids($user_id, $tag_id, $client_id, $cursor)
    {
        $dids = Did::where('dids.user_id', $user_id);

        if ($cursor) $dids->where('dids.id', '<', $cursor);
        if ($client_id) $dids->where('dids.client_id', $client_id);

        if($tag_id){
            $dids->join('did_tag', 'dids.id', '=', 'did_tag.did_id');
            $dids->where('did_tag.tag_id', $tag_id);
        }

        return $dids;
    }
}