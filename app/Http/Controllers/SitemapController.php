<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Class SitemapController
 *
 * Handles sitemap controller operations for the application.
 */
class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // 1. Landing Page
        $xml .= $this->buildUrl(route('landing'), 'daily', '1.0', now()->toAtomString());

        // 2. Categories Index Page
        $xml .= $this->buildUrl(route('categories'), 'weekly', '0.8', now()->toAtomString());

        // 3. Category Detail Pages (mshop_catalog)
        try {
            $catalogs = DB::table('mshop_catalog')
                ->where('status', 1)
                ->get(['code', 'mtime']);
            
            foreach ($catalogs as $catalog) {
                if ($catalog->code) {
                    $url = route('categories.show', ['selected_category' => $catalog->code]);
                    $lastmod = $catalog->mtime ? date('c', strtotime($catalog->mtime)) : now()->toAtomString();
                    $xml .= $this->buildUrl($url, 'daily', '0.8', $lastmod);
                }
            }
        } catch (\Exception $e) {
            // Log or fallback silently
        }

        // 4. Product Detail Pages (mshop_product)
        try {
            $products = DB::table('mshop_product')
                ->where('status', 1)
                ->get(['id', 'mtime']);
            
            foreach ($products as $product) {
                $url = route('products.show', ['id' => $product->id]);
                $lastmod = $product->mtime ? date('c', strtotime($product->mtime)) : now()->toAtomString();
                $xml .= $this->buildUrl($url, 'daily', '0.9', $lastmod);
            }
        } catch (\Exception $e) {
            // Log or fallback silently
        }

        // 5. Shop/Merchant Detail Pages (mshop_locale_site)
        try {
            $approvedSiteIds = DB::table('users')
                ->where('seller_status', 'approved')
                ->whereNotNull('siteid')
                ->pluck('siteid');

            if ($approvedSiteIds->isNotEmpty()) {
                $shops = DB::table('mshop_locale_site')
                    ->whereIn('id', $approvedSiteIds)
                    ->where('status', 1)
                    ->get(['code', 'mtime']);

                foreach ($shops as $shop) {
                    if ($shop->code) {
                        $url = route('shops.show', ['shop_code' => $shop->code]);
                        $lastmod = $shop->mtime ? date('c', strtotime($shop->mtime)) : now()->toAtomString();
                        $xml .= $this->buildUrl($url, 'weekly', '0.7', $lastmod);
                    }
                }
            }
        } catch (\Exception $e) {
            // Log or fallback silently
        }

        // 6. Static Pages
        $staticRoutes = [
            'tentang-kami' => '0.5',
            'cara-kerja' => '0.5',
            'karir' => '0.5',
            'help-center' => '0.5',
            'keamanan' => '0.5',
            'syarat-ketentuan' => '0.5',
        ];

        foreach ($staticRoutes as $routeName => $priority) {
            try {
                if (URL::route($routeName, [], false)) {
                    $xml .= $this->buildUrl(route($routeName), 'monthly', $priority, now()->toAtomString());
                }
            } catch (\Exception $ex) {
                // Route not found or failed, skip
            }
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Build standard sitemap URL XML snippet.
     */
    private function buildUrl(string $loc, string $changefreq, string $priority, ?string $lastmod = null): string
    {
        $url = '<url>';
        $url .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        if ($lastmod) {
            $url .= '<lastmod>' . $lastmod . '</lastmod>';
        }
        $url .= '<changefreq>' . $changefreq . '</changefreq>';
        $url .= '<priority>' . $priority . '</priority>';
        $url .= '</url>';
        return $url;
    }
}
