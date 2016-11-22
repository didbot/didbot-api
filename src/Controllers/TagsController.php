<?php

namespace Didbot\DidbotApi\Controllers;

use Illuminate\Http\Request;
use Didbot\DidbotApi\Models\Tag;

class TagsController extends Controller
{
    /**
     * @param Request $request
     */
    public function getTags(Request $request)
    {
        return $request->user()->tags;
    }

    /**
     * @param Request $request
     */
    public function postTag(Request $request)
    {
        $this->validate($request, ['text' => 'required|max:255']);

        $did = new Tag();
        $did->user_id = $request->user()->id;
        $did->text = $request->text;
        $did->save();
    }

    /**
     * @param Request $request
     * @param         $id
     */
    public function patchTag(Request $request, $id)
    {
        $this->validate($request, ['text' => 'required|max:255']);

        $tag = Tag()::findOrFail($id);
        $tag->text = $request->text;
        $tag->save();
    }

    /**
     * @param Request $request
     * @param         $id
     */
    public function deleteTag(Request $request, $id)
    {
        $tag = Tag::where(['id' => $id, 'user_id' => $request->user()->id])->firstOrFail();

        // Detach all tags from the did
        $tag->dids()->detach();

        $tag->delete();
    }
}
