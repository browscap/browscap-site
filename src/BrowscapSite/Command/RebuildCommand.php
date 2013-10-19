<?php

namespace BrowscapSite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Browscap\Parser\IniParser;

/**
 * @author James Titcumb <james@asgrim.com>
 */
class RebuildCommand extends Command
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('rebuild')
            ->setDescription('Rebuild site metadata and whatnot')
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$metadata = array();

    	$iniParser = new IniParser('./build/browscap.ini');
    	$fileData = $iniParser->parse();

    	$versionData = $fileData['GJK_Browscap_Version'];

    	$metadata['version'] = $versionData['Version'];
    	$metadata['released'] = $versionData['Released'];

    	$metadata['filesizes'] = array();
    	$metadata['filesizes']['BrowsCapINI'] = $this->getKbSize('./build/browscap.ini');
    	$metadata['filesizes']['Full_BrowsCapINI'] = $this->getKbSize('./build/full_asp_browscap.ini');
    	$metadata['filesizes']['PHP_BrowsCapINI'] = $this->getKbSize('./build/php_browscap.ini');
    	$metadata['filesizes']['Full_PHP_BrowsCapINI'] = $this->getKbSize('./build/full_php_browscap.ini');

    	$this->writeArray('./build/metadata.php', $metadata);

    	unlink('./cache/browscap.ini');
    	unlink('./cache/cache.php');
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
