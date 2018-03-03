<?php
declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use Browscap\Data\Factory\DataCollectionFactory;
use Browscap\Generator\BuildGenerator;
use Browscap\Parser\IniParser;
use Browscap\Writer\Factory\FullCollectionFactory;
use BrowscapSite\Metadata\ArrayMetadataBuilder;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @codeCoverageIgnore
 */
final class UpdateBrowscapVersionUsedFactory
{
    private const BUILD_DIRECTORY = __DIR__ . '/../../../vendor/build';
    private const RESOURCE_DIRECTORY = __DIR__ . '/../../../vendor/browscap/browscap/resources/';

    /**
     * @return UpdateBrowscapVersionUsed
     * @throws \Exception
     */
    public function __invoke(): UpdateBrowscapVersionUsed
    {
        $logLevel = getenv('BC_BUILD_LOG') ?: Logger::NOTICE;

        $stream = new StreamHandler('php://output', $logLevel);
        $stream->setFormatter(new LineFormatter('%message%' . "\n"));

        $logger = new Logger('browscap');
        $logger->pushHandler($stream);
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

        return new UpdateBrowscapVersionUsed(
            self::BUILD_DIRECTORY,
            new BuildGenerator(
                self::BUILD_DIRECTORY,
                self::RESOURCE_DIRECTORY,
                $logger,
                (new FullCollectionFactory())->createCollection($logger, self::BUILD_DIRECTORY),
                new DataCollectionFactory($logger)
            ),
            new ArrayMetadataBuilder(
                new IniParser(self::BUILD_DIRECTORY . '/browscap.ini'),
                self::BUILD_DIRECTORY
            ),
            new OcramiusDeterminePackageVersion()
        );
    }
}
