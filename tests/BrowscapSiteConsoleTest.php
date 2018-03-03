<?php
declare(strict_types=1);

namespace BrowscapSiteTest;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use BrowscapSite\BrowscapSiteConsole;
use Symfony\Component\Console\Command\Command;

/**
 * @covers \BrowscapSite\BrowscapSiteConsole
 */
class BrowscapSiteConsoleTest extends TestCase
{
    private function assertAppHasCommand(Application $app, string $command): void
    {
        $cmdObject = $app->get($command);

        self::assertInstanceOf(Command::class, $cmdObject);
        self::assertSame($command, $cmdObject->getName());
    }

    public function testApplication()
    {
        $app = new BrowscapSiteConsole();

        $this->assertAppHasCommand($app, 'generate-statistics');
    }
}
