<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'item_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'lost_at' => 'required|date',
        ]);

        // Default status is 'PENDING'
        $validated['status'] = 'PENDING';

        // Use authenticated user or fallback to request input
        // Assumes typical auth flow, adjust if user_id is sent differently
        $validated['user_id'] = $user->userId;

        $pendingAnnouncement = Announcement::where('item_id', $request->item_id)
            ->where('status', 'PENDING')
            ->first();
        if ($pendingAnnouncement) {
            return response()->json([
                'message' => 'Terdapat pengumuman yang sedang berlangsung untuk item ini',
                'data' => $pendingAnnouncement
            ], 409); // Conflict
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
        // Load user and we assume user relation has 'profile' relationship defined
        $announcement = Announcement::with(['user', 'user.profile'])->findOrFail($id);

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
}
