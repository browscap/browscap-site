<?php
declare(strict_types=1);

namespace BrowscapSiteTest\BuildGenerator;

use BrowscapSite\SimpleIO\SimpleIOInterface;

final class TestSimpleIO implements SimpleIOInterface
{
    public $output = [];

    public function write(string $message) : void
    {
        $this->output[] = $message;
    }
}
