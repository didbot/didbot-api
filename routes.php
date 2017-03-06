<?php

Route::group([
    'middleware' => ['auth:api', 'didbot.xml-http-request', 'didbot.throttle:450,15'],
    'namespace' => 'Didbot\DidbotApi\Controllers'
], function () {

    Route::resource('dids', 'DidController');
    Route::resource('tags', 'TagController');
    Route::resource('sources', 'SourceController');

});