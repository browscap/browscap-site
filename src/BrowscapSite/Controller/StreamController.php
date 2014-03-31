<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use BrowscapSite\Tool\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

    /**
     * Prepare a response object
     *
     * @param int $status
     * @param string $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function failed($status, $message)
    {
        $response = new Response();
        $response->setStatusCode($status);
        $response->setContent($message);
        return $response;
    }

    public function indexAction()
    {
        $request = $this->app->getRequest();

        if (!$request->query->has('q')) {
            return $this->failed(400, 'The version requested could not be found');
        }

        $browscapVersion = strtolower($request->query->get('q'));

        // Convert requested short code to the filename
        $file = $this->getFilenameFromCode($browscapVersion);
        if (!$file) {
            return $this->failed(404, 'The version requested could not be found');
        }

        // Check the file to be downloaded exists
        $fullpath = $this->buildDirectory . $file;
        if (!file_exists($fullpath)) {
            return $this->failed(500, 'The original file for the version requested could not be found');
        }

        // Check for rate limiting
        $remoteAddr = $_SERVER['REMOTE_ADDR'];
        $remoteUserAgent = $_SERVER['HTTP_USER_AGENT'];
        if ($this->rateLimiter->isPermanentlyBanned($remoteAddr))
        {
            return $this->failed(403, 'Rate limit exceeded. You have been permantly banned for abuse.');
        }
        if ($this->rateLimiter->isTemporarilyBanned($remoteAddr))
        {
            return $this->failed(429, 'Rate limit exceeded. Please try again later.');
        }
        $this->rateLimiter->logDownload($remoteAddr, $remoteUserAgent, $browscapVersion);

        // Offer the download
        $response = new BinaryFileResponse($fullpath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
        return $response;
    }

    /**
     * Convert a "download code" to the real filename
     *
     * @param string $browscapCode
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
        return false;
    }
}
