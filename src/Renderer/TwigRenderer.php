<?php

declare(strict_types=1);

namespace BrowscapSite\Renderer;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

final class TwigRenderer implements Renderer
{
    private Twig $twig;
    private ResponseInterface $baseResponse;

    public function __construct(Twig $twig, ResponseInterface $baseResponse)
    {
        $this->twig = $twig;
        $this->baseResponse = $baseResponse;
    }

    public function render(string $template, array $params = []) : ResponseInterface
    {
        return $this->twig->render($this->baseResponse, $template, $params);
    }
}
