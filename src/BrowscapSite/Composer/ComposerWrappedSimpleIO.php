<?php
declare(strict_types=1);

namespace BrowscapSite\Composer;

use Composer\IO\IOInterface;

final class ComposerWrappedSimpleIO implements SimpleIOInterface
{
    /**
     * @var IOInterface
     */
    private $composerIo;

    public function __construct(IOInterface $composerIo)
    {
        $this->composerIo = $composerIo;
    }

    public function write(string $message) : void
    {
        $this->composerIo->write($message);
    }
}
