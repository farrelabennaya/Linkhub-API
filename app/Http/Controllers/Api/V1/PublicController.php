<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function profile($username)
    {
        $profile = \App\Models\Profile::with(['user.links' => fn($q) => $q->where('is_active', true)->orderBy('position')])
            ->where('username', strtolower($username))->firstOrFail();

        return response()->json([
            'profile' => [
                'username' => $profile->username,
                'display_name' => $profile->display_name,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_url,
                'theme' => $profile->theme,
            ],
            'links' => $profile->user->links->map(fn($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'url' => $l->url
            ])->values()
        ]);
    }
}
