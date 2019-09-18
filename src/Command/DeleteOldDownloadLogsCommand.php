<?php
declare(strict_types=1);

namespace BrowscapSite\Command;

use BrowscapSite\Tool\DeleteOldDownloadLogs;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteOldDownloadLogsCommand extends Command
{
    /**
     * @var DeleteOldDownloadLogs
     */
    private $deleteOldDownloadLogs;

    public function __construct(DeleteOldDownloadLogs $deleteOldDownloadLogs)
    {
        $this->deleteOldDownloadLogs = $deleteOldDownloadLogs;
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
            ->setName('delete-old-download-logs')
            ->setDescription('Deletes old download logs to free up space in DB');
    }

    /**
     * (non-PHPdoc).
     * @see \Symfony\Component\Console\Command\Command::execute()
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());

        $io->write('<info>Deleting old download logs...</info>');

        $this->deleteOldDownloadLogs->__invoke();

        $io->write('<info>All done.</info>');
    }
}
