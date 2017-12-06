<?php

namespace BrowscapSite;

use Assert\Assert;
use Silex\Application as SilexApplication;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class BrowscapSiteWeb extends SilexApplication
{
    protected $config;

    public function __construct()
    {
        parent::__construct();

        $this->config = require __DIR__ . '/../../config/config.php';

        if ($this->getConfig('debug')) {
            $this['debug'] = true;
        }

        $this->defineServices();
        $this->defineControllers();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key)
    {
        Assert::that($this->config)->keyExists($key);

        return $this->config[$key];
    }

    /**
     * Get the HTTP request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        /** @var RequestStack $stack */
        $stack = $this['request_stack'];
        return $stack->getCurrentRequest();
    }

    public function defineServices()
    {
        $this->register(new ServiceControllerServiceProvider());

        $this['pdo'] = function () {
            $dbConfig = $this->getConfig('db');
            return new \PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass']);
        };

        $this['rateLimiter'] = function () {
            $banConfiguration = $this->getConfig('rateLimiter');
            if (!$banConfiguration) {
                throw new \RuntimeException('Rate limit configuration not set');
            }
            return new Tool\RateLimiter($this['pdo'], $banConfiguration);
        };

        $this['metadata'] = function () {
            return require __DIR__ . '/../../vendor/build/metadata.php';
        };

        $this['downloads.controller'] = function () {
            $banConfiguration = $this->getConfig('rateLimiter');
            if (!$banConfiguration) {
                throw new \RuntimeException('Rate limit configuration not set');
            }
            return new Controller\DownloadController($this, $this->getFiles(), $banConfiguration);
        };

        $this['stream.controller'] =     function () {
            $buildDirectory = __DIR__ . '/../../vendor/build/';
            return new Controller\StreamController(
                $this['rateLimiter'],
                $this->getFiles(),
                $this['metadata'],
                $buildDirectory
            );
        };

        $this['stats.controller'] =     function () {
            return new Controller\StatsController($this, $this['pdo']);
        };

        $this['version.controller'] =     function () {
            return new Controller\VersionController($this);
        };

        $this['version.number.controller'] =     function () {
            return new Controller\VersionNumberController($this);
        };

        $this['version.xml.controller'] =     function () {
            return new Controller\VersionXmlController($this->getFiles(), $this['metadata']);
        };

        $this['ualookup.controller'] =     function () {
            return new Controller\UserAgentLookupController($this);
        };

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/../../views',
        ]);
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
        return [
            'asp' => [
                'BrowsCapINI' => [
                    'name' => 'browscap.ini',
                    'size' => null,
                    'description' => 'This is the standard version of browscap.ini file for IIS 5.x and greater.',
                ],
                'Full_BrowsCapINI' => [
                    'name' => 'full_asp_browscap.ini',
                    'size' => null,
                    'description' => 'This is a larger version of browscap.ini with all the new properties.',
                ],
                'Lite_BrowsCapINI' => [
                    'name' => 'lite_asp_browscap.ini',
                    'size' => null,
                    'description' => 'This is a smaller version of browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                ],
            ],
            'php' => [
                'PHP_BrowsCapINI' => [
                    'name' => 'php_browscap.ini',
                    'size' => null,
                    'description' => 'This is a special version of browscap.ini for PHP users only!',
                ],
                'Full_PHP_BrowsCapINI' => [
                    'name' => 'full_php_browscap.ini',
                    'size' => null,
                    'description' => 'This is a larger version of php_browscap.ini with all the new properties.',
                ],
                'Lite_PHP_BrowsCapINI' => [
                    'name' => 'lite_php_browscap.ini',
                    'size' => null,
                    'description' => 'This is a smaller version of php_browscap.ini file containing major browsers & search engines. This file is adequate for most websites.',
                ],
            ],
            'other' => [
                'BrowsCapXML' => [
                    'name' => 'browscap.xml',
                    'size' => null,
                    'description' => 'This is the standard version of browscap.ini file in XML format.',
                ],
                'BrowsCapCSV' => [
                    'name' => 'browscap.csv',
                    'size' => null,
                    'description' => 'This is an industry-standard comma-separated-values version of browscap.ini. Easily imported into Access, Excel, MySQL & others.',
                ],
                'BrowsCapJSON' => [
                    'name' => 'browscap.json',
                    'size' => null,
                    'description' => 'This is a JSON (JavaScript Object Notation) version of browscap.ini. This is usually used with JavaScript.',
                ],
                'BrowsCapZIP' => [
                    'name' => 'browscap.zip',
                    'size' => null,
                    'description' => 'This archive combines all the above files into one download that is smaller than all eight files put together.',
                ],
            ],
        ];
    }
}
