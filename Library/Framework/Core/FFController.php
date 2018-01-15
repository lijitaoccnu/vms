<?php
/**
 * 控制器
 */

namespace FF\Framework\Core;

use FF\Framework\Common\Code;
use FF\Framework\Common\Format;
use FF\Framework\Utils\Output;

class FFController
{
    private $params = array();

    public function __construct()
    {
        Output::setFormat(Format::JSON);
        $this->setParams($_REQUEST);
        $this->init();
    }

    protected function init()
    {
        //To override
    }

    /**
     * 设置请求参数
     * @param array $params
     */
    protected function setParams($params)
    {
        if (!is_array($params)) return;

        $this->params = $params;
    }

    /**
     * 获取全部请求参数
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 获取请求参数
     * @param string $key 参数名
     * @param bool $require 是否必须有值
     * @param string $default 默认值
     * @return mixed
     * @throws \Exception
     */
    protected function getParam($key, $require = true, $default = '')
    {
        if (isset($this->params[$key]) && !is_empty($this->params[$key])) {
            return $this->params[$key];
        } elseif ($require) {
            FF::throwException(Code::PARAMS_MISSED, "参数{{$key}}缺失");
        }
        return $default;
    }

    /**
     * 渲染页面
     * @param string $tpl
     * @param array $data
     */
    protected function display($tpl, $data = array())
    {
        Output::display($tpl, $data);
    }
}