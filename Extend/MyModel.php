<?php
/**
 * 数据模型扩展
 */

namespace FF\Extend;

use FF\Framework\Driver\DbDriver;
use FF\Factory\Dao;
use FF\Framework\Core\FFModel;

class MyModel extends FFModel
{
    private $dbAlias = ''; //数据库别名(配置名)
    private $table = ''; //数据真实表名

    public function __construct($dbAlias = '', $table = '')
    {
        $this->dbAlias = $dbAlias;
        $this->table = $table;
    }

    /**
     * 获取数据库操作实例
     * @return DbDriver
     */
    public function db()
    {
        $pdo = Dao::db($this->dbAlias);

        return new DbDriver($pdo, $this->table);
    }

    /**
     * 分页查询
     * @param int $page
     * @param int $limit
     * @param array $where
     * @param string $fields
     * @param array $orderBy
     * @param string $groupBy
     * @return array
     */
    public function getPageList($page, $limit, $where = array(), $fields = null, $orderBy = array(), $groupBy = '')
    {
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $limit;

        $data = array(
            'total' => 0, 'limit' => $limit, 'page' => $page, 'list' => array()
        );

        $result = $this->fetchOne($where, 'COUNT(1) AS count', null, $groupBy);
        $data['total'] = $result ? (int)$result['count'] : 0;

        if ($data['total']) {
            $data['list'] = $this->fetchAll($where, $fields, $orderBy, $groupBy, $limit, $offset);
        }

        return $data;
    }

    public function getOneById($id)
    {
        return $this->fetchOne(array('id' => $id));
    }

    public function updateById($id, $data)
    {
        if (!$data) return 0;

        return $this->update($data, array('id' => $id));
    }

    public function deleteById($id)
    {
        return $this->delete(array('id' => $id));
    }
}