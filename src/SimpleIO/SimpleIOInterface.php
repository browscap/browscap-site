<?php
declare(strict_types=1);

namespace BrowscapSite\SimpleIO;

interface SimpleIOInterface
{
    public function write(string $message): void;
}
