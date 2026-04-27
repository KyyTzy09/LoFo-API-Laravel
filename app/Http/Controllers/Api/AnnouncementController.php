<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        $validated['user_id'] = Auth::id() ?? request('user_id');

        $announcement = Announcement::create($validated);

        return response()->json([
            'message' => 'Announcement created successfully',
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
            'data' => $announcement
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
            'message' => 'Announcement status updated successfully',
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
            'message' => 'Announcement deleted successfully'
        ], 200);
    }
}
