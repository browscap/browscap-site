<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use Browscap\Generator\GeneratorInterface;
use BrowscapSite\Metadata\MetadataBuilder;
use BrowscapSite\SimpleIO\SimpleIOInterface;
use BrowscapSite\UserAgentTool\UserAgentTool;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use Webmozart\Assert\Assert;

use function explode;
use function file_exists;
use function is_dir;
use function mkdir;
use function sprintf;

use const DATE_ATOM;

final class BuildGenerator
{
    /** @psalm-var callable():GeneratorInterface */
    private $buildGeneratorLazyFactory;

    /** @psalm-param callable():GeneratorInterface $buildGeneratorLazyFactory */
    public function __construct(
        private string $buildDirectory,
        callable $buildGeneratorLazyFactory,
        private MetadataBuilder $metadataBuilder,
        private DeterminePackageVersion $determinePackageVersion,
        private DeterminePackageReleaseDate $determinePackageReleaseDate,
        private UserAgentTool $userAgentTool,
    ) {
        $this->buildGeneratorLazyFactory = $buildGeneratorLazyFactory;
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws Exception
     */
    public function __invoke(SimpleIOInterface $io): void
    {
        $packageBuildNumber = $this->determineBuildNumberFromPackage('browscap/browscap');
        $currentBuildNumber = $this->getCurrentBuildNumber();

        if ($packageBuildNumber !== $currentBuildNumber) {
            $generationDate = $this->determinePackageReleaseDate->__invoke();
            $io->write(sprintf('<info>Generating new Browscap build: %s (%s)</info>', $packageBuildNumber, $generationDate->format(DATE_ATOM)));
            $this->createBuild($packageBuildNumber, $generationDate, $io);
            $io->write('<info>All done</info>');
        } else {
            Assert::integer($currentBuildNumber);
            $io->write(sprintf('<info>Current build %s is up to date</info>', $currentBuildNumber));
        }
    }

    /**
     * Converts a package number e.g. 1.2.3 into a "build number" e.g. 1002003
     *
     * There are three digits for each version, so 001002003 becomes 1002003 when cast to int to drop the leading zeros
     */
    private function convertPackageVersionToBuildNumber(string $version): int
    {
        Assert::regex($version, '#^(\d+\.)(\d+\.)(\d+)$#');

        return (int) sprintf('%03d%03d%03d', ...explode('.', $version));
    }

    /**
     * Try to determine the build number from a composer package.
     *
     * @throws OutOfBoundsException
     */
    private function determineBuildNumberFromPackage(string $packageName): int
    {
        return $this->convertPackageVersionToBuildNumber($this->determinePackageVersion->__invoke($packageName));
    }

    private function getCurrentBuildNumber(): int|null
    {
        $metadataFile = $this->buildDirectory . '/metadata.php';

        if (! file_exists($metadataFile)) {
            return null;
        }

        /** @noinspection PhpIncludeInspection */
        $metadata = require $metadataFile;

        return (int) $metadata['version'];
    }

    /**
     * Generate a build for build number specified.
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    private function createBuild(int $buildNumber, DateTimeImmutable $generationDate, SimpleIOInterface $io): void
    {
        if (
            ! file_exists($this->buildDirectory)
            && ! mkdir($this->buildDirectory, 0775, true)
            && ! is_dir($this->buildDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $this->buildDirectory));
        }

        $buildGeneratorLazyFactory = $this->buildGeneratorLazyFactory;
        $buildGenerator            = $buildGeneratorLazyFactory();

        $io->write('  - Creating browscap build');
        $buildGenerator->run((string) $buildNumber, $generationDate);

        $io->write('  - Generating metadata');
        $this->metadataBuilder->build();

        $io->write('  - Updating cache');
        $this->userAgentTool->update();
    }
}
