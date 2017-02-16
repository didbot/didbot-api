<?php

namespace Didbot\DidbotApi\Traits;


trait HasDids
{
    /**
     * Get the dids for the given user.
     */
    public function dids()
    {
        return $this->hasMany('Didbot\DidbotApi\Models\Did');
    }

    /**
     * Get the tags for the given user.
     */
    public function tags()
    {
        return $this->hasMany('Didbot\DidbotApi\Models\Tag');
    }
}