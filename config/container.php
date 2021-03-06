<?php
declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$container = new ServiceManager($config['dependencies']);

// Inject config
$container->setService('Config', $config);
$container->setAlias('config', 'Config');
$container->setAlias('Configuration', 'Config');

return $container;
