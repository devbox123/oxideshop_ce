<?php

namespace OxidEsales\Eshop\Core;


class PdoDatabase implements DatabaseInterface
{
    private $pdo;

    public function __construct(\oxConfig $config)
    {
        $dsn = 'mysql:dbname=%s;host=%s';

        $this->pdo = new \PDO(
            sprintf(
                $dsn,
                $config->getConfigParam('dbName'),
                $config->getConfigParam('localhost')
            ),
            $config->getConfigParam('dbUser'),
            $config->getConfigParam('dbPwd')
        );
    }


    public function getRow($sql, array $params = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function getOne($sql, array $params = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    public function execute($sql, array $params = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    /**
     * @param $sql
     * @param array|null $params
     * @return \Generator
     */
    public function getAll($sql, array $params = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        //return $statement->fetchAll();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            yield $result;
        }
    }

    public function getCol($sql, array $params = null)
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function startTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commitTransaction()
    {
        return $this->pdo->commit();
    }

    public function rollbackTransaction()
    {
        return $this->pdo->rollBack();
    }

    public function quote($sValue)
    {
        return $this->pdo->quote($sValue);
    }

    public function quoteArray($arrayOfStrings)
    {
        return array_map(
            function ($value) {
                return $this->quote($value);
            },
            $arrayOfStrings
        );
    }
}
