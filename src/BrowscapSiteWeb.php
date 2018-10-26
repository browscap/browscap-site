<?php

namespace BrowscapSite;

class BrowscapSiteWeb
{
    public function defineServices()
    {
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
    }

    public function defineControllers()
    {
        $this->get('/statistics', 'stats.controller:indexAction');
        $this->get('/version', 'version.controller:indexAction');
        $this->get('/version-number', 'version.number.controller:indexAction');
        $this->get('/version.xml', 'version.xml.controller:indexAction');
    }
}
