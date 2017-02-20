<?php
use Didbot\DidbotApi\Middleware\XmlHttpRequest;

Route::group([
    'middleware' => ['auth:api', XmlHttpRequest::class],
    'namespace' => 'Didbot\DidbotApi\Controllers'
], function () {

    Route::resource('dids', 'DidController');
    Route::resource('tags', 'TagController');

});