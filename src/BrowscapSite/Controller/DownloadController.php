<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;

class DownloadController
{
    protected $app;
    protected $fileList;

    public function __construct(BrowscapSiteWeb $app, array $fileList)
    {
        $this->app = $app;
        $this->fileList = $fileList;
    }

    public function indexAction()
    {
        $metadata = $this->app['metadata'];

        $this->mergeMetadataToFiles($metadata, $this->fileList);

        $baseHost = 'http://' . $_SERVER['SERVER_NAME'];

        $releaseDate = new \DateTime($metadata['released']);

        return $this->app['twig']->render('downloads.html', array(
            'files' => $this->fileList,
            'version' => $metadata['version'],
            'releaseDate' => $releaseDate->format('jS M Y'),
            'baseHost' => $baseHost,
        ));
    }

    public function mergeMetadataToFiles($metadata, &$files)
    {
        $files['asp']['BrowsCapINI']['size'] = number_format($metadata['filesizes']['BrowsCapINI']);
        $files['asp']['Full_BrowsCapINI']['size'] = number_format($metadata['filesizes']['Full_BrowsCapINI']);
        $files['asp']['Lite_BrowsCapINI']['size'] = number_format($metadata['filesizes']['Lite_BrowsCapINI']);
        $files['php']['PHP_BrowsCapINI']['size'] = number_format($metadata['filesizes']['PHP_BrowsCapINI']);
        $files['php']['Full_PHP_BrowsCapINI']['size'] = number_format($metadata['filesizes']['Full_PHP_BrowsCapINI']);
        $files['php']['Lite_PHP_BrowsCapINI']['size'] = number_format($metadata['filesizes']['Lite_PHP_BrowsCapINI']);
        $files['other']['BrowsCapXML']['size'] = number_format($metadata['filesizes']['BrowsCapXML']);
        $files['other']['BrowsCapCSV']['size'] = number_format($metadata['filesizes']['BrowsCapCSV']);
    }
}
