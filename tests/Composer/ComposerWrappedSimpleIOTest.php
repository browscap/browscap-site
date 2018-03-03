<?php
declare(strict_types=1);

namespace BrowscapSiteTest\Composer;

use BrowscapSite\Composer\ComposerWrappedSimpleIO;
use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BrowscapSite\Composer\ComposerWrappedSimpleIO
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
