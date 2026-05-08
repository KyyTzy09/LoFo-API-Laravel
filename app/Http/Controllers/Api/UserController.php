<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Item;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function Profile(Request $request)
    {
        $user = $request->user();
        $existingUser = User::where("userId", $user->userId)
            ->with('profile')
            ->first();
        if (!$existingUser) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User Profile',
            'user' => $existingUser,
        ]);
    }

    public function Items(Request $request)
    {
        $user = $request->user();
        $items = Item::where('user_id', $user->userId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'User Items retrieved successfully',
            'data' => $items,
        ]);
    }

    public function UpdateProfile(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'info' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->errors()
            ]);
        }

        $validated = $validator->validated();

        $existingProfile = Profile::where("user_id", $user->userId)->first();
        if (!$existingProfile) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found',
            ]);
        }

        $existingProfile->update($validated);
        return response()->json([
            'status' => 200,
            'message' => 'User Profile updated successfully',
            'user' => $existingProfile,
        ]);
    }

    public function Announcement(Request $request)
    {
        $user = $request->user();
        $announcements = Announcement::where('user_id', $user->userId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'User Announcement retrieved successfully',
            'data' => $announcements,
        ]);
    }
}
