<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http; // <-- Penting: Untuk nembak API Python AI
use Exception;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['user', 'user.profile', 'item'])->get();
        return response()->json([
            'message' => 'Semua pengumuman berhasil didapatkan',
            'data' => $announcements
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'lost_at' => 'required|date_format:d/m/Y H:i',
        ]);

        $lost_at = Carbon::createFromFormat('d/m/Y H:i', $request->lost_at);

        // Default status is 'PENDING'
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $validated['user_id'] = $user->userId;
        $validated['lost_at'] = $lost_at;
        $validated['status'] = 'PENDING';

        if ($request->item_id !== null) {
            $pendingAnnouncement = Announcement::where('item_id', $request->item_id)
                ->where('status', 'PENDING')
                ->first();

            if ($pendingAnnouncement) {
                return response()->json([
                    'message' => 'Terdapat pengumuman yang sedang berlangsung untuk item ini',
                    'data' => $pendingAnnouncement
                ], 409); // Conflict
            }
        }

        $announcement = Announcement::create($validated);
        if ($request->item_id !== null) {
            Item::where('itemId', $request->item_id)->update(['status' => 'HILANG']);
        }

        return response()->json([
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $announcement
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $announcement = Announcement::with(['user', 'user.profile', 'item'])->findOrFail($id);
        return response()->json([
            'message' => 'Pengumuman berhasil didapatkan',
            'data' => $announcement
        ]);
    }

    public function showPending()
    {
        $pendingAnnouncements = Announcement::where('status', 'PENDING')->with(['user', 'user.profile'])->get();

        return response()->json([
            'message' => 'Semua pengumuman yang sedang berlangsung berhasil didapatkan',
            'data' => $pendingAnnouncements
        ]);
    }

    public function showByUser(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::where('user_id', $user->userId)->with(['user', 'user.profile'])->get();

        return response()->json([
            'message' => 'Semua pengumuman milik pengguna berhasil didapatkan',
            'data' => $announcements
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Only updates the status (e.g. mark as CLOSED).
     */
    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $request->validate([
            'status' => 'required|in:PENDING,CLOSED'
        ]);

        $announcement->update([
            'status' => $request->input('status')
        ]);

        $itemId = $announcement->item_id;
        if ($itemId && $request->input('status') === 'CLOSED') {
            Item::where('itemId', $itemId)->update(['status' => 'TERSEDIA']);
        }

        return response()->json([
            'message' => 'Status pengumuman berhasil diperbarui',
            'data' => $announcement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json([
            'message' => 'Pengumuman berhasil dihapus'
        ], 200);
    }

    /**
     * ========================================================
     * JEMBATAN KE AI SERVICE (END-POINT UNTUK PROSES VOICE TEXT)
     * ========================================================
     */
    public function storeVoice(Request $request)
    {
       try {
            // 2. Tembak langsung ke URL Python port 8001
            $aiUrl = 'http://127.0.0.1:8001/api/announcements/create-voice';
            
            // KUNCI LANGSUNG TOKENNYA DI SINI
            $aiToken = 'LoFo-AI-API';

            // 3. Tembak (POST Request) ke AI Service Python port 8001
            $response = Http::withToken($aiToken)
                ->post($aiUrl, [
                    'text' => $request->text,
                    'connect_item' => false,
                    'items' => []
                ]);

            // 4. Jika Python & Gemini sukses mengekstrak data
            if ($response->successful()) {
                $aiData = $response->json()['data'];

                // 5. Otomatis simpan hasil olahan AI Gemini langsung ke database MySQL Laravel
                $announcement = Announcement::create([
                    'user_id'     => 1, // Di-hardcode sementara ke ID 1 agar bisa ditest tanpa login
                    'item_id'     => null,
                    'title'       => $aiData['name'] ?? 'Kehilangan dari Suara',
                    'description' => $aiData['description'] ?? $request->text,
                    'location'    => $aiData['location'] ?? 'Tidak diketahui',
                    // Jika AI mengembalikan lost_at gunakan itu, jika tidak pakai waktu sekarang
                    'lost_at'     => isset($aiData['lost_at']) ? Carbon::parse($aiData['lost_at']) : Carbon::now(),
                    'status'      => 'PENDING'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pengumuman suara berhasil diproses AI Python & disimpan ke MySQL!',
                    'data'    => $announcement
                ], 201);
            }

            // Jika Python merespon tapi mengembalikan error
            return response()->json([
                'success' => false,
                'message' => 'AI Service Python gagal memproses data.',
                'error'   => $response->body()
            ], $response->status());

        } catch (Exception $e) {
            // Jika ada error jaringan atau server down
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan teknis pada server Laravel.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}