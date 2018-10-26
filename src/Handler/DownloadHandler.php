<?php
declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Http\Response;

final class DownloadHandler implements RequestHandlerInterface
{
    /** @var Metadata */
    private $metadata;

    /** @var array */
    private $fileList;

    /** @var array */
    private $banConfiguration;

    public function __construct(Metadata $metadata, array $fileList, array $banConfiguration)
    {
        $this->metadata = $metadata;
        $this->fileList = $fileList;
        $this->banConfiguration = $banConfiguration;
    }

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $renderData = [
            'files' => $this->mergeMetadataToFiles($this->fileList),
            'version' => $this->metadata->version(),
            'releaseDate' => $this->metadata->released()->format('jS M Y'),
            'banConfig' => $this->banConfiguration,
        ];
        return new Response(200);
    }

    private function mergeMetadataToFiles($files): array
    {
        $files['asp']['BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('BrowsCapINI'));
        $files['asp']['Full_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Full_BrowsCapINI'));
        $files['asp']['Lite_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Lite_BrowsCapINI'));
        $files['php']['PHP_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('PHP_BrowsCapINI'));
        $files['php']['Full_PHP_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Full_PHP_BrowsCapINI'));
        $files['php']['Lite_PHP_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Lite_PHP_BrowsCapINI'));
        $files['other']['BrowsCapXML']['size'] = number_format($this->metadata->filesizeOf('BrowsCapXML'));
        $files['other']['BrowsCapCSV']['size'] = number_format($this->metadata->filesizeOf('BrowsCapCSV'));
        $files['other']['BrowsCapJSON']['size'] = number_format($this->metadata->filesizeOf('BrowsCapJSON'));
        $files['other']['BrowsCapZIP']['size'] = number_format($this->metadata->filesizeOf('BrowsCapZIP'));
        return $files;
    }
}
