<?php

declare(strict_types=1);

namespace BrowscapSite\Metadata;

use DateTimeImmutable;
use Exception;
use Psl\Type;
use Webmozart\Assert\Assert;

/**
 * @psalm-type MetadataArray = array{
 *   version: string,
 *   released: string,
 *   filesizes: array{
 *     BrowsCapINI: int,
 *     Full_BrowsCapINI: int,
 *     Lite_BrowsCapINI: int,
 *     PHP_BrowsCapINI: int,
 *     Full_PHP_BrowsCapINI: int,
 *     Lite_PHP_BrowsCapINI: int,
 *     BrowsCapXML: int,
 *     BrowsCapCSV: int,
 *     BrowsCapJSON: int,
 *     BrowsCapZIP: int,
 *   },
 * }
 */
final class Metadata
{
    /** @psalm-var MetadataArray */
    private array $metadataArray;

    /** @psalm-param MetadataArray $metadataArray */
    private function __construct(array $metadataArray)
    {
        $this->metadataArray = $metadataArray;
    }

    /** @param mixed[] $array */
    public static function fromArray(array $array): self
    {
        return new self(Type\shape([
            'version' => Type\string(),
            'released' => Type\string(),
            'filesizes' => Type\shape([
                'BrowsCapINI' => Type\int(),
                'Full_BrowsCapINI' => Type\int(),
                'Lite_BrowsCapINI' => Type\int(),
                'PHP_BrowsCapINI' => Type\int(),
                'Full_PHP_BrowsCapINI' => Type\int(),
                'Lite_PHP_BrowsCapINI' => Type\int(),
                'BrowsCapXML' => Type\int(),
                'BrowsCapCSV' => Type\int(),
                'BrowsCapJSON' => Type\int(),
                'BrowsCapZIP' => Type\int(),
            ]),
        ])->assert($array));
    }

    public function version(): string
    {
        return $this->metadataArray['version'];
    }

    /** @throws Exception */
    public function released(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->metadataArray['released']);
    }

    public function filesizeOf(string $fileKey): int
    {
        Assert::keyExists(
            $this->metadataArray['filesizes'],
            $fileKey,
            'File key specified was invalid',
        );

        return $this->metadataArray['filesizes'][$fileKey];
    }
}
