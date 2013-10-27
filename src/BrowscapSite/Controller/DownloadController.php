<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;

class DownloadController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
    }

    public function indexAction()
    {
        $metadata = $this->getMetadata();
        $files = $this->getFiles();

        $this->mergeMetadataToFiles($metadata, $files);

        $baseHost = 'http://' . $_SERVER['SERVER_NAME'];

        return $this->app['twig']->render('downloads.html', array(
        	'files' => $files,
            'version' => $metadata['version'],
            'baseHost' => $baseHost,
        ));
    }

    public function mergeMetadataToFiles($metadata, &$files)
    {
        $files['asp']['BrowsCapINI']['size'] = $metadata['filesizes']['BrowsCapINI'];
        $files['asp']['Full_BrowsCapINI']['size'] = $metadata['filesizes']['Full_BrowsCapINI'];
        $files['asp']['Lite_BrowsCapINI']['size'] = $metadata['filesizes']['Lite_BrowsCapINI'];
        $files['php']['PHP_BrowsCapINI']['size'] = $metadata['filesizes']['PHP_BrowsCapINI'];
        $files['php']['Full_PHP_BrowsCapINI']['size'] = $metadata['filesizes']['Full_PHP_BrowsCapINI'];
        $files['php']['Lite_PHP_BrowsCapINI']['size'] = $metadata['filesizes']['Lite_PHP_BrowsCapINI'];
    }

    public function getMetadata()
    {
        return require_once(__DIR__ . '/../../../build/metadata.php');
    }

    public function getFiles()
    {
        return array(
        	'asp' => array(
        	   'BrowsCapINI' => array(
        	       'name' => 'browscap.ini',
        	       'size' => null,
        	       'description' => 'The standard version of browscap.ini file for IIS 5.x and greater.'
        	    ),
        	   'Full_BrowsCapINI' => array(
        	       'name' => 'full_asp_browscap.ini',
        	       'size' => null,
        	       'description' => 'A larger version of browscap.ini with all the new properties.'
        	   ),
        	   'Lite_BrowsCapINI' => array(
        	       'name' => 'lite_asp_browscap.ini',
        	       'size' => null,
        	       'description' => 'A smaller version of browscap.ini file containing major browsers & search engines. This file is adequate for most websites.'
        	   ),
            ),
        	'php' => array(
        	   'PHP_BrowsCapINI' => array(
        	       'name' => 'php_browscap.ini',
        	       'size' => null,
        	       'description' => 'A special version of browscap.ini for PHP users only!'
        	    ),
        	   'Full_PHP_BrowsCapINI' => array(
        	       'name' => 'full_php_browscap.ini',
        	       'size' => null,
        	       'description' => 'A larger version of php_browscap.ini with all the new properties.'
        	   ),
        	   'Lite_PHP_BrowsCapINI' => array(
        	       'name' => 'lite_php_browscap.ini',
        	       'size' => null,
        	       'description' => 'A smaller version of php_browscap.ini file containing major browsers & search engines. This file is adequate for most websites.'
        	   ),
            ),
        );
    }
}
