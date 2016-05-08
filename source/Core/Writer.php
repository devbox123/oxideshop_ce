<?php

namespace OxidEsales\Eshop\Core;

class Writer
{
    private $database;
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * @param $id
     * @param Base $target
     * @return mixed
     */
    public function save($id, Base $target)
    {
        if ($this->exists($id, $target)) {
            return $this->update($id, $target);
        }

        return $this->insert($id, $target);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {

    }

    /**
     * @param $id
     * @param Base $target
     * @return bool
     */
    public function exists($id, Base $target)
    {
        $query = "SELECT 1 FROM " . $target->getViewName() . ' WHERE oxid = ?';
        return (bool) $this->database->getOne($query, [$id]);
    }

    /**
     * @param $id
     * @param Base $target
     * @return mixed
     */
    private function update($id, Base $target)
    {
        $coreTableName = $target->getCoreTableName();
        $data = $this->getUpdateFields($target);

        $query = "UPDATE {$coreTableName} SET " . implode(',', array_keys($data)) . " WHERE {$coreTableName}.oxid = ?";
        return $this->database->execute($query, array_merge(array_values($data), [$id]));
    }

    /**
     * @param Base $target
     * @return array
     */
    protected function getUpdateFields(Base $target)
    {
        $data = [];
        foreach ($target->getFieldNames() as $name) {
            $longName = $target->getFieldLongName($name);
            if (null !== $target->{$longName}->getRawValue()) {
                $data["{$name} = ?"] = $target->{$longName}->getRawValue();
            }

        }

        return $data;
    }

    /**
     * @param $id
     * @param Base $target
     * @return mixed
     */
    private function insert($id, Base $target)
    {
        $coreTableName = $target->getCoreTableName();
        $data = $this->getInsertFields($target);

        $query = "INSERT INTO {$coreTableName} SET " . implode(',', array_keys($data));
        return $this->database->execute($query, array_merge(array_values($data)));
    }

    /**
     * @param Base $target
     * @return array
     */
    protected function getInsertFields(Base $target)
    {
        $data = [];
        foreach ($target->getFieldNames() as $name) {
            $longName = $target->getFieldLongName($name);
            if (null !== $target->{$longName}->getRawValue()) {
                $data["{$name} = ?"] = $target->{$longName}->getRawValue();
            }

        }

        return $data;
    }
}