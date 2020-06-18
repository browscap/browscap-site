<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PsrRequestHandlerWrapper
{
    private RequestHandlerInterface $requestHandler;

    public function __construct(RequestHandlerInterface $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /** @param mixed $mixed */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $mixed): ResponseInterface
    {
        return $this->requestHandler->handle($request);
    }
}
