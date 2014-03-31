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
            return isset($this->config[$key]) ? $this->config[$key] : false;
        }
        else
        {
            return $this->config;
        }
    }

    /**
     * Get the HTTP request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this['request'];
    }

    public function defineServices()
    {
        $this->register(new ServiceControllerServiceProvider());

        $this['pdo'] = $this->share(function() {
            $dbConfig = $this->getConfig('db');
            return new \PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
        });

        $this['rateLimiter'] = $this->share(function() {
            $banConfiguration = $this->getConfig('rateLimiter');
            if (!$banConfiguration) {
                throw new \RuntimeException('Rate limit configuration not set');
            }
            return new Tool\RateLimiter($this['pdo'], $banConfiguration);
        });

        $this['metadata'] = $this->share(function() {
            return require_once(__DIR__ . '/../../build/metadata.php');
        });

        $this['downloads.controller'] = $this->share(function() {
            $banConfiguration = $this->getConfig('rateLimiter');
            if (!$banConfiguration) {
                throw new \RuntimeException('Rate limit configuration not set');
            }
            return new Controller\DownloadController($this, $this->getFiles(), $banConfiguration);
        });

        $this['stream.controller'] = $this->share(function() {
            $buildDirectory = __DIR__ . '/../../build/';
            return new Controller\StreamController($this, $this['rateLimiter'], $this->getFiles(), $buildDirectory);
        });

        $this['stats.controller'] = $this->share(function() {
            return new Controller\StatsController($this, $this['pdo']);
        });

        $this['version.controller'] = $this->share(function() {
            return new Controller\VersionController($this);
        });

        $this['version.number.controller'] = $this->share(function() {
            return new Controller\VersionNumberController($this);
        });

        $this['version.xml.controller'] = $this->share(function() {
            return new Controller\VersionXmlController($this->getFiles(), $this['metadata']);
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
        $this->get('/statistics', 'stats.controller:indexAction');
        $this->get('/stream', 'stream.controller:indexAction');
        $this->get('/version', 'version.controller:indexAction');
        $this->get('/version-number', 'version.number.controller:indexAction');
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
               'BrowsCapZIP' => array(
                   'name' => 'browscap.zip',
                   'size' => null,
                   'description' => 'Combines all the above files into one download that is smaller than all eight files put together.'
               ),
            ),
        );
    }
}
