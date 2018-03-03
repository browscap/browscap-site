<?php
declare(strict_types=1);

namespace BrowscapSite;

use BrowscapSite\Tool\AnalyseStatistics;
use Symfony\Component\Console\Application;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        // @todo Config should come from a shared container, this is terrible
        $config = require __DIR__ . '/../../config/config.php';

        $commands = [
            new Command\GenerateStatisticsCommand(new AnalyseStatistics(
                new \PDO($config['db']['dsn'], $config['db']['user'], $config['db']['pass'])
            )),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
