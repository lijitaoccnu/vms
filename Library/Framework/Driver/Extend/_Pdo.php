<?php
/**
 * 数据库操作类
 * 基于PDO
 */

namespace FF\Framework\Driver\Extend;

use FF\Framework\Common\Code;
use FF\Framework\Common\DBResult;
use FF\Framework\Utils\Log;

class _Pdo
{
    private $pdo;

    private $config;

    public function __construct($dsn, $username, $passwd, $options)
    {
        $this->config = array(
            'dsn' => $dsn,
            'username' => $username,
            'passwd' => $passwd,
            'options' => $options
        );
    }

    /**
     * @return \PDO
     */
    private function pdo()
    {
        if ($this->pdo) {
            return $this->pdo;
        }

        try {
            $config = $this->config;
            $this->pdo = new \PDO($config['dsn'], $config['username'], $config['passwd'], $config['options']);
            $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            return $this->pdo;
        } catch (\PDOException $e) {
            Log::error('Database connect failed' . json_encode($this->config), 'db_error.log');
            throw new \PDOException('Database connect failed', Code::DB_CONNECT_FAILED);
        }
    }

    /**
     * 查询参数过滤
     * @param $str
     * @return string
     */
    public function quote($str)
    {
        return $this->pdo()->quote($str);
    }

    /**
     * 获取最近插入行ID
     * @return int
     */
    public function lastInsertId()
    {
        return (int)$this->pdo()->lastInsertId();
    }

    /**
     * 直接查询
     * @param string $sql 执行SQL
     * @param int $mode 返回值类型
     * @return array|int|\PDOStatement
     */
    public function query($sql, $mode = DBResult::FETCH_ONE)
    {
        return $this->execute($sql, array(), $mode, false);
    }

    /**
     * 执行数据库操作
     * @param string $sql SQL语句
     * @param array $params 绑定参数
     * @param int $mode 返回值类型
     * @param bool $prepare 是否走prepare模式
     * @return array|int|\PDOStatement
     */
    public function execute($sql, $params = array(), $mode = DBResult::AFFECTED_ROWS, $prepare = true)
    {
        $time = microtime(true);

        if ($prepare) {
            $statement = $this->pdo()->prepare($sql);
        } else {
            $statement = $this->pdo()->query($sql);
        }

        if (!$statement) {
            $this->error($sql, $params, null, $prepare ? 'prepare' : 'query');
        }

        if ($prepare) {
            $result = $statement->execute($params);
            if (!$result) {
                $this->error($sql, $params, $statement, 'execute');
            }
        }

        $row_count = $statement->rowCount();

        $cost = (int)((microtime(true) - $time) * 1000);
        $log = array('cost' => $cost, 'sql' => $sql, 'params' => $params, 'rows' => $row_count);
        Log::info($log, 'db_query.log');

        return $this->fetchResult($statement, $mode);
    }

    /**
     * 获取返回值
     * @param \PDOStatement $statement
     * @param int $mode
     * @return int|array|\PDOStatement
     */
    private function fetchResult($statement, $mode)
    {
        $row_count = $statement->rowCount();

        if ($mode == DBResult::AFFECTED_ROWS) {
            return $row_count;
        }
        if ($mode == DBResult::FETCH_ONE) {
            return $row_count ? $statement->fetch(\PDO::FETCH_ASSOC) : array();
        }
        if ($mode == DBResult::FETCH_ALL) {
            return $row_count ? $statement->fetchAll(\PDO::FETCH_ASSOC) : array();
        }
        if ($mode == DBResult::STATEMENT) {
            return $statement;
        }

        return 0;
    }

    /**
     * 错误处理
     * @param string $sql
     * @param array $params
     * @param \PDOStatement $statement
     * @param string $action
     */
    private function error($sql, $params, $statement, $action)
    {
        $errorInfo = $statement ? $statement->errorInfo() : $this->pdo()->errorInfo();
        $errorMessage = '[' . $errorInfo[2] . '][' . $sql . ']' . json_encode($params);
        Log::error("Database {$action} failed {$errorMessage}", 'db_error.log');

        throw new \PDOException("Database {$action} failed", Code::DB_EXECUTE_FAILED);
    }
}