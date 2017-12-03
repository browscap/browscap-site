<?php
declare(strict_types=1);

namespace BrowscapSiteTest\Command;

use PHPUnit\Framework\TestCase;
use BrowscapSite\Command\AutobuildCommand;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * @covers \BrowscapSite\Command\AutobuildCommand
 */
class AutobuildCommandTest extends TestCase
{
    public function testConfigureSetsUpCommand(): void
    {
        /** @var AutobuildCommand $command */
        $command = $this->getMockBuilder(AutobuildCommand::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $command->setDefinition(new InputDefinition());

        self::assertEmpty($command->getName());

        $command->configure();

        self::assertSame('autobuild', $command->getName());
    }
}
