<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$context = app('aimeos.context')->get(false);
$localeManager = \Aimeos\MShop::create($context, 'locale');
$locale = $localeManager->bootstrap('default', '', '', false);
$context->setLocale($locale);

\Aimeos\MShop::cache(false);
$manager = \Aimeos\MShop::create($context, 'catalog');

// Find all root nodes
$filter = $manager->filter()->add(['catalog.level' => 0]);
$rootNodes = $manager->search($filter);

// The first root node is the real one, created during setup. 
// Any others were created by the bug.
$isFirst = true;
foreach ($rootNodes as $node) {
    if ($isFirst) {
        echo "Keeping real root node: " . $node->getId() . "\n";
        $isFirst = false;
        continue;
    }
    echo "Deleting orphaned root node: " . $node->getId() . " (" . $node->getCode() . ")\n";
    $manager->begin();
    $manager->delete($node);
    $manager->commit();
}
echo "Done.\n";
