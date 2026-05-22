<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List all active products. Optionally filter by seller site code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $context = app('aimeos.context')->get(false);

        $siteCode = $request->query('site', 'default');
        $search   = $request->query('search', '');
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = 20;

        try {
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $site = $siteManager->find($siteCode);
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
            $context->setLocale($locale);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Site not found.'], 404);
        }

        $manager = \Aimeos\MShop::create($context, 'product');
        $filter  = $manager->filter(true); // status=1 (active only)

        if ($search) {
            $filter->add($filter->compare('~=', 'product.label', $search));
        }

        $filter->slice(($page - 1) * $perPage, $perPage);
        $products = $manager->search($filter);

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id'     => $product->getId(),
                'code'   => $product->getCode(),
                'label'  => $product->getLabel(),
                'type'   => $product->getType(),
                'status' => $product->getStatus(),
                'ctime'  => $product->getTimeCreated(),
                'mtime'  => $product->getTimeModified(),
            ];
        }

        return response()->json([
            'data'  => $data,
            'meta'  => [
                'site'    => $siteCode,
                'page'    => $page,
                'perPage' => $perPage,
                'count'   => count($data),
            ],
        ]);
    }

    /**
     * Show detail of a single product by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $context  = app('aimeos.context')->get(false);
        $siteCode = $request->query('site', 'default');

        try {
            $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
            $context->setLocale($locale);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Site not found.'], 404);
        }

        try {
            $manager = \Aimeos\MShop::create($context, 'product');
            $product = $manager->get($id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json([
            'data' => [
                'id'     => $product->getId(),
                'code'   => $product->getCode(),
                'label'  => $product->getLabel(),
                'type'   => $product->getType(),
                'status' => $product->getStatus(),
                'ctime'  => $product->getTimeCreated(),
                'mtime'  => $product->getTimeModified(),
            ],
        ]);
    }
}
