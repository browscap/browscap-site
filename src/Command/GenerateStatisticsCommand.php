<?php
declare(strict_types=1);

namespace BrowscapSite\Command;

use BrowscapSite\Tool\AnalyseStatistics;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateStatisticsCommand extends Command
{
    /**
     * @var AnalyseStatistics
     */
    private $analyseStatistics;

    public function __construct(AnalyseStatistics $analyseStatistics)
    {
        $this->analyseStatistics = $analyseStatistics;
        parent::__construct();
    }

    /**
     * (non-PHPdoc).
     * @see \Symfony\Component\Console\Command\Command::configure()
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    public function configure()
    {
        $this
            ->setName('generate-statistics')
            ->setDescription('Generate statistics into data tables');
    }

    /**
     * (non-PHPdoc).
     * @see \Symfony\Component\Console\Command\Command::execute()
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());

        $io->write('<info>Generating statistics...</info>');

        $this->analyseStatistics->__invoke();

        $io->write('<info>All done.</info>');
    }
}
