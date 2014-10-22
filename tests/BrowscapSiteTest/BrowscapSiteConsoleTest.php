<?php

namespace BrowscapSiteTest;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use BrowscapSite\BrowscapSiteConsole;

class BrowscapSiteConsoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $command
     */
    private function assertAppHasCommand(Application $app, $command)
    {
        $cmdObject = $app->get($command);

        self::assertInstanceOf('Symfony\Component\Console\Command\Command', $cmdObject);
        self::assertSame($command, $cmdObject->getName());
    }

    public function testApplication()
    {
        $app = new BrowscapSiteConsole();

        $this->assertAppHasCommand($app, 'rebuild');
        $this->assertAppHasCommand($app, 'autobuild');
    }
}
