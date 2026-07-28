<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Admin\SellerVerificationController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Class AdminController
 *
 * Handles admin controller operations for the application.
 */
class AdminController extends Controller
{
    /**
     * Merchants index.
     */
    public function merchantsIndex(Request $request)
    {
        $response = app(SellerVerificationController::class)->index();
        $merchants = $response->getData(true)['data'] ?? [];

        return view('pages.admin.merchants.index', [
            'merchants' => collect($merchants)->map(function ($merchant) {
                return [
                    'id' => $merchant['id'] ?? null,
                    'store_name' => $merchant['name'] ?? 'Toko Merchant',
                    'user' => (object) ['name' => $merchant['name'] ?? 'Merchant'],
                    'created_at' => $merchant['created_at'] ?? now(),
                ];
            }),
        ]);
    }

    /**
     * Merchant show.
     */
    public function merchantShow(Request $request, $merchant)
    {
        $user = User::findOrFail($merchant);

        return view('pages.admin.merchants.show', [
            'merchant' => (object) [
                'id' => $user->id,
                'store_name' => $user->name,
                'address' => $user->address1 ?? '-',
                'bank_name' => optional($user->bankDetail)->bank_name ?? '-',
                'bank_account_number' => optional($user->bankDetail)->bank_account_number ?? '-',
                'ktp_path' => $user->ktp_url ?? null,
                'user' => (object) ['name' => $user->name, 'email' => $user->email],
            ],
        ]);
    }

    /**
     * Approve.
     */
    public function approve(Request $request, $merchant)
    {
        app(SellerVerificationController::class)->approve($request, $merchant);
        return back()->with('success', 'Merchant approved successfully.');
    }

    /**
     * Reject.
     */
    public function reject(Request $request, $merchant)
    {
        app(SellerVerificationController::class)->reject($request, $merchant);
        return back()->with('success', 'Merchant rejected successfully.');
    }
}
