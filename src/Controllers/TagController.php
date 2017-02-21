<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Tag;
use Didbot\DidbotApi\CustomCursor as Cursor;
use Didbot\DidbotApi\Transformers\TagTransformer;

class TagController extends Controller
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
        $tags = $request->user()->tags()
            ->searchFilter($request->q)
            ->cursorFilter($request->cursor)
            ->orderBy('id', 'DESC')->limit(20)->get();

        $results = fractal()
            ->collection($tags, new TagTransformer())
            ->withCursor(new Cursor($request->cursor, $request->prev, $tags));

        return $results;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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

        $did = new Tag();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->save();
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
            $tag = $request->user()->tags()->where('id', $id)->firstOrFail();
            $results = fractal()->item($tag, new TagTransformer());
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
        $this->validate($request, ['text' => 'required|max:255']);

        $tag = Tag()::findOrFail($id);
        $tag->text = $request->text;
        $tag->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $tag = Tag::where(['id' => $id, 'user_id' => $request->user()->id])->firstOrFail();

        // Detach all tags from the did
        $tag->dids()->detach();

        $tag->delete();
    }
}