<?php

declare(strict_types=1);

namespace BrowscapSite\Command;

use BrowscapSite\Tool\AnalyseStatistics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateStatisticsCommand extends Command
{
    public function __construct(private AnalyseStatistics $analyseStatistics)
    {
        parent::__construct();
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     *
     * @throws InvalidArgumentException
     */
    public function configure(): void
    {
        $this
            ->setName('generate-statistics')
            ->setDescription('Generate statistics into data tables');
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Generating statistics...</info>');

        $this->analyseStatistics->__invoke();

        $output->writeln('<info>All done.</info>');

        return 0;
    }
}
