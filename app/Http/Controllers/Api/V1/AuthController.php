<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate(
            [
                'name'     => 'required|string|max:120',
                'email'    => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'username' => 'required|alpha_dash|unique:profiles,username',
            ],
            [   // custom messages
                'name.required'      => 'Nama wajib diisi.',
                'email.required'     => 'Email wajib diisi.',
                'email.email'        => 'Format email tidak valid.',
                'email.unique'       => 'Email sudah terdaftar.',
                'password.required'  => 'Password wajib diisi.',
                'password.min'       => 'Password minimal 8 karakter.',
                'username.required'  => 'Username wajib diisi.',
                'username.alpha_dash'=> 'Username hanya boleh huruf, angka, dash, dan underscore.',
                'username.unique'    => 'Username sudah dipakai.',
            ]
        );

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Profile::create([
            'user_id'      => $user->id,
            'username'     => strtolower($data['username']),
            'display_name' => $data['name'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        // kalau mau sekalian kirim profil
        $user->load('profile');

        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function login(Request $r)
    {
        $data = $r->validate(
            [
                'email'    => 'required|email',
                'password' => 'required|string|min:8',
            ],
            [
                'email.required'     => 'Email wajib diisi.',
                'email.email'        => 'Format email tidak valid.',
                'password.required'  => 'Password wajib diisi.',
                'password.min'       => 'Password minimal 8 karakter.',
            ]
        );

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'code'    => 'INVALID_CREDENTIALS',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;
        $user->load('profile');

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function me(Request $r)
    {
        return $r->user()->load('profile');
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return response()->json(['ok' => true]);
    }
}
