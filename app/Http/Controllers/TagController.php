<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResoure;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get_tags()
    {
        $tags = Tag::paginate(20);
        return $this->sendResponse(TagResoure::collection($tags)->response()->getData(true), 'Tags retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function create_tag(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $data['slug'] = Str::slug($data['name']);
        $tag = Tag::create($data);
        $created_tag = Tag::find($tag->id);
        return $this->sendResponse(TagResoure::make($created_tag)->response()->getData(true), 'Tag created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function get_tag(Tag $tag)
    {
        return $this->sendResponse(TagResoure::make($tag)->response()->getData(true), 'Tag retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_tag(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $data['slug'] = Str::slug($data['name']);
        $tag->update($data);
        $updated_tag = Tag::find($tag->id);
        return $this->sendResponse(TagResoure::make($updated_tag)->response()->getData(true), 'Tag updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete_tag(Tag $tag)
    {
        $tag->delete();
        return $this->sendResponse([], 'Tag deleted successfully');
    }
}
