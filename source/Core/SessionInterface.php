<?php


namespace OxidEsales\Eshop\Core;

interface SessionInterface
{
    public function getVariable($name);
    public function setVariable($name, $value);
    public function getBasket();
    public function hiddenSid();
    public function getSessionChallengeToken();
    public function isActualSidInCookie();
    public function getId();
    public function sid($blForceSid = false);
    public function regenerateSessionId();
    public function checkSessionChallenge();
    public function deleteVariable($name);
    public function delBasket();
    public function processUrl($sUrl);
    public function isSessionStarted();
    public function isHeaderSent();
    public function setForceNewSession();
    public function start();
    public function freeze();
}