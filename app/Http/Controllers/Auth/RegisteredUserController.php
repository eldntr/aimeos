<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->check($request);

        $user = $this->user($request);

        event(new Registered($user));

        Auth::login($user);

        if ($request->wantsJson()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 201);
        }

        $params = config( 'app.shop_multishop' ) && config( 'app.shop_registration' ) && $request->code ? ['site' => $request->code] : [];
        return redirect(airoute( 'aimeos_home', $params ));
    }


    /**
     * Returns the site ID the user should be associated to
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string Site ID
     */
    protected function siteid(Request $request) : string
    {
        $context = app( 'aimeos.context' )->get();
        $manager = \Aimeos\MShop::create( $context, 'locale/site' );

        $site = $request->route( 'site', $request->input( 'site', config( 'shop.mshop.locale.site', 'default' ) ) );
        $root = $manager->find( $site );
        $siteId = $root->getSiteId();

        if( config( 'app.shop_multishop' ) && config( 'app.shop_registration' ) )
        {
            $code = $request->code;
            $item = $manager->create()->setCode( $code )->setLabel( $code )->setStatus( 1 );
            $site = $manager->insert( $item, $root->getId() );

            \Aimeos\Setup::use( new \Aimeos\Bootstrap() )->context( $context )->verbose( '' )->up( $code );

            $siteId = $site->getSiteId();
        }

        return $siteId;
    }


    /**
     * Returns the newly created user
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Models\User $user
     */
    protected function user(Request $request) : \App\Models\User
    {
        $user = User::create([
            'name' => strip_tags( $request->code ?? $request->name ),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'siteid' => $this->siteid($request),
        ]);

        if( config( 'app.shop_multishop' ) && config( 'app.shop_registration' ) )
        {
            $context = app( 'aimeos.context' )->get();
            $context->setLocale( \Aimeos\MShop::create( $context, 'locale' )->bootstrap( $request->code ) );

            $manager = \Aimeos\MShop::create( $context, 'customer' );

            $group = \Aimeos\MShop::create( $context, 'group' )->find( config( 'app.shop_permission', 'admin' ) );
            $customer = $manager->get( $user->id, ['group'] )->setGroups( [$group->getId()]);

            $manager->save( $customer );
        }

        return $user;
    }


    /**
     * Validates the values entered for the user
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function check(Request $request)
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if( config( 'app.shop_multishop' ) && config( 'app.shop_registration' ) ) {
            $rules['code'] = ['required', 'string', 'max:255', 'unique:mshop_locale_site', 'regex:/^[a-z0-9\-]+(\.[a-z0-9\-]+)?$/i'];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);
    }

    /**
     * Register a new Customer via API.
     */
    public function registerCustomer(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $context = app('aimeos.context')->get();
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $defaultSite = $siteManager->find('default');
        $siteId = $defaultSite->getSiteId();

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'siteid' => $siteId,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Register a new Seller (Merchant) via API.
     */
    public function registerSeller(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:mshop_locale_site', 'regex:/^[a-z0-9\-]+(\.[a-z0-9\-]+)?$/i'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $context = app('aimeos.context')->get();
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        
        $rootSite = $siteManager->find('default');
        
        $code = $request->code;
        $item = $siteManager->create()->setCode($code)->setLabel($code)->setStatus(1);
        $newSite = $siteManager->insert($item, $rootSite->getId());
        $siteId = $newSite->getSiteId();

        \Aimeos\Setup::use(new \Aimeos\Bootstrap())->context($context)->verbose('')->up($code);

        $user = User::create([
            'name' => strip_tags($code),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'siteid' => $siteId,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $context->setLocale(\Aimeos\MShop::create($context, 'locale')->bootstrap($code));
        $customerManager = \Aimeos\MShop::create($context, 'customer');
        $groupManager = \Aimeos\MShop::create($context, 'group');
        $group = $groupManager->find('admin');
        
        $customer = $customerManager->get($user->id, ['group'])
            ->setGroups([$group->getId()]);
        $customerManager->save($customer);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Register a new Admin (Global / Super Admin) via API.
     */
    public function registerAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $context = app('aimeos.context')->get();
        $siteManager = \Aimeos\MShop::create($context, 'locale/site');
        $defaultSite = $siteManager->find('default');
        $siteId = $defaultSite->getSiteId();

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'siteid' => $siteId,
        ]);

        event(new Registered($user));
        Auth::login($user);

        $context->setLocale(\Aimeos\MShop::create($context, 'locale')->bootstrap('default'));
        
        $customerManager = \Aimeos\MShop::create($context, 'customer');
        $groupManager = \Aimeos\MShop::create($context, 'group');
        $group = $groupManager->find('admin');
        
        $customer = $customerManager->get($user->id, ['group'])
            ->setGroups([$group->getId()]);
        $customerManager->save($customer);
        \Illuminate\Support\Facades\Log::info('mshop_customer_list count: ' . \Illuminate\Support\Facades\DB::table('mshop_customer_list')->count());
        \Illuminate\Support\Facades\Log::info('mshop_customer_list rows: ' . json_encode(\Illuminate\Support\Facades\DB::table('mshop_customer_list')->get()));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }
}
