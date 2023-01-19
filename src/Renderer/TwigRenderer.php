<?php

declare(strict_types=1);

namespace BrowscapSite\Renderer;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class TwigRenderer implements Renderer
{
    public function __construct(private Twig $twig, private ResponseInterface $baseResponse)
    {
    }

    /** @param mixed[] $params */
    public function render(string $template, array $params = []): ResponseInterface
    {
        return $this->twig->render($this->baseResponse, $template, $params);
    }
}
