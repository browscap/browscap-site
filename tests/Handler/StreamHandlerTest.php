<?php

declare(strict_types=1);

namespace BrowscapSiteTest\Handler;

use BrowscapSite\ConfigProvider\AppConfig;
use BrowscapSite\Handler\StreamHandler;
use BrowscapSite\Tool\RateLimiter;
use BrowscapSiteTest\TestHelper;
use Exception;
use Laminas\Diactoros\ServerRequest;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function random_int;
use function sprintf;
use function strtolower;
use function uniqid;

/** @covers \BrowscapSite\Handler\StreamHandler */
final class StreamHandlerTest extends TestCase
{
    /** @var RateLimiter&MockObject */
    private $rateLimiter;
    private vfsStreamDirectory $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->rateLimiter = $this->createMock(RateLimiter::class);
        $this->filesystem  = vfsStream::setup();
    }

    /** @throws Exception */
    public function testDownloadStreamIsOffered(): void
    {
        $clientIp           = sprintf('%d.%d.%d.%d', random_int(1, 254), random_int(1, 254), random_int(1, 254), random_int(1, 254));
        $clientUserAgent    = uniqid('clientUserAgent', true);
        $streamFileContents = uniqid('browscapIniContents', true);
        $requestedFile      = 'BrowsCapINI';

        file_put_contents($this->filesystem->url() . '/browscap.ini', $streamFileContents);

        $this->rateLimiter
            ->expects(self::once())
            ->method('logDownload')
            ->with($clientIp, $clientUserAgent, strtolower($requestedFile));

        $response = (new StreamHandler(
            $this->rateLimiter,
            TestHelper::createMetadataForTesting(),
            AppConfig::DEFAULT_FILES_LIST,
            $this->filesystem->url()
        ))->handle(
            (new ServerRequest(['REMOTE_ADDR' => $clientIp, 'HTTP_USER_AGENT' => $clientUserAgent]))
                ->withQueryParams(['q' => $requestedFile])
        );

        self::assertEquals($streamFileContents, $response->getBody()->__toString());
        self::assertSame(200, $response->getStatusCode());
    }
}
