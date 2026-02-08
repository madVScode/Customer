<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function uploadAvatar(Request $request)
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Delete old avatar
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }

    $file = $request->file('avatar');
    $filename = $user->id . '_' . time() . '.' . $file->extension();

    // ✅ SIMPAN KE DISK PUBLIC
    $path = $file->storeAs('avatars', $filename, 'public');

    $user->avatar = $path;
    $user->save();

    return response()->json([
        'success' => true,
        'avatar_url' => asset('storage/' . $path),
    ]);
}


    public function deleteAvatar()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save(); // ✅ FIXED

        return response()->json([
            'success' => true,
            'avatar_url' => $user->avatar_url,
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([ // ✅ FIXED
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);
    }
}
