<?php
declare(strict_types=1);

namespace BrowscapSite\Composer;

use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleWrappedSimpleIO implements SimpleIOInterface
{
    /**
     * @var OutputInterface
     */
    private $consoleOutput;

    public function __construct(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    public function write(string $message) : void
    {
        $this->consoleOutput->writeln($message);
    }
}
