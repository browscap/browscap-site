<?php

namespace BrowscapSite;

class BrowscapSiteWeb
{
    public function defineServices()
    {
        $this['version.number.controller'] =     function () {
            return new Controller\VersionNumberController($this);
        };

        $this['version.xml.controller'] =     function () {
            return new Controller\VersionXmlController($this->getFiles(), $this['metadata']);
        };
    }

    public function defineControllers()
    {
        $this->get('/version-number', 'version.number.controller:indexAction');
        $this->get('/version.xml', 'version.xml.controller:indexAction');
    }
}
