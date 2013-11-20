<?php

namespace BrowscapSite\Controller;

class StreamController
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function failed($status, $message)
    {
        header("HTTP/1.0 {$status}");
        echo $message;
        die();
    }

    public function indexAction()
    {
        // @todo - this is horrendous
        if (!isset($_GET['q'])) {
            $this->failed('400 Bad Request', 'The version requested could not be found');
        }

        $browscapVersion = strtolower($_GET['q']);

        switch ($browscapVersion)
        {
            case 'browscapini':
                $file = "browscap.ini";
                break;
            case 'full_browscapini':
                $file = "full_asp_browscap.ini";
                break;
            case 'lite_browscapini':
                $file = "lite_asp_browscap.ini";
                break;
            case 'php_browscapini':
                $file = "php_browscap.ini";
                break;
            case 'full_php_browscapini':
                $file = "full_php_browscap.ini";
                break;
            case 'lite_php_browscapini':
                $file = "lite_php_browscap.ini";
                break;
            default:
                $this->failed('404 Not Found', 'The version requested could not be found');
        }

        $buildDirectory = __DIR__ . '/../../../build/';

        $fullpath = $buildDirectory . $file;

        if (!file_exists($fullpath)) {
            $this->failed('500 Internal Server Error', 'The original file for the version requested could not be found');
        }

        if (!$this->checkLimit($_SERVER['REMOTE_ADDR']))
        {
            $this->failed('429 Too Many Requests', 'Rate limit exceeded. Please try again later.');
        }

        $this->logDownload($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $browscapVersion);

        header("HTTP/1.0 200 OK");
        header("Cache-Control: public");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($fullpath));
        header("Content-Disposition: attachment; filename=" . $file);
        readfile($fullpath);
        die();
    }

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
