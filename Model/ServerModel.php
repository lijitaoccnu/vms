<?php
/**
 * 服务器模型
 */

namespace FF\Model;

use FF\Extend\MyModel;

class ServerModel extends MyModel
{
    public function __construct()
    {
        parent::__construct(DB_MAIN, 't_server');
    }

    public function addOne($data)
    {
        $data['createTime'] = now();

        return $this->insert($data);
    }

    public function getOneByHost($projId, $host)
    {
        $where = array('projId' => $projId, 'host' => $host);

        return $this->fetchOne($where);
    }

    public function getAllByProject($projId, $fields = null)
    {
        $where = array('projId' => $projId);

        return $this->fetchAll($where, $fields);
    }
}