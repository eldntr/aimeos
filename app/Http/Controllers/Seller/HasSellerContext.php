<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Support\Facades\Auth;

trait HasSellerContext
{
    /**
     * Get the Aimeos context bootstrapped to the seller's own site.
     */
    protected function getSellerContext(): \Aimeos\MShop\ContextIface
    {
        $context  = app('aimeos.context')->get(false);
        
        // Bootstrap temporary context to 'default' so we have a locale object set
        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap('default', '', '', false);
        $context->setLocale($locale);

        $user     = Auth::user();
        $siteCode = $this->getSiteCodeFromSiteId($context, $user->siteid);

        $locale = \Aimeos\MShop::create($context, 'locale')->bootstrap($siteCode, '', '', false);
        $context->setLocale($locale);

        return $context;
    }

    /**
     * Resolve site code from the siteid path stored in users.siteid.
     * Aimeos stores siteid as a hierarchical path string (e.g. "1.5.").
     */
    protected function getSiteCodeFromSiteId(\Aimeos\MShop\ContextIface $context, string $siteid): string
    {
        $manager = \Aimeos\MShop::create($context, 'locale/site');
        $filter  = $manager->filter();
        $parts   = array_filter(explode('.', trim($siteid, '.')));
        $numericId = end($parts);
        $filter->add($filter->compare('==', 'locale.site.id', (int) $numericId));
        $sites   = $manager->search($filter);

        if ($sites->isEmpty()) {
            abort(403, 'Seller site not found.');
        }

        return $sites->first()->getCode();
    }
}
