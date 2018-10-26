<?php
declare(strict_types=1);

namespace BrowscapSiteTest\Composer;

use BrowscapSite\SimpleIO\ComposerWrappedSimpleIO;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BrowscapSite\SimpleIO\ComposerWrappedSimpleIO
 */
final class ComposerWrappedSimpleIOTest extends TestCase
{
    public function testComposerIOIsDelegated()
    {
        $message = uniqid('message', true);

        $composerIo = $this->createMock(IOInterface::class);
        $composerIo->expects(self::once())->method('write')->with($message);

        $delegator = new ComposerWrappedSimpleIO($composerIo);
        $delegator->write($message);
    }
}
