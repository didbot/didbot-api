<?php
namespace Didbot\DidbotApi\Middleware;

class ThrottleRequest extends \Illuminate\Routing\Middleware\ThrottleRequests
{
    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        return $request->user()->id;
    }
}