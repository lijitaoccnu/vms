<?php
/**
 * 环境配置
 */

use FF\Framework\Common\Env;

$config = array(
    'publish.dev.com' => Env::DEVELOPMENT,
    'publish.test.xxx.com' => Env::TESTING,
    'publish.pre.xxx.com' => Env::PREVIEW,
    'publish.xxx.com' => Env::PRODUCTION
);

return $config;