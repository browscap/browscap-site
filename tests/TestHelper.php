<?php

declare(strict_types=1);

namespace BrowscapSiteTest;

use BrowscapSite\Metadata\Metadata;
use DateTimeImmutable;

abstract class TestHelper
{
    public const string EXAMPLE_RELEASE_VERSION = '1001001'; // 1.1.1

    public static function createMetadataForTesting(string $version = self::EXAMPLE_RELEASE_VERSION, DateTimeImmutable|null $date = null): Metadata
    {
        if ($date === null) {
            $date = new DateTimeImmutable();
        }

        return Metadata::fromArray([
            'version' => $version,
            'released' => $date->format('r'),
            'filesizes' =>
                [
                    'BrowsCapINI' => 1001,
                    'Full_BrowsCapINI' => 1002,
                    'Lite_BrowsCapINI' => 1003,
                    'PHP_BrowsCapINI' => 1004,
                    'Full_PHP_BrowsCapINI' => 1005,
                    'Lite_PHP_BrowsCapINI' => 1006,
                    'BrowsCapXML' => 1007,
                    'BrowsCapCSV' => 1008,
                    'BrowsCapJSON' => 1009,
                    'BrowsCapZIP' => 1010,
                ],
        ]);
    }
}
