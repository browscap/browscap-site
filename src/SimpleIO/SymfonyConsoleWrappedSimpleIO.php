<?php
declare(strict_types=1);

namespace BrowscapSite\SimpleIO;

use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleWrappedSimpleIO implements SimpleIOInterface
{
    private OutputInterface $consoleOutput;

    public function __construct(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    public function write(string $message) : void
    {
        $this->consoleOutput->writeln($message);
    }
}
