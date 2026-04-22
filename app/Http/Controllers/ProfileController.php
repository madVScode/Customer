<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Cast Auth::user() kepada User model secara eksplisit.
     * Auth::user() return Authenticatable interface — Intelephense
     * tidak kenal save(), update(), dll. tanpa cast ini.
     */
    private function getUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Upload avatar — simpan ke storage/app/public/avatars/
     * Return URL penuh supaya JS boleh update img src terus
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $user = $this->getUser();

        // Padam avatar lama kalau ada
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Simpan avatar baru — PATH sahaja dalam DB
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar = $path;
        $user->save();

        return response()->json([
            'success'    => true,
            'avatar_url' => asset('storage/' . $path),
            'message'    => 'Avatar updated successfully',
        ]);
    }

    /**
     * Delete avatar
     */
    public function deleteAvatar(Request $request)
    {
        $user = $this->getUser();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar deleted',
        ]);
    }

    /**
     * Get current profile
     */
    public function getProfile(Request $request)
    {
        $user = $this->getUser();

        return response()->json([
            'success' => true,
            'data'    => [
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Update profile info (nama, email)
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getUser();

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
        ]);
    }
}