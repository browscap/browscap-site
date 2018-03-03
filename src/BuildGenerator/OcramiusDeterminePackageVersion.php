<?php
declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use PackageVersions\Versions;

final class OcramiusDeterminePackageVersion implements DeterminePackageVersion
{
    /**
     * {@inheritDoc}
     * @throws \OutOfBoundsException
     */
    public function __invoke(string $packageName) : string
    {
        return Versions::getVersion($packageName);
    }
}
