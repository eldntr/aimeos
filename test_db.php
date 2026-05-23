<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sites = Illuminate\Support\Facades\DB::table('mshop_locale_site')->get();
echo "SITES:\n";
foreach($sites as $site) { echo $site->id . " - " . $site->code . "\n"; }
