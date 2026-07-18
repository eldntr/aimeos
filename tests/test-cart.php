<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
auth()->login($user);

$req = \Illuminate\Http\Request::create('/api/cart', 'POST', ['product_id' => '3', 'quantity' => 1]);
$req->headers->set('Accept', 'application/json');
$req->setUserResolver(function() use ($user) { return $user; });

$res = app()->handle($req);
echo "Status: " . $res->getStatusCode() . "\n";
echo "Content: " . $res->getContent() . "\n";
