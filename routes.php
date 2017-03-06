<?php

Route::group([
    'middleware' => ['auth:api', 'didbot.xml-http-request', 'didbot.throttle:450,15'],
    'namespace' => 'Didbot\DidbotApi\Controllers'
], function () {
    $prefix = env('ROUTE_PREFIX', '/api/1.0');
    Route::resource($prefix . '/dids', 'DidController');
    Route::resource($prefix . '/tags', 'TagController');
    Route::resource($prefix . '/sources', 'SourceController');

});