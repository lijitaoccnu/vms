<?php
/**
 * 版本业务逻辑
 */

namespace FF\Controller;

use FF\Framework\Common\Code;
use FF\Extend\MyController;
use FF\Factory\Model;
use FF\Framework\Core\FF;
use FF\Library\Utils\Pager;

class VersionController extends MyController
{
    public function index()
    {
        $projId = (int)$this->getParam('projId', false, 0);
        $page = (int)$this->getParam('page', false, 1);
        $limit = (int)$this->getParam('limit', false, 10);

        if ($projId) {
            $where = array('projId' => $projId);
            $result = Model::version()->getPageList($page, $limit, $where, null, 'id DESC');
            $data['list'] = $result['list'];
            $data['pager'] = new Pager($result);
            $data['project'] = Model::project()->getOneById($projId);
            $uuids = array_filter(array_column($data['list'], 'package'));
            $data['packages'] = Model::package()->getAllByUuids($uuids);
        }

        $data['projects'] = Model::project()->fetchAll();

        $this->display('index.html', $data);
    }

    public function edit()
    {
        $id = (int)$this->getParam('id', false, 0);

        $data['version'] = $id ? Model::version()->getOneById($id) : array();
        $data['projects'] = Model::project()->fetchAll();

        if ($data['version'] && $data['version']['package']) {
            $data['package'] = Model::package()->getOneByUuid($data['version']['package']);
        }

        $upload_max_filesize = strtoupper(ini_get('upload_max_filesize'));
        $upload_max_filesize = ((float)substr($upload_max_filesize, 0, -1)) * 1024 * 1024;
        $data['upload_max_filesize'] = $upload_max_filesize;

        $this->display('edit.html', $data);
    }

    public function create()
    {
        $data = array(
            'projId' => (int)$this->getParam('projId'),
            'version' => $this->getParam('version'),
            'title' => $this->getParam('title'),
            'detail' => $this->getParam('detail'),
            'package' => $this->getParam('package', false),
            'createTime' => now(),
        );

        $result = $this->_create('version', $data);
        $result['redirect'] = '/version/index?projId=' . $data['projId'];

        return $result;
    }

    public function update()
    {
        $id = (int)$this->getParam('id');

        $data = array(
            'projId' => (int)$this->getParam('projId'),
            'version' => $this->getParam('version'),
            'title' => $this->getParam('title'),
            'detail' => $this->getParam('detail'),
            'package' => $this->getParam('package', false),
        );

        $result = $this->_update('version', $id, $data);
        $result['redirect'] = '/version/index?projId=' . $data['projId'];

        return $result;
    }

    public function delete()
    {
        $id = (int)$this->getParam('id');

        return $this->_delete('version', $id);
    }

    public function lists()
    {
        $projId = (int)$this->getParam('projId');

        return Model::version()->getReleaseAble($projId);
    }

    protected function checkData($modelName, $data, $oldData = null)
    {
        if (isset($data['projId']) || isset($data['version'])) {
            $projId = isset($data['projId']) ? $data['projId'] : $oldData['projId'];
            $version = isset($data['version']) ? $data['version'] : $oldData['version'];
            if (Model::version()->getOneByVersion($projId, $version)) {
                FF::throwException(Code::FAILED, '版本号不能重复');
            }
        }

        if ($oldData) {
            if ($oldData['pushOverTime'] && isset($data['version'])) {
                FF::throwException(Code::FAILED, '版本已经发布完成，不能修改版本号');
            } elseif ($oldData['pushStartTime'] && isset($data['version'])) {
                FF::throwException(Code::FAILED, '版本正在发布，不能修改版本号');
            } elseif ($oldData['publishOverTime'] && isset($data['package'])) {
                FF::throwException(Code::FAILED, '版本已经发布完成，不能修改更新包');
            } elseif ($oldData['publishStartTime'] && isset($data['package'])) {
                FF::throwException(Code::FAILED, '版本正在发布，不能修改更新包');
            }
        }
    }
}