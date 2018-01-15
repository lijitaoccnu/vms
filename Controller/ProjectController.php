<?php
/**
 * 项目业务相关
 */

namespace FF\Controller;

use FF\Framework\Common\Code;
use FF\Extend\MyController;
use FF\Factory\Model;
use FF\Framework\Core\FF;

class ProjectController extends MyController
{
    public function index()
    {
        $data['projects'] = Model::project()->fetchAll();

        $this->display('index.html', $data);
    }

    public function edit()
    {
        $id = (int)$this->getParam('id', false, 0);

        $data['project'] = $id ? Model::project()->getOneById($id) : array();

        $this->display('edit.html', $data);
    }

    protected function checkData($modelName, $data, $oldData = null)
    {
        if (isset($data['name']) && Model::project()->getOneByName($data['name'])) {
            FF::throwException(Code::FAILED, '项目名称不能跟其他项目重复');
        }
    }

    public function create()
    {
        $data = array(
            'name' => $this->getParam('name'),
            'code' => $this->getParam('code'),
            'rootPath' => $this->getParam('rootPath'),
            'cachePath' => $this->getParam('cachePath', false),
            'ignorePath' => $this->getParam('ignorePath', false),
            'createTime' => now(),
        );

        $result = $this->_create('project', $data);

        $this->createConfigFile($data);

        return $result;
    }

    public function update()
    {
        $id = (int)$this->getParam('id');

        $data = array(
            'name' => $this->getParam('name'),
            'code' => $this->getParam('code'),
            'rootPath' => $this->getParam('rootPath'),
            'cachePath' => $this->getParam('cachePath', false),
            'ignorePath' => $this->getParam('ignorePath', false),
        );

        $result = $this->_update('project', $id, $data);

        $this->createConfigFile($data);

        return $result;
    }

    public function delete()
    {
        $id = (int)$this->getParam('id');

        return $this->_delete('project', $id);
    }

    private function createConfigFile($data)
    {
        $configFile = PATH_ROOT . "/Scripts/conf/{$data['code']}.conf";

        $content = "document_root='{$data['rootPath']}'\n";

        if ($data['ignorePath']) {
            $backup_filters = array();
            $ignorePaths = explode(';', $data['ignorePath']);
            foreach ($ignorePaths as $path) {
                $path = trim($path);
                if (!$path) continue;
                $backup_filters[] = "--exclude={$path}";
            }
            if ($backup_filters) {
                $backup_filter = implode(' ', $backup_filters);
                $content .= "backup_filter='{$backup_filter}'\n";
            }
        }

        file_put_contents($configFile, $content);
    }
}