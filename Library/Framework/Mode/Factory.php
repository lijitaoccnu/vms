<?php
/**
 * 工厂类
 */

namespace FF\Framework\Mode;

use FF\Framework\Common\Code;

class Factory
{
    protected static $instances = array();

    /**
     * 获取某个工厂下的对象实例
     * @param string $class 模型类名
     * @param string | null $identify 模型标识
     * @param array $args 实例化构造参数
     * @return mixed
     * @throws \Exception
     */
    public static function getInstance($class, $identify = null, $args = array())
    {
        if ($identify === null) {
            $identify = $class;
        }
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = array();
        }
        if (isset(self::$instances[$class][$identify])) {
            return self::$instances[$class][$identify];
        }
        if (!class_exists($class, true)) {
            throw new \Exception("Load class $class failed", Code::CLASS_NOT_EXIST);
        }
        //获取对象构造函数的参数
        $instance = new $class(...$args);
        self::$instances[$class][$identify] = $instance;
        return $instance;
    }
}