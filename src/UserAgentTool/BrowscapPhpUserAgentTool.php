<?php
declare(strict_types=1);

namespace BrowscapSite\UserAgentTool;

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class BrowscapPhpUserAgentTool implements UserAgentTool
{
    private const INI_FILE = __DIR__ . '/../../vendor/build/full_php_browscap.ini';

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;


    public function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Perform an update from the latest generated build (locally, not from web)
     * @throws \BrowscapPHP\Exception
     */
    public function update(): void
    {
        $updater = new BrowscapUpdater($this->cache, $this->logger);
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
        $browscap = new Browscap($this->cache, $this->logger);
        return $browscap->getBrowser($userAgent);
    }
}
