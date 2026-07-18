<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$context = app('aimeos.context')->get(false);
\Aimeos\MShop::cache(false);
$manager = \Aimeos\MShop::create($context, 'catalog');
echo "Manager created successfully\n";
