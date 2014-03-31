<?php

namespace BrowscapSite\Controller;

use BrowscapSite\Tool\RateLimiter;

class StreamController
{
    /**
     * @var \BrowscapSite\Tool\RateLimiter
     */
    protected $rateLimiter;

    /**
     * @var array
     */
    protected $fileList;

    public function __construct(RateLimiter $rateLimiter, array $fileList)
    {
        $this->rateLimiter = $rateLimiter;
        $this->fileList = $fileList;
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
            return $this->failed('400 Bad Request', 'The version requested could not be found');
        }

        $browscapVersion = strtolower($_GET['q']);

        $file = $this->getFilenameFromCode($browscapVersion);
        if (!$file) {
            return $this->failed('404 Not Found', 'The version requested could not be found');
        }

        $buildDirectory = __DIR__ . '/../../../build/';

        $fullpath = $buildDirectory . $file;

        if (!file_exists($fullpath)) {
            return $this->failed('500 Internal Server Error', 'The original file for the version requested could not be found');
        }

        if (!$this->rateLimiter->checkLimit($_SERVER['REMOTE_ADDR']))
        {
            return $this->failed('429 Too Many Requests', 'Rate limit exceeded. Please try again later.');
        }

        $this->rateLimiter->logDownload($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $browscapVersion);

        header("HTTP/1.0 200 OK");
        header("Cache-Control: public");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length:" . filesize($fullpath));
        header("Content-Disposition: attachment; filename=" . $file);
        readfile($fullpath);
        die();
    }

    /**
     * Convert
     * @param unknown $browscapCode
     * @return string|boolean
     */
    protected function getFilenameFromCode($browscapCode)
    {
        foreach ($this->fileList as $fileset) {
            foreach ($fileset as $existingCode => $info) {
                if (strtolower($existingCode) == strtolower($browscapCode)) {
                    return $info['name'];
                }
            }
        }
    }
}
