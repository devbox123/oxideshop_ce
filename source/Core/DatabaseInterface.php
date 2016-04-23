<?php


namespace OxidEsales\Eshop\Core;


interface DatabaseInterface
{
    public function getRow($sql, array $params = null);
    public function getOne($sql, array $params = null);
    public function execute($sql, array $params = null);
    public function getAll($sql, array $params = null);
    public function startTransaction();
    public function commitTransaction();
    public function rollbackTransaction();
    public function getCol($sql, array $params = null);
    public function quote($sValue);
    public function quoteArray($arrayOfStrings);
}