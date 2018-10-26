<?php

declare(strict_types=1);

namespace BrowscapSite\Metadata;

use Assert\Assert;
use DateTimeImmutable;

final class Metadata
{
    /** @var string[]|int[][] */
    private $metadataArray;

    private function __construct()
    {
    }

    public static function fromArray(array $array)
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

        $instance = new self();
        $instance->metadataArray = $array;
        return $instance;
    }

    public function version(): string
    {
        return $this->metadataArray['version'];
    }

    /**
     * @throws \Exception
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
