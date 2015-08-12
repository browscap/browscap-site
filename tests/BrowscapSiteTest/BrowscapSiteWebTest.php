<?php

namespace BrowscapSiteTest;

use PHPUnit_Framework_TestCase;
use Silex\Application as SilexApp;
use BrowscapSite\BrowscapSiteWeb;

/**
 * @covers \BrowscapSite\BrowscapSiteWeb
 */
class BrowscapSiteWebTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \BrowscapSite\BrowscapSiteWeb::__construct
     */
    public function testAppConstruct()
    {
        $app = new BrowscapSiteWeb();

        $this->assertInstanceOf(SilexApp::class, $app);
    }

    /**
     * @covers \BrowscapSite\BrowscapSiteWeb::defineControllers
     */
    public function testDefineControllers()
    {
        $app = new BrowscapSiteWeb();

        /** @var \Silex\ControllerCollection $controllerCollection */
        $controllerCollection = $app['controllers'];
        $this->assertAttributeNotEmpty('controllers', $controllerCollection);
    }
}
