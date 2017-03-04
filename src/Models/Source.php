<?php

namespace Didbot\DidbotApi\Models;

use Illuminate\Database\Eloquent\Model;
use Didbot\DidbotApi\Traits\Uuids;

class Source extends Model
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
     * The dids that belong to the source.
     */
    public function dids()
    {
        return $this->hasMany('Didbot\DidbotApi\Models\Did');
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
            return $query->where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($q) . '%');
        }
    }

    public function scopeSourceableFilter($query, $sourceable_id, $sourceable_type)
    {
        return $query->where([
            'sourceable_id' => $sourceable_id,
            'sourceable_type' => $sourceable_type,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    |
    |
    */


    /**
     * Get all of the owning sourceable models.
     */
    public function sourceable()
    {
        return $this->morphTo();
    }


    /**
     * Why create this intermediary table rather then referring to the oauth_clients table directly?
     *
     * We want every did record to have a source_id that identifies where the did
     * was created (web, android, ios, google home, ifttt.com, ect). Every did must have come from exactly one source
     * that source record should not be removed without cascading to all related dids.
     *
     * However, depending on what type of token is being used we want to name the source using a different method.
     * It would make sense to refer to personal access tokens by token.name since a user might generate several
     * different tokens to use on various services. The user would want to know which of those services the dids came
     * from.
     *
     * On the other hand an app that uses full oAuth2, such as the official didbot mobile app, will over time issue
     * multiple tokens to the same user. In this case it would make sense to use the client's name so all dids coming
     * from the same app will be labeled the same and share a common id.
     *
     * We want to persist this source information to a separate table to easily search by user_id. In the case of oAuth2
     * clients, the clients owner isn't the same user as the one proving the access token. Additionally we don't want
     * to use a tag type for this since
     */

    public function getSourceFromCurrentUser($user)
    {
        $token = $user->token();
        $client = $token->client;

        if($client->personal_access_client){
            $name = $token->name;
            $sourceable_id = $token->token_id;
            $sourceable_type = 'token';
        } else {
            $name = $client->name;
            $sourceable_id = $client->id;
            $sourceable_type = 'client';
        }


        // if the source already exists return it
        $source = $user->sources()->sourceableFilter($sourceable_id, $sourceable_type)->first();
        if($source) return $source;

        // otherwise setup a new source
        $source = new Source();
        $source->sourceable_id = $sourceable_id;
        $source->name = $name;
        $source->sourceable_type = $sourceable_type;
        $source->user_id = $user->id;
        $source->save();

        return $source;
    }

}
