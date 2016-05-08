<?php

namespace OxidEsales\Eshop\Core;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SymfonySession implements SessionInterface
{
    private $symfonySession;

    public function __construct()
    {
        $storage = new NativeSessionStorage(array(), new NativeFileSessionHandler());

        $this->symfonySession = new Session($storage);
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

    private $basket;
    public function getBasket()
    {
        if (null === $this->basket) {
            /* #var \oxbasket $basket */
            $this->basket = oxNew('oxbasket');
            $this->basket->load($this->getId());
            $this->basket->setId($this->getId());
        }

        return $this->basket;
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