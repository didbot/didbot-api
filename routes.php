<?php

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;

Route::group([
    'middleware' => 'auth:api'
], function () {

    // Get dids
    Route::get('/dids', function (Request $request) {
        return $request->user()->dids;
    });
    // Delete did
    Route::delete('/dids/{did_id}', function (Request $request, $did_id) {
        Did::where(['id' => $did_id, 'user_id' => $request->user()->id])->delete();
    });
    // Create did
    Route::post('/dids', function (Request $request) {

        //$this->validate($request, ['text' => 'required|max:255']);

        $did = new Did();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->save();
    });


});