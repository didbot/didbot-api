<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\CustomCursor as Cursor;
use Didbot\DidbotApi\Transformers\DidTransformer;
use Laravel\Passport\Passport;

class DidsController extends Controller
{
    /**
     * @param Request $request
     * @param Did $did
     *
     * @return \Illuminate\Http\Response
     */
    public function getDids(Request $request, Did $did)
    {

        $dids = $did->getDids(
                    $request->user()->id,
                    $request->tag_id,
                    $request->client_id,
                    $request->cursor
                )->with(['tags', 'client'])->orderBy('dids.id', 'DESC')->limit(20)->get();

        $results = fractal()
                ->collection($dids, new DidTransformer())
                ->withCursor(new Cursor($request->cursor, $request->prev, $dids));

        return response()->json($results);
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
        $did->client_id = $request->user()->token()->client->id;
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
