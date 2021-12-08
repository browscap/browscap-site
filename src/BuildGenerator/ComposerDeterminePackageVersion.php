<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use Composer\InstalledVersions;

final class ComposerDeterminePackageVersion implements DeterminePackageVersion
{
    public function __invoke(string $packageName) : string
    {
        return InstalledVersions::getPrettyVersion($packageName);
    }
}
