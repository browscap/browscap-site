<?php
declare(strict_types=1);

namespace BrowscapSite;

use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\Tool\AnalyseStatistics;
use BrowscapSite\Tool\DeleteOldDownloadLogs;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        /** @var ContainerInterface $container */
        $container = require __DIR__ . '/../config/container.php';

        $commands = [
            new Command\GenerateStatisticsCommand($container->get(AnalyseStatistics::class)),
            new Command\GenerateBuild($container->get(BuildGenerator::class)),
            new Command\DeleteOldDownloadLogsCommand($container->get(DeleteOldDownloadLogs::class)),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
