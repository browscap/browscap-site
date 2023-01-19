<?php

declare(strict_types=1);

namespace BrowscapSite\Command;

use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\SimpleIO\SymfonyConsoleWrappedSimpleIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateBuild extends Command
{
    public function __construct(private BuildGenerator $buildGenerator)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('generate-build')
            ->setDescription('Generate the browscap build and cache');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->buildGenerator->__invoke(
            new SymfonyConsoleWrappedSimpleIO($output),
        );

        return 0;
    }
}
