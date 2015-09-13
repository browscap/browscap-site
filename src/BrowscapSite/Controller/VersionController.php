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
        $metadata = $this->app['metadata'];

        return $this->app['twig']->render('version.html', [
            'released' => $metadata['released'],
        ]);
    }
}
