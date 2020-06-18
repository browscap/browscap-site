<?php

declare(strict_types=1);

namespace BrowscapSite\Renderer;

use Psr\Http\Message\ResponseInterface;

interface Renderer
{
    /** @param mixed[] $params */
    public function render(string $template, array $params = []): ResponseInterface;
}
