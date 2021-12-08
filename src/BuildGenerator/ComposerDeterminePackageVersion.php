<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use Composer\InstalledVersions;
use Webmozart\Assert\Assert;

final class ComposerDeterminePackageVersion implements DeterminePackageVersion
{
    public function __invoke(string $packageName): string
    {
        $version = InstalledVersions::getPrettyVersion($packageName);

        Assert::notNull($version);

        return $version;
    }
}
