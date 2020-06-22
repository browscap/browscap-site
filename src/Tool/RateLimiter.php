<?php

declare(strict_types=1);

namespace BrowscapSite\Tool;

interface RateLimiter
{
    /**
     * Check to see if an IP is temp banned.
     *
     * If the IP is not banned, it checks the download rate, and if it exceeds
     * it, we ban them. If they have exceeded the temporary ban rate, we will
     * permanently ban them.
     */
    public function isTemporarilyBanned(string $ip): bool;

    /**
     * Check to see if an IP is permanently banned.
     */
    public function isPermanentlyBanned(string $ip): bool;

    /**
     * Log that a download has happened.
     */
    public function logDownload(string $ip, string $userAgent, string $fileCode): void;
}
