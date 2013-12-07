<?php

namespace BrowscapSite\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BrowscapSite\Tool\Rebuilder;

/**
 * @author James Titcumb <james@asgrim.com>
 */
class RebuildCommand extends Command
{
    protected $rebuilder;

    public function __construct(Rebuilder $rebuilder)
    {
        $this->rebuilder = $rebuilder;

        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('rebuild')
            ->setDescription('Rebuild site metadata and whatnot');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rebuilder->rebuild();
    }
}
