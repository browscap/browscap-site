<?php

declare(strict_types=1);

namespace BrowscapSite\Tool;

use DateTime;
use LazyPDO\LazyPDO as PDO;

final class PdoRateLimiter implements RateLimiter
{
    private PDO $pdo;
    /** @var int[] */
    private array $banConfiguration;

    /** @param int[] $banConfiguration */
    public function __construct(PDO $pdo, array $banConfiguration)
    {
        $this->pdo              = $pdo;
        $this->banConfiguration = $banConfiguration;
    }

    /**
     * Check to see if an IP is temp banned.
     *
     * If the IP is not banned, it checks the download rate, and if it exceeds
     * it, we ban them. If they have exceeded the temporary ban rate, we will
     * permanently ban them.
     */
    public function isTemporarilyBanned(string $ip): bool
    {
        // Check to see if a temporary ban already exists
        $tempBan = $this->getTemporaryBan($ip);
        if ($tempBan !== false) {
            return true;
        }

        // Check download log to see if we should create a new ban
        $isOverLimit = $this->isOverLimit($ip);
        if ($isOverLimit) {
            $this->createBan($ip, $this->shouldPermanentlyBan($ip));

            return true;
        }

        return false;
    }

    /**
     * Check to see if an IP is permanently banned.
     */
    public function isPermanentlyBanned(string $ip): bool
    {
        return $this->getPermanentBan($ip) !== false;
    }

    /**
     * Log that a download has happened.
     */
    public function logDownload(string $ip, string $userAgent, string $fileCode): void
    {
        $sql = 'INSERT INTO downloadLog (ipAddress, downloadDate, fileCode, userAgent) VALUES(:ip, NOW(), :fileCode, :ua)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('fileCode', $fileCode);
        $stmt->bindValue('ua', $userAgent);

        $stmt->execute();
    }

    /**
     * Check whether an IP has gone over the download limit.
     *
     * Returns true if IP is over limit
     */
    private function isOverLimit(string $ip): bool
    {
        $rateLimitPeriod    = $this->banConfiguration['rateLimitPeriod'];
        $rateLimitDownloads = (int) $this->banConfiguration['rateLimitDownloads'];

        $cutoff = new DateTime($rateLimitPeriod . ' hours ago');

        $sql = 'SELECT COUNT(*) FROM downloadLog WHERE ipAddress = :ip AND downloadDate >= :cutoff';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('cutoff', $cutoff->format('Y-m-d H:i:s'));

        $stmt->execute();
        $downloads = (int) $stmt->fetchColumn();

        return $downloads >= $rateLimitDownloads;
    }

    /**
     * Should we permanently ban an IP address.
     */
    private function shouldPermanentlyBan(string $ip): bool
    {
        $tempBanLimit = (int) $this->banConfiguration['tempBanLimit'];

        $recentBanCount = $this->getRecentTemporaryBanCount($ip);

        return $recentBanCount > $tempBanLimit;
    }

    /**
     * Get how many temporary bans the IP has received recently.
     */
    private function getRecentTemporaryBanCount(string $ip): int
    {
        $tempBanPeriod = $this->banConfiguration['tempBanPeriod'];

        $cutoff = new DateTime($tempBanPeriod . ' days ago');

        $sql = '
            SELECT
                COUNT(*)
            FROM banLog
            WHERE
                ipAddress = :ip
                AND banDate >= :cutoff
                AND isPermanent = 0';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('cutoff', $cutoff->format('Y-m-d H:i:s'));

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Get information about any existing temporary ban from the database.
     *
     * Returns boolean(false) if no temporary ban exists.
     *
     * @return int[]|string[]|bool
     * @psalm-return bool|array{
     *   id: int,
     *   ipAddress: string,
     *   banDate: string,
     *   isPermanent: int,
     *   unbanDate: string,
     * }
     */
    private function getTemporaryBan(string $ip)
    {
        $rateLimitPeriod = $this->banConfiguration['rateLimitPeriod'];

        $cutoff = new DateTime($rateLimitPeriod . ' hour ago');

        $sql = '
            SELECT
                banLog.*,
                DATE_ADD(banDate, INTERVAL 1 HOUR) AS unbanDate
            FROM banLog
            WHERE
                ipAddress = :ip
                AND banDate >= :cutoff
                AND isPermanent = 0
            LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('cutoff', $cutoff->format('Y-m-d H:i:s'));

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get information about a permanent ban for an IP address.
     *
     * Returns boolean(false) if no permanent ban exists.
     *
     * @return int[]|string[]|bool
     * @psalm-return bool|array{
     *   id: int,
     *   ipAddress: string,
     *   banDate: string,
     *   isPermanent: int,
     *   unbanDate: string,
     * }
     */
    private function getPermanentBan(string $ip)
    {
        $sql = '
            SELECT
                banLog.*
            FROM banLog
            WHERE
                ipAddress = :ip
                AND isPermanent = 1
            LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);

        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Create a banLog entry.
     */
    private function createBan(string $ip, bool $permanent): void
    {
        $sql = 'INSERT INTO banLog (ipAddress, banDate, isPermanent) VALUES(:ip, NOW(), :permanent)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('permanent', $permanent ? '1' : '0');

        $stmt->execute();
    }
}
