<?php

namespace BrowscapSiteTest\Command;

use PHPUnit_Framework_TestCase;
use BrowscapSite\Command\AutobuildCommand;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * @covers \BrowscapSite\Command\AutobuildCommand
 */
class AutobuildCommandTest extends PHPUnit_Framework_TestCase
{
    public function testConfigureSetsUpCommand()
    {
        /** @var AutobuildCommand $command */
        $command = $this->getMockBuilder(AutobuildCommand::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $command->setDefinition(new InputDefinition());

        $this->assertEmpty($command->getName());

        $command->configure();

        $this->assertSame('autobuild', $command->getName());
    }
}
