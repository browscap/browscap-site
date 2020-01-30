<?php
declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    new \BrowscapSite\ConfigProvider\SlimDependencies(),
    new \BrowscapSite\ConfigProvider\AppConfig(),
    new ArrayProvider($cacheConfig),
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
