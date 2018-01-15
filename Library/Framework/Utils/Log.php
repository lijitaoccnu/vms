<?php
/**
 * 日志管理器
 */

namespace FF\Framework\Utils;

class Log
{
    const ERROR = 0;
    const WARNING = 1;
    const INFO = 2;
    const DEBUG = 3;

    private static $level = 2;
    private static $path = '';
    private static $format = '%d [%t] [%a] [%u] %m';

    public static function setOption($option)
    {
        if (!is_array($option)) return;

        isset($option['level']) && (self::$level = (int)$option['level']);
        isset($option['path']) && (self::$path = (string)$option['path']);
        isset($option['format']) && (self::$format = (string)$option['format']);
    }

    public static function debug($data, $file = 'debug.log')
    {
        if (self::$level < self::DEBUG) return;

        self::writeLog('DEBUG', $data, $file);
    }

    public static function info($data, $file = 'info.log')
    {
        if (self::$level < self::INFO) return;

        self::writeLog('INFO', $data, $file);
    }

    public static function warning($data, $file = 'warning.log')
    {
        if (self::$level < self::WARNING) return;

        self::writeLog('WARNING', $data, $file);
    }

    public static function error($data, $file = 'error.log')
    {
        if (self::$level < self::ERROR) return;

        self::writeLog('ERROR', $data, $file);
    }

    private static function writeLog($tag, $data, $file)
    {
        $uri = is_cli() ? '/' : explode('?', $_SERVER['REQUEST_URI'])[0];
        $ip = is_cli() ? '127.0.0.1' : get_ip();
        $msg = is_scalar($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);

        $find = array('%d', '%t', '%a', '%u', '%m');
        $replace = array(now(), $tag, $ip, $uri, $msg);
        $log = str_replace($find, $replace, self::$format);

        $dir = self::$path . '/' . date('Ymd');
        if ((!is_dir($dir) && !mkdir($dir)) || !is_writeable($dir)) return;

        $file = $dir . '/' . $file;
        if (file_exists($file) && !is_writeable($file)) return;

        file_put_contents($file, $log . "\n", FILE_APPEND);
    }
}