<?php

namespace Didbot\DidbotApi\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The dids that belong to the tag.
     */
    public function dids()
    {
        return $this->belongsToMany('Didbot\DidbotApi\Models\Did');
    }

}
