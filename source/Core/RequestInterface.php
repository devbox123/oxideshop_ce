<?php


namespace OxidEsales\Eshop\Core;


interface RequestInterface
{
    public function getRequestParameter($name, $blRaw = false);
    public function getRequestRawParameter($name, $defaultValue = null);
    public function getRequestEscapedParameter($name, $defaultValue = null);
    public function checkParamSpecialChars(& $sValue, $aRaw = null);
}