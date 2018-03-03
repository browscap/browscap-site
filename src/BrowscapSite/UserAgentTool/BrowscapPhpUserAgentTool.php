<?php
declare(strict_types=1);

namespace BrowscapSite\UserAgentTool;

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Cache\BrowscapCache;
use BrowscapPHP\Cache\BrowscapCacheInterface;
use WurflCache\Adapter\File;

final class BrowscapPhpUserAgentTool implements UserAgentTool
{
    private const CACHE_DIRECTORY = __DIR__ . '/../../../cache/';
    private const INI_FILE = __DIR__ . '/../../../vendor/build/full_php_browscap.ini';

    /**
     * @var BrowscapCacheInterface
     */
    private $cache;

    public function __construct()
    {
        $this->cache = new BrowscapCache(
            new File([
                File::DIR => self::CACHE_DIRECTORY,
            ])
        );
    }

    /**
     * Perform an update from the latest generated build (locally, not from web)
     * @throws \BrowscapPHP\Exception
     */
    public function update(): void
    {
        $updater = new BrowscapUpdater();
        $updater->setCache($this->cache);
        $updater->convertFile(self::INI_FILE);
    }

    /**
     * Identify a user agent
     *
     * @param string $userAgent
     * @return \stdClass
     * @throws \BrowscapPHP\Exception
     */
    public function identify(string $userAgent): \stdClass
    {
        $browscap = new Browscap();
        $browscap->setCache($this->cache);
        return $browscap->getBrowser($userAgent);
    }
}
