<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;

if (PHP_SAPI === 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = pathinfo(__FILE__, PATHINFO_BASENAME);
    assert(array_key_exists('REQUEST_URI', $_SERVER));
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    if (array_key_exists('path', $url) && is_string($url['path'])) {
        $file = __DIR__ . $url['path'];
        if (is_file($file)) {
            return false;
        }
    }
}

chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = require __DIR__ . '/../config/container.php';
assert($container instanceof ContainerInterface);
$app = AppFactory::createFromContainer($container);
/** @psalm-suppress InvalidArgument */
(require __DIR__ . '/../config/middleware.php')($app);
/** @psalm-suppress InvalidArgument */
(require __DIR__ . '/../config/routes.php')($app);

$app->run();
