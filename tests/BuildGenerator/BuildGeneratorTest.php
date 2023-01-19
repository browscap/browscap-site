<?php

declare(strict_types=1);

namespace BrowscapSiteTest\BuildGenerator;

use Assert\AssertionFailedException;
use Browscap\Generator\GeneratorInterface;
use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\BuildGenerator\DeterminePackageReleaseDate;
use BrowscapSite\BuildGenerator\DeterminePackageVersion;
use BrowscapSite\Metadata\MetadataBuilder;
use BrowscapSite\UserAgentTool\UserAgentTool;
use DateTimeImmutable;
use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_put_contents;
use function mkdir;

final class BuildGeneratorTest extends TestCase
{
    private vfsStreamDirectory $filesystem;

    private GeneratorInterface&MockObject $browscapBuildGenerator;

    private MetadataBuilder&MockObject $metadataBuilder;

    private DeterminePackageVersion&MockObject $determinePackageVersion;

    private DeterminePackageReleaseDate&MockObject $determinePackageReleaseDate;

    private UserAgentTool&MockObject $userAgentTool;

    private TestSimpleIO $io;

    private BuildGenerator $buildGenerator;

    public function setUp(): void
    {
        $this->filesystem                  = vfsStream::setup();
        $this->browscapBuildGenerator      = $this->createMock(GeneratorInterface::class);
        $this->metadataBuilder             = $this->createMock(MetadataBuilder::class);
        $this->determinePackageVersion     = $this->createMock(DeterminePackageVersion::class);
        $this->determinePackageReleaseDate = $this->createMock(DeterminePackageReleaseDate::class);
        $this->userAgentTool               = $this->createMock(UserAgentTool::class);
        $this->io                          = new TestSimpleIO();
        $this->buildGenerator              = new BuildGenerator(
            $this->filesystem->url() . '/build',
            function (): GeneratorInterface {
                return $this->browscapBuildGenerator;
            },
            $this->metadataBuilder,
            $this->determinePackageVersion,
            $this->determinePackageReleaseDate,
            $this->userAgentTool,
        );
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function testBuildIsGeneratedWhenNoPreviousBuildExists(): void
    {
        $generationDate     = new DateTimeImmutable('1970-01-01 00:00:00');
        $packageVersion     = '1.2.3';
        $packageBuildNumber = '1002003';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->determinePackageReleaseDate->expects(self::once())
            ->method('__invoke')
            ->willReturn($generationDate);
        $this->browscapBuildGenerator->expects(self::once())->method('run')->with($packageBuildNumber);
        $this->metadataBuilder->expects(self::once())->method('build');
        $this->userAgentTool->expects(self::once())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            [
                '<info>Generating new Browscap build: 1002003 (1970-01-01T00:00:00+00:00)</info>',
                '  - Creating browscap build',
                '  - Generating metadata',
                '  - Updating cache',
                '<info>All done</info>',
            ],
            $this->io->output,
        );
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function testBuildIsGeneratedWhenOutdatedBuildExists(): void
    {
        $generationDate = new DateTimeImmutable('1970-01-01 00:00:00');
        mkdir($this->filesystem->url() . '/build', 0777, true);
        file_put_contents(
            $this->filesystem->url() . '/build/metadata.php',
            '<?php return ["version" => "1002002"];',
        );
        $packageVersion     = '1.2.3';
        $packageBuildNumber = '1002003';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->determinePackageReleaseDate->expects(self::once())
            ->method('__invoke')
            ->willReturn($generationDate);
        $this->browscapBuildGenerator->expects(self::once())->method('run')->with($packageBuildNumber);
        $this->metadataBuilder->expects(self::once())->method('build');
        $this->userAgentTool->expects(self::once())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            [
                '<info>Generating new Browscap build: 1002003 (1970-01-01T00:00:00+00:00)</info>',
                '  - Creating browscap build',
                '  - Generating metadata',
                '  - Updating cache',
                '<info>All done</info>',
            ],
            $this->io->output,
        );
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function testBuildIsNotGeneratedWhenBuildAlreadyExistsAndMatchesVersion(): void
    {
        mkdir($this->filesystem->url() . '/build', 0777, true);
        file_put_contents(
            $this->filesystem->url() . '/build/metadata.php',
            '<?php return ["version" => "1002003"];',
        );
        $packageVersion = '1.2.3';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::never())->method('run');
        $this->metadataBuilder->expects(self::never())->method('build');
        $this->userAgentTool->expects(self::never())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            ['<info>Current build 1002003 is up to date</info>'],
            $this->io->output,
        );
    }

    /**
     * @throws AssertionFailedException
     * @throws Exception
     */
    public function testExceptionIsThrownWhenUnableToCreateBuildDirectory(): void
    {
        $this->filesystem->chmod(000);

        $packageVersion = '1.2.3';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::never())->method('run');
        $this->metadataBuilder->expects(self::never())->method('build');
        $this->userAgentTool->expects(self::never())->method('update');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('was not created');
        $this->buildGenerator->__invoke($this->io);
    }
}
