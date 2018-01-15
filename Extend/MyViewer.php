<?php
/**
 * 视图器扩展
 */

namespace FF\Extend;

use FF\Framework\Common\Code;
use FF\Framework\Core\FF;
use FF\Framework\Core\FFViewer;

class MyViewer extends FFViewer
{
    private function getSmarty()
    {
        file_require(PATH_LIB . '/Vendor/Smarty/libs/Smarty.class.php');

        $isProduct = FF::isProduct();

        $smarty = new \Smarty();
        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';
        $smarty->force_compile = !$isProduct;
        $smarty->compile_check = !$isProduct;
        $smarty->debugging = false;
        $smarty->caching = $isProduct;
        $smarty->cache_lifetime = 7 * 86400;
        $smarty->setTemplateDir(PATH_VIEW);
        $smarty->setCompileDir(PATH_VIEW . '/Compile');
        $smarty->setCacheDir(PATH_VIEW . '/Cache');

        return $smarty;
    }

    protected function tplRendering($tpl, $data = array())
    {
        $smarty = $this->getSmarty();

        $data['REQUEST'] = $_REQUEST;

        $data['JS_URL'] = JS_URL;
        $data['CSS_URL'] = CSS_URL;
        $data['IMG_URL'] = IMG_URL;

        foreach ($data as $key => $val) {
            $smarty->assign($key, $val);
        }

        $path = FF::getRouter()->getPath();
        $controller = FF::getRouter()->getController();
        $tpl = $path . '/' . $controller . '/' . $tpl;
        $tpl = substr($tpl, 1);

        if (!$smarty->templateExists($tpl)) {
            $this->error(Code::FILE_NOT_EXIST, "Template {$tpl} not found");
        }

        $smarty->display($tpl);
    }
}