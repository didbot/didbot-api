<?php
namespace Didbot\DidbotApi\Middleware;
use Closure;

class ReturnJson
{
    public function handle($request, Closure $next)
    {

        $response = $next($request);
        $response->header('Content-Type', 'application/json');

        //add more headers here
        return $response;
    }
}