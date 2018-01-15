<?php
/**
 * 服务器业务
 */

namespace FF\Controller;

use FF\Framework\Common\Code;
use FF\Extend\MyController;
use FF\Factory\Model;
use FF\Framework\Core\FF;
use FF\Library\Utils\Pager;

class ServerController extends MyController
{
    public function index()
    {
        $projId = (int)$this->getParam('projId', false, 0);
        $page = (int)$this->getParam('page', false, 1);
        $limit = (int)$this->getParam('limit', false, 10);

        if ($projId) {
            $where = array('projId' => $projId);
            $result = Model::server()->getPageList($page, $limit, $where);
            $data['servers'] = $result['list'];
            $data['pager'] = new Pager($result);
        }

        $data['projects'] = Model::project()->fetchAll();

        $this->display('index.html', $data);
    }

    public function edit()
    {
        $id = (int)$this->getParam('id', false, 0);

        $data['server'] = $id ? Model::server()->getOneById($id) : array();
        $data['projects'] = Model::project()->fetchAll();

        $this->display('edit.html', $data);
    }

    protected function checkData($modelName, $data, $oldData = null)
    {
        if (isset($data['projId']) || isset($data['host'])) {
            $projId = isset($data['projId']) ? $data['projId'] : $oldData['projId'];
            $host = isset($data['host']) ? $data['host'] : $oldData['host'];
            if (Model::server()->getOneByHost($projId, $host)) {
                FF::throwException(Code::FAILED, '服务器地址已存在');
            }
        }
    }

    public function create()
    {
        $data = array(
            'projId' => $this->getParam('projId'),
            'host' => $this->getParam('host'),
            'user' => $this->getParam('user'),
            'pwd' => $this->getParam('pwd'),
            'createTime' => now(),
        );

        $result = $this->_create('server', $data);
        $result['redirect'] = '/server/index?projId=' . $data['projId'];

        return $result;
    }

    public function update()
    {
        $id = (int)$this->getParam('id');

        $data = array(
            'projId' => (int)$this->getParam('projId'),
            'host' => $this->getParam('host'),
            'user' => $this->getParam('user'),
            'pwd' => $this->getParam('pwd'),
        );

        $result = $this->_update('server', $id, $data);
        $result['redirect'] = '/server/index?projId=' . $data['projId'];

        return $result;
    }

    public function delete()
    {
        $id = (int)$this->getParam('id');

        return $this->_delete('server', $id);
    }

    public function lists()
    {
        $projId = (int)$this->getParam('projId');

        $servers = Model::server()->getAllByProject($projId, 'id,host');

        return $servers;
    }
}