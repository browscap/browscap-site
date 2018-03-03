<?php
declare(strict_types=1);

namespace BrowscapSiteTest\BuildGenerator;

use Browscap\Generator\GeneratorInterface;
use BrowscapSite\BuildGenerator\BuildGenerator;
use BrowscapSite\BuildGenerator\DeterminePackageVersion;
use BrowscapSite\Composer\SimpleIOInterface;
use BrowscapSite\Metadata\MetadataBuilder;
use BrowscapSite\UserAgentTool\UserAgentTool;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

final class BuildGeneratorTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $filesystem;

    /**
     * @var GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $browscapBuildGenerator;

    /**
     * @var MetadataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataBuilder;

    /**
     * @var DeterminePackageVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $determinePackageVersion;

    /**
     * @var UserAgentTool
     */
    private $userAgentTool;

    /**
     * @var TestSimpleIO
     */
    private $io;

    /**
     * @var BuildGenerator
     */
    private $buildGenerator;

    public function setUp()
    {
        $this->filesystem = vfsStream::setup();
        $this->browscapBuildGenerator = $this->createMock(GeneratorInterface::class);
        $this->metadataBuilder = $this->createMock(MetadataBuilder::class);
        $this->determinePackageVersion = $this->createMock(DeterminePackageVersion::class);
        $this->userAgentTool = $this->createMock(UserAgentTool::class);
        $this->io = new TestSimpleIO();
        $this->buildGenerator = new BuildGenerator(
            $this->filesystem->url() . '/build',
            $this->browscapBuildGenerator,
            $this->metadataBuilder,
            $this->determinePackageVersion,
            $this->userAgentTool
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function testBuildIsGeneratedWhenNoPreviousBuildExists(): void
    {
        $packageVersion = '1.2.3@' . sha1(uniqid('gitHash', true));
        $packageBuildNumber = '1002003';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::once())->method('run')->with($packageBuildNumber);
        $this->metadataBuilder->expects(self::once())->method('build');
        $this->userAgentTool->expects(self::once())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            [
                '<info>Generating new Browscap build: 1002003</info>',
                '  - Creating browscap build',
                '  - Generating metadata',
                '  - Updating cache',
                '<info>All done</info>',
            ],
            $this->io->output
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function testBuildIsGeneratedWhenOutdatedBuildExists(): void
    {
        mkdir($this->filesystem->url() . '/build', 0777, true);
        file_put_contents(
            $this->filesystem->url() . '/build/metadata.php',
            '<?php return ["version" => "1002002"];'
        );
        $packageVersion = '1.2.3@' . sha1(uniqid('gitHash', true));
        $packageBuildNumber = '1002003';
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::once())->method('run')->with($packageBuildNumber);
        $this->metadataBuilder->expects(self::once())->method('build');
        $this->userAgentTool->expects(self::once())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            [
                '<info>Generating new Browscap build: 1002003</info>',
                '  - Creating browscap build',
                '  - Generating metadata',
                '  - Updating cache',
                '<info>All done</info>',
            ],
            $this->io->output
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function testBuildIsNotGeneratedWhenBuildAlreadyExistsAndMatchesVersion(): void
    {
        mkdir($this->filesystem->url() . '/build', 0777, true);
        file_put_contents(
            $this->filesystem->url() . '/build/metadata.php',
            '<?php return ["version" => "1002003"];'
        );
        $packageVersion = '1.2.3@' . sha1(uniqid('gitHash', true));
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::never())->method('run');
        $this->metadataBuilder->expects(self::never())->method('build');
        $this->userAgentTool->expects(self::never())->method('update');

        $this->buildGenerator->__invoke($this->io);

        self::assertEquals(
            [
                '<info>Current build 1002003 is up to date</info>',
            ],
            $this->io->output
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     * @throws \Exception
     */
    public function testExceptionIsThrownWhenUnableToCreateBuildDirectory(): void
    {
        $this->filesystem->chmod(000);

        $packageVersion = '1.2.3@' . sha1(uniqid('gitHash', true));
        $this->determinePackageVersion->expects(self::once())
            ->method('__invoke')
            ->with('browscap/browscap')
            ->willReturn($packageVersion);
        $this->browscapBuildGenerator->expects(self::never())->method('run');
        $this->metadataBuilder->expects(self::never())->method('build');
        $this->userAgentTool->expects(self::never())->method('update');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('was not created');
        $this->buildGenerator->__invoke($this->io);
    }
}
