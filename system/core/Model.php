<?php


namespace ErkinApp;


use Envms\FluentPDO\Query;


abstract class Model
{
    protected $table_name;

    /**
     * @var Query
     */
    protected $db;

    /**
     * Model constructor.
     * @param $db
     */
    function __construct()
    {
        $this->db = ErkinApp()->DB('default');
    }

    public function __get($name)
    {
        return ErkinApp()->Get($name);
    }


    /**
     * @return mixed
     */
    function getTableName()
    {
        return $this->table_name;
    }

    function getDb($dbkey = 'default')
    {
        return ErkinApp()->DB($dbkey);
    }

    function from($primary_key = null)
    {
        return $this->db->from($this->table_name, $primary_key);
    }

    function get($primary_key = null)
    {
        return $this->db->from($this->table_name, $primary_key)->fetch();
    }

    /**
     * @param $condition
     * @param array $parameters
     * @param string $separator
     * @return \Envms\FluentPDO\Queries\Select
     * @throws \Envms\FluentPDO\Exception
     */
    function where($condition, $parameters = [], $separator = 'AND')
    {
        return $this->db->from($this->table_name)->where($condition, $parameters, $separator);
    }

    /**
     * @param $primary_key
     * @return \Envms\FluentPDO\Queries\Delete
     * @throws \Envms\FluentPDO\Exception
     */
    function delete($primary_key = null)
    {
        return $this->db->delete($this->table_name, $primary_key);
    }

    /**
     *
     * Use batch operation
     *
     * @param array $values
     * @return bool
     * @throws \Envms\FluentPDO\Exception
     */
    function insertTransaction($values = [])
    {
        $this->beginTransaction();
        foreach ($values as $value) {
            $this->insert($value)->execute();
        }
        return $this->commit();
    }

    /**
     * @return bool
     */
    function beginTransaction()
    {
        return $this->db->getPdo()->beginTransaction();
    }

    /**
     * @param array $values
     * @return \Envms\FluentPDO\Queries\Insert
     * @throws \Envms\FluentPDO\Exception
     */
    function insert($values = [])
    {
        return $this->db->insertInto($this->table_name, $values);
    }

    /**
     * @return bool
     */
    function commit()
    {
        return $this->db->getPdo()->commit();
    }

    /**
     *
     *  Use batch operation
     *
     * @param array $values
     * @return bool
     * @throws \Envms\FluentPDO\Exception
     */
    function insertOrUpdateTransaction($values = [])
    {
        $this->beginTransaction();
        foreach ($values as $value) {
            $this->insertOrUpdate($value)->execute();
        }
        return $this->commit();
    }

    /**
     * @param array $values
     * @return \Envms\FluentPDO\Queries\Insert
     * @throws \Envms\FluentPDO\Exception
     */
    function insertOrUpdate($values = [], $onDuplicateKeyUpdateValues = [])
    {
        if (!$onDuplicateKeyUpdateValues) $onDuplicateKeyUpdateValues = $values;
        return $this->db->insertInto($this->table_name, $values)->onDuplicateKeyUpdate($onDuplicateKeyUpdateValues);
    }

    /**
     * @param array $set
     * @param null $primary_key
     * @return \Envms\FluentPDO\Queries\Update
     * @throws \Envms\FluentPDO\Exception
     */
    function update($set = [], $primary_key = null)
    {
        return $this->db->update($this->table_name, $set, $primary_key);
    }

    function getLastInsertId()
    {
        return $this->db->getPdo()->lastInsertId();
    }

    function getLastError()
    {
        if ($this->db->getPdo()->errorCode() == '000000') return false;
        return implode(':', $this->db->getPdo()->errorInfo());
    }


}