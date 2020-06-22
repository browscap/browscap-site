<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use Browscap\Data\Factory\DataCollectionFactory;
use Browscap\Generator\BuildGenerator as BrowscapBuildGenerator;
use Browscap\Parser\IniParser;
use Browscap\Writer\Factory\FullCollectionFactory;
use BrowscapSite\Metadata\ArrayMetadataBuilder;
use BrowscapSite\UserAgentTool\UserAgentTool;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function file_exists;
use function getenv;
use function is_dir;
use function mkdir;
use function sprintf;

/**
 * @codeCoverageIgnore
 */
final class BuildGeneratorFactory
{
    private const BUILD_DIRECTORY    = __DIR__ . '/../../vendor/build';
    private const RESOURCE_DIRECTORY = __DIR__ . '/../../vendor/browscap/browscap/resources/';

    /**
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): BuildGenerator
    {
        $logLevel = (int) (getenv('BC_BUILD_LOG') ?: Logger::NOTICE);

        $stream = new StreamHandler('php://output', $logLevel);
        $stream->setFormatter(new LineFormatter('%message%' . "\n"));

        $logger = new Logger('browscap');
        $logger->pushHandler($stream);
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

        if (
            ! file_exists(self::BUILD_DIRECTORY)
            && ! mkdir(self::BUILD_DIRECTORY, 0775, true)
            && ! is_dir(self::BUILD_DIRECTORY)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', self::BUILD_DIRECTORY));
        }

        return new BuildGenerator(
            self::BUILD_DIRECTORY,
            static function () use ($logger): BrowscapBuildGenerator {
                return new BrowscapBuildGenerator(
                    self::RESOURCE_DIRECTORY,
                    self::BUILD_DIRECTORY,
                    $logger,
                    (new FullCollectionFactory())->createCollection($logger, self::BUILD_DIRECTORY),
                    new DataCollectionFactory($logger)
                );
            },
            new ArrayMetadataBuilder(
                new IniParser(self::BUILD_DIRECTORY . '/browscap.ini'),
                self::BUILD_DIRECTORY
            ),
            new OcramiusDeterminePackageVersion(),
            new ComposerLockDeterminePackageReleaseDate(),
            $container->get(UserAgentTool::class)
        );
    }
}
