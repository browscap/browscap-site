<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
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

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../config/container.php';
$app = new \Slim\App($container);
(require __DIR__ . '/../config/middleware.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

/** @noinspection PhpUnhandledExceptionInspection */
$app->run();
