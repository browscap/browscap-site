<?php

namespace BrowscapSite;

use Symfony\Component\Console\Application;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        $commands = array(
            new \BrowscapSite\Command\RebuildCommand(),
        );

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
