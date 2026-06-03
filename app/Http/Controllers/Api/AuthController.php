<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    // Normalisasi nomor telepon untuk memastikan format konsisten (62xxx atau 0xxx)
    private function normalizePhoneNumber($phone)
    {
        if (!$phone) return $phone;
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    // REGISTER
    public function register(Request $request)
    {
        if ($request->has('phone_number')) {
            $request->merge([
                'phone_number' => $this->normalizePhoneNumber($request->phone_number)
            ]);
        }

        $random = rand(1, 1000);
        $avatar = "https://api.dicebear.com/9.x/avataaars/svg?seed=$random&backgroundColor=c084fc,9333ea";

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'phone_number' => 'required|digits_between:11,14|unique:users,phone_number',
            'password' => 'required|min:8',
            'address' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $user = User::create([
            'phone_number' => $request->phone_number, // Sudah pasti berformat 62
            'password' => Hash::make($request->password)
        ]);

        $user->profile()->create([
            'username' => $request->username,
            'address' => $request->address,
            'avatar' => $avatar
        ]);

        return response()->json(['message' => 'Register Berhasil', 'data' => $user], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        if ($request->has('phone_number')) {
            $request->merge([
                'phone_number' => $this->normalizePhoneNumber($request->phone_number)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits_between:11,14',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        // DB akan mencari string '628xxxxxxxx' yang sama persis
        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Nomor Telepon atau Password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'message' => 'Berhasil mendapatkan data sesi',
            'user' => $request->user()
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout Berhasil'], 200);
    }
}
