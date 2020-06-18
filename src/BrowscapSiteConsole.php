<?php

declare(strict_types=1);

namespace BrowscapSite;

use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\Tool\AnalyseStatistics;
use BrowscapSite\Tool\DeleteOldDownloadLogs;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

use function assert;

class BrowscapSiteConsole extends Application
{
    public function __construct()
    {
        parent::__construct('Browscap Website', 'dev-master');

        $container = require __DIR__ . '/../config/container.php';
        assert($container instanceof ContainerInterface);

        $commands = [
            new Command\GenerateStatisticsCommand($container->get(AnalyseStatistics::class)),
            new Command\GenerateBuild($container->get(BuildGenerator::class)),
            new Command\DeleteOldDownloadLogsCommand($container->get(DeleteOldDownloadLogs::class)),
        ];

        foreach ($commands as $command) {
            /** @noinspection UnusedFunctionResultInspection */
            $this->add($command);
        }
    }
}
