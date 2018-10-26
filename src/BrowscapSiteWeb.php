<?php

namespace BrowscapSite;

class BrowscapSiteWeb
{
    public function defineServices()
    {
        $this['version.xml.controller'] =     function () {
            return new Controller\VersionXmlController($this->getFiles(), $this['metadata']);
        };
    }

    public function defineControllers()
    {
        $this->get('/version.xml', 'version.xml.controller:indexAction');
    }
}
