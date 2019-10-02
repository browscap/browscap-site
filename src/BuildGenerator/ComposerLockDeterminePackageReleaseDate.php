<?php
declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use DateTimeImmutable;
use Exception;
use RuntimeException;

final class ComposerLockDeterminePackageReleaseDate implements DeterminePackageReleaseDate
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke() : DateTimeImmutable
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../composer.lock'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['packages'] as $package) {
            if ($package['name'] === 'browscap/browscap') {
                return new DateTimeImmutable($package['time']);
            }
        }

        throw new RuntimeException('Unable to determine browscap release date');
    }
}
