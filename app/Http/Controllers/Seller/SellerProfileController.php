<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerProfileController extends Controller
{
    /**
     * Re-upload KTP for rejected sellers.
     */
    public function reuploadKtp(Request $request)
    {
        $request->validate([
            'ktp_image' => ['required', 'image', 'max:5120'], // Max 5MB
        ]);

        $user = $request->user();

        if ($user->seller_status === 'approved') {
            return response()->json(['message' => 'Your seller account is already approved.'], 400);
        }

        $fileService = new \App\Services\FileServerService();
        $ktpUrl = $fileService->uploadFile($request->file('ktp_image'));
        $fileService->triggerCompression();

        $user->ktp_url = $ktpUrl;
        $user->seller_status = 'pending';
        $user->rejection_reason = null;
        $user->save();

        return response()->json(['message' => 'KTP re-uploaded successfully. Your status is now pending review.']);
    }
}
