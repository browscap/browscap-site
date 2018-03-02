<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

chdir(dirname(__DIR__));

$app = new BrowscapSite\BrowscapSiteWeb();
$app->run();
