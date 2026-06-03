<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Aimeos\MShop;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'code' => 'required|string|max:255', // Usually code must be unique
            'status' => 'boolean',
            'commission_rate' => 'sometimes|numeric|min:0|max:100'
        ]);

        $context = app('aimeos.context')->get(false);
        MShop::cache(false);

        $manager = MShop::create($context, 'catalog');

        try {
            // Check if code already exists
            $search = $manager->filter();
            $search->add($search->compare('==', 'catalog.code', $request->code));
            if (count($manager->search($search)) > 0) {
                return response()->json(['message' => 'Kode kategori sudah digunakan.'], 400);
            }

            $manager->begin();

            $item = $manager->create();
            $item->setLabel($request->label);
            $item->setCode($request->code);
            $item->setStatus($request->input('status', 1));

            $manager->insert($item);
            $manager->commit();

            // Store commission rate using SystemSetting
            $commissionRate = $request->input('commission_rate', 5.0);
            \App\Models\SystemSetting::setVal('category_commission_' . $item->getId(), (float) $commissionRate);

            return response()->json([
                'message' => 'Kategori master berhasil ditambahkan.',
                'data' => [
                    'id' => $item->getId(),
                    'code' => $item->getCode(),
                    'label' => $item->getLabel(),
                    'status' => $item->getStatus(),
                    'commission_rate' => $commissionRate
                ]
            ], 201);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menambahkan kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean',
            'commission_rate' => 'sometimes|numeric|min:0|max:100'
        ]);

        $context = app('aimeos.context')->get(false);
        MShop::cache(false);

        $manager = MShop::create($context, 'catalog');

        try {
            $item = $manager->get($id);
            $manager->begin();

            if ($request->has('label')) {
                $item->setLabel($request->label);
            }
            if ($request->has('status')) {
                $item->setStatus($request->status);
            }

            $manager->save($item);
            $manager->commit();

            // Store commission rate if passed
            if ($request->has('commission_rate')) {
                \App\Models\SystemSetting::setVal('category_commission_' . $id, (float) $request->commission_rate);
            }

            $rate = (float) \App\Models\SystemSetting::getVal('category_commission_' . $id, 5.0);

            return response()->json([
                'message' => 'Kategori master berhasil diperbarui.',
                'data' => [
                    'id' => $item->getId(),
                    'code' => $item->getCode(),
                    'label' => $item->getLabel(),
                    'status' => $item->getStatus(),
                    'commission_rate' => $rate
                ]
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal memperbarui kategori.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $context = app('aimeos.context')->get(false);
        MShop::cache(false);

        $manager = MShop::create($context, 'catalog');

        try {
            $item = $manager->get($id);
            $manager->begin();
            $manager->delete($item);
            $manager->commit();

            return response()->json([
                'message' => 'Kategori master berhasil dihapus.'
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Kategori tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            $manager->rollback();
            return response()->json(['message' => 'Gagal menghapus kategori.', 'error' => $e->getMessage()], 500);
        }
    }
}
