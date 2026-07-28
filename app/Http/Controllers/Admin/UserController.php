<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * Class UserController
 *
 * Handles user controller operations for the application.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(20);

        // Map users to include calculated role
        $items = collect($users->items())->map(function($user) {
            if ($user->superuser == 1) {
                $user->role = 'Admin';
            } elseif ($user->siteid && $user->siteid !== '1.') {
                $user->role = 'Merchant';
            } else {
                $user->role = 'Pelanggan';
            }
            return $user;
        });

        return response()->json([
            'data' => $items,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'total' => $users->total()
        ]);
    }

    /**
     * Update status.
     */
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
