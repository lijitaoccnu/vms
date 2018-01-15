<?php
/**
 * 数据访问对象工厂
 */

namespace FF\Factory;

use FF\Framework\Driver\Extend\_Pdo;
use FF\Framework\Mode\Factory;
use FF\Framework\Utils\Config;

class Dao extends Factory
{
    /**
     * @param $name
     * @return _Pdo
     */
    public static function db($name = DB_MAIN)
    {
        $config = Config::get('database', $name);

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";

        $options = isset($config['options']) ? $config['options'] : null;

        $args = array($dsn, $config['username'], $config['passwd'], $options);

        return self::getInstance('FF\\Framework\\Driver\\Extend\\_Pdo', $name, $args);
    }

    /**
     * @param $name
     * @return \Redis
     */
    public static function redis($name = 'main')
    {
        $args = Config::get('redis', $name);

        return self::getInstance('FF\\Framework\\Driver\\Extend\\_Redis', $name, $args);
    }

    /**
     * @param $name
     * @return \Memcached
     */
    public static function memcache($name = 'main')
    {
        $args = Config::get('memcache', $name);

        return self::getInstance('FF\\Framework\\Driver\\Extend\\_Memcached', $name, $args);
    }
}