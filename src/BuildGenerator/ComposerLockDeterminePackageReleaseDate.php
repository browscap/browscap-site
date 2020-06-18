<?php

declare(strict_types=1);

namespace BrowscapSite\BuildGenerator;

use DateTimeImmutable;
use Exception;
use RuntimeException;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class ComposerLockDeterminePackageReleaseDate implements DeterminePackageReleaseDate
{
    /**
     * @throws Exception
     *
     * @inheritDoc
     */
    public function __invoke(): DateTimeImmutable
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
