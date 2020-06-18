<?php

declare(strict_types=1);

namespace BrowscapSite\Metadata;

use Assert\Assert;
use DateTimeImmutable;
use Exception;

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

    /**
     * @param mixed[] $array
     */
    public static function fromArray(array $array): self
    {
        Assert::that($array)
            ->keyExists('version')
            ->keyExists('released')
            ->keyExists('filesizes');

        Assert::that($array['version'])->string();
        Assert::that($array['released'])->string();
        Assert::that($array['filesizes'])
            ->isArray()
            ->keyExists('BrowsCapINI')
            ->keyExists('Full_BrowsCapINI')
            ->keyExists('Lite_BrowsCapINI')
            ->keyExists('PHP_BrowsCapINI')
            ->keyExists('Full_PHP_BrowsCapINI')
            ->keyExists('Lite_PHP_BrowsCapINI')
            ->keyExists('BrowsCapXML')
            ->keyExists('BrowsCapCSV')
            ->keyExists('BrowsCapJSON')
            ->keyExists('BrowsCapZIP')
            ->all()->integer();

        return new self($array);
    }

    public function version(): string
    {
        return $this->metadataArray['version'];
    }

    /**
     * @throws Exception
     */
    public function released(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->metadataArray['released']);
    }

    public function filesizeOf(string $fileKey): int
    {
        Assert::that($this->metadataArray['filesizes'])->keyExists($fileKey, 'File key specified was invalid');

        return $this->metadataArray['filesizes'][$fileKey];
    }
}
