<?php
/**
 * 更新包模型
 */

namespace FF\Model;

use FF\Extend\MyModel;

class PackageModel extends MyModel
{
    public function __construct()
    {
        parent::__construct(DB_MAIN, 't_package');
    }

    public function addOne($uuid, $filename, $savePath, $saveName)
    {
        $data = array(
            'uuid' => $uuid,
            'filename' => $filename,
            'savePath' => $savePath,
            'saveName' => $saveName,
            'uploadTime' => now()
        );

        return $this->insert($data);
    }

    public function getOneByUuid($uuid)
    {
        return $this->fetchOne(array('uuid' => $uuid));
    }

    public function getAllByUuids($uuids)
    {
        if (!$uuids || !is_array($uuids)) return array();

        $result = $this->fetchAll(array('uuid' => array('in', $uuids)));

        return $result ? array_column($result, null, 'uuid') : array();
    }
}