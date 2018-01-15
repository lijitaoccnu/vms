<?php
/**
 * 首页
 */
namespace FF\Controller;

use FF\Extend\MyController;
use FF\Framework\Utils\Config;

class IndexController extends MyController
{
    public function index()
    {
        $data['menu'] = Config::get('menus');

        $this->display('index.html', $data);
    }
}