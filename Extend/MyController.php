<?php
/**
 * 控制器扩展
 */

namespace FF\Extend;

use FF\Framework\Common\Code;
use FF\Framework\Core\FF;
use FF\Framework\Core\FFController;

class MyController extends FFController
{
    /**
     * uri过滤检查
     * @param $filter
     * @return bool
     */
    protected function isInFilter($filter)
    {
        $route = FF::getRouter()->getRoute();
        if (in_array($route, $filter)) return true;

        $path = FF::getRouter()->getPath();
        $controller = FF::getRouter()->getController();
        if (in_array("{$path}/{$controller}/*", $filter)) return true;
        if (in_array("{$path}/*", $filter)) return true;

        return false;
    }

    /**
     * 根据model名字返回实例
     * @param $modelName
     * @return MyModel
     * @throws \Exception
     */
    protected function model($modelName)
    {
        if (!method_exists('FF\\Factory\\Model', $modelName)) {
            FF::throwException(Code::FAILED, "Model {$modelName} not exist!");
        }

        return call_user_func(array('FF\\Factory\\Model', $modelName));
    }

    /**
     * CURD-Create
     * @param string $modelName
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function _create($modelName, $data)
    {
        $this->checkData($modelName, $data);
        $model = $this->model($modelName);
        $result = $model->insert($data);

        if (!$result) {
            FF::throwException(Code::FAILED, '保存失败');
        }

        return array('message' => '已保存', 'reload' => true);
    }

    /**
     * CURD-Update
     * @param string $modelName
     * @param int $id
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function _update($modelName, $id, $data)
    {
        $model = $this->model($modelName);
        $oldData = $model->fetchOne(array('id' => $id));

        if (!$oldData) {
            FF::throwException(Code::FAILED, '记录不存在');
        }

        foreach ($data as $key => $val) {
            if ($val === $oldData[$key]) unset($data[$key]);
        }

        if ($data) {
            $this->checkData($modelName, $data, $oldData);
            $result = $model->update($data, array('id' => $id));
            if (!$result) {
                FF::throwException(Code::FAILED, '保存失败');
            }
        }

        return array('message' => '已保存', 'reload' => true);
    }

    /**
     * CURD-Read
     * @param string $modelName
     * @param int $id
     * @param string $fields
     * @return array
     * @throws \Exception
     */
    protected function _read($modelName, $id, $fields = null)
    {
        $model = $this->model($modelName);

        return $model->fetchOne(array('id' => $id), $fields);
    }

    /**
     * CURD-Delete
     * @param string $modelName
     * @param int $id
     * @return array
     * @throws \Exception
     */
    protected function _delete($modelName, $id)
    {
        if (!$row = $this->_read($modelName, $id)) {
            FF::throwException(Code::PARAMS_INVALID, '记录不存在');
        }

        if ($modelName == 'version') {
            if ($row['publishOverTime']) {
                FF::throwException(Code::FAILED, '版本已发布，不能删除');
            } elseif ($row['publishStartTime']) {
                FF::throwException(Code::FAILED, '版本正在发布，不能删除');
            }
        }

        $this->model($modelName)->delete(array('id' => $id));

        return array('message' => '已删除', 'reload' => true);
    }

    /**
     * CURD-检查输入数据
     * @param string $modelName
     * @param array $data
     * @param null | array $oldData
     * @throws \Exception
     */
    protected function checkData($modelName, $data, $oldData = null)
    {
        //to override
    }
}