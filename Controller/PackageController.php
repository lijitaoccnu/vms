<?php
/**
 * 更新包业务逻辑
 */

namespace FF\Controller;

use FF\Framework\Common\Code;
use FF\Extend\MyController;
use FF\Factory\Model;
use FF\Framework\Core\FF;

class PackageController extends MyController
{
    public function upload()
    {
        if (empty($_FILES)) {
            FF::throwException(Code::PARAMS_INVALID, '没有上传文件');
        }

        $data = array();
        $file = array_values($_FILES)[0];
        $data[] = $this->saveFile($file);

        return $data;
    }

    private function saveFile($file)
    {
        if (!is_array($file) || empty($file['name']) || empty($file['tmp_name'])) {
            FF::throwException(Code::FAILED, '无效文件数据');
        }

        $filename = $file['name'];

        $uuid = md5(uniqid(mt_rand(), true));
        $saveName = $uuid . '.zip';
        $savePath = '/Upload/' . date('Ym');
        $fullPath = PATH_ROOT . $savePath;

        if (!is_dir($fullPath)) {
            $result = mkdir($fullPath, 0777, true);
            if (!$result) {
                FF::throwException(Code::FAILED, error_get_last()['message']);
            }
        }

        $result = move_uploaded_file($file['tmp_name'], $fullPath . '/' . $saveName);

        if (!$result) {
            FF::throwException(Code::FAILED, error_get_last()['message']);
        }

        $result = Model::package()->addOne($uuid, $filename, $savePath, $saveName);

        if (!$result) {
            FF::throwException(Code::FAILED, '保存更新包失败');
        }

        return array(
            'uuid' => $uuid,
            'filename' => $filename,
        );
    }

    public function info()
    {
        $uuid = $this->getParam('uuid');

        $package = Model::package()->getOneByUuid($uuid);

        return $package ? $package : null;
    }

    public function view()
    {
        $uuid = $this->getParam('uuid');

        $zipFile = $this->getPackageFile($uuid);

        if (!$files = zip_get_files($zipFile)) {
            FF::throwException(Code::FAILED, '读取更新包内容失败');
        }

        $data['files'] = $this->getDirTree($files);

        $this->display('view.html', $data);
    }

    public function fileView()
    {
        $uuid = $this->getParam('uuid');
        $file = $this->getParam('file');

        $zipFile = $this->getPackageFile($uuid);

        $data['content'] = htmlentities(zip_read_file($zipFile, $file));

        $this->display('file.html', $data);
    }

    private function getPackageFile($uuid)
    {
        if (!$package = Model::package()->getOneByUuid($uuid)) {
            FF::throwException(Code::PARAMS_INVALID, '更新包不存在');
        }

        $zipFile = PATH_ROOT . $package['savePath'] . '/' . $package['saveName'];

        if (!file_exists($zipFile)) {
            FF::throwException(Code::FAILED, '更新包不存在');
        }

        return $zipFile;
    }

    private function getDirTree($files)
    {
        $tree = array();

        foreach ($files as $file) {
            $paths = explode('/', $file);
            $file = array_pop($paths);
            $_tree = $this->getDirLayers($paths, $file);
            if ($_tree) {
                $tree = array_merge_recursive($tree, $_tree);
            }
        }

        return $tree;
    }

    private function getDirLayers($paths, $file)
    {
        if (!$paths) {
            return $file ? array($file) : array();
        }

        $layers = array();
        $path = array_shift($paths);
        $layers[$path] = $this->getDirLayers($paths, $file);

        return $layers;
    }
}