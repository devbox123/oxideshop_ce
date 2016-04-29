<?php

namespace OxidEsales\Eshop\Core;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

class SymfonySession implements SessionInterface
{
    private $symfonySession;

    public function __construct()
    {
        $this->symfonySession = new Session(new PhpBridgeSessionStorage());
        $this->symfonySession->start();
    }

    public function getVariable($name)
    {
        return $this->symfonySession->get($name);
    }

    public function setVariable($name, $value)
    {
        $this->symfonySession->set($name, $value);
    }

    public function getBasket()
    {
        return oxNew('oxBasket');
    }

    public function hiddenSid()
    {
        //$sToken = "<input type=\"hidden\" name=\"stoken\" value=\"" . $this->symfonySession->getSessionChallengeToken() . "\" />";

        //return $sToken;
        return '';
    }

    public function getSessionChallengeToken()
    {
        return '';
    }

    public function isActualSidInCookie()
    {
        return false;
    }

    public function getId()
    {
        return $this->symfonySession->getId();
    }

    public function sid($blForceSid = false)
    {
        return 'sid=' . $this->getId();
    }

    public function regenerateSessionId()
    {
        $this->symfonySession->migrate();
    }

    public function checkSessionChallenge()
    {
        return true;
    }

    public function deleteVariable($name)
    {
        $this->symfonySession->remove($name);
    }

    public function delBasket()
    {
    }

    public function processUrl($sUrl)
    {
        return $sUrl;
    }

    public function isSessionStarted()
    {
        return $this->symfonySession->isStarted();
    }

    public function isHeaderSent()
    {
        return headers_sent();
    }

    public function setForceNewSession()
    {

    }

    public function start()
    {
    }

    public function freeze()
    {
        $bl = $this->symfonySession->isStarted();
        $this->symfonySession->save();
    }
}