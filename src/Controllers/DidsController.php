<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;
use League\Fractal\Pagination\Cursor;
use Didbot\DidbotApi\Transformers\DidTransformer;

class DidsController extends Controller
{
    /**
     * @param Request $request
     */
    public function getDids(Request $request)
    {

        $currentCursor = ($request->cursor) ? (int)$request->cursor : null;
        $previousCursor = ($request->prev) ? (int)$request->prev : null;

        $dids = $request->user()->dids()->with('tags')->orderBy('id','DESC')->take(20);
        if($currentCursor) $dids->where('id', '<', $currentCursor);
        $dids = $dids->get();

        // Prevent error if no results
        $nextCursor = (count($dids)) ? $dids->last()->id : null;

        return fractal()
                ->collection($dids, new DidTransformer())
                ->withCursor(new Cursor($currentCursor, $previousCursor, $nextCursor, count($dids)))
                ->toJson();
    }

    /**
     * @param Request $request
     */
    public function postDid(Request $request)
    {
        $this->validate($request, ['text' => 'required|max:255']);
        $this->validate($request, ['tags' => 'array']);

        $did = new Did();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->save();

        if(is_array($request->tags) && !empty($request->tags)) $did->tags()->attach($request->tags);

    }

    /**
     * @param Request $request
     * @param integer $id
     */
    public function patchDid(Request $request, $id)
    {
        //
    }

    /**
     * @param Request $request
     * @param integer $id
     */
    public function deleteDid(Request $request, $id)
    {
        $did = Did::where(['id' => $id, 'user_id' => $request->user()->id])->firstOrFail();

        // Detach all tags from the did
        $did->tags()->detach();

        $did->delete();
    }
}
