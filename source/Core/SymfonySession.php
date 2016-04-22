<?php

namespace OxidEsales\Eshop\Core;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

class SymfonySession implements oxSessionInterface
{
    private $symfonySession;
    private $sessionStarted = false;

    public function __construct()
    {
        $this->symfonySession = new Session(new PhpBridgeSessionStorage());
    }

    private function start()
    {
        if (false === $this->sessionStarted) {
            //$this->sessionStarted = $this->symfonySession->start();
        }
    }

    public function getVariable($name)
    {
        $this->start();
        return $this->symfonySession->get($name);
    }

    public function setVariable($name, $value)
    {
        $this->start();
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
}