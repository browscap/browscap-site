<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\App;

if (PHP_SAPI === 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = pathinfo(__FILE__, PATHINFO_BASENAME);
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

chdir(dirname(__DIR__));

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = require __DIR__ . '/../config/container.php';
assert($container instanceof ContainerInterface);
$app = new App($container);
(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection UnusedFunctionResultInspection */
$app->run();
