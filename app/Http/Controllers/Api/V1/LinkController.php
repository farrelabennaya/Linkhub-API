<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function index(Request $r)
    {
        return $r->user()->links()->get();
    }
    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:120',
            'url' => 'required|url',
            'is_active' => 'boolean'
        ]);
        $pos = ($r->user()->links()->max('position') ?? 0) + 1;
        $link = $r->user()->links()->create($data + ['position' => $pos]);
        return response()->json($link, 201);
    }
    public function update(Request $r, $id)
    {
        $data = $r->validate([
            'title' => 'sometimes|string|max:120',
            'url' => 'sometimes|url',
            'is_active' => 'sometimes|boolean'
        ]);
        $link = $r->user()->links()->findOrFail($id);
        $link->update($data);
        return $link;
    }
    public function destroy(Request $r, $id)
    {
        $link = $r->user()->links()->findOrFail($id);
        $link->delete();
        return response()->json(['ok' => true]);
    }
    public function reorder(Request $r)
    {
        // body: { order: [linkId1, linkId2, ...] }
        $data = $r->validate(['order' => 'required|array']);
        foreach ($data['order'] as $i => $linkId) {
            $r->user()->links()->where('id', $linkId)->update(['position' => $i + 1]);
        }
        return response()->json(['ok' => true]);
    }
}
