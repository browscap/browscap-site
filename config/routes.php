<?php
declare(strict_types=1);

use BrowscapSite\Handler\DownloadHandler;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (App $app, ContainerInterface $container): void {
    $app->get('/', DownloadHandler::class);
};
