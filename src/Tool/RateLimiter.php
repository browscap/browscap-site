<?php
declare(strict_types=1);

namespace BrowscapSite\Tool;

final class RateLimiter
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $banConfiguration;

    public function __construct(\PDO $pdo, $banConfiguration)
    {
        $this->pdo = $pdo;
        $this->banConfiguration = $banConfiguration;
    }

    /**
     * Check to see if an IP is temp banned.
     *
     * If the IP is not banned, it checks the download rate, and if it exceeds
     * it, we ban them. If they have exceeded the temporary ban rate, we will
     * permanently ban them.
     *
     * @param string $ip
     * @return bool
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
     *
     * @param string $ip
     * @return bool
     */
    public function isPermanentlyBanned(string $ip): bool
    {
        return $this->getPermanentBan($ip) !== false;
    }

    /**
     * Log that a download has happened.
     *
     * @param string $ip
     * @param string $userAgent
     * @param string $fileCode
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
     *
     * @param string $ip
     * @return bool
     */
    private function isOverLimit(string $ip): bool
    {
        $rateLimitPeriod = $this->banConfiguration['rateLimitPeriod'];
        $rateLimitDownloads = (int)$this->banConfiguration['rateLimitDownloads'];

        $cutoff = new \DateTime($rateLimitPeriod . ' hours ago');

        $sql = 'SELECT COUNT(*) FROM downloadLog WHERE ipAddress = :ip AND downloadDate >= :cutoff';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('cutoff', $cutoff->format('Y-m-d H:i:s'));

        $stmt->execute();
        $downloads = (int)$stmt->fetchColumn();

        return $downloads >= $rateLimitDownloads;
    }

    /**
     * Should we permanently ban an IP address.
     *
     * @param string $ip
     * @return bool
     */
    private function shouldPermanentlyBan(string $ip): bool
    {
        $tempBanLimit = (int)$this->banConfiguration['tempBanLimit'];

        $recentBanCount = $this->getRecentTemporaryBanCount($ip);

        return ($recentBanCount > $tempBanLimit);
    }

    /**
     * Get how many temporary bans the IP has received recently.
     *
     * @param string $ip
     * @return int
     */
    private function getRecentTemporaryBanCount(string $ip): int
    {
        $tempBanPeriod = $this->banConfiguration['tempBanPeriod'];

        $cutoff = new \DateTime($tempBanPeriod . ' days ago');

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
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get information about any existing temporary ban from the database.
     *
     * Returns boolean(false) if no temporary ban exists.
     *
     * @param string $ip
     * @return array|bool
     */
    private function getTemporaryBan(string $ip)
    {
        $rateLimitPeriod = $this->banConfiguration['rateLimitPeriod'];

        $cutoff = new \DateTime($rateLimitPeriod . ' hour ago');

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
     * @param string $ip
     * @return array|bool
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
     *
     * @param string $ip
     * @param bool $permanent
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
