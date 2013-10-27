<?php

namespace BrowscapSite;

use Silex\Application as SilexApplication;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use BrowscapSite\Controller\DownloadController;
use BrowscapSite\Controller\StreamController;
use BrowscapSite\Controller\VersionController;
use BrowscapSite\Controller\UserAgentLookupController;

class BrowscapSiteWeb extends SilexApplication
{
	public function __construct()
	{
		parent::__construct();

        //$this['debug'] = true;

		$this->defineServices();
		$this->defineControllers();
	}

	public function defineServices()
	{
	    $this->register(new ServiceControllerServiceProvider());

	    $this['downloads.controller'] = $this->share(function() {
	    	return new DownloadController($this);
	    });

	    $this['stream.controller'] = $this->share(function() {
	    	return new StreamController();
	    });

	    $this['version.controller'] = $this->share(function() {
	    	return new VersionController($this);
	    });

	    $this['ualookup.controller'] = $this->share(function() {
	    	return new UserAgentLookupController($this);
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
	    $this->get('/ua-lookup', 'ualookup.controller:indexAction');
	    $this->post('/ua-lookup', 'ualookup.controller:indexAction');
	}
}
