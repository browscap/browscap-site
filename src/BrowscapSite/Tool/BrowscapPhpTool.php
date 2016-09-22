<?php

namespace BrowscapSite\Tool;

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Cache\BrowscapCache;
use WurflCache\Adapter\File;

class BrowscapPhpTool
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var string
     */
    private $remoteIniFile;

    public function __construct($cacheDirectory = null, $remoteIniFile = null)
    {
        $this->cacheDirectory = $cacheDirectory;

        if (null === $this->cacheDirectory) {
            $this->cacheDirectory = __DIR__ . '/../../../cache/';
        }

        $this->remoteIniFile = $remoteIniFile;
        if (null === $this->remoteIniFile) {
            $this->remoteIniFile = __DIR__ . '/../../../build/full_php_browscap.ini';
        }
    }

    /**
     * Perform an update from the latest generated build (locally, not from web)
     * @throws \BrowscapPHP\Exception
     */
    public function update()
    {
        $updater = new BrowscapUpdater();
        $updater->setCache($this->getCache());
        $updater->convertFile($this->remoteIniFile);
    }

    /**
     * Identify a user agent
     *
     * @param string $userAgent
     * @return mixed
     * @throws \BrowscapPHP\Exception
     */
    public function identify($userAgent)
    {
        $browscap = new Browscap();
        $browscap->setCache($this->getCache());
        return $browscap->getBrowser($userAgent);
    }

    /**
     * @return BrowscapCache
     */
    private function getCache()
    {
        return new BrowscapCache(
            new File([
                File::DIR => $this->cacheDirectory,
            ])
        );
    }
}
