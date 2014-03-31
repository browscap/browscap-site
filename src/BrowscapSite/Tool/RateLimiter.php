<?php

namespace BrowscapSite\Tool;

class RateLimiter
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $banConfiguration;

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
     * @return boolean
     */
    public function isTemporarilyBanned($ip)
    {
        return $this->isOverLimit($ip);
    }

    /**
     * Check to see if an IP is permanently banned
     *
     * @param string $ip
     * @return boolean
     */
    public function isPermanentlyBanned($ip)
    {
        return false;
    }

    /**
     * Check whether an IP has gone over the download limit.
     *
     * Returns true if IP is over limit
     *
     * @param string $ip
     * @return boolean
     */
    protected function isOverLimit($ip)
    {
        // This allows for 50 downloads in a 24 hour period
        $downloadLimit = 50;
        $cutoff = new \DateTime('24 hours ago');

        $sql = "SELECT COUNT(*) FROM downloadLog WHERE ipAddress = :ip AND downloadDate >= :cutoff";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('cutoff', $cutoff->format('Y-m-d H:i:s'));

        $stmt->execute();
        $downloads = (int)$stmt->fetchColumn();

        if ($downloads >= $downloadLimit) {
            return true;
        }

        return false;
    }

    /**
     * Log that a download has happened
     *
     * @param string $ip
     * @param string $userAgent
     * @param string $fileCode
     */
    public function logDownload($ip, $userAgent, $fileCode)
    {
        $sql = "INSERT INTO downloadLog (ipAddress, downloadDate, fileCode, userAgent) VALUES(:ip, NOW(), :fileCode, :ua)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('fileCode', $fileCode);
        $stmt->bindValue('ua', $userAgent);

        $stmt->execute();
    }
}
