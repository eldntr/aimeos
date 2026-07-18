<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SellerApproved;
use App\Notifications\SellerRejected;
use App\Notifications\SellerSuspended;
use App\Notifications\SellerReactivated;
use Illuminate\Http\Request;

class SellerVerificationController extends Controller
{
    /**
     * Get a list of all pending sellers
     */
    public function index()
    {
        $sellers = User::whereNotNull('siteid')
            ->where('siteid', '!=', '1.') // Exclude default site
            ->where('seller_status', 'pending')
            ->get();

        return response()->json(['data' => $sellers]);
    }

    /**
     * Approve a seller
     */
    public function approve(Request $request, $id)
    {
        $seller = User::findOrFail($id);

        if ($seller->seller_status !== 'pending') {
            return response()->json(['message' => 'Seller is not in pending status'], 400);
        }

        $seller->seller_status = 'approved';
        $seller->rejection_reason = null;
        $seller->save();

        $seller->notify(new SellerApproved());

        return response()->json(['message' => 'Seller approved successfully']);
    }

    /**
     * Reject a seller
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $seller = User::findOrFail($id);

        if ($seller->seller_status !== 'pending') {
            return response()->json(['message' => 'Seller is not in pending status'], 400);
        }

        $seller->seller_status = 'rejected';
        $seller->rejection_reason = strip_tags($request->reason);
        $seller->save();

        $seller->notify(new SellerRejected($seller->rejection_reason));

        return response()->json(['message' => 'Seller rejected successfully']);
    }

    /**
     * Get a list of all active/approved sellers
     */
    public function activeSellers()
    {
        $sellers = User::whereNotNull('siteid')
            ->where('siteid', '!=', '1.')
            ->where('seller_status', 'approved')
            ->get();

        return response()->json(['data' => $sellers]);
    }

    /**
     * Toggle active/blocked status of a seller
     */
    public function toggleSellerStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        $seller = User::findOrFail($id);
        $seller->status = (int) $request->status;
        $seller->save();

        if ($seller->status === 1) {
            $seller->notify(new SellerReactivated());
        } else {
            $seller->notify(new SellerSuspended());
        }

        $statusText = $seller->status === 1 ? 'diaktifkan' : 'ditangguhkan (suspend)';

        return response()->json([
            'message' => "Status merchant berhasil diubah menjadi $statusText.",
            'data' => $seller
        ]);
    }
}
