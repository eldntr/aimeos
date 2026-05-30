<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    private function getSellerContext()
    {
        $context = app('aimeos.context')->get(false);
        $user = auth()->user();

        // Find seller site based on user ID (assuming seller site code or id is linked)
        // Here we assume the seller has an approved site and we use their site
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        
        // Let's assume site code is seller's ID or similar. 
        // For testing/simplicity, we can pass site code in query or use a default logic.
        $siteCode = request()->query('site', 'default');
        
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
        $context->setLocale($locale);

        return $context;
    }

    public function index(Request $request)
    {
        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'coupon');
        $codeManager = \Aimeos\MShop::create($context, 'coupon/code');

        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'coupon.status', 1));

        $coupons = $manager->search($filter);

        $data = [];
        foreach ($coupons as $coupon) {
            // Fetch codes for this coupon
            $cfilter = $codeManager->filter();
            $cfilter->add($cfilter->compare('==', 'coupon.code.parentid', $coupon->getId()));
            $codes = $codeManager->search($cfilter);
            
            $codeList = [];
            foreach ($codes as $cItem) {
                $codeList[] = $cItem->getCode();
            }

            $data[] = [
                'id' => $coupon->getId(),
                'code' => !empty($codeList) ? $codeList[0] : null,
                'name' => $coupon->getLabel(),
                'provider' => $coupon->getProvider(),
                'config' => $coupon->getConfig(),
                'status' => $coupon->getStatus(),
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:mshop_coupon_code,code',
            'name' => 'required|string',
            'type' => 'required|in:fixed,percent',
            'discount' => 'required|numeric|min:1',
            'max_amount' => 'nullable|numeric|min:1', // For percentage max limit
        ]);

        $context = $this->getSellerContext();
        $manager = \Aimeos\MShop::create($context, 'coupon');
        $codeManager = \Aimeos\MShop::create($context, 'coupon/code');
        
        $manager->begin();
        try {
            $coupon = $manager->create();
            $coupon->setLabel($request->name);
            $coupon->setStatus(1);

            $config = [];
            if ($request->type === 'fixed') {
                $coupon->setProvider('FixedRebate');
                $config['fixedrebate.rebate'] = $request->discount;
                $config['fixedrebate.productcode'] = 'rebate'; 
            } else {
                $coupon->setProvider('PercentRebate');
                $config['percentrebate.rebate'] = $request->discount;
                $config['percentrebate.productcode'] = 'rebate';
                if ($request->max_amount) {
                    $config['percentrebate.max_amount'] = $request->max_amount;
                }
            }
            
            $coupon->setConfig($config);
            $manager->save($coupon);

            // Create coupon code
            $codeItem = $codeManager->create();
            $codeItem->setParentId($coupon->getId());
            $codeItem->setCode($request->code);
            $codeManager->save($codeItem);

            $manager->commit();

            return response()->json([
                'message' => 'Voucher berhasil dibuat.',
                'data' => [
                    'id' => $coupon->getId(),
                    'code' => $codeItem->getCode(),
                    'name' => $coupon->getLabel(),
                    'provider' => $coupon->getProvider(),
                    'config' => $coupon->getConfig(),
                ]
            ], 201);
            
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
