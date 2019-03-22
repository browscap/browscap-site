<?php

declare(strict_types=1);

namespace BrowscapSite\Renderer;

use Psr\Http\Message\ResponseInterface;

interface Renderer
{
    public function render(string $template, array $params = []): ResponseInterface;
}
