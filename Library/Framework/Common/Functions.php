<?php
/**
 * 常用方法
 */

function uint($num)
{
    return max(0, (int)$num);
}

function now()
{
    return date('Y-m-d H:i:s');
}

function today()
{
    return date('Y-m-d');
}

function yesterday()
{
    return date('Y-m-d', time() - 86400);
}

function is_empty($var)
{
    return $var === '' || $var === null;
}

function get_ip()
{
    $ip = '';
    $unknown = 'unknown';

    if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]) && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], $unknown)) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"]) && strcasecmp($_SERVER["REMOTE_ADDR"], $unknown)) {
        $ip = $_SERVER["REMOTE_ADDR"];
    }

    if (false !== strpos($ip, ",")) {
        $ip = explode(",", $ip)[0];
    }

    return $ip;
}

function get_host_url()
{
    if (is_cli()) return '';

    $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];

    return $url;
}

function is_ajax()
{
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    } else {
        return false;
    }
}

function dp3p()
{
    header("P3P:CP='ALL DSP CURa ADMa DEVa CONi OUT DELa IND PHY ONL PUR COM NAV DEM CNT STA PRE'");
}

function _microtime()
{
    return (double)(microtime(true) * 1000);
}

function is_cli()
{
    return php_sapi_name() === 'cli';
}

function file_require($file)
{
    if (!file_exists($file)) {
        throw new \Exception("File {$file} isn't exits!", \FF\Framework\Common\Code::FILE_NOT_EXIST);
    } else {
        return require($file);
    }
}

function file_include($file, &$exists = false)
{
    $exists = file_exists($file);

    return $exists ? include($file) : null;
}

function redirect($url)
{
    header('location: ' . $url);
    exit(0);
}

function array_recombine($data, $keys)
{
    $result = array();

    foreach ($keys as $key) {
        $result[$key] = isset($data[$key]) ? $data[$key] : null;
    }

    return $result;
}

function zip_get_files($zip_file)
{
    if (!$zip = zip_open($zip_file)) return false;

    $files = array();

    while ($zip_entry = zip_read($zip)) {
        $files[] = zip_entry_name($zip_entry);
    }

    zip_close($zip);

    return $files;
}

function zip_read_file($zip_file, $file)
{
    if (!$zip = zip_open($zip_file)) return false;

    $content = '';
    if (substr($file, 0, 1) == '/') {
        $file = substr($file, 1);
    }

    while ($zip_entry = zip_read($zip)) {
        $entry_name = zip_entry_name($zip_entry);
        if ($entry_name == $file) {
            if (zip_entry_open($zip, $zip_entry)) {
                while ($str = zip_entry_read($zip_entry)) {
                    $content .= $str;
                }
                zip_entry_close($zip_entry);
            }
            break;
        }
    }

    zip_close($zip);

    return $content;
}