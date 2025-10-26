<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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

        $user = $r->user();
        $file = $r->file('avatar');

        // nama unik
        $key = 'avatars/' . Str::uuid() . '.' . $file->extension();

        // upload ke R2 (disk 'r2')
        // catatan: untuk R2, gunakan put() + file_get_contents agar aman di banyak env
        Storage::disk('r2')->put($key, file_get_contents($file->getRealPath()), 'public');

        // hapus file lama kalau URL lama adalah URL R2
        if ($user->profile?->avatar_url) {
            $oldUrl = $user->profile->avatar_url;
            $prefix = rtrim(config('filesystems.disks.r2.url'), '/') . '/'; // R2_PUBLIC_URL/
            if (str_starts_with($oldUrl, $prefix)) {
                $oldKey = ltrim(Str::after($oldUrl, $prefix), '/');
                Storage::disk('r2')->delete($oldKey);
            }
        }

        // generate URL publik (langsung simpan ke avatar_url)
        $publicUrl = Storage::disk('r2')->url($key);

        $user->profile->update([
            'avatar_url' => $publicUrl,  // <-- hanya kolom ini yang dipakai
        ]);

        return response()->json([
            'avatar_url' => $publicUrl,
            'message'    => 'Avatar diperbarui.',
        ]);
    }
    public function deleteAvatar(Request $r)
    {
        $user = $r->user();
        $url  = $user->profile?->avatar_url;

        if ($url) {
            $prefix = rtrim(config('filesystems.disks.r2.url'), '/') . '/';
            if (str_starts_with($url, $prefix)) {
                $key = ltrim(Str::after($url, $prefix), '/');
                Storage::disk('r2')->delete($key);
            }
            $user->profile->update(['avatar_url' => null]);
        }

        return response()->json(['message' => 'Avatar dihapus.']);
    }
}
