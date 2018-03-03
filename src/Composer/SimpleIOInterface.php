<?php
declare(strict_types=1);

namespace BrowscapSite\Composer;

interface SimpleIOInterface
{
    public function write(string $message): void;
}
