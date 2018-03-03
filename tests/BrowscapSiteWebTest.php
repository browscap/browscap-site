<?php
declare(strict_types=1);

namespace BrowscapSiteTest;

use PHPUnit\Framework\TestCase;
use Silex\Application as SilexApp;
use BrowscapSite\BrowscapSiteWeb;

/**
 * @covers \BrowscapSite\BrowscapSiteWeb
 */
class BrowscapSiteWebTest extends TestCase
{
    public function testAppConstruct(): void
    {
        $app = new BrowscapSiteWeb();

        self::assertInstanceOf(SilexApp::class, $app);
    }

    public function testDefineControllers(): void
    {
        $app = new BrowscapSiteWeb();

        /** @var \Silex\ControllerCollection $controllerCollection */
        $controllerCollection = $app['controllers'];
        self::assertAttributeNotEmpty('controllers', $controllerCollection);
    }
}
