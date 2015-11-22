<?php

namespace BrowscapSite\Tool;

use BrowscapPHP\Browscap;
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
     */
    public function update()
    {
        $browscap = $this->getBrowscapPhp();
        $browscap->convertFile($this->remoteIniFile);
    }

    /**
     * Identify a user agent
     *
     * @param string $userAgent
     * @return mixed
     */
    public function identify($userAgent)
    {
        return $this->getBrowscapPhp()->getBrowser($userAgent);
    }

    /**
     * Returns a configured Browscap PHP instance for use on browscap-site
     *
     * @return Browscap
     * @throws \BrowscapPHP\Exception
     */
    private function getBrowscapPhp()
    {
        $browscap = new Browscap();
        $browscap->setCache(
            new BrowscapCache(
                new File([
                    File::DIR => $this->cacheDirectory,
                ])
            )
        );

        return $browscap;
    }
}
