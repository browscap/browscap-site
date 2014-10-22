<?php

namespace BrowscapSite\Tool;

use Browscap\Parser\ParserInterface;
use Browscap\Parser\IniParser;

class Rebuilder
{
    /**
     * @var \Browscap\Parser\ParserInterface
     */
    protected $parser;

    protected $buildDir;

    public function __construct($buildDir)
    {
        $this->buildDir = $buildDir;
    }

    public function setParser(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return \Browscap\Parser\ParserInterface
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new IniParser($this->buildDir . '/browscap.ini');
        }

        return $this->parser;
    }

    public function rebuild()
    {
        $metadata = array();

        $parser = $this->getParser();
        $fileData = $parser->parse();

        $versionData = $fileData['GJK_Browscap_Version'];

        $metadata['version'] = $versionData['Version'];
        $metadata['released'] = $versionData['Released'];

        $metadata['filesizes'] = array();
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

    public function niceDelete($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public function writeArray($filename, $array)
    {
        $phpArray = var_export($array, true);
        file_put_contents($filename, "<?php\n\nreturn " . $phpArray . ";");
    }

    public function getKbSize($filename)
    {
        return round(filesize($filename) / 1024);
    }
}
