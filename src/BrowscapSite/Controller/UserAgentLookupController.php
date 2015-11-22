<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use BrowscapSite\Tool\BrowscapPhpTool;

class UserAgentLookupController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
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

            $uaInfo = (array)(new BrowscapPhpTool())->identify($ua);
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

        if (!$requestHasToken || !$csrfToken || !hash_equals($csrfToken, $request->request->get('csrfToken'))) {
            throw new \Exception('CSRF token not correct...');
        }
    }

    public function csrfSet()
    {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrfToken'] = $csrfToken;
        return $csrfToken;
    }
}
