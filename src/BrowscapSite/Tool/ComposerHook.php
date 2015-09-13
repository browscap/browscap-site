<?php

namespace BrowscapSite\Tool;

use Composer\Script\Event;
use Composer\Package\PackageInterface;
use Composer\IO\IOInterface;
use Browscap\Generator\BuildGenerator;
use Browscap\Helper\CollectionCreator;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Browscap\Writer\Factory\FullCollectionFactory;

class ComposerHook
{
    public static function postInstall(Event $event)
    {
        self::postUpdate($event);
    }

    /**
     * @param Event $event
     * @throws \Exception
     */
    public static function postUpdate(Event $event)
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
        if ($buildNumber != $currentBuildNumber) {
            $event->getIO()->write(sprintf('<info>Generating new Browscap build: %s</info>', $buildNumber));
            self::createBuild($buildNumber, $event->getIO());
            $event->getIO()->write(sprintf('<info>All done</info>', $buildNumber));
        } else {
            $event->getIO()->write(sprintf('<info>Current build %s is up to date</info>', $currentBuildNumber));
        }
    }

    /**
     * Try to determine the build number from a composer package.
     *
     * @param \Composer\Package\PackageInterface $package
     * @return string
     */
    public static function determineBuildNumberFromPackage(PackageInterface $package)
    {
        if ($package->isDev()) {
            $buildNumber = self::determineBuildNumberFromBrowscapBuildFile();

            if (is_null($buildNumber)) {
                $buildNumber = substr($package->getSourceReference(), 0, 8);
            }
        } else {
            $installedVersion = $package->getPrettyVersion();

            // SemVer supports build numbers, but fall back to just using
            // version number if not available; at time of writing, composer
            // did not support SemVer 2.0.0 build numbers fully:
            // @see https://github.com/composer/composer/issues/2422
            $plusPos = strpos($installedVersion, '+');
            if ($plusPos !== false) {
                $buildNumber = substr($installedVersion, ($plusPos + 1));
            } else {
                $buildNumber = self::determineBuildNumberFromBrowscapBuildFile();

                if (is_null($buildNumber)) {
                    $buildNumber = $installedVersion;
                }
            }
        }

        return $buildNumber;
    }

    /**
     * This is a temporary fallback until Composer supports SemVer 2.0.0 properly.
     *
     * @return string|NULL
     */
    public static function determineBuildNumberFromBrowscapBuildFile()
    {
        $buildFile = __DIR__ . '/../../../vendor/browscap/browscap/BUILD_NUMBER';

        if (file_exists($buildFile)) {
            $buildNumber = file_get_contents($buildFile);
            return trim($buildNumber);
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public static function getCurrentBuildNumber()
    {
        $buildFolder = __DIR__ . '/../../../build/';
        $metadataFile = $buildFolder . 'metadata.php';

        if (file_exists($metadataFile)) {
            $metadata = require $metadataFile;
            return $metadata['version'];
        } else {
            return null;
        }
    }

    /**
     * @param string $buildNumber
     */
    private static function moveSymlink($buildNumber)
    {
        $buildLink = __DIR__ . '/../../../build';

        if (file_exists($buildLink) && !is_link($buildLink)) {
            throw new \RuntimeException("Build folder '{$buildLink}' was not a symbolic link");
        } elseif (file_exists($buildLink) && is_link($buildLink)) {
            unlink($buildLink);
        }

        $target = realpath($buildLink . '-' . $buildNumber);
        if (!symlink($target, $buildLink)) {
            throw new \RuntimeException("Unable to create symbolic link for target '{$target}'");
        }
    }

    /**
     * Generate a build for build number specified.
     *
     * @param string $buildNumber
     */
    public static function createBuild($buildNumber, IOInterface $io = null)
    {
        $buildFolder = __DIR__ . '/../../../build-' . $buildNumber . '/';
        $resourceFolder = __DIR__ . '/../../../vendor/browscap/browscap/resources/';

        if (!file_exists($buildFolder)) {
            if ($io) {
                $io->write('  - Creating build folder');
            }
            mkdir($buildFolder, 0775, true);
        }

        $logLevel = getenv('BC_BUILD_LOG') ? getenv('BC_BUILD_LOG') : Logger::NOTICE;
        if ($io) {
            $io->write('  - Log level set to ' . $logLevel);
        }

        // Create a logger
        if ($io) {
            $io->write('  - Setting up logging');
        }
        $stream = new StreamHandler('php://output', $logLevel);
        $stream->setFormatter(new LineFormatter('%message%' . "\n"));

        $logger = new Logger('browscap');
        $logger->pushHandler($stream);
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

        $collectionCreator = new CollectionCreator();

        if ($io) {
            $io->write('  - Creating writer collection');
        }
        $writerCollectionFactory = new FullCollectionFactory();
        $writerCollection        = $writerCollectionFactory->createCollection($logger, $buildFolder);

        // Generate the actual browscap.ini files
        if ($io) {
            $io->write('  - Creating actual build');
        }
        $buildGenerator = new BuildGenerator($resourceFolder, $buildFolder);
        $buildGenerator
            ->setLogger($logger)
            ->setCollectionCreator($collectionCreator)
            ->setWriterCollection($writerCollection)
            ->run($buildNumber)
        ;

        // Generate the metadata for the site
        if ($io) {
            $io->write('  - Generating metadata');
        }
        $rebuilder = new Rebuilder($buildFolder);
        $rebuilder->rebuild();

        // Update the symlink
        if ($io) {
            $io->write('  - Updating symlink to point to ' . $buildNumber);
        }
        self::moveSymlink($buildNumber);
    }
}
