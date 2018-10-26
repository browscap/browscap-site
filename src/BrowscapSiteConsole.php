<?php
declare(strict_types=1);

namespace BrowscapSite;

use BrowscapSite\Tool\AnalyseStatistics;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        /** @var ContainerInterface $container */
        $container = require __DIR__ . '/../config/container.php';

        $dbConfig = $container->get('Config')['db'];

        $commands = [
            new Command\GenerateStatisticsCommand(new AnalyseStatistics(
                new \PDO($dbConfig['dsn'], $dbConfig['user'], $dbConfig['pass'])
            )),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
