<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$context = app('aimeos.context')->get();
$context->setEditor('test');
$localeManager = \Aimeos\MShop::create($context, 'locale');
$localeItem = $localeManager->bootstrap('default', '', '', false, null, true);
$context->setLocale($localeItem);

$customerManager = \Aimeos\MShop::create($context, 'customer');
$groupManager = \Aimeos\MShop::create($context, 'group');

$group = $groupManager->find('admin');
echo "Group ID: " . $group->getId() . "\n";

$user = App\Models\User::first();
echo "User ID: " . $user->id . "\n";

$customer = $customerManager->get($user->id, ['group']);
$customer->setGroups([$group->getId()]);

$customerManager->save($customer);
echo "Saved.\n";
