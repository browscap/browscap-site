<?php
declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class VersionHandler implements RequestHandlerInterface
{
    /** @var Renderer */
    private $renderer;

    /** @var Metadata */
    private $metadata;

    public function __construct(Renderer $renderer, Metadata $metadata)
    {
        $this->renderer = $renderer;
        $this->metadata = $metadata;
    }

    /**
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->renderer->render(
            'version.html',
            [
                'released' => $this->metadata->released()->format('r')
            ]
        );
    }
}
