<?php
/**
 * 配置管理器
 */

namespace FF\Framework\Utils;

use FF\Framework\Core\FF;
use FF\Framework\Common\Code;

class Config
{
    private static $config = array();

    /**
     * 获取配置目录列表
     * 优先顺序：PATH_APP/Config/ENV > PATH_APP/Config > PATH_ROOT/Config/ENV > PATH_ROOT/Config
     * @return array
     */
    private static function getPaths()
    {
        $paths = array();

        if (defined('ENV')) {
            $paths[] = PATH_APP . '/Config/' . ENV;
        }
        $paths[] = PATH_APP . '/Config';

        if (PATH_APP != PATH_ROOT) {
            if (defined('ENV')) {
                $paths[] = PATH_ROOT . '/Config/' . ENV;
            }
            $paths[] = PATH_ROOT . '/Config';
        }

        return $paths;
    }

    /**
     * 加载配置
     * @param $name
     * @param bool $require 是否必须有该配置
     * @return mixed
     */
    public static function load($name, $require = true)
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        $config = null;
        $paths = self::getPaths();

        foreach ($paths as $path) {
            $config = file_include($path . "/{$name}.php");
            if ($config) break;
        }

        if ($config === null) {
            if ($require) {
                $message = "Config {$name} is missed";
                FF::throwException(Code::CONFIG_MISSED, $message);
            } else {
                return null;
            }
        }

        self::$config[$name] = $config;

        return self::$config[$name];
    }

    /**
     * 获取配置内容
     * @param string $name 配置名称
     * @param string $key 下级配置，支持多级，斜线"/"分隔
     * @param bool $require 是否必须有该配置
     * @return mixed
     */
    public static function get($name, $key = '', $require = true)
    {
        $config = self::load($name, $require);

        //支持直接获取下级配置
        //支持多级，斜线"/"分隔
        if (!is_empty($key)) {
            $nodes = explode('/', $key);
            foreach ($nodes as $node) {
                if (!is_empty($node) && is_array($config) && isset($config[$node])) {
                    $config = $config[$node];
                } else {
                    return null;
                }
            }
        }

        return $config;
    }

    /**
     * 设置配置
     * @param string $name
     * @param string $key 下级配置，支持多级，斜线"/"分隔
     * @param mixed $data
     */
    public static function set($name, $key, $data)
    {
        $config = self::load($name, false);

        if (!is_empty($key)) {
            $nodes = explode('/', $key);
            if (count($nodes) > 1) array_reverse($nodes);
            foreach ($nodes as $node) {
                if (!is_empty($node)) $data = array($node => $data);
            }
            if (is_array($config)) {
                $config = array_merge_recursive($config, $data);
            } else {
                $config = $data;
            }
        } else {
            $config = $data;
        }

        self::$config[$name] = $config;
    }
}