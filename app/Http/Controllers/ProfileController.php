<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('pages.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile.
     */
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $request->user()
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile avatar.
     */
    public function updateAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            $filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
            $folder = config('chatify.user_avatar.folder', 'users-avatar');
            
            // Store under storage/users-avatar
            $file->storeAs($folder, $filename, 'public');
            
            // Delete old avatar if not default
            if ($user->avatar && $user->avatar !== config('chatify.user_avatar.fallback', 'avatar.png')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($folder . '/' . $user->avatar);
            }
            
            $user->avatar = $filename;
            $user->save();
            
            $url = asset('storage/' . $folder . '/' . $filename);
            
            return response()->json([
                'message' => 'Foto profil berhasil diperbarui.',
                'url' => $url,
            ]);
        }

        return response()->json(['message' => 'Gagal mengunggah foto.'], 400);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $rules = [
            'password' => ['required', 'current-password'],
        ];

        if ($request->wantsJson()) {
            $request->validate($rules);
        } else {
            $request->validateWithBag('userDeletion', $rules);
        }

        $user = $request->user();

        if ($request->wantsJson()) {
            $user->tokens()->delete();
            $user->delete();
            return response()->json([
                'message' => 'Account deleted successfully'
            ]);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
