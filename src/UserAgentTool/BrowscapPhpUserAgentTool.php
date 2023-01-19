<?php

declare(strict_types=1);

namespace BrowscapSite\UserAgentTool;

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use stdClass;

final class BrowscapPhpUserAgentTool implements UserAgentTool
{
    private const INI_FILE = __DIR__ . '/../../vendor/build/full_php_browscap.ini';

    public function __construct(private CacheInterface $cache, private LoggerInterface $logger)
    {
    }

    /**
     * Perform an update from the latest generated build (locally, not from web)
     *
     * @throws Exception
     */
    public function update(): void
    {
        $updater = new BrowscapUpdater(
            $this->cache,
            $this->logger,
            new Client([
                'handler' => Proxy::wrapSync(new CurlMultiHandler(), new CurlHandler()),
                'headers' => ['User-Agent' => 'browscap.org BrowscapPhpUserAgentTool wrapper'],
            ]),
        );
        $updater->convertFile(self::INI_FILE);
    }

    /**
     * Identify a user agent
     *
     * @throws Exception
     */
    public function identify(string $userAgent): stdClass
    {
        $browscap = new Browscap($this->cache, $this->logger);

        return $browscap->getBrowser($userAgent);
    }
}
