<?php

namespace BrowscapSite\Controller;

use BrowscapSite\BrowscapSiteWeb;
use phpbrowscap\Browscap as BrowscapPHP;

class UserAgentLookupController
{
    protected $app;

    public function __construct(BrowscapSiteWeb $app)
    {
        $this->app = $app;
    }

    public function getBrowscap()
    {
        $baseHost = 'http://' . $_SERVER['SERVER_NAME'];

        $browscap = new BrowscapPHP(__DIR__ . '/../../../cache/');
        $browscap->remoteIniUrl = $baseHost  . '/stream?q=Full_PHP_BrowsCapINI';
        $browscap->remoteVerUrl = $baseHost . '/version-date.php';

        return $browscap;
    }

    public function indexAction()
    {
        $metadata = $this->getMetadata();

        $ua = $_SERVER['HTTP_USER_AGENT'];
        $uaInfo = false;

        session_start();

        if (isset($_POST['ua'])) {
            $this->csrfCheck();

            $ua = $_POST['ua'];

            $browscap = $this->getBrowscap();
            $uaInfo = $browscap->getBrowser($ua, true);
            $this->convertBooleansToStrings($uaInfo);
        }

        $csrfToken = $this->csrfSet();

        return $this->app['twig']->render('ua-lookup.html', array(
            'uaInfo' => $uaInfo,
            'ua' => $ua,
            'csrfToken' => $csrfToken,
            'version' => $metadata['version'],
        ));
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
        $csrfToken = isset($_SESSION['csrfToken']) ? $_SESSION['csrfToken'] : null;
        unset($_SESSION['csrfToken']);

        if (!isset($_POST['csrfToken']) || !$csrfToken || ($_POST['csrfToken'] != $csrfToken)) {
            throw new \Exception("CSRF token not correct...");
        }
    }

    public function csrfSet()
    {
        $csrfToken = hash('sha256', uniqid() . microtime());
        $_SESSION['csrfToken'] = $csrfToken;
        return $csrfToken;
    }

    public function getMetadata()
    {
        return require_once(__DIR__ . '/../../../build/metadata.php');
    }
}
