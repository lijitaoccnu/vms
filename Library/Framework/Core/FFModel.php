<?php
/**
 * 建模器
 */

namespace FF\Framework\Core;

use FF\Framework\Driver\DbDriver;

class FFModel
{
    private $driver; //数据库实例
    private $buffer = array(); //数据缓存

    /**
     * 获取数据库操作实例
     * @return DbDriver
     */
    public function db()
    {
        return $this->driver;
    }

    /**
     * 设置数据库操作实例
     * @param $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * 插入一条数据到数据库
     * @param $data
     * @return int
     */
    public function insert($data)
    {
        return $this->db()->insert($data)->execute();
    }

    /**
     * 插入多条数据到数据库
     * @param array $data
     * @return int
     */
    public function insertMulti($data)
    {
        return $this->db()->insertMulti($data);
    }

    /**
     * 从数据库查询一条数据
     * @param array | string $where
     * @param string $fields
     * @param array $orderBy
     * @param string $groupBy
     * @return array
     */
    public function fetchOne($where, $fields = null, $orderBy = array(), $groupBy = '')
    {
        return $this->db()
            ->select($fields)
            ->where($where)
            ->orderBy($orderBy)
            ->groupBy($groupBy)
            ->limit(1)
            ->fetchOne();
    }

    /**
     * 从数据库查询多条数据
     * @param array $where
     * @param string $fields
     * @param array $orderBy
     * @param string $groupBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function fetchAll($where = array(), $fields = null, $orderBy = array(), $groupBy = '', $limit = 0, $offset = 0)
    {
        return $this->db()
            ->select($fields)
            ->where($where)
            ->orderBy($orderBy)
            ->groupBy($groupBy)
            ->limit($limit, $offset)
            ->fetchAll();
    }

    /**
     * 从数据库查询一条数据[脚本缓存]
     * @param $bufferKey
     * @param $where
     * @param string $fields
     * @param array $orderBy
     * @param string $groupBy
     * @return array|mixed
     */
    public function fetchOneWithBuffer($bufferKey, $where, $fields = null, $orderBy = array(), $groupBy = '')
    {
        $result = $this->getBuffer($bufferKey);

        if (!$result) {
            $result = $this->fetchOne($where, $fields, $orderBy, $groupBy);
            $result && $this->setBuffer($bufferKey, $result);
        }

        return $result;
    }

    /**
     * 更新一条数据库记录
     * @param array $data
     * @param array $where
     * @param int $limit
     * @return int
     */
    public function update($data, $where, $limit = 1)
    {
        return $this->db()
            ->update($data)
            ->where($where)
            ->limit($limit)
            ->execute();
    }

    /**
     * 删除一条数据库记录
     * @param array $where
     * @param int $limit
     * @return int
     */
    public function delete($where, $limit = 1)
    {
        return $this->db()
            ->delete()
            ->where($where)
            ->limit($limit)
            ->execute();
    }

    /**
     * 获取数据缓存
     * @param string $key
     * @return mixed
     */
    public function getBuffer($key)
    {
        if (isset($this->buffer[$key])) {
            return $this->buffer[$key];
        } else {
            return null;
        }
    }

    /**
     * 设置数据缓存
     * @param string $key
     * @param mixed $data
     */
    public function setBuffer($key, $data)
    {
        $this->buffer[$key] = $data;
    }
}