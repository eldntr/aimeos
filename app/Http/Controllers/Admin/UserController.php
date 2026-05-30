<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Cannot deactivate yourself if you are admin? Let's just allow it for now or prevent it
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat mengubah status akun sendiri.'], 400);
        }

        $user->status = (int) $request->status;
        $user->save();

        $statusText = $user->status === 1 ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Akun berhasil $statusText.",
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status
            ]
        ]);
    }
}
