<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

interface DeterminePackageVersion
{
    public function __invoke(string $packageName): string;
}
