<?php
/**
 * 项目模型
 */

namespace FF\Model;

use FF\Extend\MyModel;

class ProjectModel extends MyModel
{
    public function __construct()
    {
        parent::__construct(DB_MAIN, 't_project');
    }

    public function addOne($data)
    {
        $data['createTime'] = now();

        return $this->insert($data);
    }

    public function getOneByName($name)
    {
        return $this->fetchOne(array('name' => $name));
    }
}