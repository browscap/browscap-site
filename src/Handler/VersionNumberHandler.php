<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class VersionNumberHandler implements RequestHandlerInterface
{
    public function __construct(private Renderer $renderer, private Metadata $metadata)
    {
    }

    /** @throws Exception */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->renderer->render(
            'version-number.html',
            [
                'version' => $this->metadata->version(),
            ],
        );
    }
}
