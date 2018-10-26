<?php
declare(strict_types=1);

use BrowscapSite\Handler\DownloadHandler;
use BrowscapSite\Handler\StatsHandler;
use BrowscapSite\Handler\StreamHandler;
use BrowscapSite\Handler\UserAgentLookupHandler;
use BrowscapSite\Handler\VersionHandler;
use BrowscapSite\Handler\VersionNumberHandler;
use BrowscapSite\Handler\VersionXmlHandler;
use Slim\App;

return function (App $app): void {
    $app->get('/', DownloadHandler::class);
    $app->any('/ua-lookup', UserAgentLookupHandler::class);
    $app->any('/stream', StreamHandler::class);
    $app->any('/statistics', StatsHandler::class);
    $app->any('/version', VersionHandler::class);
    $app->any('/version-number', VersionNumberHandler::class);
    $app->any('/versionxml', VersionXmlHandler::class);
};
