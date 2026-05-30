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
    protected function getNumericSiteId($siteid)
    {
        $parts = array_filter(explode('.', trim($siteid, '.')));
        return end($parts);
    }

    protected function getSellerContext()
    {
        $context = app('aimeos.context')->get(false);
        $user = auth()->user();
        if ($user && $user->siteid) {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $numericId = $this->getNumericSiteId($user->siteid);
            $siteItem = $siteManager->get($numericId);
            $context->setLocale(app('aimeos.locale')->get($context, $siteItem->getCode()));
        }
        return $context;
    }

    public function getShop(Request $request)
    {
        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }
        
        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        $site = $siteManager->get($numericId);
        
        return response()->json([
            'data' => [
                'name' => $site->getLabel(),
                'logo' => $site->getLogo(),
                'config' => $site->getConfig(),
            ]
        ]);
    }

    public function updateShop(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:5120',
            'address' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }

        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        
        $siteManager->begin();
        try {
            $site = $siteManager->get($numericId);
            
            if ($request->has('name')) {
                $site->setLabel($request->name);
            }
            
            if ($request->hasFile('logo')) {
                $fileService = new \App\Services\FileServerService();
                $logoUrl = $fileService->uploadFile($request->file('logo'));
                $fileService->triggerCompression();
                $site->setLogo($logoUrl);
            }

            if ($request->has('address')) {
                $config = $site->getConfig();
                $config['address'] = $request->address;
                $site->setConfig($config);
            }
            
            $siteManager->save($site);
            $siteManager->commit();

            return response()->json([
                'message' => 'Profil toko berhasil diperbarui.',
                'data' => [
                    'name' => $site->getLabel(),
                    'logo' => $site->getLogo(),
                    'config' => $site->getConfig(),
                ]
            ]);
        } catch (\Exception $e) {
            $siteManager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        if (!$user->siteid) {
            return response()->json(['message' => 'Seller shop not initialized.'], 404);
        }

        $context = app('aimeos.context')->get(false);
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $numericId = $this->getNumericSiteId($user->siteid);
        
        $siteManager->begin();
        try {
            $site = $siteManager->get($numericId);
            
            $config = $site->getConfig();
            $config['bank.name'] = $request->bank_name;
            $config['bank.account_number'] = $request->account_number;
            $config['bank.account_name'] = $request->account_name;
            
            $site->setConfig($config);
            $siteManager->save($site);
            $siteManager->commit();

            return response()->json([
                'message' => 'Informasi bank berhasil diperbarui.',
                'data' => [
                    'config' => $site->getConfig(),
                ]
            ]);
        } catch (\Exception $e) {
            $siteManager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
