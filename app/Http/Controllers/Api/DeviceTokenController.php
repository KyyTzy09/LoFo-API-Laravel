<?php

// app/Http/Controllers/Api/DeviceTokenController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Token tidak valid', 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Simpan atau update token untuk user yang sedang login
        $request->user()->deviceTokens()->updateOrCreate(
            ['token' => $validated['token']], // Cek apakah token sudah ada
            ['token' => $validated['token']]  // Jika tidak ada, buat baru
        );

        return response()->json(['message' => 'Token berhasil disimpan']);
    }
}
