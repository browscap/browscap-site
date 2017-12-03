<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use BrowscapSite\Tool\RateLimiter;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\ServerBag;

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

    /**
     * @var string
     */
    protected $buildDirectory;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @param RateLimiter $rateLimiter
     * @param array $fileList
     * @param string $buildDirectory
     */
    public function __construct(RateLimiter $rateLimiter, array $fileList, array $metadata, $buildDirectory)
    {
        $this->rateLimiter = $rateLimiter;
        $this->fileList = $fileList;
        $this->buildDirectory = $buildDirectory;
        $this->metadata = $metadata;
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
     * @param ServerBag $serverBag
     * @return string
     */
    public function getRemoteAddr(ServerBag $serverBag)
    {
        if ($serverBag->has('HTTP_CF_CONNECTING_IP')) {
            return $serverBag->get('HTTP_CF_CONNECTING_IP');
        }
        if ($serverBag->has('HTTP_X_FORWARDED_FOR')) {
            $ips = explode(',', $serverBag->get('HTTP_X_FORWARDED_FOR'));
            return $ips[0];
        }
        return $serverBag->get('REMOTE_ADDR');
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse|Response
     * @throws \InvalidArgumentException
     */
    public function indexAction(Request $request)
    {
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
        $fullPath = $this->buildDirectory . $file;
        if (!file_exists($fullPath)) {
            return $this->failed(500, 'The original file for the version requested could not be found');
        }

        // Check for rate limiting
        $remoteAddr = $this->getRemoteAddr($request->server);
        $remoteUserAgent = $request->server->has('HTTP_USER_AGENT') ? $request->server->get('HTTP_USER_AGENT') : 'Unknown UA';
        if ($this->rateLimiter->isPermanentlyBanned($remoteAddr)) {
            return $this->failed(403, 'Rate limit exceeded for ' . $remoteAddr . '. You have been permantly banned for abuse.');
        }
        if ($this->rateLimiter->isTemporarilyBanned($remoteAddr)) {
            return $this->failed(429, 'Rate limit exceeded for ' . $remoteAddr . '. Please try again later.');
        }
        $this->rateLimiter->logDownload($remoteAddr, $remoteUserAgent, $browscapVersion);

        // Offer the download
        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
        $response->setCache([
            'etag' => sha1($this->metadata['version'] . $browscapVersion),
            'last_modified' => new DateTime($this->metadata['released']),
            'max_age' => 86400,
            's_maxage' => 86400,
        ]);
        $response->isNotModified($request);
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
