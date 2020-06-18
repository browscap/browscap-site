<?php

declare(strict_types=1);

namespace BrowscapSite\Metadata;

use DateTimeImmutable;
use Exception;
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

    /**
     * @param mixed[] $array
     */
    public static function fromArray(array $array): self
    {
        Assert::keyExists($array, 'version');
        Assert::keyExists($array, 'released');
        Assert::keyExists($array, 'filesizes');

        Assert::string($array['version']);
        Assert::string($array['released']);
        Assert::isArray($array['filesizes']);

        Assert::keyExists($array['filesizes'], 'BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'Full_BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'Lite_BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'PHP_BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'Full_PHP_BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'Lite_PHP_BrowsCapINI');
        Assert::keyExists($array['filesizes'], 'BrowsCapXML');
        Assert::keyExists($array['filesizes'], 'BrowsCapCSV');
        Assert::keyExists($array['filesizes'], 'BrowsCapJSON');
        Assert::keyExists($array['filesizes'], 'BrowsCapZIP');

        Assert::allInteger($array['filesizes']);

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
        Assert::keyExists(
            $this->metadataArray['filesizes'],
            $fileKey,
            'File key specified was invalid'
        );

        return $this->metadataArray['filesizes'][$fileKey];
    }
}
