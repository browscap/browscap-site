<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function number_format;

/**
 * @psalm-import-type FilesList from \BrowscapSite\ConfigProvider\AppConfig
 * @psalm-import-type BanConfiguration from \BrowscapSite\ConfigProvider\AppConfig
 */
final class DownloadHandler implements RequestHandlerInterface
{
    private Renderer $renderer;
    private Metadata $metadata;
    /** @psalm-var FilesList */
    private array $fileList;
    /** @psalm-var BanConfiguration */
    private array $banConfiguration;

    /**
     * @psalm-param FilesList $fileList
     * @psalm-param BanConfiguration $banConfiguration
     */
    public function __construct(Renderer $renderer, Metadata $metadata, array $fileList, array $banConfiguration)
    {
        $this->renderer         = $renderer;
        $this->metadata         = $metadata;
        $this->fileList         = $fileList;
        $this->banConfiguration = $banConfiguration;
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->renderer->render(
            'downloads.html',
            [
                'files' => $this->mergeMetadataToFiles($this->fileList),
                'version' => $this->metadata->version(),
                'releaseDate' => $this->metadata->released()->format('jS M Y'),
                'banConfig' => $this->banConfiguration,
            ]
        );
    }

    /**
     * @psalm-param FilesList $files
     * @psalm-return FilesList
     */
    private function mergeMetadataToFiles(array $files): array
    {
        $files['asp']['BrowsCapINI']['size']          = number_format($this->metadata->filesizeOf('BrowsCapINI'));
        $files['asp']['Full_BrowsCapINI']['size']     = number_format($this->metadata->filesizeOf('Full_BrowsCapINI'));
        $files['asp']['Lite_BrowsCapINI']['size']     = number_format($this->metadata->filesizeOf('Lite_BrowsCapINI'));
        $files['php']['PHP_BrowsCapINI']['size']      = number_format($this->metadata->filesizeOf('PHP_BrowsCapINI'));
        $files['php']['Full_PHP_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Full_PHP_BrowsCapINI'));
        $files['php']['Lite_PHP_BrowsCapINI']['size'] = number_format($this->metadata->filesizeOf('Lite_PHP_BrowsCapINI'));
        $files['other']['BrowsCapXML']['size']        = number_format($this->metadata->filesizeOf('BrowsCapXML'));
        $files['other']['BrowsCapCSV']['size']        = number_format($this->metadata->filesizeOf('BrowsCapCSV'));
        $files['other']['BrowsCapJSON']['size']       = number_format($this->metadata->filesizeOf('BrowsCapJSON'));
        $files['other']['BrowsCapZIP']['size']        = number_format($this->metadata->filesizeOf('BrowsCapZIP'));

        return $files;
    }
}
