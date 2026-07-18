<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks for clean truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clean tables dynamically to remove Aimeos defaults and previous seed data
        $tables = [
            'mshop_coupon',
            'mshop_coupon_code',
            'mshop_product_list',
            'mshop_product_property',
            'mshop_product_tag',
            'mshop_product',
            'mshop_price',
            'mshop_text',
            'mshop_media',
            'mshop_catalog_list',
            'mshop_catalog',
            'mshop_review',
            'mshop_customer',
            'mshop_customer_list',
            'mshop_supplier_list',
            'mshop_supplier_address',
            'mshop_supplier',
            'mshop_attribute_list',
            'mshop_attribute',
            'mshop_stock',
            'mshop_order',
            'mshop_order_product',
            'mshop_order_address',
            'mshop_order_service',
            'mshop_order_coupon',
            'mshop_order_status',
            'ch_messages',
            'ch_favorites',
            'seller_bank_details',
            'seller_withdrawals',
            'wallet_ledgers',
            'notifications',
            'user_reports'
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('mshop_locale_site')) {
            DB::table('mshop_locale_site')->where('code', '!=', 'default')->delete();
            // Update root site hierarchy nright value back to 2
            DB::table('mshop_locale_site')->where('id', 1)->update(['nright' => 2]);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('mshop_locale')) {
            DB::table('mshop_locale')->where('siteid', '!=', '1.')->delete();
        }

        DB::table('users')->where('email', '!=', 'admin@reborns.id')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Sync existing admin user to mshop_customer if not present
        $adminUser = DB::table('users')->where('email', 'admin@reborns.id')->first();
        if ($adminUser) {
            DB::table('users')->where('id', $adminUser->id)->update(['email_verified_at' => now()]);
            DB::table('mshop_customer')->insert([
                'id' => $adminUser->id,
                'siteid' => $adminUser->siteid,
                'code' => $adminUser->email,
                'label' => $adminUser->name,
                'email' => $adminUser->email,
                'password' => $adminUser->password,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);
        }

        // Closure helper to create users in both Laravel users and Aimeos mshop_customer tables
        $createUser = function($name, $email, $siteId, $sellerStatus = 'pending') {
            $userId = DB::table('users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'siteid' => $siteId,
                'seller_status' => $sellerStatus,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('mshop_customer')->insert([
                'id' => $userId,
                'siteid' => $siteId,
                'code' => $email,
                'label' => $name,
                'email' => $email,
                'password' => Hash::make('password123'),
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            return $userId;
        };

        // 1. Seed Master Categories (mshop_catalog)
        // Root Category (necessary for Aimeos nested category tree navigation)
        DB::table('mshop_catalog')->insert([
            'id' => 1,
            'siteid' => '1.',
            'parentid' => 0,
            'level' => 0,
            'code' => 'home',
            'label' => 'Home',
            'nleft' => 1,
            'nright' => 14,
            'status' => 1,
            'ctime' => now(),
            'mtime' => now(),
            'editor' => 'seeder'
        ]);

        $categories = [
            ['id' => 2, 'code' => 'fashion-wanita', 'label' => 'Fashion Wanita'],
            ['id' => 3, 'code' => 'fashion-pria', 'label' => 'Fashion Pria'],
            ['id' => 4, 'code' => 'sepatu-sneakers', 'label' => 'Sepatu & Sneakers'],
            ['id' => 5, 'code' => 'tas-aksesoris', 'label' => 'Tas & Aksesoris'],
            ['id' => 6, 'code' => 'hobi-koleksi', 'label' => 'Hobi & Koleksi'],
            ['id' => 7, 'code' => 'elektronik-gadget', 'label' => 'Elektronik & Gadget'],
        ];

        foreach ($categories as $index => $cat) {
            DB::table('mshop_catalog')->insert([
                'id' => $cat['id'],
                'siteid' => '1.',
                'parentid' => 1,
                'level' => 1,
                'code' => $cat['code'],
                'label' => $cat['label'],
                'nleft' => ($index + 1) * 2,
                'nright' => (($index + 1) * 2) + 1,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);
        }

        // 2. Seed Sellers & Shops (mshop_locale_site & users)
        $sellersData = [
            [
                'name' => 'Sarah Amanda',
                'email' => 'sarah@reborns.id',
                'shop_name' => 'Chic Preloved Boutique',
                'shop_code' => 'chic_preloved',
                'site_id' => '2.',
                'bank' => 'BCA',
                'account' => '8293749201'
            ],
            [
                'name' => 'Bambang Utomo',
                'email' => 'bambang@reborns.id',
                'shop_name' => 'Retro Classic Man',
                'shop_code' => 'retro_classic',
                'site_id' => '3.',
                'bank' => 'Mandiri',
                'account' => '1370009876543'
            ],
            [
                'name' => 'Kevin Sanjaya',
                'email' => 'kevin@reborns.id',
                'shop_name' => 'KickStore Sneakers',
                'shop_code' => 'kickstore',
                'site_id' => '4.',
                'bank' => 'BNI',
                'account' => '0987654321'
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina@reborns.id',
                'shop_name' => 'Hype Accessories',
                'shop_code' => 'hype_accessories',
                'site_id' => '5.',
                'bank' => 'BCA',
                'account' => '7392018473'
            ],
            [
                'name' => 'Zaky Fahri',
                'email' => 'zaky@reborns.id',
                'shop_name' => 'Zilch Second Tech',
                'shop_code' => 'zilch_tech',
                'site_id' => '6.',
                'bank' => 'BSI',
                'account' => '9002817293'
            ]
        ];

        $sellerUsers = [];

        foreach ($sellersData as $index => $seller) {
            $siteNumId = intval($seller['site_id']);
            $nleft = ($index + 1) * 2;
            $nright = (($index + 1) * 2) + 1;

            // Create Aimeos Merchant Site
            DB::table('mshop_locale_site')->insert([
                'id' => $seller['site_id'],
                'parentid' => 1,
                'siteid' => $seller['site_id'],
                'code' => $seller['shop_code'],
                'label' => $seller['shop_name'],
                'status' => 1,
                'theme' => 'default',
                'config' => '{}',
                'level' => 0,
                'nleft' => $nleft,
                'nright' => $nright,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Create default Locale for this site
            DB::table('mshop_locale')->insert([
                'site_id' => $siteNumId,
                'siteid' => $seller['site_id'],
                'langid' => 'id',
                'currencyid' => 'IDR',
                'pos' => 0,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Update root site hierarchy nright value
            DB::table('mshop_locale_site')->where('id', 1)->update(['nright' => $nright + 1]);

            // Create User account using helper
            $userId = $createUser($seller['name'], $seller['email'], $seller['site_id'], 'approved');

            // Seed Aimeos Customer Group relation
            $group = DB::table('mshop_group')->where('siteid', $seller['site_id'])->where('code', 'admin')->first();
            if (!$group) {
                $groupId = DB::table('mshop_group')->insertGetId([
                    'siteid' => $seller['site_id'],
                    'code' => 'admin',
                    'label' => 'Administrator',
                    'mtime' => now(),
                    'ctime' => now(),
                    'editor' => 'seeder'
                ]);
            } else {
                $groupId = $group->id;
            }

            DB::table('mshop_customer_list')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $userId,
                'key' => '',
                'type' => 'default',
                'domain' => 'group',
                'refid' => $groupId,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            $sellerUsers[$seller['shop_code']] = [
                'user_id' => $userId,
                'site_id' => $seller['site_id'],
                'shop_name' => $seller['shop_name'],
                'shop_code' => $seller['shop_code']
            ];

            // Create Bank Detail
            DB::table('seller_bank_details')->insert([
                'user_id' => $userId,
                'bank_name' => $seller['bank'],
                'bank_account_number' => $seller['account'],
                'bank_account_name' => $seller['name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. Seed Buyers (10 dummy accounts)
        $buyersData = [
            ['name' => 'Budi Santoso', 'email' => 'budi@email.com'],
            ['name' => 'Siti Rahma', 'email' => 'siti@email.com'],
            ['name' => 'Andi Wijaya', 'email' => 'andi@email.com'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@email.com'],
            ['name' => 'Rian Hidayat', 'email' => 'rian@email.com'],
            ['name' => 'Fahmi Yusuf', 'email' => 'fahmi@email.com'],
            ['name' => 'Indah Permata', 'email' => 'indah@email.com'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra@email.com'],
            ['name' => 'Citra Lestari', 'email' => 'citra@email.com'],
            ['name' => 'Ahmad Rofiq', 'email' => 'rofiq@email.com']
        ];

        $buyerUsers = [];
        foreach ($buyersData as $buyer) {
            $buyerId = $createUser($buyer['name'], $buyer['email'], '1.', 'pending');
            $buyerUsers[] = [
                'id' => $buyerId,
                'name' => $buyer['name'],
                'email' => $buyer['email']
            ];
        }

        // 4. Seed Products (24 unique items, 4 per category)
        $productsData = [
            // Category 1: Fashion Wanita
            [
                'cat_id' => 1,
                'seller_code' => 'chic_preloved',
                'name' => 'Blazer Kulit Premium Zara',
                'code' => 'blazer-kulit-zara',
                'price' => 185000,
                'original' => 450000,
                'image' => 'https://images.unsplash.com/photo-1548624149-f9b1859aa7d0?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Blazer kulit Zara preloved, kondisi 95% sangat mulus. Bahan kulit sintetis tebal, tidak ada lecet atau robek. Jahitan rapi, size M. Cocok untuk acara formal maupun casual modern.',
                'location' => 'Kota Jakarta Selatan',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 1,
                'seller_code' => 'chic_preloved',
                'name' => 'Dress Vintage Floral H&M',
                'code' => 'dress-vintage-hm',
                'price' => 95000,
                'original' => 299000,
                'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Dress motif bunga vintage merek H&M, bahan katun adem. Kondisi seperti baru (like new), jarang dipakai. Size S fit to M. Sempurna untuk OOTD piknik sore.',
                'location' => 'Kota Bandung',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => false,
                    'brand_tier' => 'normal',
                    'eco_friendly' => true,
                    'is_clearance' => true,
                    'supports_cod' => false
                ]
            ],
            [
                'cat_id' => 1,
                'seller_code' => 'chic_preloved',
                'name' => 'Rok Plisket Velvet Korean Style',
                'code' => 'rok-plisket-velvet',
                'price' => 65000,
                'original' => 180000,
                'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Rok plisket bahan velvet tebal berkilau mewah. Model Korean style pinggang karet sangat nyaman dipakai. Kondisi 98% terawat baik, tidak ada benang ketarik.',
                'location' => 'Kota Bogor',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'normal',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 1,
                'seller_code' => 'hype_accessories',
                'name' => 'Cardigan Rajut Oversized Mustard',
                'code' => 'cardigan-rajut-mustard',
                'price' => 110000,
                'original' => 250000,
                'image' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Outerwear rajut tebal nan lembut warna kuning mustard. Model oversized kekinian fit to XL. Hangat dipakai saat musim hujan, preloved mulus no defect.',
                'location' => 'Kota Depok',
                'rating' => 4.6,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'normal',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],

            // Category 2: Fashion Pria
            [
                'cat_id' => 2,
                'seller_code' => 'retro_classic',
                'name' => 'Jaket Denim Levi\'s Vintage Sherpa',
                'code' => 'jaket-levis-sherpa',
                'price' => 450000,
                'original' => 1200000,
                'image' => 'https://images.unsplash.com/photo-1611312449412-6cefac5dc3e4?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Jaket jip denim Levi\'s Sherpa Vintage asli impor USA. Bagian dalam bulu domba hangat, denim tebal berkualitas tinggi. Size L, kondisi pudar alami yang keren sekali.',
                'location' => 'Kab. Sleman',
                'rating' => 5.0,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 2,
                'seller_code' => 'retro_classic',
                'name' => 'Kemeja Flanel Uniqlo Lengan Panjang',
                'code' => 'flanel-uniqlo-pria',
                'price' => 125000,
                'original' => 399000,
                'image' => 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Kemeja flanel tebal andalan Uniqlo, motif merah-hitam. Kondisi masih sangat pekat, kancing lengkap no minus. Size M, siap pakai santai sehari-hari.',
                'location' => 'Kota Malang',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => false,
                    'brand_tier' => 'normal',
                    'eco_friendly' => true,
                    'is_clearance' => true,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 2,
                'seller_code' => 'retro_classic',
                'name' => 'Hoodie Champion Original Navy Blue',
                'code' => 'hoodie-champion-navy',
                'price' => 280000,
                'original' => 799000,
                'image' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Hoodie Champion berlogo kecil di dada warna biru navy pekat. Bahan cotton fleece lembut hangat. Kondisi 90% terawat wajar, karet tangan kencang. Size L.',
                'location' => 'Kota Yogyakarta',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ],
            [
                'cat_id' => 2,
                'seller_code' => 'kickstore',
                'name' => 'Celana Chino Dickies Slim Fit',
                'code' => 'chino-dickies-original',
                'price' => 220000,
                'original' => 600000,
                'image' => 'https://images.unsplash.com/photo-1479064555552-3ef4979f8908?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Celana panjang kerja santai Chino Dickies Slim Fit warna khaki. Kain katun drill khas Dickies yang kokoh namun bersahabat di kulit. Size 32, kondisi 93% pekat.',
                'location' => 'Kota Tangerang',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],

            // Category 3: Sepatu & Sneakers
            [
                'cat_id' => 3,
                'seller_code' => 'kickstore',
                'name' => 'Converse Chuck Taylor 70s Black White',
                'code' => 'converse-70s-original',
                'price' => 390000,
                'original' => 999000,
                'image' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sepatu sneakers Converse Chuck 70 original, size 42. Kondisi sol bawah masih tebal, minus pemakaian wajar kotor sedikit. Box asli masih lengkap.',
                'location' => 'Kota Jakarta Barat',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 3,
                'seller_code' => 'kickstore',
                'name' => 'Adidas Stan Smith Primegreen',
                'code' => 'adidas-stan-smith',
                'price' => 480000,
                'original' => 1500000,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sepatu Adidas Stan Smith edisi ramah lingkungan Primegreen. Kulit vegan berserat premium, size 41. Kondisi 92% bersih terawat, mulus.',
                'location' => 'Kota Tangerang',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ],
            [
                'cat_id' => 3,
                'seller_code' => 'kickstore',
                'name' => 'Nike Air Force 1 Triple White',
                'code' => 'nike-af1-white',
                'price' => 650000,
                'original' => 1650000,
                'image' => 'https://images.unsplash.com/photo-1597045566677-8cf032ed6634?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sneakers Nike Air Force 1 Low model serba putih kulit legendaris. Size 43, kondisi sol luar sedikit yellowing retro, bagian kulit atas mulus terawat no crack.',
                'location' => 'Kota Surabaya',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 3,
                'seller_code' => 'retro_classic',
                'name' => 'Vans Old Skool Classic Black White',
                'code' => 'vans-old-skool',
                'price' => 320000,
                'original' => 899000,
                'image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sneakers Vans Old Skool legendaris, bahan suede & canvas hitam garis putih. Kondisi wafel sol masih sangat bagus dan kesat, size 40.5. No box.',
                'location' => 'Kab. Sleman',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => false,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => true,
                    'supports_cod' => true
                ]
            ],

            // Category 4: Tas & Aksesoris
            [
                'cat_id' => 4,
                'seller_code' => 'hype_accessories',
                'name' => 'Tas Tangan Charles & Keith Original',
                'code' => 'tas-charles-keith',
                'price' => 320000,
                'original' => 950000,
                'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Handbag Charles & Keith warna hitam berkelas. Kondisi 90% terawat, minus pemakaian wajar di bagian handle besi. Lengkap dengan dustbag original.',
                'location' => 'Kota Surabaya',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 4,
                'seller_code' => 'hype_accessories',
                'name' => 'Kacamata Hitam Rayban Wayfarer Classic',
                'code' => 'rayban-wayfarer-classic',
                'price' => 850000,
                'original' => 2400000,
                'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sunglasses Rayban Wayfarer original seri RB2140. Lensa polarized hitam pekat anti silau, frame solid mulus. Ada grafir tipis RB di kaca sebelah kiri.',
                'location' => 'Kota Jakarta Pusat',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 4,
                'seller_code' => 'zilch_tech',
                'name' => 'Jam Tangan Casio G-Shock DW-5600',
                'code' => 'gshock-dw5600-original',
                'price' => 550000,
                'original' => 1200000,
                'image' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Jam digital G-Shock legendaris DW-5600 warna hitam doff klasik. Tahan air 200m, fungsi alarm beep normal, lampu backlight hijau aktif. Preloved terawat wajar.',
                'location' => 'Kota Depok',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 4,
                'seller_code' => 'hype_accessories',
                'name' => 'Backpack Fjallraven Kanken Classic',
                'code' => 'kanken-classic-black',
                'price' => 450000,
                'original' => 1300000,
                'image' => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Tas punggung Kanken Classic warna hitam vinylon kokoh. Kondisi 91% mulus, pemakaian normal ke kampus, warna sedikit pudar vintage khas Kanken. Original 100%.',
                'location' => 'Kota Bandung',
                'rating' => 4.6,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => false,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ],

            // Category 5: Hobi & Koleksi
            [
                'cat_id' => 5,
                'seller_code' => 'retro_classic',
                'name' => 'Kamera Film Analog Canon AE-1 Vintage',
                'code' => 'canon-ae1-analog',
                'price' => 1650000,
                'original' => 3500000,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Kamera analog 35mm legendaris Canon AE-1. Kondisi fisik 90% tergolong mulus untuk barang antik, fungsi normal (lightmeter hidup, shutter empuk). Terpasang lensa FD 50mm f/1.8.',
                'location' => 'Kota Yogyakarta',
                'rating' => 5.0,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ],
            [
                'cat_id' => 5,
                'seller_code' => 'retro_classic',
                'name' => 'Vinyl LP The Beatles - Abbey Road',
                'code' => 'vinyl-beatles-abbey',
                'price' => 420000,
                'original' => 800000,
                'image' => 'https://images.unsplash.com/photo-1539628399213-d6aa89c93074?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Piringan hitam (Vinyl LP) album legendaris The Beatles - Abbey Road rilis ulang tahun 2012. Kondisi piringan mulus (Near Mint), cover bersahabat ada tekukan wajar.',
                'location' => 'Kota Yogyakarta',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => true,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 5,
                'seller_code' => 'zilch_tech',
                'name' => 'Action Figure Gundam RG RX-78-2',
                'code' => 'gundam-rg-rx78',
                'price' => 380000,
                'original' => 650000,
                'image' => 'https://images.unsplash.com/photo-1608889175123-8ec330b86f84?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Gundam Real Grade (RG) 1/144 RX-78-2 Bandai. Kondisi sudah dirakit sangat rapi (clean build), panel lining halus, decal terpasang lengkap dengan stand base gratis.',
                'location' => 'Kota Bekasi',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'normal',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 5,
                'seller_code' => 'zilch_tech',
                'name' => 'Konsol Retro Game Boy Color Dandelion',
                'code' => 'gameboy-color-dandelion',
                'price' => 750000,
                'original' => 1500000,
                'image' => 'https://images.unsplash.com/photo-1531525645387-7f14be1bdbbd?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Konsol genggam Nintendo Game Boy Color casing kuning Dandelion original. Mesin 100% normal lancar, tombol empuk, suara speaker jernih no sember. Layar no kerut.',
                'location' => 'Kota Bekasi',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ],

            // Category 6: Elektronik & Gadget
            [
                'cat_id' => 6,
                'seller_code' => 'zilch_tech',
                'name' => 'Keyboard Mechanical Keychron K2 V2',
                'code' => 'keychron-k2-keyboard',
                'price' => 790000,
                'original' => 1400000,
                'image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Keyboard mechanical nirkabel Keychron K2, switch Gateron Brown taktil. Konektivitas Bluetooth lancar, lampu backlight RGB aktif normal. Lengkap keycap ekstra.',
                'location' => 'Kota Bekasi',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => true,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 6,
                'seller_code' => 'zilch_tech',
                'name' => 'Sony Noise Cancelling Headphones WH-1000XM4',
                'code' => 'sony-wh1000xm4-preloved',
                'price' => 2100000,
                'original' => 4200000,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Headphone premium peredam bising Sony XM4. Kondisi earpad kulit masih kencang and bersih, kualitas suara megah, baterai super awet. Lengkap dengan case pelindung.',
                'location' => 'Kota Depok',
                'rating' => 4.9,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 6,
                'seller_code' => 'zilch_tech',
                'name' => 'Apple iPad 7th Gen 32GB Wifi Only',
                'code' => 'ipad-7th-gen-preloved',
                'price' => 2400000,
                'original' => 4800000,
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Tablet Apple iPad Generasi 7 warna Space Grey layar lebar 10.2 inci. Baterai health 85%, iCloud aman bebas reset, layar bening retina display no shadow. Kelengkapan charger saja.',
                'location' => 'Kota Bandung',
                'rating' => 4.8,
                'filters' => [
                    'free_shipping' => true,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => true
                ]
            ],
            [
                'cat_id' => 6,
                'seller_code' => 'zilch_tech',
                'name' => 'Sony Mirrorless A6000 Kit 16-50mm',
                'code' => 'sony-a6000-kit',
                'price' => 4200000,
                'original' => 7500000,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Kamera mirrorless andalan kreator Sony Alpha 6000 warna silver. Autofokus super cepat, lensa kit 16-50mm f/3.5-5.6 OSS normal no jamur, sensor bersih. Shutter count rendah.',
                'location' => 'Kota Depok',
                'rating' => 4.7,
                'filters' => [
                    'free_shipping' => false,
                    'voucher_eligible' => true,
                    'brand_tier' => 'super',
                    'eco_friendly' => false,
                    'is_clearance' => false,
                    'supports_cod' => false
                ]
            ]
        ];

        $productMap = [];

        foreach ($productsData as $prodIndex => $p) {
            $seller = $sellerUsers[$p['seller_code']];
            $prodId = $prodIndex + 1;

            // Insert core product
            DB::table('mshop_product')->insert([
                'id' => $prodId,
                'siteid' => $seller['site_id'],
                'dataset' => 'default',
                'type' => 'default',
                'code' => $p['code'],
                'label' => $p['name'],
                'rating' => $p['rating'],
                'ratings' => rand(5, 15),
                'instock' => rand(1, 3),
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder',
                'free_shipping' => $p['filters']['free_shipping'],
                'voucher_eligible' => $p['filters']['voucher_eligible'],
                'brand_tier' => $p['filters']['brand_tier'],
                'eco_friendly' => $p['filters']['eco_friendly'],
                'is_clearance' => $p['filters']['is_clearance'],
                'supports_cod' => $p['filters']['supports_cod']
            ]);

            $productMap[$p['code']] = [
                'id' => $prodId,
                'site_id' => $seller['site_id'],
                'price' => $p['price'],
                'name' => $p['name'],
                'code' => $p['code'],
                'seller_code' => $p['seller_code']
            ];

            // Insert Price
            $priceId = DB::table('mshop_price')->insertGetId([
                'siteid' => $seller['site_id'],
                'type' => 'default',
                'domain' => 'product',
                'label' => 'Price for ' . $p['name'],
                'currencyid' => 'IDR',
                'quantity' => 1,
                'value' => $p['price'],
                'costs' => 0.00,
                'rebate' => $p['original'] - $p['price'], // Slashed price difference
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Link Price
            DB::table('mshop_product_list')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $prodId,
                'key' => '',
                'type' => 'default',
                'domain' => 'price',
                'refid' => $priceId,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Insert Text description
            $textId = DB::table('mshop_text')->insertGetId([
                'siteid' => $seller['site_id'],
                'type' => 'short',
                'langid' => 'id',
                'domain' => 'product',
                'label' => 'Description',
                'content' => $p['desc'],
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Link Text
            DB::table('mshop_product_list')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $prodId,
                'key' => '',
                'type' => 'default',
                'domain' => 'text',
                'refid' => $textId,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Insert Media Image
            $mediaId = DB::table('mshop_media')->insertGetId([
                'siteid' => $seller['site_id'],
                'type' => 'default',
                'fsname' => '',
                'langid' => 'id',
                'domain' => 'product',
                'label' => 'Image for ' . $p['name'],
                'link' => $p['image'],
                'preview' => $p['image'],
                'mimetype' => 'image/jpeg',
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Link Media
            DB::table('mshop_product_list')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $prodId,
                'key' => '',
                'type' => 'default',
                'domain' => 'media',
                'refid' => $mediaId,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Link Product to Category (mshop_catalog_list)
            DB::table('mshop_catalog_list')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $p['cat_id'] + 1, // Category ID (shifted due to root category ID 1)
                'key' => '',
                'type' => 'default',
                'domain' => 'product',
                'refid' => $prodId,
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Insert custom location product property
            DB::table('mshop_product_property')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $prodId,
                'type' => 'location',
                'langid' => 'id',
                'value' => $p['location'],
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Seed Product Stock in mshop_stock
            DB::table('mshop_stock')->insert([
                'siteid' => $seller['site_id'],
                'prodid' => $prodId,
                'type' => 'default',
                'stocklevel' => rand(2, 8),
                'backdate' => null,
                'timeframe' => '',
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // 5. Seed Reviews for this Product
            $productReviews = [
                'blazer-kulit-zara' => [
                    ['rating' => 5, 'comment' => "Blazer Zara-nya original dan kulitnya mulus banget! Size M pas di badan saya.\n\n[Attached Photo: https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=300&q=80]"],
                    ['rating' => 5, 'comment' => 'Bahannya tebal, tidak ada lecet. Pengemasan sangat aman dan wangi. Makasih seller!']
                ],
                'hm-dress-floral' => [
                    ['rating' => 5, 'comment' => 'Dress floral H&M nya anggun sekali saat dipakai, bahannya jatuh dan adem.'],
                    ['rating' => 4, 'comment' => 'Sesuai foto, preloved tapi serasa baru beli dari store H&M langsung. Pengiriman super cepat.']
                ],
                'rok-plisket-velvet' => [
                    ['rating' => 5, 'comment' => 'Rok plisket velvetnya mewah banget, kilapnya cantik dan bahannya jatuh.'],
                    ['rating' => 4, 'comment' => 'Plisketnya masih rapi banget, karet pinggang kencang. Recommended!']
                ],
                'knit-cardigan-vintage' => [
                    ['rating' => 5, 'comment' => 'Cardigan knitnya anget banget dipake, rajutannya tebal dan gak gatal.'],
                    ['rating' => 5, 'comment' => 'Vibe vintage nya dapet banget, kancing lengkap no minus. Mulus sekali.']
                ],
                'levis-sherpa-denim' => [
                    ['rating' => 5, 'comment' => 'Jaket denim Levi\'s legendaris, sherpa bulunya masih putih bersih dan tebal.'],
                    ['rating' => 4, 'comment' => 'Preloved berkualitas, pudar pemakaian wajar tapi malah makin keren. Jahitan aman.']
                ],
                'uniqlo-flanel-merah' => [
                    ['rating' => 5, 'comment' => 'Kemeja flanel Uniqlo favorit, bahan flanelnya halus dan warna merahnya masih cerah.'],
                    ['rating' => 5, 'comment' => 'Size L pas sesuai panduan, rapi no minus, wanginya khas laundry. Mantap!']
                ],
                'champion-hoodie-grey' => [
                    ['rating' => 5, 'comment' => 'Hoodie Champion tebal dan anget. Logo bordir di dada masih sangat mulus.'],
                    ['rating' => 4, 'comment' => 'Karet pergelangan tangan masih kencang, no noda membandel. Pengiriman kilat.']
                ],
                'dickies-chino-khaki' => [
                    ['rating' => 5, 'comment' => 'Celana chino Dickies original, bahan katun twill-nya kuat dan warnanya pas.'],
                    ['rating' => 5, 'comment' => 'Kondisi 95% mulus, resleting lancar no minus. Celana Dickies terbaik.']
                ],
                'converse-chuck-70s' => [
                    ['rating' => 5, 'comment' => 'Converse Chuck 70s ori, sol luar dalam masih tebal dan empuk banget.'],
                    ['rating' => 4, 'comment' => 'Vibe klasiknya oke banget, ada lecet pemakaian sedikit tapi heel patch aman. Box ada.']
                ],
                'adidas-stan-smith' => [
                    ['rating' => 5, 'comment' => 'Sepatu Adidas Stan Smith putih bersih, kulitnya lembut dan solnya masih putih.'],
                    ['rating' => 5, 'comment' => 'Minimalis dan sangat terawat, heel tab warna hijau khas Stan Smith mulus. Top!']
                ],
                'nike-air-force-1' => [
                    ['rating' => 5, 'comment' => 'Nike Air Force 1 legendaris! Putih mulus, toe box aman no crease parah.'],
                    ['rating' => 4, 'comment' => 'Ori no debat, sol bawah belum banyak terkikis, heel drag tipis. Worth it banget.']
                ],
                'vans-old-skool' => [
                    ['rating' => 5, 'comment' => 'Vans Old Skool waffle IFC, kanvasnya tebal dan sol karetnya masih menggigit.'],
                    ['rating' => 5, 'comment' => 'Preloved tapi terawat sekali, jazz stripe kulitnya bersih. Puas banget belanja disini.']
                ],
                'charles-keith-handbag' => [
                    ['rating' => 5, 'comment' => 'Handbag Charles & Keith cantik banget, tali panjang lengkap, resleting emasnya kinclong.'],
                    ['rating' => 5, 'comment' => 'Tas berkelas, kulit sintetisnya no mengelupas. Box dan dustbag lengkap.']
                ],
                'rayban-wayfarer' => [
                    ['rating' => 5, 'comment' => 'Kacamata Rayban Wayfarer original, lensa G-15 adem banget di mata buat siang hari.'],
                    ['rating' => 4, 'comment' => 'Frame hitam kokoh, engsel kencang, dapet case kulit Rayban asli. Mulus.']
                ],
                'casio-gshock-black' => [
                    ['rating' => 5, 'comment' => "Jam tangan G-Shock tangguh, semua fitur alarm dan backlight normal jaya.\n\n[Attached Photo: https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=300&q=80]"],
                    ['rating' => 5, 'comment' => 'Bezel karetnya masih mulus tidak melar, kaca bersih no scratch. Jam idaman.']
                ],
                'kanken-classic-black' => [
                    ['rating' => 5, 'comment' => 'Backpack Fjallraven Kanken classic ori, bahan Vinylon F masih kaku dan waterproof.'],
                    ['rating' => 4, 'comment' => 'Sesuai ekspektasi, logo reflektifnya masih bagus. Pengemasan bubble wrap aman.']
                ],
                'canon-ae1-analog' => [
                    ['rating' => 5, 'comment' => 'Kamera analog legendaris Canon AE-1, lightmeter aktif, shutter speed akurat.'],
                    ['rating' => 5, 'comment' => 'Lensa kit bersih no jamur atau fog, view finder jernih. Body minim scratch. Bintang 5!']
                ],
                'vinyl-beatles-abbey' => [
                    ['rating' => 5, 'comment' => 'Piringan hitam The Beatles Abbey Road original press, suara jernih no skips.'],
                    ['rating' => 4, 'comment' => 'Sleeve cover masih rapi, piringan minim goresan halus. Harta karun koleksi!']
                ],
                'gundam-rg-rx78' => [
                    ['rating' => 5, 'comment' => 'Gundam RG RX-78-2 box mulus belum dirakit, parts lengkap tersegel plastik.'],
                    ['rating' => 5, 'comment' => 'Seller ramah, pengiriman super aman dibungkus kardus tebal. Terima kasih!']
                ],
                'gameboy-color-dandelion' => [
                    ['rating' => 5, 'comment' => 'Retro Game Boy Color kuning mulus, tombol empuk, speaker nyaring jernih.'],
                    ['rating' => 4, 'comment' => 'Kaca layar minim goresan, tutup baterai ori masih ada. Bernostalgia lagi!']
                ],
                'keychron-k2-keyboard' => [
                    ['rating' => 5, 'comment' => 'Keyboard mechanical Keychron K2, switch gateron brown empuk dan tactile.'],
                    ['rating' => 5, 'comment' => 'Backlight rgb nyala semua, koneksi bluetooth ke mac lancar jaya. Mulus.']
                ],
                'sony-headphones-xm4' => [
                    ['rating' => 5, 'comment' => 'Headphones Sony WH-1000XM4, ANC sunyi senyap, kualitas audio mantap.'],
                    ['rating' => 5, 'comment' => 'Earpad kulit sintetisnya masih empuk no pecah-pecah, baterai awet. Bintang 5!']
                ],
                'apple-ipad-7' => [
                    ['rating' => 5, 'comment' => 'Apple iPad 7th gen normal tanpa kendala, baterai health 89% awet seharian.'],
                    ['rating' => 4, 'comment' => 'Layar retina jernih no whitespot, icloud aman bebas reset. Kelengkapan charger.']
                ],
                'sony-mirrorless-a6000' => [
                    ['rating' => 5, 'comment' => "Kamera mirrorless Sony A6000 normal 100%, autofokus kilat, sensor bersih.\n\n[Attached Photo: https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=300&q=80]"],
                    ['rating' => 4, 'comment' => 'Body minim pemakaian wajar, lensa kit 16-50mm no jamur, dapet tas kamera juga. Mantap!']
                ]
            ];

            $reviewsList = $productReviews[$p['code']] ?? [
                ['rating' => 5, 'comment' => 'Barang bagus sesuai deskripsi, preloved berkualitas.'],
                ['rating' => 4, 'comment' => 'Sesuai ekspektasi, pengemasan rapi dan aman.']
            ];

            // Shuffled buyers to randomize reviewers
            $shuffledBuyers = $buyerUsers;
            shuffle($shuffledBuyers);
            $assignedBuyers = array_slice($shuffledBuyers, 0, count($reviewsList));

            $totalRating = 0;
            $ratingCount = 0;

            foreach ($assignedBuyers as $buyerIdx => $buyer) {
                $revData = $reviewsList[$buyerIdx];
                $date = now()->subDays(rand(1, 30))->subHours(rand(1, 12));

                // 1. Create matching order first so they actually bought it
                $orderId = DB::table('mshop_order')->insertGetId([
                    'siteid' => $seller['site_id'],
                    'sitecode' => $seller['shop_code'],
                    'customerid' => (string) $buyer['id'],
                    'relatedid' => '',
                    'channel' => 'web',
                    'invoiceno' => 'INV/' . $date->format('Ymd') . '/' . (2000 + $prodId * 10 + $buyerIdx),
                    'datepayment' => $date,
                    'datedelivery' => $date->copy()->addDays(2),
                    'statuspayment' => 2, // Paid / Success
                    'statusdelivery' => 2, // Delivered
                    'cdate' => $date->format('Y-m-d'),
                    'cmonth' => $date->format('Y-m'),
                    'cweek' => $date->format('Y-W'),
                    'cwday' => $date->format('N'),
                    'chour' => $date->format('H'),
                    'langid' => 'id',
                    'currencyid' => 'IDR',
                    'price' => $p['price'],
                    'costs' => 10000.00, // Shipping fee
                    'rebate' => 0.00,
                    'tax' => 0.00,
                    'taxflag' => 0,
                    'customerref' => '',
                    'comment' => 'Semoga barang cepat sampai ya.',
                    'ctime' => $date,
                    'mtime' => $date,
                    'editor' => 'seeder'
                ]);

                // 2. Create mshop_order_product relation
                DB::table('mshop_order_product')->insert([
                    'siteid' => $seller['site_id'],
                    'parentid' => $orderId,
                    'ordprodid' => null,
                    'ordaddrid' => null,
                    'type' => 'default',
                    'prodid' => (string) $prodId,
                    'parentprodid' => '',
                    'prodcode' => $p['code'],
                    'stocktype' => 'default',
                    'vendor' => $seller['shop_name'],
                    'name' => $p['name'],
                    'description' => 'Produk preloved original thrift.',
                    'mediaurl' => '',
                    'target' => '',
                    'timeframe' => '',
                    'quantity' => 1,
                    'qtyopen' => 0,
                    'currencyid' => 'IDR',
                    'price' => $p['price'],
                    'costs' => 0.00,
                    'rebate' => 0.00,
                    'tax' => 0.00,
                    'taxrate' => '{}',
                    'taxflag' => 0,
                    'flags' => 0,
                    'pos' => 1,
                    'statuspayment' => 2,
                    'statusdelivery' => 2,
                    'notes' => '',
                    'ctime' => $date,
                    'mtime' => $date,
                    'editor' => 'seeder'
                ]);

                // 3. Create review associated with the product and buyer
                DB::table('mshop_review')->insert([
                    'siteid' => $seller['site_id'],
                    'domain' => 'product',
                    'refid' => $prodId,
                    'customerid' => $buyer['id'],
                    'name' => $buyer['name'],
                    'status' => 1, // Approved review
                    'rating' => $revData['rating'],
                    'comment' => $revData['comment'],
                    'response' => '',
                    'ctime' => $date->copy()->addDays(1), // Reviewed 1 day after order
                    'mtime' => now(),
                    'editor' => 'seeder'
                ]);

                $totalRating += $revData['rating'];
                $ratingCount++;
            }

            // Update rating and ratings columns in mshop_product table
            if ($ratingCount > 0) {
                $avgRating = round($totalRating / $ratingCount, 2);
                DB::table('mshop_product')->where('id', $prodId)->update([
                    'rating' => $avgRating,
                    'ratings' => $ratingCount
                ]);
            }
        }

        // Running balance tracker for each seller wallet
        $sellerBalances = [];
        foreach ($sellerUsers as $shopCode => $s) {
            $sellerBalances[$shopCode] = 0.0;
        }

        // 6. Seed Orders & Transactions for Charts & Stats (25 orders over the last 30 days)
        $orderCounter = 1000;
        for ($i = 0; $i < 25; $i++) {
            $orderCounter++;
            $date = now()->subDays(30 - $i)->subHours(rand(1, 12));
            
            // Randomly choose a product
            $pKeys = array_keys($productMap);
            $randomProdKey = $pKeys[$i % count($pKeys)];
            $prod = $productMap[$randomProdKey];
            
            $buyer = $buyerUsers[$i % count($buyerUsers)];
            $seller = $sellerUsers[$prod['seller_code']];
            
            $priceValue = $prod['price'];

            // Insert into mshop_order
            $orderId = DB::table('mshop_order')->insertGetId([
                'siteid' => $prod['site_id'],
                'sitecode' => $prod['seller_code'],
                'customerid' => (string) $buyer['id'],
                'relatedid' => '',
                'channel' => 'web',
                'invoiceno' => 'INV/' . $date->format('Ymd') . '/' . $orderCounter,
                'datepayment' => $date,
                'datedelivery' => $date->copy()->addDays(2),
                'statuspayment' => 2, // Paid / Success
                'statusdelivery' => 2, // Delivered
                'cdate' => $date->format('Y-m-d'),
                'cmonth' => $date->format('Y-m'),
                'cweek' => $date->format('Y-W'),
                'cwday' => $date->format('N'),
                'chour' => $date->format('H'),
                'langid' => 'id',
                'currencyid' => 'IDR',
                'price' => $priceValue,
                'costs' => 10000.00, // Shipping fee
                'rebate' => 0.00,
                'tax' => 0.00,
                'taxflag' => 0,
                'customerref' => '',
                'comment' => 'Semoga barang cepat sampai ya.',
                'ctime' => $date,
                'mtime' => $date,
                'editor' => 'seeder'
            ]);

            // Insert into mshop_order_product
            DB::table('mshop_order_product')->insert([
                'siteid' => $prod['site_id'],
                'parentid' => $orderId,
                'ordprodid' => null, // Left null (bigint) to avoid sql errors
                'ordaddrid' => null, // Left null (bigint) to avoid sql errors
                'type' => 'default',
                'prodid' => (string) $prod['id'],
                'parentprodid' => '',
                'prodcode' => $prod['code'],
                'stocktype' => 'default',
                'vendor' => $seller['shop_name'],
                'name' => $prod['name'],
                'description' => 'Produk preloved original thrift.',
                'mediaurl' => '',
                'target' => '',
                'timeframe' => '',
                'quantity' => 1,
                'qtyopen' => 0,
                'currencyid' => 'IDR',
                'price' => $priceValue,
                'costs' => 0.00,
                'rebate' => 0.00,
                'tax' => 0.00,
                'taxrate' => '{}',
                'taxflag' => 0,
                'flags' => 0,
                'pos' => 1,
                'statuspayment' => 2,
                'statusdelivery' => 2,
                'notes' => '',
                'ctime' => $date,
                'mtime' => $date,
                'editor' => 'seeder'
            ]);

            // Track running balance
            $currentBalance = $sellerBalances[$prod['seller_code']] ?? 0.0;
            $newBalance = $currentBalance + $priceValue;
            $sellerBalances[$prod['seller_code']] = $newBalance;

            // Log ledger history for seller wallet
            DB::table('wallet_ledgers')->insert([
                'user_id' => $seller['user_id'],
                'order_id' => (string) $orderId,
                'type' => 'credit',
                'amount' => $priceValue,
                'balance_after' => $newBalance,
                'description' => 'Pendapatan penjualan produk ' . $prod['name'],
                'reference_number' => 'REF-' . strtoupper(Str::random(10)),
                'status' => 'success',
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }

        // 7. Seed Withdrawal Requests & Debit Ledgers
        foreach ($sellerUsers as $shopCode => $seller) {
            $withdrawAmount = 150000.00;

            DB::table('seller_withdrawals')->insert([
                'siteid' => $seller['site_id'],
                'amount' => $withdrawAmount,
                'status' => 'approved',
                'bank_name' => 'BCA',
                'bank_account_number' => '8293749201',
                'bank_account_name' => $seller['shop_name'],
                'notes' => 'Penarikan dana otomatis via seeder',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)
            ]);

            $currentBalance = $sellerBalances[$shopCode] ?? 0.0;
            $newBalance = max(0.0, $currentBalance - $withdrawAmount);
            $sellerBalances[$shopCode] = $newBalance;

            // Log ledger debit entry
            DB::table('wallet_ledgers')->insert([
                'user_id' => $seller['user_id'],
                'order_id' => null,
                'type' => 'debit',
                'amount' => $withdrawAmount,
                'balance_after' => $newBalance,
                'description' => 'Penarikan dana otomatis ke rekening BCA',
                'reference_number' => 'REF-WD-' . strtoupper(Str::random(10)),
                'status' => 'success',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)
            ]);
        }

        // 8. Seed Vouchers/Coupons for each seller
        foreach ($sellersData as $seller) {
            // Coupon 1: Fixed Rebate
            $couponId1 = DB::table('mshop_coupon')->insertGetId([
                'siteid' => $seller['site_id'],
                'label' => 'Potongan Harga Rp 50.000',
                'provider' => 'FixedRebate',
                'config' => json_encode([
                    'fixedrebate.rebate' => 50000,
                    'fixedrebate.productcode' => 'rebate'
                ]),
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            DB::table('mshop_coupon_code')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $couponId1,
                'code' => strtoupper($seller['shop_code']) . '50K',
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            // Coupon 2: Percent Rebate
            $couponId2 = DB::table('mshop_coupon')->insertGetId([
                'siteid' => $seller['site_id'],
                'label' => 'Diskon Spesial 10%',
                'provider' => 'PercentRebate',
                'config' => json_encode([
                    'percentrebate.rebate' => 10,
                    'percentrebate.productcode' => 'rebate'
                ]),
                'status' => 1,
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);

            DB::table('mshop_coupon_code')->insert([
                'siteid' => $seller['site_id'],
                'parentid' => $couponId2,
                'code' => strtoupper($seller['shop_code']) . '10',
                'ctime' => now(),
                'mtime' => now(),
                'editor' => 'seeder'
            ]);
        }
    }
}
