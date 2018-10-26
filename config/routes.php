<?php
declare(strict_types=1);

use BrowscapSite\Handler\DownloadHandler;
use BrowscapSite\Handler\UserAgentLookupHandler;
use Psr\Container\ContainerInterface;
use Slim\App;

return function (App $app, ContainerInterface $container): void {
    $app->get('/', DownloadHandler::class);
    $app->any('/ua-lookup', UserAgentLookupHandler::class);
};
