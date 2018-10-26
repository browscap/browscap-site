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

        $this->config = require __DIR__ . '/../config/config.php';

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

        $this['downloads.controller'] = function () {
            $banConfiguration = $this->getConfig('rateLimiter');
            if (!$banConfiguration) {
                throw new \RuntimeException('Rate limit configuration not set');
            }
            return new Controller\DownloadController($this, $this->getFiles(), $banConfiguration);
        };

        $this['stream.controller'] =     function () {
            $buildDirectory = __DIR__ . '/../vendor/build/';
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
            return new Controller\UserAgentLookupHandler($this);
        };

        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__ . '/../views',
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
        return ;
    }
}
