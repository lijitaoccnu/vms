<?php
/**
 * Class DbDriver
 */

namespace FF\Framework\Driver;

use FF\Framework\Common\Code;
use FF\Framework\Common\DBResult;
use FF\Framework\Driver\Extend\_Pdo;
use FF\Framework\Core\FF;

class DbDriver
{
    /**
     * @var _Pdo
     */
    protected $pdo = null;

    protected $table = '';
    protected $action = '';
    protected $fields = '';
    protected $data = array();
    protected $where = array();
    protected $orderBy = array();
    protected $groupBy = array();
    protected $offset = null;
    protected $limit = null;
    protected $params = array();

    public function __construct($pdo, $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function select($fields = null)
    {
        $this->action = 'SELECT';
        $this->fields = $fields ? $fields : '*';
        return $this;
    }

    public function insert($data)
    {
        $this->action = 'INSERT';
        $this->data = $data;
        return $this;
    }

    public function update($data)
    {
        $this->action = 'UPDATE';
        $this->data = $data;
        return $this;
    }

    public function delete()
    {
        $this->action = 'DELETE';
        $this->limit = 1;
        return $this;
    }

    public function where($where)
    {
        $this->where = $where;
        return $this;
    }

    public function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function limit($limit, $offset = NUll)
    {
        $this->offset = $offset;
        $this->limit = $limit;
        return $this;
    }

    /**
     * 构造sql
     * @return string
     * @throws \Exception
     */
    public function buildSql()
    {
        $sql = "";

        $builder = new SqlBuilder($this->pdo);

        switch ($this->action) {
            case 'INSERT':
                $sets = $builder->sets($this->data, $this->params);
                if (!$sets) $this->throwException('sets is empty');
                $sql = "INSERT IGNORE INTO {$this->table} SET {$sets}";
                break;
            case 'SELECT':
                $wheres = $builder->where($this->where, $this->params);
                if (!$wheres) $wheres = 1;
                $sql = "SELECT {$this->fields} FROM {$this->table} WHERE {$wheres}";
                break;
            case 'UPDATE':
                $sets = $builder->sets($this->data, $this->params);
                if (!$sets) $this->throwException('sets is empty');
                $wheres = $builder->where($this->where, $this->params);
                if (!$wheres) $this->throwException('where is invalid');
                $sql = "UPDATE {$this->table} SET {$sets} WHERE {$wheres}";
                break;
            case 'DELETE':
                $wheres = $builder->where($this->where, $this->params);
                if (!$wheres) $this->throwException('where is invalid');
                $sql = "DELETE FROM {$this->table} WHERE {$wheres}";
                break;
            default:
                $this->throwException('action is invalid');
                break;
        }

        if ($this->groupBy) {
            $sql .= " GROUP BY {$this->groupBy}";
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY " . $builder->orderBy($this->orderBy);
        }

        if ($this->limit) {
            $sql .= " LIMIT " . ($this->offset ? "{$this->offset},{$this->limit}" : $this->limit);
        }

        return $sql;
    }

    /**
     * 抛出查询错误
     * @param $message
     * @throws \Exception
     */
    private function throwException($message)
    {
        FF::throwException(Code::SYSTEM_ERROR, "SqlDriver::{$message}");
    }

    /**
     * 构造并执行sql
     * @param int $resultMode 返回值类型
     * @return array|int|\PDOStatement
     */
    public function execute($resultMode = DBResult::AFFECTED_ROWS)
    {
        $sql = $this->buildSql();

        $result = $this->pdo->execute($sql, $this->params, $resultMode);

        if ($this->action == 'INSERT' && $result) {
            $lastInsertId = $this->pdo->lastInsertId();
            $result = $lastInsertId ? $lastInsertId : $result;
        }

        return $result;
    }

    /**
     * 直接执行指定sql
     * @param string $sql SQL语句
     * @param int $resultMode 返回值类型
     * @return array|int|\PDOStatement
     */
    public function query($sql, $resultMode = DBResult::AFFECTED_ROWS)
    {
        return $this->pdo->query($sql, $resultMode);
    }

    /**
     * 插入多条记录
     * @param array $data 插入数据(二维数组)
     * @return int
     */
    public function insertMulti($data)
    {
        if (!$data || !is_array($data)) {
            return 0;
        }

        $values = array();
        $fields = array_keys($data[0]);

        foreach ($data as $row) {
            $row = array_recombine($row, $fields);
            foreach ($row as $k => $v) {
                $row[$k] = $this->pdo->quote($v);
            }
            $values[] = '(' . implode(',', $row) . ')';
        }

        if (!$values) return 0;

        $fields = implode(',', $fields);
        $values = implode(',', $values);

        $sql = "INSERT IGNORE INTO {$this->table} ({$fields}) VALUES {$values}";

        return $this->pdo->query($sql, DBResult::AFFECTED_ROWS);
    }

    /**
     * 执行sql查询并获取一条记录
     * @return array
     */
    public function fetchOne()
    {
        return $this->execute(DBResult::FETCH_ONE);
    }

    /**
     * 执行sql查询并获取所有记录
     * @return array
     */
    public function fetchAll()
    {
        return $this->execute(DBResult::FETCH_ALL);
    }
}