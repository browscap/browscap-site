<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use phpbrowscap\Browscap as BrowscapPHP;
use Symfony\Component\HttpFoundation\Request;

class UserAgentLookupController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
    }

    public function getBrowscap(Request $request)
    {
        $baseHost = $request->getSchemeAndHttpHost();

        $browscap = new BrowscapPHP(__DIR__ . '/../../../cache/');
        $browscap->remoteIniUrl = $baseHost . '/stream?q=Full_PHP_BrowsCapINI';
        $browscap->remoteVerUrl = $baseHost . '/version';

        return $browscap;
    }

    public function indexAction()
    {
        $metadata = $this->app['metadata'];
        $request = $this->app->getRequest();

        $ua = $request->server->get('HTTP_USER_AGENT');
        $uaInfo = false;

        session_start();

        if ($request->request->has('ua')) {
            $this->csrfCheck();

            $ua = $request->request->get('ua');

            $browscap = $this->getBrowscap($request);
            $uaInfo = $browscap->getBrowser($ua, true);
            $this->convertBooleansToStrings($uaInfo);
        }

        $csrfToken = $this->csrfSet();

        return $this->app['twig']->render('ua-lookup.html', [
            'uaInfo' => $uaInfo,
            'ua' => $ua,
            'csrfToken' => $csrfToken,
            'version' => $metadata['version'],
        ]);
    }

    public function convertBooleansToStrings(&$uaInfo)
    {
        foreach ($uaInfo as $key => $value) {
            if (is_bool($value)) {
                $uaInfo[$key] = ($value ? 'true' : 'false');
            }
        }
    }

    public function csrfCheck()
    {
        if ($this->app->getConfig('debug')) {
            return;
        }

        $csrfToken = isset($_SESSION['csrfToken']) ? $_SESSION['csrfToken'] : null;
        unset($_SESSION['csrfToken']);

        $request = $this->app->getRequest();
        $requestHasToken = $request->request->has('csrfToken');

        if (!$requestHasToken || !$csrfToken || ($request->request->get('csrfToken') != $csrfToken)) {
            throw new \Exception('CSRF token not correct...');
        }
    }

    public function csrfSet()
    {
        $csrfToken = hash('sha256', uniqid() . microtime());
        $_SESSION['csrfToken'] = $csrfToken;
        return $csrfToken;
    }
}
