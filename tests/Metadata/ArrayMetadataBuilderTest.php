<?php

declare(strict_types=1);

namespace BrowscapSiteTest\Metadata;

use Browscap\Parser\ParserInterface;
use BrowscapSite\Metadata\ArrayMetadataBuilder;
use DateTimeImmutable;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_walk;

use const DATE_RFC2822;

/** @covers \BrowscapSite\Metadata\ArrayMetadataBuilder */
final class ArrayMetadataBuilderTest extends TestCase
{
    private ParserInterface&MockObject $parser;

    private vfsStreamDirectory $filesystem;

    private ArrayMetadataBuilder $metadataBuilder;

    public function setUp(): void
    {
        $this->parser          = $this->createMock(ParserInterface::class);
        $this->filesystem      = vfsStream::setup();
        $this->metadataBuilder = new ArrayMetadataBuilder(
            $this->parser,
            $this->filesystem->url(),
        );
    }

    public function testMetadataGenerated(): void
    {
        $version     = '1002003';
        $releaseDate = (new DateTimeImmutable('now'))->format(DATE_RFC2822);
        $this->parser->expects(self::once())->method('parse')->willReturn([
            'GJK_Browscap_Version' => [
                'Version' => $version,
                'Released' => $releaseDate,
            ],
        ]);

        $expectedFiles = [
            'browscap.ini' => 1,
            'full_asp_browscap.ini' => 2,
            'lite_asp_browscap.ini' => 3,
            'php_browscap.ini' => 4,
            'full_php_browscap.ini' => 5,
            'lite_php_browscap.ini' => 6,
            'browscap.xml' => 7,
            'browscap.csv' => 8,
            'browscap.json' => 9,
            'browscap.zip' => 10,
        ];

        array_walk(
            $expectedFiles,
            function (int $kbSize, string $filename): void {
                vfsStream::newFile($filename)
                    ->withContent(LargeFileContent::withKilobytes($kbSize))
                    ->at($this->filesystem);
            },
        );

        $this->metadataBuilder->build();

        /** @psalm-suppress UnresolvableInclude */
        self::assertEquals(
            [
                'version' => $version,
                'released' => $releaseDate,
                'filesizes' => [
                    'BrowsCapINI' => 1,
                    'Full_BrowsCapINI' => 2,
                    'Lite_BrowsCapINI' => 3,
                    'PHP_BrowsCapINI' => 4,
                    'Full_PHP_BrowsCapINI' => 5,
                    'Lite_PHP_BrowsCapINI' => 6,
                    'BrowsCapXML' => 7,
                    'BrowsCapCSV' => 8,
                    'BrowsCapJSON' => 9,
                    'BrowsCapZIP' => 10,
                ],
            ],
            require $this->filesystem->url() . '/metadata.php',
        );
    }
}
