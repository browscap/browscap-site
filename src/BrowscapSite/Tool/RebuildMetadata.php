<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use Browscap\Parser\ParserInterface;

final class RebuildMetadata
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var string
     */
    private $buildDir;

    public function __construct(ParserInterface $parser, string $buildDir)
    {
        $this->parser = $parser;
        $this->buildDir = $buildDir;
    }

    public function rebuildMetadata(): void
    {
        $metadata = [];

        $fileData = $this->parser->parse();

        $versionData = $fileData['GJK_Browscap_Version'];

        $metadata['version'] = $versionData['Version'];
        $metadata['released'] = $versionData['Released'];

        $metadata['filesizes'] = [];
        $metadata['filesizes']['BrowsCapINI'] = $this->getKbSize($this->buildDir . '/browscap.ini');
        $metadata['filesizes']['Full_BrowsCapINI'] = $this->getKbSize($this->buildDir . '/full_asp_browscap.ini');
        $metadata['filesizes']['Lite_BrowsCapINI'] = $this->getKbSize($this->buildDir . '/lite_asp_browscap.ini');
        $metadata['filesizes']['PHP_BrowsCapINI'] = $this->getKbSize($this->buildDir . '/php_browscap.ini');
        $metadata['filesizes']['Full_PHP_BrowsCapINI'] = $this->getKbSize($this->buildDir . '/full_php_browscap.ini');
        $metadata['filesizes']['Lite_PHP_BrowsCapINI'] = $this->getKbSize($this->buildDir . '/lite_php_browscap.ini');
        $metadata['filesizes']['BrowsCapXML'] = $this->getKbSize($this->buildDir . '/browscap.xml');
        $metadata['filesizes']['BrowsCapCSV'] = $this->getKbSize($this->buildDir . '/browscap.csv');
        $metadata['filesizes']['BrowsCapJSON'] = $this->getKbSize($this->buildDir . '/browscap.json');
        $metadata['filesizes']['BrowsCapZIP'] = $this->getKbSize($this->buildDir . '/browscap.zip');

        $this->writeArray($this->buildDir . '/metadata.php', $metadata);

        $this->niceDelete($this->buildDir . '/../cache/browscap.ini');
        $this->niceDelete($this->buildDir . '/../cache/cache.php');
    }

    private function niceDelete(string $filename): void
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    private function writeArray(string $filename, array $array): void
    {
        file_put_contents($filename, "<?php\n\nreturn " . var_export($array, true) . ';', LOCK_EX);
    }

    private function getKbSize(string $filename): int
    {
        return (int)round(filesize($filename) / 1024);
    }
}
