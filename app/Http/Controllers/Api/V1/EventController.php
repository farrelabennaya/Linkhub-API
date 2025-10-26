<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function click(Request $r)
    {
        $data = $r->validate(['link_id' => 'required|exists:links,id']);
        $ua = substr($r->userAgent() ?? '', 0, 255);
        $ipHash = hash('sha256', $r->ip() ?? '');
        $ref = substr($r->headers->get('referer', ''), 0, 255);
        \App\Models\ClickEvent::create(['link_id' => $data['link_id'], 'ua' => $ua, 'ip_hash' => $ipHash, 'referer' => $ref]);
        return response()->json(['ok' => true]);
    }
}
