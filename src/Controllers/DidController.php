<?php

namespace Didbot\DidbotApi\Controllers;

use Didbot\DidbotApi\Services\LocationService;
use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Did;
use Didbot\DidbotApi\Models\Source;
use Didbot\DidbotApi\CustomCursor as Cursor;
use Didbot\DidbotApi\Transformers\DidTransformer;
use Phaza\LaravelPostgis\Geometries\Point;
use DB;
use phpDocumentor\Reflection\Location;

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
        $prev = null;
        $user = $request->user();

        $this->validate($request, [
            'since'     => 'iso8601',
            'until'     => 'iso8601',
            'cursor'    => 'uuid',
            'tag_id'    => 'uuid',
            'source_id' => 'uuid',
            ]);

        $dids = $user->dids()
            ->fullTextSearchFilter($request->q, $user->id)
            ->tagFilter($request->tag_id)
            ->sourceFilter($request->source_id)
            ->cursorFilter($request->cursor)
            ->dateFilter($request->since, $request->until)
            ->with(['tags', 'source'])
            ->orderBy(DB::raw('uuid_v1_timestamp(id)'), 'DESC')
            ->limit($limit)->get();

        // if the cursor is being passed in then we need to obtain the previous id.
        if(!empty($request->cursor)){
            $dids_prev = $user->dids()
                ->fullTextSearchFilter($request->q, $user->id)
                ->tagFilter($request->tag_id)
                ->sourceFilter($request->source_id)
                ->prevFilter($request->cursor)
                ->dateFilter($request->since, $request->until)
                ->orderBy(DB::raw('uuid_v1_timestamp(id)'), 'ASC')
                ->limit($limit)->get();

            if($dids_prev->count() == $limit) $prev = $dids_prev->last()->id;
        }

        $results = fractal()
            ->collection($dids, new DidTransformer())
            ->withCursor(new Cursor($request->cursor, $prev, $dids, $limit));

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
     * @param  \Didbot\DidbotApi\Models\Source  $source
     * @param  \Didbot\DidbotApi\Services\LocationService  $loc_service
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Source $source, LocationService $loc_service)
    {
        $this->validate($request, [
            'text' => 'required|max:255',
            'tags' => 'array',
            'geo' => 'geo'
        ]);

        if(count($request->tags) > 20) abort(422, 'This request has exceeded the maximum number of tags');

        $this->validate($request, ['tags.*' => 'uuid|exists:tags,id'],
            ['tags.*' => [
                'uuid' => 'Tag identifier(s) must be in the 8-4-4-4-12 uuid format and match an existing tag identifier.'
            ]
        ]);

        $user = $request->user();
        $source = $source->getSourceFromCurrentUser($user);
        $geo = $loc_service->getLocationFromRequest($request);

        $did = new Did();
        $did->user_id   = $request->user()->id;
        $did->text      = $request->text;
        $did->source_id = $source->id;
        $did->geo       = $geo;
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
        $this->validate($request, [
            'id' => 'uuid'
        ]);

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
        $this->validate($request, [
            'id' => 'uuid'
        ]);

        $did = Did::where(['id' => $id, 'user_id' => $request->user()->id])->firstOrFail();

        // Detach all tags from the did
        $did->tags()->detach();

        $did->delete();
    }
}
