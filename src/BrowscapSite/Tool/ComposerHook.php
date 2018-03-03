<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use Browscap\Data\Factory\DataCollectionFactory;
use Composer\Script\Event;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Browscap\Generator\BuildGenerator;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Browscap\Writer\Factory\FullCollectionFactory;
use PackageVersions\Versions;

final class ComposerHook
{
    private const BUILD_DIRECTORY = __DIR__ . '/../../../vendor/build';
    private const RESOURCE_DIRECTORY = __DIR__ . '/../../../vendor/browscap/browscap/resources/';

    /**
     * @param Event $event
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public static function postInstall(Event $event): void
    {
        self::postUpdate($event);
    }

    /**
     * @param Event $event
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public static function postUpdate(Event $event): void
    {
        $installed = $event->getComposer()->getRepositoryManager()->getLocalRepository();

        $requiredPackage = 'browscap/browscap';

        $packages = $installed->findPackages($requiredPackage);

        if (!count($packages)) {
            throw new \Exception("The package {$requiredPackage} does not seem to be installed, and it is required.");
        }

        $package = reset($packages);
        $buildNumber = self::determineBuildNumberFromPackage($package);

        if (empty($buildNumber)) {
            throw new \Exception("Could not determine build number from package {$requiredPackage}");
        }

        $currentBuildNumber = self::getCurrentBuildNumber();
        if ($buildNumber !== $currentBuildNumber) {
            $event->getIO()->write(sprintf('<info>Generating new Browscap build: %s</info>', $buildNumber));
            self::createBuild($buildNumber, $event->getIO());
            $event->getIO()->write('<info>All done</info>');
        } else {
            $event->getIO()->write(sprintf('<info>Current build %s is up to date</info>', $currentBuildNumber));
        }
    }

    /**
     * Converts a package number e.g. 1.2.3 into a "build number" e.g. 1002003
     *
     * There are three digits for each version, so 001002003 becomes 1002003 when cast to int to drop the leading zeros
     *
     * @param string $version
     * @return int
     */
    private static function convertPackageVersionToBuildNumber(string $version): int
    {
        return (int)sprintf('%03d%03d%03d', ...explode('.', $version));
    }

    /**
     * Try to determine the build number from a composer package.
     *
     * @param \Composer\Package\PackageInterface $package
     * @return int
     * @throws \OutOfBoundsException
     */
    public static function determineBuildNumberFromPackage(PackageInterface $package): int
    {
        return self::convertPackageVersionToBuildNumber(Versions::getVersion($package->getName()));
    }

    /**
     * @return int|null
     */
    public static function getCurrentBuildNumber(): ?int
    {
        $metadataFile = self::BUILD_DIRECTORY . 'metadata.php';

        if (!file_exists($metadataFile)) {
            return null;
        }

        /** @noinspection PhpIncludeInspection */
        $metadata = require $metadataFile;
        return (int)$metadata['version'];
    }

    /**
     * Write a log message, if IO interface provided
     *
     * @param string $message
     * @param IOInterface|null $io
     */
    private static function log($message, IOInterface $io = null): void
    {
        if ($io) {
            $io->write($message);
        }
    }

    /**
     * Generate a build for build number specified.
     *
     * @param string $buildNumber
     * @param IOInterface|null $io
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    public static function createBuild($buildNumber, IOInterface $io = null): void
    {
        if (!file_exists(self::BUILD_DIRECTORY)
            && !mkdir(self::BUILD_DIRECTORY, 0775, true)
            && !is_dir(self::BUILD_DIRECTORY)
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', self::BUILD_DIRECTORY));
        }

        $logLevel = getenv('BC_BUILD_LOG') ?: Logger::NOTICE;

        $stream = new StreamHandler('php://output', $logLevel);
        $stream->setFormatter(new LineFormatter('%message%' . "\n"));

        $logger = new Logger('browscap');
        $logger->pushHandler($stream);
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

        self::log('  - Creating browscap build', $io);
        $buildGenerator = new BuildGenerator(
            self::RESOURCE_DIRECTORY,
            self::BUILD_DIRECTORY,
            $logger,
            (new FullCollectionFactory())->createCollection($logger, self::BUILD_DIRECTORY),
            new DataCollectionFactory($logger)
        );
        $buildGenerator->run($buildNumber);

        // Generate the metadata for the site
        self::log('  - Generating metadata', $io);
        $rebuilder = new Rebuilder(self::BUILD_DIRECTORY);
        $rebuilder->rebuild();

        // Updating browscap.ini cache
        self::log('  - Updating cache...');
        (new BrowscapPhpTool())->update();
    }
}
