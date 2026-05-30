<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SellerWithdrawal;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = SellerWithdrawal::query()->orderBy('created_at', 'desc');
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $withdrawals = $query->paginate(20);
        
        return response()->json([
            'message' => 'Daftar penarikan dana berhasil diambil.',
            'data' => $withdrawals
        ]);
    }

    public function approve($id)
    {
        $withdrawal = SellerWithdrawal::find($id);
        
        if (!$withdrawal) {
            return response()->json(['message' => 'Permintaan penarikan tidak ditemukan.'], 404);
        }
        
        if ($withdrawal->status === 'approved') {
            return response()->json(['message' => 'Permintaan penarikan sudah disetujui sebelumnya.'], 400);
        }
        
        // Execute escrow logically
        $withdrawal->status = 'approved';
        $withdrawal->save();
        
        return response()->json([
            'message' => 'Penarikan dana berhasil disetujui. Dana telah ditransfer (escrow executed).',
            'data' => $withdrawal
        ]);
    }
}
