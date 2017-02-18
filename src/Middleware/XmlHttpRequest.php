<?php
namespace Didbot\DidbotApi\Middleware;
use Closure;

class XmlHttpRequest
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if($request->headers->get('X-Requested-With') != 'XMLHttpRequest'){
            abort(422, 'This endpoint requires the X-Requested-With:XMLHttpRequest header');
        }

        return $response;
    }
}