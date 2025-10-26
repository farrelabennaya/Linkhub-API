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
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB
        ], [
            'avatar.required' => 'File avatar wajib diisi.',
            'avatar.image'    => 'File harus berupa gambar.',
            'avatar.mimes'    => 'Format gambar harus jpeg, png, jpg, atau webp.',
            'avatar.max'      => 'Ukuran maksimal 2MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = $r->user();
        $file = $r->file('avatar');
        /** @var UploadedFile $file */

        // simpan ke storage/app/public/avatars
        $path = $file->store('avatars', 'public');

        // kalau mau hapus avatar lama (opsional)
        if ($user->profile && $user->profile->avatar_url) {
            // jika avatar_url berupa storage path (bukan URL eksternal)
            $old = str_replace('/storage/', '', $user->profile->avatar_url);
            if (Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        // generate URL publik
        $url = Storage::disk('public')->url($path);

        // simpan ke profile
        $user->profile->update([
            'avatar_url' => $url,
        ]);

        return response()->json([
            'avatar_url' => $url,
            'message' => 'Avatar diperbarui.',
        ]);
    }
    public function deleteAvatar(Request $r)
    {
        $user = $r->user();

        if (!$user->profile) {
            return response()->json(['message' => 'Profile tidak ditemukan'], 404);
        }

        $url = $user->profile->avatar_url;

        // Kalau avatar disimpan di storage public (bukan URL eksternal)
        if ($url) {
            // Ubah /storage/avatars/xxx.jpg -> avatars/xxx.jpg
            $relative = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH) ?? '');

            if ($relative && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        }

        $user->profile->update(['avatar_url' => null]);

        return response()->json(['ok' => true, 'avatar_url' => null, 'message' => 'Avatar dihapus.']);
    }
}
