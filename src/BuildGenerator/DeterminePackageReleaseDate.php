<?php
declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use DateTimeImmutable;
use RuntimeException;

interface DeterminePackageReleaseDate
{
    /** @throws RuntimeException */
    public function __invoke(): DateTimeImmutable;
}
