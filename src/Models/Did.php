<?php
namespace Didbot\DidbotApi\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
}