<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

use Assert\Assert;
use Browscap\Data\Factory\DataCollectionFactory;
use Browscap\Generator\BuildGenerator;
use Browscap\Parser\IniParser;
use Browscap\Writer\Factory\FullCollectionFactory;
use BrowscapSite\Metadata\ArrayMetadataBuilder;
use BrowscapSite\Metadata\MetadataBuilder;
use Composer\IO\IOInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PackageVersions\Versions;

final class UpdateBrowscapVersionUsed
{
    /**
     * @var string
     */
    private $buildDirectory;

    /**
     * @var string
     */
    private $resourceDirectory;

    /**
     * @var MetadataBuilder
     */
    private $metadataBuilder;

    public function __construct(
        string $buildDirectory,
        string $resourceDirectory,
        MetadataBuilder $metadataBuilder
    ) {
        $this->buildDirectory = $buildDirectory;
        $this->resourceDirectory = $resourceDirectory;
        $this->metadataBuilder = $metadataBuilder;
    }

    /**
     * @param IOInterface $io
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     * @throws \Assert\AssertionFailedException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     */
    public function __invoke(IOInterface $io): void
    {
        $packageBuildNumber = $this->determineBuildNumberFromPackage('browscap/browscap');
        $currentBuildNumber = $this->getCurrentBuildNumber();

        if ($packageBuildNumber !== $currentBuildNumber) {
            $io->write(sprintf('<info>Generating new Browscap build: %s</info>', $packageBuildNumber));
            $this->createBuild($packageBuildNumber, $io);
            $io->write('<info>All done</info>');
        } else {
            $io->write(sprintf('<info>Current build %s is up to date</info>', $currentBuildNumber));
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
    private function convertPackageVersionToBuildNumber(string $version): int
    {
        Assert::that($version)->regex('#^(\d+\.)(\d+\.)(\d+)$#');
        return (int)sprintf('%03d%03d%03d', ...explode('.', $version));
    }

    /**
     * Try to determine the build number from a composer package.
     *
     * @param string $packageName
     * @return int
     * @throws \OutOfBoundsException
     */
    private function determineBuildNumberFromPackage(string $packageName): int
    {
        $packageVersion = Versions::getVersion($packageName);
        return $this->convertPackageVersionToBuildNumber(substr($packageVersion, 0, strpos($packageVersion, '@')));
    }

    /**
     * @return int|null
     */
    private function getCurrentBuildNumber(): ?int
    {
        $metadataFile = $this->buildDirectory . '/metadata.php';

        if (!file_exists($metadataFile)) {
            return null;
        }

        /** @noinspection PhpIncludeInspection */
        $metadata = require $metadataFile;
        return (int)$metadata['version'];
    }

    /**
     * Generate a build for build number specified.
     *
     * @param int $buildNumber
     * @param IOInterface|null $io
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     * @throws \Assert\AssertionFailedException
     */
    private function createBuild(int $buildNumber, IOInterface $io): void
    {
        if (!file_exists($this->buildDirectory)
            && !mkdir($this->buildDirectory, 0775, true)
            && !is_dir($this->buildDirectory)
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->buildDirectory));
        }

        $logLevel = getenv('BC_BUILD_LOG') ?: Logger::NOTICE;

        $stream = new StreamHandler('php://output', $logLevel);
        $stream->setFormatter(new LineFormatter('%message%' . "\n"));

        $logger = new Logger('browscap');
        $logger->pushHandler($stream);
        $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel));

        $io->write('  - Creating browscap build');
        $buildGenerator = new BuildGenerator(
            $this->resourceDirectory,
            $this->buildDirectory,
            $logger,
            (new FullCollectionFactory())->createCollection($logger, $this->buildDirectory),
            new DataCollectionFactory($logger)
        );
        $buildGenerator->run((string)$buildNumber);

        $io->write('  - Generating metadata');
        $this->metadataBuilder->build();

        $io->write('  - Updating cache');
        (new BrowscapPhpTool())->update();
    }
}
