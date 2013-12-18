<?php

namespace BrowscapSite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use BrowscapSite\Tool\ComposerHook;

/**
 * @author James Titcumb <james@asgrim.com>
 */
class AutobuildCommand extends Command
{
    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getArgument('version');
        ComposerHook::createBuild($version);
    }
}
