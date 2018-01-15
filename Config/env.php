<?php
/**
 * 环境配置
 */

use FF\Framework\Common\Env;

$config = array(
    'vms.dev.com' => Env::DEVELOPMENT,
    'vms.test.xxx.com' => Env::TESTING,
    'vms.pre.xxx.com' => Env::PREVIEW,
    'vms.xxx.com' => Env::PRODUCTION
);

return $config;