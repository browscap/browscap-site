<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;

class VersionController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
    }

    public function indexAction()
    {
        $metadata = $this->getMetadata();

        return $this->app['twig']->render('version.html', array(
        	'released' => $metadata['released'],
        ));
    }

    public function getMetadata()
    {
        return require_once(__DIR__ . '/../../../build/metadata.php');
    }
}
