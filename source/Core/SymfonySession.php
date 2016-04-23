<?php

namespace OxidEsales\Eshop\Core;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

class SymfonySession implements oxSessionInterface
{
    private $symfonySession;

    public function __construct()
    {
        $this->symfonySession = new Session(new PhpBridgeSessionStorage());
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
}