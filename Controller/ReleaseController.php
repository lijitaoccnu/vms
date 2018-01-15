<?php
/**
 * 发布业务逻辑
 */

namespace FF\Controller;

use FF\Framework\Common\Code;
use FF\Extend\MyController;
use FF\Factory\Model;
use FF\Framework\Core\FF;
use FF\Framework\Utils\Log;

class ReleaseController extends MyController
{
    public function release()
    {
        $versionId = (int)$this->getParam('versionId');

        $version = $this->checkVersion($versionId, 'publish');
        $project = $this->checkProject($version['projId']);

        $data['version'] = $version;
        $data['project'] = $project;

        $this->display('release.html', $data);
    }

    public function rollback()
    {
        $versionId = (int)$this->getParam('versionId');

        $version = $this->checkVersion($versionId, 'rollback');
        $project = $this->checkProject($version['projId']);

        if ($project['version'] == $version['version']) {
            FF::throwException(Code::FAILED, '该版本正在运行，无需回滚');
        }

        $data['version'] = $version;
        $data['project'] = $project;

        $this->display('rollback.html', $data);
    }

    private function checkProject($projId)
    {
        $project = Model::project()->getOneById($projId);

        if (!$project) {
            FF::throwException(Code::PARAMS_INVALID, "项目不存在[ID=$projId]");
        }

        return $project;
    }

    private function checkVersion($versionId, $action)
    {
        $version = Model::version()->getOneById($versionId);

        if (!$version) {
            FF::throwException(Code::PARAMS_INVALID, "版本不存在[ID={$versionId}]");
        }

        if ($action == 'push' && $version['publishOverTime']) {
            FF::throwException(Code::PARAMS_INVALID, '发布已完成');
        }

        if ($action == 'publish' && $version['publishOverTime']) {
            FF::throwException(Code::PARAMS_INVALID, '发布已完成');
        }

        if ($action == 'rollback' && !$version['publishOverTime']) {
            FF::throwException(Code::PARAMS_INVALID, '发布尚未完成');
        }

        return $version;
    }

    private function checkServer($serverId)
    {
        $server = Model::server()->getOneById($serverId);

        if (!$server) {
            FF::throwException(Code::PARAMS_INVALID, "服务器不存在[ID={$serverId}]");
        }

        return $server;
    }

    private function executeShell($shell, $params = array())
    {
        if (!function_exists('exec')) {
            FF::throwException(Code::FAILED, '请修改php配置以允许exec函数运行');
        }

        $shell = PATH_ROOT . '/Scripts/' . $shell;

        if ($params) {
            foreach ($params as $k => $v) {
                $shell .= " -{$k} {$v}";
            }
        }

        putenv('caller=PHP');
        exec($shell, $output, $ret);

        Log::info(array($shell, $output), 'shell.log');

        if ($ret !== 0) {
            FF::throwException(Code::FAILED);
        }
    }

    public function pushToServer()
    {
        $versionId = (int)$this->getParam('versionId');
        $serverId = (int)$this->getParam('serverId');

        $version = $this->checkVersion($versionId, 'push');
        $server = $this->checkServer($serverId);

        if ($version['projId'] != $server['projId']) {
            FF::throwException(Code::PARAMS_INVALID, '版本和服务器不属于同一个项目');
        }

        $project = $this->checkProject($version['projId']);

        $params = array(
            'p' => $project['code'],
            'v' => $version['version'],
            'h' => $server['host'],
            'u' => $server['user'],
            'P' => $server['pwd'],
        );
        $this->executeShell('push.sh', $params);
    }

    public function publishToServer()
    {
        $versionId = (int)$this->getParam('versionId');
        $serverId = (int)$this->getParam('serverId');

        $version = $this->checkVersion($versionId, 'publish');
        $server = $this->checkServer($serverId);

        if ($version['projId'] != $server['projId']) {
            FF::throwException(Code::PARAMS_INVALID, '版本和服务器不属于同一个项目');
        }

        $project = $this->checkProject($version['projId']);

        $params = array(
            'p' => $project['code'],
            'v' => $version['version'],
            'h' => $server['host'],
            'u' => $server['user'],
            'P' => $server['pwd'],
        );
        $this->executeShell('publish.sh', $params);
    }

    public function rollbackToServer()
    {
        $versionId = (int)$this->getParam('versionId');
        $serverId = (int)$this->getParam('serverId');

        $version = $this->checkVersion($versionId, 'rollback');
        $server = $this->checkServer($serverId);

        if ($version['projId'] != $server['projId']) {
            FF::throwException(Code::PARAMS_INVALID, '版本和服务器不属于同一个项目');
        }

        $project = $this->checkProject($version['projId']);

        $params = array(
            'p' => $project['code'],
            'v' => $version['version'],
            'h' => $server['host'],
            'u' => $server['user'],
            'P' => $server['pwd'],
        );
        $this->executeShell('rollback.sh', $params);
    }

    public function pushStart()
    {
        $versionId = (int)$this->getParam('versionId');

        $this->checkVersion($versionId, 'push');

        Model::version()->updateById($versionId, array('pushStartTime' => now()));
    }

    public function pushOver()
    {
        $versionId = (int)$this->getParam('versionId');

        $this->checkVersion($versionId, 'push');

        Model::version()->updateById($versionId, array('pushOverTime' => now()));
    }

    public function publishStart()
    {
        $versionId = (int)$this->getParam('versionId');

        $this->checkVersion($versionId, 'publish');

        Model::version()->updateById($versionId, array('publishStartTime' => now()));
    }

    public function publishOver()
    {
        $versionId = (int)$this->getParam('versionId');

        $version = $this->checkVersion($versionId, 'publish');

        $projId = $version['projId'];

        Model::version()->updateById($versionId, array('publishOverTime' => now()));
        Model::project()->updateById($projId, array('version' => $version['version']));
    }

    public function rollbackOver()
    {
        $versionId = (int)$this->getParam('versionId');

        $version = $this->checkVersion($versionId, 'rollback');

        $projId = $version['projId'];

        Model::version()->updateById($versionId, array('publishOverTime' => now()));
        Model::project()->updateById($projId, array('version' => $version['version']));
    }
}