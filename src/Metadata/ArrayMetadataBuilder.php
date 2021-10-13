<?php

declare(strict_types=1);

namespace BrowscapSite\Metadata;

use Browscap\Parser\ParserInterface;

use function file_exists;
use function file_put_contents;
use function filesize;
use function round;
use function unlink;
use function var_export;

/**
 * @psalm-import-type MetadataArray from Metadata
 */
final class ArrayMetadataBuilder implements MetadataBuilder
{
    private ParserInterface $parser;
    private string $buildDir;

    public function __construct(ParserInterface $parser, string $buildDir)
    {
        $this->parser   = $parser;
        $this->buildDir = $buildDir;
    }

    public function build(): void
    {
        /** @psalm-var array{Version: string, Released: string} $versionData */
        $versionData = $this->parser->parse()['GJK_Browscap_Version'];

        $this->writeArray(
            $this->buildDir . '/metadata.php',
            [
                'version' => $versionData['Version'],
                'released' => $versionData['Released'],
                'filesizes' => [
                    'BrowsCapINI' => $this->getKbSize($this->buildDir . '/browscap.ini'),
                    'Full_BrowsCapINI' => $this->getKbSize($this->buildDir . '/full_asp_browscap.ini'),
                    'Lite_BrowsCapINI' => $this->getKbSize($this->buildDir . '/lite_asp_browscap.ini'),
                    'PHP_BrowsCapINI' => $this->getKbSize($this->buildDir . '/php_browscap.ini'),
                    'Full_PHP_BrowsCapINI' => $this->getKbSize($this->buildDir . '/full_php_browscap.ini'),
                    'Lite_PHP_BrowsCapINI' => $this->getKbSize($this->buildDir . '/lite_php_browscap.ini'),
                    'BrowsCapXML' => $this->getKbSize($this->buildDir . '/browscap.xml'),
                    'BrowsCapCSV' => $this->getKbSize($this->buildDir . '/browscap.csv'),
                    'BrowsCapJSON' => $this->getKbSize($this->buildDir . '/browscap.json'),
                    'BrowsCapZIP' => $this->getKbSize($this->buildDir . '/browscap.zip'),
                ],
            ]
        );

        $this->niceDelete($this->buildDir . '/../cache/browscap.ini');
        $this->niceDelete($this->buildDir . '/../cache/cache.php');
    }

    private function niceDelete(string $filename): void
    {
        if (! file_exists($filename)) {
            return;
        }

        unlink($filename);
    }

    /** @psalm-param MetadataArray $array */
    private function writeArray(string $filename, array $array): void
    {
        /** @noinspection FilePutContentsRaceConditionInspection */
        file_put_contents($filename, "<?php\n\nreturn " . var_export($array, true) . ';');
    }

    private function getKbSize(string $filename): int
    {
        return (int) round(filesize($filename) / 1024);
    }
}
