<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSeller
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user is a seller by verifying they have a
     * non-default siteid (sellers have their own dedicated Aimeos sub-site).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || empty($user->siteid)) {
            return response()->json([
                'message' => 'Forbidden. Only sellers can access this resource.',
            ], 403);
        }

        // Resolve the default site's siteid and compare
        try {
            $context  = app('aimeos.context')->get(false);
            $siteManager = \Aimeos\MShop::create($context, 'locale/site');
            $defaultSite = $siteManager->find('default');
            $defaultSiteId = $defaultSite->getSiteId();

            // Sellers have their own sub-site, NOT the default site
            if ($user->siteid === $defaultSiteId) {
                return response()->json([
                    'message' => 'Forbidden. Only sellers can access this resource.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Forbidden. Unable to verify seller status.',
            ], 403);
        }

        return $next($request);
    }
}

