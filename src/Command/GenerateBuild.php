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
    /** @var BuildGenerator */
    private $buildGenerator;

    public function __construct(BuildGenerator $buildGenerator)
    {
        parent::__construct();
        $this->buildGenerator = $buildGenerator;
    }

    public function configure()
    {
        $this
            ->setName('generate-build')
            ->setDescription('Generate the browscap build and cache');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->buildGenerator->__invoke(
            new SymfonyConsoleWrappedSimpleIO($output)
        );
    }
}
