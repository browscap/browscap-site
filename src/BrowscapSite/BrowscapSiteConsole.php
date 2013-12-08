<?php

namespace BrowscapSite;

use Symfony\Component\Console\Application;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        $commands = array(
            new Command\RebuildCommand(new Tool\Rebuilder(__DIR__ . '/../../build/')),
        );

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
