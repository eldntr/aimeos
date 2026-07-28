<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Class PasswordController
 *
 * Handles password controller operations for the application.
 */
class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $rules = [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        if ($request->wantsJson()) {
            $validated = $request->validate($rules);
        } else {
            $validated = $request->validateWithBag('updatePassword', $rules);
        }

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Password updated successfully']);
        }

        return back()->with('status', 'password-updated');
    }
}
