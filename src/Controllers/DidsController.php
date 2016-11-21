<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;

class DidsController extends Controller
{
    /**
     * @param Request $request
     */
    public function getDids(Request $request)
    {
        return $request->user()->dids;
    }

    /**
     * @param Request $request
     */
    public function postDid(Request $request)
    {
        $this->validate($request, ['text' => 'required|max:255']);

        $did = new Did();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->save();
    }

    /**
     * @param Request $request
     * @param         $id
     */
    public function patchDid(Request $request, $id)
    {
        //
    }

    /**
     * @param Request $request
     * @param         $id
     */
    public function deleteDid(Request $request, $id)
    {
        Did::where(['id' => $id, 'user_id' => $request->user()->id])->delete();
    }
}
