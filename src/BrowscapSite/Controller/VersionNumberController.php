<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;

class VersionNumberController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
    }

    public function indexAction()
    {
        $metadata = $this->app['metadata'];

        return $this->app['twig']->render('version-number.html', array(
            'version' => $metadata['version'],
        ));
    }
}
