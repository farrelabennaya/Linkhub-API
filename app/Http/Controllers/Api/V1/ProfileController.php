<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $r)
    {
        return $r->user()->profile;
    }
    public function update(Request $r)
    {
        $data = $r->validate([
            'display_name' => 'nullable|string|max:120',
            'bio' => 'nullable|string|max:1000',
            'avatar_url' => 'nullable|url',
            'theme' => 'nullable|in:light,dark,minimal,neon',
            'username' => 'nullable|alpha_dash|unique:profiles,username,' . ($r->user()->profile->id ?? 'NULL'),
        ]);
        $p = $r->user()->profile;
        $p->update(array_filter($data, fn($v) => !is_null($v)));
        return $p->fresh();
    }

    public function uploadAvatar(Request $r)
    {
        $r->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = $r->user();
        $file = $r->file('avatar');

        // simpan ke R2 (public)
        // pakai nama unik juga bisa: $path = 'avatars/'.Str::uuid().'.'.$file->extension();
        // Storage::disk('r2')->putFileAs('avatars', $file, basename($path), ['visibility' => 'public']);
        $path = $file->storePublicly('avatars', 'r2'); // -> 'avatars/xxx.jpg'

        // hapus avatar lama kalau ada dan itu path (bukan url eksternal)
        if ($user->profile && $user->profile->avatar_path) {
            Storage::disk('r2')->delete($user->profile->avatar_path);
        }

        // simpan PATH ke DB
        $user->profile->update([
            'avatar_path' => $path, // <-- simpan path
        ]);

        // generate URL publik buat FE
        $publicUrl = Storage::disk('r2')->url($path);

        return response()->json([
            'avatar_url' => $publicUrl, // FE pakai ini
            'avatar_path' => $path,     // kalau FE mau simpan path juga
            'message' => 'Avatar diperbarui.',
        ]);
    }
    public function deleteAvatar(Request $r)
{
    /** @var \App\Models\User $user */
    $user = $r->user();

    if ($user->profile && $user->profile->avatar_path) {
        Storage::disk('r2')->delete($user->profile->avatar_path);
        $user->profile->update([
            'avatar_path' => null,
            'avatar_url'  => null, // kalau masih ada field lama
        ]);
    }

    return response()->json(['message' => 'Avatar dihapus.']);
}

}
