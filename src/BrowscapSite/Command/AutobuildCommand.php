<?php

namespace BrowscapSite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use BrowscapSite\Tool\ComposerHook;
use Composer\IO\ConsoleIO;

/**
 * @author James Titcumb <james@asgrim.com>
 */
class AutobuildCommand extends Command
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    public function configure()
    {
        $this
            ->setName('autobuild')
            ->setDescription('Combines browscap build and metadata build')
            ->addArgument('version', InputArgument::REQUIRED, "Version number to apply");
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());

        $version = $input->getArgument('version');

        $io->write('<info>Generating build for version ' . $version . '</info>');

        ComposerHook::createBuild($version, $io);

        $io->write('<info>All done.</info>');
    }
}
