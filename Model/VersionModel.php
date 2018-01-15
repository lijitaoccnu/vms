<?php
/**
 * 版本模型
 */

namespace FF\Model;

use FF\Extend\MyModel;

class VersionModel extends MyModel
{
    public function __construct()
    {
        parent::__construct(DB_MAIN, 't_version');
    }

    public function addOne($data, $createBy)
    {
        $data['createBy'] = $createBy;
        $data['createTime'] = now();

        return $this->insert($data);
    }

    public function getOneByVersion($projId, $version)
    {
        $where = array('projId' => $projId, 'version' => $version);

        return $this->fetchOne($where);
    }

    public function getReleaseAble($projId)
    {
        $fields = 'id,version,title,package,pushOverTime';

        $where = array('projId' => $projId, 'publishOverTime' => null);

        return $this->fetchAll($where, $fields, 'id DESC', null, 10);
    }
}