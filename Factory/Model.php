<?php
/**
 * 模型对象工厂
 */

namespace FF\Factory;

use FF\Framework\Mode\Factory;
use FF\Model\PackageModel;
use FF\Model\ProjectModel;
use FF\Model\ServerModel;
use FF\Model\VersionModel;

class Model extends Factory
{
    /**
     * @return ProjectModel
     */
    public static function project()
    {
        return self::getInstance('FF\\Model\\ProjectModel');
    }

    /**
     * @return ServerModel
     */
    public static function server()
    {
        return self::getInstance('FF\\Model\\ServerModel');
    }

    /**
     * @return VersionModel
     */
    public static function version()
    {
        return self::getInstance('FF\\Model\\VersionModel');
    }

    /**
     * @return PackageModel
     */
    public static function package()
    {
        return self::getInstance('FF\\Model\\PackageModel');
    }
}