<?php


Route::group([
    'middleware' => ['auth:api', 'ReturnJson'],
    'namespace' => 'Didbot\DidbotApi\Controllers'
], function () {

    // Did routes
    Route::get('/dids',            'DidsController@getDids');
    Route::post('/dids',           'DidsController@postDid');
    Route::delete('/dids/{id}', 'DidsController@deleteDid');

    // Tag routes
    Route::get('/tags', 'TagsController@getTags');
    Route::post('/tags', 'TagsController@postTag');
    Route::patch('/tags/{id}', 'TagsController@patchTag');
    Route::delete('/tags/{id}', 'TagsController@deleteTag');

});