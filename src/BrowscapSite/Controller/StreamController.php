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

    /**
     * @param BrowscapSiteWeb $app
     * @param RateLimiter $rateLimiter
     * @param array $fileList
     * @param string $buildDirectory
     */
    public function __construct(BrowscapSiteWeb $app, RateLimiter $rateLimiter, array $fileList, $buildDirectory)
    {
        $this->app = $app;
        $this->rateLimiter = $rateLimiter;
        $this->fileList = $fileList;
        $this->buildDirectory = $buildDirectory;
    }

    /**
     * Prepare a response object.
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

    /**
     * @return string
     */
    public function getRemoteAddr()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return $ips[0];
        }
        return $_SERVER['REMOTE_ADDR'];
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
        $remoteAddr = $this->getRemoteAddr();
        $remoteUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown UA';
        if ($this->rateLimiter->isPermanentlyBanned($remoteAddr)) {
            return $this->failed(403, 'Rate limit exceeded for ' . $remoteAddr . '. You have been permantly banned for abuse.');
        }
        if ($this->rateLimiter->isTemporarilyBanned($remoteAddr)) {
            return $this->failed(429, 'Rate limit exceeded for ' . $remoteAddr . '. Please try again later.');
        }
        $this->rateLimiter->logDownload($remoteAddr, $remoteUserAgent, $browscapVersion);

        // Offer the download
        $response = new BinaryFileResponse($fullpath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
        $response->setMaxAge(0);
        $response->expire();
        return $response;
    }

    /**
     * Convert a "download code" to the real filename.
     *
     * @param string $browscapCode
     * @return string|bool
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
