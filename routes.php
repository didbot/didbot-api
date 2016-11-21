<?php


Route::group([
    'middleware' => 'auth:api',
    'namespace' => 'Didbot\DidbotApi\Controllers'
], function () {

    // Did routes
    Route::get('/dids',            'DidsController@getDids');
    Route::post('/dids',           'DidsController@postDid');
    Route::delete('/dids/{did_id}', 'DidsController@deleteDid');

});