<?php
declare(strict_types=1);

namespace BrowscapSite\UserAgentTool;

interface UserAgentTool
{
    public function update(): void;

    public function identify(string $userAgent): \stdClass;
}
