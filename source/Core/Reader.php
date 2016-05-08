<?php

namespace OxidEsales\Eshop\Core;

class Reader
{
    private $database;
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function load($id, Base $target)
    {

        $query = "select * from " . $target->getViewName() . ' where oxid = ?';
        $row = $this->database->getRow($query, [$id]);
        if (false === $row) {
            return false;
        }

        foreach ($row as $field => $value) {
            $target->setFieldValue($field, $value);
        }

        return true;
    }

}