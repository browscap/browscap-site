<?php

declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapPHP\Exception;
use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use BrowscapSite\UserAgentTool\UserAgentTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

use function array_key_exists;
use function bin2hex;
use function hash_equals;
use function is_array;
use function is_bool;
use function is_string;
use function random_bytes;

final class UserAgentLookupHandler implements RequestHandlerInterface
{
    private Renderer $renderer;
    private Metadata $metadata;
    private UserAgentTool $userAgentTool;
    private bool $checkCsrf;

    public function __construct(Renderer $renderer, Metadata $metadata, UserAgentTool $userAgentTool, bool $checkCsrf = true)
    {
        $this->renderer      = $renderer;
        $this->metadata      = $metadata;
        $this->userAgentTool = $userAgentTool;
        $this->checkCsrf     = $checkCsrf;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userAgent     = $request->getServerParams()['HTTP_USER_AGENT'];
        $userAgentInfo = false;

        $parsedBody = $request->getParsedBody();

        if (is_array($parsedBody) && array_key_exists('ua', $parsedBody) && is_string($parsedBody['ua'])) {
            $this->csrfCheck($request);

            $userAgent = $parsedBody['ua'];

            $userAgentInfo = (array) $this->userAgentTool->identify($userAgent);
            $this->convertBooleansToStrings($userAgentInfo);
        }

        return $this->renderer->render(
            'ua-lookup.html',
            [
                'uaInfo' => $userAgentInfo,
                'ua' => $userAgent,
                'csrfToken' => $this->csrfSet(),
                'version' => $this->metadata->version(),
            ]
        );
    }

    /**
     * @param mixed[] $uaInfo
     */
    private function convertBooleansToStrings(array &$uaInfo): void
    {
        foreach ($uaInfo as $key => $value) {
            if (! is_bool($value)) {
                continue;
            }

            $uaInfo[$key] = ($value ? 'true' : 'false');
        }
    }

    private function csrfCheck(ServerRequestInterface $request): void
    {
        if (! $this->checkCsrf) {
            return;
        }

        $csrfTokenFromSession = $_SESSION['csrfToken'] ?? null;
        unset($_SESSION['csrfToken']);

        $parsedBody = $request->getParsedBody();

        if (
            ! is_array($parsedBody)
            || ! array_key_exists('csrfToken', $parsedBody)
            || ! $csrfTokenFromSession
            || ! hash_equals($csrfTokenFromSession, $parsedBody['csrfToken'])
        ) {
            throw new RuntimeException('CSRF token not correct...');
        }
    }

    /**
     * @throws \Exception
     */
    private function csrfSet(): string
    {
        $csrfToken             = bin2hex(random_bytes(32));
        $_SESSION['csrfToken'] = $csrfToken;

        return $csrfToken;
    }
}
