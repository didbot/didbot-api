<?php

namespace Didbot\DidbotApi\Services;

use Illuminate\Http\Request;
use Phaza\LaravelPostgis\Geometries\Point;
use Didbot\DidbotApi\Models\Did;
use GuzzleHttp\Client;

class LocationService
{


    public function getLocationFromRequest(Request $request)
    {

        $coordinates = $request->geo;
        if(env('APP_ENV') == 'local' || env('APP_ENV') == 'testing') $coordinates = '34.073823, -118.239975';

        // get coordinates by ip if they were not included with the request
        if(!$coordinates){
            $ip = $request->ip();

            // if this ip_address alaready exists, use existing location information
            $did = Did::where('ip_address', $ip)->first();
            if($did)return $did->geo;

            // TODO: setup a container that holds the Geonames database (for example: https://hub.docker.com/r/jumplead/geonames-server/)
            $client = new Client();
            $response = $client->get('https://ipinfo.io/'. $ip .'/geo');
            $body = json_decode($response->getBody()->getContents());
            $coordinates = $body->loc;

        }

        $coordinates = explode(',', $coordinates);
        $lat = trim($coordinates[0]);
        $lng = trim($coordinates[1]);
        $geo = new Point($lat, $lng);

        return $geo;
    }
}