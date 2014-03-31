<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use BrowscapSite\Tool\RateLimiter;

class StreamController
{
    /**
     * @var \BrowscapSite\BrowscapSiteWeb
     */
    protected $app;

    /**
     * @var \BrowscapSite\Tool\RateLimiter
     */
    protected $rateLimiter;

    /**
     * @var array
     */
    protected $fileList;

    /**
     * @var string
     */
    protected $buildDirectory;

    public function __construct(BrowscapSiteWeb $app, RateLimiter $rateLimiter, array $fileList, $buildDirectory)
    {
        $this->app = $app;
        $this->rateLimiter = $rateLimiter;
        $this->fileList = $fileList;
        $this->buildDirectory = $buildDirectory;
    }

    protected function failed($status, $message)
    {
        header("HTTP/1.0 {$status}");
        echo $message;
        die();
    }

    public function indexAction()
    {
        $request = $this->app->getRequest();

        if (!$request->query->has('q')) {
            return $this->failed('400 Bad Request', 'The version requested could not be found');
        }

        $browscapVersion = strtolower($request->query->get('q'));

        // Convert requested short code to the filename
        $file = $this->getFilenameFromCode($browscapVersion);
        if (!$file) {
            return $this->failed('404 Not Found', 'The version requested could not be found');
        }

        // Check the file to be downloaded exists
        $fullpath = $this->buildDirectory . $file;
        if (!file_exists($fullpath)) {
            return $this->failed('500 Internal Server Error', 'The original file for the version requested could not be found');
        }

        // Check for rate limiting
        if (!$this->rateLimiter->checkLimit($_SERVER['REMOTE_ADDR']))
        {
            return $this->failed('429 Too Many Requests', 'Rate limit exceeded. Please try again later.');
        }
        $this->rateLimiter->logDownload($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $browscapVersion);

        // Offer the download
        // @todo refactor this
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
