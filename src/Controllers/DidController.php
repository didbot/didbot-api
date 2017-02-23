<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\CustomCursor as Cursor;
use Didbot\DidbotApi\Transformers\DidTransformer;

class DidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = 20;
        $user = $request->user();

        $dids = $user->dids()
            ->fullTextSearchFilter($request->q, $user->id)
            ->tagFilter($request->tag_id)
            ->clientFilter($request->client_id)
            ->cursorFilter($request->cursor)
            ->with(['tags', 'client'])->orderBy('id', 'DESC')->limit($limit)->get();

        $results = fractal()
            ->collection($dids, new DidTransformer())
            ->withCursor(new Cursor($request->cursor, $request->prev, $dids, $limit));

        return response()->json($results);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, ['text' => 'required|max:255']);
        $this->validate($request, ['tags' => 'array']);

        $did = new Did();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->client_id = $request->user()->token()->client->id;
        $did->save();

        if(is_array($request->tags) && !empty($request->tags)) $did->tags()->attach($request->tags);

        $results = fractal()->item($did, new DidTransformer());
        return response()->json($results);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        try{
            $did = $request->user()->dids()->where('id', $id)->with(['tags', 'client'])->firstOrFail();
            $results = fractal()->item($did, new DidTransformer());
            return response()->json($results);
        }catch (\Exception $e){
            return response()->json([ 'error' => 404, 'message' => 'Not found' ], 404);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json([ 'error' => 404, 'message' => 'Not found' ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $did = Did::where(['id' => $id, 'user_id' => $request->user()->id])->firstOrFail();

        // Detach all tags from the did
        $did->tags()->detach();

        $did->delete();
    }
}
