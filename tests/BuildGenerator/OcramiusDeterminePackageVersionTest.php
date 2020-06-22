<?php

declare(strict_types=1);

namespace BrowscapSiteTest\BuildGenerator;

use BrowscapSite\BuildGenerator\OcramiusDeterminePackageVersion;
use PHPUnit\Framework\TestCase;

final class OcramiusDeterminePackageVersionTest extends TestCase
{
    public function testPackageVersionReturnedInValidFormat(): void
    {
        self::assertMatchesRegularExpression(
            '#^(\d+\.)(\d+\.)(\d+)@.*$#',
            (new OcramiusDeterminePackageVersion())->__invoke('browscap/browscap')
        );
    }
}
