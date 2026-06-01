<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends Controller
{
    private function getContext()
    {
        $context = app('aimeos.context')->get(false);
        $localeManager = \Aimeos\MShop::create($context, 'locale');
        $localeItem = $localeManager->bootstrap('default', '', '', false);
        
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $siteItem = $siteManager->create();
        $siteItem->setId('1.');
        $siteItem->setCode('default');
        
        $ref = new \ReflectionClass($localeItem);
        if ($ref->hasProperty('siteItem')) {
            $prop = $ref->getProperty('siteItem');
            $prop->setAccessible(true);
            $prop->setValue($localeItem, $siteItem);
        }
        
        $context->setLocale($localeItem);
        return $context;
    }

    private function getAddressManager()
    {
        return \Aimeos\MShop::create($this->getContext(), 'customer/address');
    }

    public function index(Request $request)
    {
        $manager = $this->getAddressManager();
        $user = $request->user();

        $filter = $manager->filter();
        $filter->add($filter->compare('==', 'customer.address.parentid', $user->id));
        
        $items = $manager->search($filter);
        
        $addresses = [];
        foreach ($items as $item) {
            $addresses[] = $item->toArray();
        }

        return response()->json([
            'data' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:32',
            'address1' => 'required|string|max:255', // Street address
            'address2' => 'nullable|string|max:255', // Apartment, suite, etc.
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255', // Province / State
            'postal' => 'required|string|max:32',
        ]);

        $manager = $this->getAddressManager();
        $item = $manager->create();
        
        $item->setParentId($request->user()->id);
        $item->setFirstname($request->firstname);
        $item->setLastname($request->lastname ?: '');
        $item->setTelephone($request->telephone);
        $item->setAddress1($request->address1);
        $item->setAddress2($request->address2 ?: '');
        $item->setCity($request->city);
        $item->setState($request->state ?: '');
        $item->setPostal($request->postal);
        
        $manager->save($item);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $item->toArray()
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:32',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal' => 'required|string|max:32',
        ]);

        $manager = $this->getAddressManager();
        
        try {
            $item = $manager->get($id);
            
            // Security check
            if ($item->getParentId() != $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $item->setFirstname($request->firstname);
            $item->setLastname($request->lastname ?: '');
            $item->setTelephone($request->telephone);
            $item->setAddress1($request->address1);
            $item->setAddress2($request->address2 ?: '');
            $item->setCity($request->city);
            $item->setState($request->state ?: '');
            $item->setPostal($request->postal);
            
            $manager->save($item);

            return response()->json([
                'message' => 'Alamat berhasil diperbarui.',
                'data' => $item->toArray()
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }
    }

    public function destroy(Request $request, $id)
    {
        $manager = $this->getAddressManager();
        
        try {
            $item = $manager->get($id);
            
            // Security check
            if ($item->getParentId() != $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $manager->delete($id);

            return response()->json([
                'message' => 'Alamat berhasil dihapus.'
            ]);
        } catch (\Aimeos\MShop\Exception $e) {
            return response()->json(['message' => 'Alamat tidak ditemukan.'], 404);
        }
    }
}
