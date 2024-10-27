<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\ConfigProvider\AppConfig;
use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Tool\RateLimiter;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_key_exists;
use function explode;
use function file_exists;
use function fopen;
use function sha1;
use function strtolower;

/** @psalm-import-type FilesList from AppConfig */
final class StreamHandler implements RequestHandlerInterface
{
    /** @psalm-param FilesList $fileList */
    public function __construct(private RateLimiter $rateLimiter, private Metadata $metadata, private array $fileList, private string $buildDirectory)
    {
    }

    /** @throws Exception */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        if (! array_key_exists('q', $queryParams)) {
            return $this->failed(400, 'The version requested could not be found');
        }

        $browscapVersion = strtolower($queryParams['q']);

        // Convert requested short code to the filename
        $file = $this->getFilenameFromCode($browscapVersion);
        if (! $file) {
            return $this->failed(404, 'The version requested could not be found');
        }

        // Check the file to be downloaded exists
        $fullPath = $this->buildDirectory . '/' . $file;
        if (! file_exists($fullPath)) {
            return $this->failed(500, 'The original file for the version requested could not be found');
        }

        // Check for rate limiting
        $remoteAddr      = $this->getRemoteAddr($request);
        $remoteUserAgent = $this->getRemoteUserAgent($request);
        if ($this->rateLimiter->isPermanentlyBanned($remoteAddr)) {
            return $this->failed(403, 'Rate limit exceeded for ' . $remoteAddr . '. You have been permantly banned for abuse.');
        }

        if ($this->rateLimiter->isTemporarilyBanned($remoteAddr)) {
            return $this->failed(429, 'Rate limit exceeded for ' . $remoteAddr . '. Please try again later.');
        }

        $this->rateLimiter->logDownload($remoteAddr, $remoteUserAgent, $browscapVersion);

        $fileHandle = fopen($fullPath, 'rb');

        return (new Response())
            ->withHeader('Content-Disposition', 'attachment;filename="' . $file . '"')
            ->withHeader('Last-Modified', $this->metadata->released()->format('D, d M Y H:i:s'))
            ->withHeader('ETag', sha1($this->metadata->version() . $browscapVersion))
            ->withHeader('Cache-Control', 'max-age=86400 s-max-age=86400')
            ->withBody(new Stream($fileHandle));
    }

    private function failed(int $status, string $message): ResponseInterface
    {
        return new Response\JsonResponse(['response' => $message], $status);
    }

    private function getRemoteAddr(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        if (array_key_exists('HTTP_CF_CONNECTING_IP', $serverParams)) {
            return $serverParams['HTTP_CF_CONNECTING_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);

            return $ips[0];
        }

        return $serverParams['REMOTE_ADDR'];
    }

    private function getRemoteUserAgent(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        if (! array_key_exists('HTTP_USER_AGENT', $serverParams)) {
            return 'Unknown UA';
        }

        return $serverParams['HTTP_USER_AGENT'];
    }

    private function getFilenameFromCode(string $browscapCode): string|null
    {
        foreach ($this->fileList as $fileset) {
            foreach ($fileset as $existingCode => $info) {
                if (strtolower($existingCode) === strtolower($browscapCode)) {
                    return $info['name'];
                }
            }
        }

        return null;
    }
}
