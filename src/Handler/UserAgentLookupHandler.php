<?php
declare(strict_types=1);

namespace BrowscapSite\Handler;

use BrowscapSite\Metadata\Metadata;
use BrowscapSite\Renderer\Renderer;
use BrowscapSite\UserAgentTool\UserAgentTool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function is_bool;

final class UserAgentLookupHandler implements RequestHandlerInterface
{
    /** @var Renderer */
    private $renderer;

    /** @var Metadata */
    private $metadata;

    /** @var UserAgentTool */
    private $userAgentTool;

    /** @var bool */
    private $checkCsrf;

    public function __construct(Renderer $renderer, Metadata $metadata, UserAgentTool $userAgentTool, bool $checkCsrf = true)
    {
        $this->renderer = $renderer;
        $this->metadata = $metadata;
        $this->userAgentTool = $userAgentTool;
        $this->checkCsrf = $checkCsrf;
    }

    /**
     * @throws \BrowscapPHP\Exception
     * @throws \Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userAgent = $request->getServerParams()['HTTP_USER_AGENT'];
        $userAgentInfo = false;

        $parsedBody = $request->getParsedBody();

        if ($parsedBody['ua']) {
            $this->csrfCheck($request);

            $userAgent = $parsedBody['ua'];

            $userAgentInfo = (array)$this->userAgentTool->identify($userAgent);
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

    public function convertBooleansToStrings(array &$uaInfo): void
    {
        foreach ($uaInfo as $key => $value) {
            if (is_bool($value)) {
                $uaInfo[$key] = ($value ? 'true' : 'false');
            }
        }
    }

    public function csrfCheck(ServerRequestInterface $request): void
    {
        if (!$this->checkCsrf) {
            return;
        }

        $csrfTokenFromSession = $_SESSION['csrfToken'] ?? null;
        unset($_SESSION['csrfToken']);

        $parsedBody = $request->getParsedBody();

        if (!array_key_exists('csrfToken', $parsedBody)
            || !$csrfTokenFromSession
            || !hash_equals($csrfTokenFromSession, $parsedBody['csrfToken'])) {
            throw new \RuntimeException('CSRF token not correct...');
        }
    }

    /**
     * @throws \Exception
     */
    public function csrfSet(): string
    {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrfToken'] = $csrfToken;
        return $csrfToken;
    }
}
