<?php

declare(strict_types=1);

namespace BrowscapSiteTest\Handler;

use BrowscapSite\ConfigProvider\AppConfig;
use BrowscapSite\Handler\DownloadHandler;
use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use BrowscapSiteTest\TestHelper;
use DateTimeImmutable;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @covers \BrowscapSite\Handler\DownloadHandler */
final class DownloadHandlerTest extends TestCase
{
    private Renderer&MockObject $renderer;
    private Metadata $metadata;
    private DateTimeImmutable $releaseDate;

    public function setUp(): void
    {
        parent::setUp();

        $this->releaseDate = new DateTimeImmutable();

        $this->renderer = $this->createMock(Renderer::class);
        $this->metadata = TestHelper::createMetadataForTesting(TestHelper::EXAMPLE_RELEASE_VERSION, $this->releaseDate);
    }

    public function testHandleRendersFileList(): void
    {
        $handler = new DownloadHandler(
            $this->renderer,
            $this->metadata,
            AppConfig::DEFAULT_FILES_LIST,
            AppConfig::DEFAULT_BAN_CONFIGURATION,
        );

        $expectedResponse = new HtmlResponse('', 200);
        $this->renderer
            ->expects(self::once())
            ->method('render')
            ->with(
                'downloads.html',
                [
                    'files' => [
                        'asp' => [
                            'BrowsCapINI' => [
                                'name' => 'browscap.ini',
                                'size' => '1,001',
                                'description' => 'This is the standard version of browscap.ini file for IIS 5.x and greater.',
                            ],
                            'Full_BrowsCapINI' => [
                                'name' => 'full_asp_browscap.ini',
                                'size' => '1,002',
                                'description' => 'This is a larger version of browscap.ini with all the new properties.',
                            ],
                            'Lite_BrowsCapINI' => [
                                'name' => 'lite_asp_browscap.ini',
                                'size' => '1,003',
                                'description' => 'This is a smaller version of browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                            ],
                        ],
                        'php' => [
                            'PHP_BrowsCapINI' => [
                                'name' => 'php_browscap.ini',
                                'size' => '1,004',
                                'description' => 'This is a special version of browscap.ini for PHP users only!',
                            ],
                            'Full_PHP_BrowsCapINI' => [
                                'name' => 'full_php_browscap.ini',
                                'size' => '1,005',
                                'description' => 'This is a larger version of php_browscap.ini with all the new properties.',
                            ],
                            'Lite_PHP_BrowsCapINI' => [
                                'name' => 'lite_php_browscap.ini',
                                'size' => '1,006',
                                'description' => 'This is a smaller version of php_browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                            ],
                        ],
                        'other' => [
                            'BrowsCapXML' => [
                                'name' => 'browscap.xml',
                                'size' => '1,007',
                                'description' => 'This is the standard version of browscap.ini file in XML format.',
                            ],
                            'BrowsCapCSV' => [
                                'name' => 'browscap.csv',
                                'size' => '1,008',
                                'description' => 'This is an industry-standard comma-separated-values version of browscap.ini. Easily imported into Access, Excel, MySQL & others.',
                            ],
                            'BrowsCapJSON' => [
                                'name' => 'browscap.json',
                                'size' => '1,009',
                                'description' => 'This is a JSON (JavaScript Object Notation) version of browscap.ini. This is usually used with JavaScript.',
                            ],
                            'BrowsCapZIP' => [
                                'name' => 'browscap.zip',
                                'size' => '1,010',
                                'description' => 'This archive combines all the above files into one download that is smaller than all eight files put together.',
                            ],
                        ],
                    ],
                    'version' => TestHelper::EXAMPLE_RELEASE_VERSION,
                    'releaseDate' => $this->releaseDate->format('jS M Y'),
                    'banConfig' => AppConfig::DEFAULT_BAN_CONFIGURATION,
                ],
            )
            ->willReturn($expectedResponse);

        self::assertSame($expectedResponse, $handler->handle(new ServerRequest()));
    }
}
