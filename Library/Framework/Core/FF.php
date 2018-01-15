<?php
/**
 * 框架核心引擎
 */

namespace FF\Framework\Core;

use FF\Framework\Common\Code;
use FF\Framework\Common\Env;
use FF\Framework\Utils\Config;
use FF\Framework\Utils\Log;
use FF\Framework\Utils\Output;

class FF
{
    /**
     * @var array
     */
    private static $options = array();

    /**
     * @var FFRouter
     */
    private static $router = null;

    /**
     * @var FFViewer
     */
    private static $viewer = null;

    /**
     * @var FFController
     */
    private static $controller = null;

    /**
     * 框架初始化
     */
    public static function init()
    {
        self::setHandlers();
        self::loadOptions();
        self::initEnvironment();
    }

    /**
     * 设置错误、异常捕获函数
     */
    private static function setHandlers()
    {
        set_error_handler('FF\\Framework\\Common\\_error_handler');//设置错误捕捉函数
        set_exception_handler('FF\\Framework\\Common\\_exception_handler');//设置异常捕捉函数
        register_shutdown_function('FF\\Framework\\Common\\_shutdown_handler');//注册脚本终止监听函数
    }

    /**
     * 加载应用配置选项
     */
    private static function loadOptions()
    {
        $options = Config::get('application', null, false);
        $defaultOptions = self::getDefaultOptions();

        if ($options) {
            $options = array_merge($defaultOptions, $options);
        } else {
            $options = $defaultOptions;
        }

        self::setOptions($options);
    }

    /**
     * 获取应用默认配置选项
     * @return array
     */
    private static function getDefaultOptions()
    {
        return array(
            'encoding' => 'UTF-8',
            'timezone' => 'Asia/Shanghai',
            'route_default' => '/Index/index',
            'extends_prefix' => 'My',
            'display_errors' => 'no',
            'error_reporting_level' => E_ALL ^ E_NOTICE,
            'log_level' => Log::INFO,
            'log_path' => PATH_LOG,
        );
    }

    /**
     * 设置运行选项
     * @param array $options
     */
    public static function setOptions($options)
    {
        //设置是否输出php错误
        if (isset($options['display_errors'])) {
            ini_set('display_errors', $options['display_errors']);
        }
        //设置php错误报告策略
        if (isset($options['error_reporting_level'])) {
            error_reporting($options['error_reporting_level']);
        }
        //设置编码
        if (isset($options['encoding'])) {
            mb_internal_encoding($options['encoding']);
        }
        //设置时区
        if (isset($options['timezone'])) {
            date_default_timezone_set($options['timezone']);
        }
        //设置日志
        if (isset($options['log_path'])) {
            Log::setOption(array(
                'level' => $options['log_level'],
                'path' => $options['log_path']
            ));
        }

        $options = array_merge(self::$options, $options);

        self::$options = $options;
    }

    /**
     * 初始化运行环境
     */
    private static function initEnvironment()
    {
        if (defined('ENV')) return;

        if (!$env = self::judgeEnv()) {
            self::throwException(Code::ENV_INIT_FAILED, 'ENV初始化失败');
        }

        define('ENV', $env);
    }

    /**
     * 运行环境判定
     * WEB请求根据HTTP_HOST识别
     * CLI请求根据env=xxx参数识别
     * @return string
     */
    private static function judgeEnv()
    {
        $env = '';

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            $env = Config::get('env', $host, false);
        } elseif (is_cli() && isset($GLOBALS['argv'])) {
            foreach ($GLOBALS['argv'] as $k => $v) {
                if ($k >= 1 && strpos($v, 'env=') === 0) {
                    $env = substr($v, 4);
                    break;
                }
            }
        }

        return $env;
    }

    /**
     * 初始化自定义扩展
     * @param string $extend
     * @return mixed
     */
    private static function initExtends($extend)
    {
        $className = 'FF\\Framework\\Core\\FF' . $extend;

        $prefix = self::$options['extends_prefix'];
        $extendClass = 'FF\\Extend\\' . $prefix . $extend;

        if ($prefix && class_exists($extendClass, true)) {
            $className = $extendClass;
        }

        return new $className();
    }

    /**
     * 获取配置项
     * @return array
     */
    public static function getOptions()
    {
        return self::$options;
    }

    /**
     * 获取路由器
     * @return FFRouter
     */
    public static function getRouter()
    {
        if (!self::$router) {
            self::$router = self::initExtends('Router');
        }
        return self::$router;
    }

    /**
     * 获取视图器
     * @return FFViewer
     */
    public static function getViewer()
    {
        if (!self::$viewer) {
            self::$viewer = self::initExtends('Viewer');
        }
        return self::$viewer;
    }

    /**
     * 获取控制器实例
     * @return FFController
     */
    public static function getController()
    {
        if (!self::$controller) {
            if (self::getRouter()->isValid()) {
                $routes = self::getRouter()->getRouteInfo();
                $controller = self::getControllerClass($routes);
                self::$controller = new $controller();
            }
        }
        return self::$controller;
    }

    /**
     * 获取控制器类名
     * @param array $routes
     * @return string
     */
    public static function getControllerClass($routes)
    {
        $space = 'FF\\Controller';

        if (PATH_APP != PATH_ROOT) {
            $appPath = substr(PATH_APP, strlen(PATH_ROOT));
            $appPath = str_replace('/', '\\', $appPath);
            $space = 'FF' . $appPath . '\\Controller';
        }

        if ($routes['path']) {
            $space .= str_replace('/', '\\', $routes['path']);
        }

        return $space . '\\' . $routes['controller'] . 'Controller';
    }

    /**
     * 判断是否是生产环境
     * @return bool
     */
    public static function isProduct()
    {
        return ENV == Env::PRODUCTION;
    }

    /**
     * 抛出异常错误
     * @param $code
     * @param string $message
     * @throws \Exception
     */
    public static function throwException($code, $message = '')
    {
        throw new \Exception($message, $code);
    }

    /**
     * 分发处理Web请求
     */
    public static function dispatch()
    {
        $error = null;
        $response = null;

        try {
            self::getRouter()->initRoute();
            $controller = self::getController();
            $method = self::getRouter()->getMethod();
            $response = call_user_func(array($controller, $method));
            if (is_int($response)) {
                $error = array('code' => $response, 'message' => '');
            } elseif (is_string($response)) {
                $error = array('code' => Code::FAILED, 'message' => $response);
            }
        } catch (\Exception $e) {
            $code = $e->getCode() ? $e->getCode() : Code::SYSTEM_ERROR;
            $error = array('code' => $code, 'message' => $e->getMessage());
            Log::error($error['message']);
        }

        //记录请求日志
        self::logRequest($error ? $error : $response);

        //响应输出
        if ($error) {
            Output::error($error['code'], $error['message']);
        } else {
            Output::data($response);
        }
    }

    /**
     * 记录请求日志
     * @param $response
     */
    private static function logRequest($response)
    {
        $controller = self::getController();

        $logs = array(
            'cost' => (int)((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000),
            'params' => $controller ? $controller->getParams() : $_REQUEST,
            'response' => $response
        );

        Log::info($logs, 'request.log');
    }

    /**
     * 错误处理
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     */
    public static function onError($code, $message, $file, $line)
    {
        Log::error(array(
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ));

        Output::error($code, $message);
    }
}