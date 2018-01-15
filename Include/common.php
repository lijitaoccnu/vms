<?php
/**
 * 通用包含文件
 */
define('PATH_ROOT', realpath(__DIR__ . '/../'));
define('PATH_BLL', PATH_ROOT . '/Bll');
define('PATH_LIB', PATH_ROOT . '/Library');

include(PATH_LIB . '/Framework/Common/Boot.php');

if (isset($_SERVER['HTTP_HOST'])) {
    define('BASE_URL', get_host_url());
    define('JS_URL', BASE_URL . '/static/js');
    define('CSS_URL', BASE_URL . '/static/css');
    define('IMG_URL', BASE_URL . '/static/image');
}

include(__DIR__ . '/consts.php');