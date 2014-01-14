<?php

namespace BrowscapSite;

use Silex\Application as SilexApplication;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use BrowscapSite\Controller;

class BrowscapSiteWeb extends SilexApplication
{
    protected $config;

    public function __construct()
    {
        parent::__construct();

        $this->config = require(__DIR__ . '/../../config/config.php');

        if ($this->getConfig('debug'))
        {
            $this['debug'] = true;
        }

        $this->defineServices();
        $this->defineControllers();
    }

    public function getConfig($key = null)
    {
        if (!empty($key))
        {
            return $this->config[$key];
        }
        else
        {
            return $this->config;
        }
    }

    public function defineServices()
    {
        $this->register(new ServiceControllerServiceProvider());

        $this['pdo'] = $this->share(function() {
            $dbConfig = $this->getConfig('db');
            return new \PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
        });

        $this['downloads.controller'] = $this->share(function() {
            return new Controller\DownloadController($this, $this->getFiles());
        });

        $this['stream.controller'] = $this->share(function() {
            return new Controller\StreamController($this['pdo']);
        });

        $this['version.controller'] = $this->share(function() {
            return new Controller\VersionController($this);
        });

        $this['version.xml.controller'] = $this->share(function() {
            return new Controller\VersionXmlController($this->getFiles());
        });

        $this['ualookup.controller'] = $this->share(function() {
            return new Controller\UserAgentLookupController($this);
        });

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../../views',
        ));
    }

    public function defineControllers()
    {
        $this->get('/', 'downloads.controller:indexAction');
        $this->get('/stream', 'stream.controller:indexAction');
        $this->get('/version', 'version.controller:indexAction');
        $this->get('/version.xml', 'version.xml.controller:indexAction');
        $this->get('/ua-lookup', 'ualookup.controller:indexAction');
        $this->post('/ua-lookup', 'ualookup.controller:indexAction');
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
            'other' => array(
               'BrowsCapXML' => array(
                   'name' => 'browscap.xml',
                   'size' => null,
                   'description' => 'The standard version of browscap.ini file in XML format.'
                ),
               'BrowsCapCSV' => array(
                   'name' => 'browscap.csv',
                   'size' => null,
                   'description' => 'An industry-standard comma-separated-values version of browscap.ini. Easily imported into Access, Excel, MySQL & others.'
               ),
            ),
        );
    }
}
