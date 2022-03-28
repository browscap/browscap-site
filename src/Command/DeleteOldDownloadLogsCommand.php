<?php

declare(strict_types=1);

namespace BrowscapSite\Command;

use BrowscapSite\Tool\DeleteOldDownloadLogs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteOldDownloadLogsCommand extends Command
{
    private DeleteOldDownloadLogs $deleteOldDownloadLogs;

    public function __construct(DeleteOldDownloadLogs $deleteOldDownloadLogs)
    {
        $this->deleteOldDownloadLogs = $deleteOldDownloadLogs;
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
            ->setName('delete-old-download-logs')
            ->setDescription('Deletes old download logs to free up space in DB')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'older-than-months',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'How many months to keep',
                        12
                    ),
                ])
            );
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Deleting old download logs...</info>');

        $olderThanMonths = (int) $input->getOption('older-than-months');
        if ($olderThanMonths <= 0) {
            $olderThanMonths = 12;
        }

        $this->deleteOldDownloadLogs->__invoke($olderThanMonths);

        $output->writeln('<info>All done.</info>');

        return 0;
    }
}
