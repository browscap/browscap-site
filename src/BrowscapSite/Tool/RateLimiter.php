<?php

namespace BrowscapSite\Tool;

class RateLimiter
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Check whether an IP has gone over the download limit
     *
     * @param string $ip
     * @return boolean
     */
    public function checkLimit($ip)
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
            return false;
        }

        return true;
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
