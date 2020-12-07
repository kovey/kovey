<?php
/**
 *
 * @description Mysql 客户端封装，基于Swoole\Coroutine\MySQL
 *
 * @package     Components\Db
 *
 * @time        Tue Sep 24 09:02:49 2019
 *
 * @author      kovey
 */
namespace Kovey\Components\Db;

use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Db\Sql\Insert;
use Kovey\Components\Db\Sql\Select;
use Kovey\Components\Db\Sql\BatchInsert;
use Kovey\Components\Db\Sql\Delete;
use Kovey\Components\Db\Sql\Where;
use Swoole\Coroutine\MySQL as SCD;
use Kovey\Components\Logger\Db as DbLogger;

class Mysql implements DbInterface
{
    /**
     * @description 数据名称
     *
     * @var string
     */
    private $dbname;

    /**
     * @description 地址
     *
     * @var string
     */
    private $host;

    /**
     * @description 用户名
     *
     * @var string
     */
    private $username;

    /**
     * @description 密码
     *
     * @var string
     */
    private $password;

    /**
     * @description 数据库链接
     *
     * @var Swoole\Coroutine\MySQL
     */
    private $connection;

    /**
     * @description 适配器
     *
     * @var string
     */
    private $adapter;

    /**
     * @description 端口号
     *
     * @var string
     */
    private $port;

    /**
     * @description 字符集
     *
     * @var string
     */
    protected $charset;

    /**
     * @description 是否开发环境
     *
     * @var bool
     */
    private $isDev = false;

    /**
     * @description 构造函数
     *
     * @param Array $config
     */
    public function __construct(Array $config)
    {
        $this->dbname = $config['dbname'] ?? '';
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->adapter = strtolower($config['adapter']);
        $this->port = $config['port'];
        $this->charset = $config['charset'] ?? 'utf8';
        $dev = $config['dev'] ?? 'Off';
        $this->isDev = $dev === 'On';

        $this->connection = new SCD();
    }

    /**
     * @description 连接服务器
     *
     * @return bool
     */
    public function connect() : bool
    {
        return $this->connection->connect(array(
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->username,
            'password' => $this->password,
            'database' => $this->dbname,
            'charset' => $this->charset,
            'fetch_mode' => true
        ));
    }

    /**
     * @description 获取错误信息
     *
     * @return string
     */
    public function getError() : string
    {
        return sprintf(
            'error code: %s, error msg: %s, connect error code: %s, connect error msg: %s',
            $this->connection->errno, $this->connection->error, $this->connection->connect_errno, $this->connection->connect_error
        );
    }

    /**
     * @description 查询
     *
     * @param string $sql
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function query($sql)
    {
        if (!$this->connection->connected) {
            $this->connect();
        }

        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }
        $result = $this->connection->query($sql);
        if (!$result) {
            if ($this->isDisconneted()) {
                $this->connect();
            }

            $result = $this->connection->query($sql);
        }

        if (!$result) {
            throw new \Exception('query fail: ' . $this->getError());
        }

        if ($result === true) {
            $result = $this->connection->fetchAll();
        }

        $end = microtime(true);
        if ($this->isDev) {
            DbLogger::write($sqlObj->toString(), $end - $begin);
        }

        return $result;
    }

    /**
     * @description 事务提交
     *
     * @return null
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * @description 开启事务
     *
     * @return bool
     */
    public function beginTransaction() : bool
    {
        if (!$this->connection->connected) {
            $this->connect();
        }

        if (!$this->connection->begin()) {
            if ($this->isDisconneted()) {
                $this->connect();
                return $this->connection->begin();
            }

            return false;
        }

        return true;
    }

    /**
     * @description 撤销事务
     *
     * @return null
     */
    public function rollBack()
    {
        $this->connection->rollback();
    }

    /**
     * @description 获取一行
     *
     * @param string $table
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function fetchRow($table, Array $condition, Array $columns = array())
    {
        $select = new Select($table);
        $select->columns($columns);
        if (count($condition) > 0) {
            $where = new Where();
            foreach ($condition as $key => $val) {
                if (is_numeric($key)) {
                    $where->statement($val);
                    continue;
                }

                if (is_array($val)) {
                    $where->in($key, $val);
                    continue;
                }

                $where->eq($key, $val);
            }

            $select->where($where);
        }

        return $this->select($select, $select::SINGLE);
    }

    /**
     * @description 获取所有行
     *
     * @param string $table
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @return Array
     *
     * @throws Exception
     */
    public function fetchAll($table, Array $condition = array(), Array $columns = array()) : array
    {
        $select = new Select($table);
        $select->columns($columns);
        if (count($condition) > 0) {
            $where = new Where();
            foreach ($condition as $key => $val) {
                if (is_numeric($key)) {
                    $where->statement($val);
                    continue;
                }

                if (is_array($val)) {
                    $where->in($key, $val);
                    continue;
                }

                $where->eq($key, $val);
            }

            $select->where($where);
        }
        
        $rows = $this->select($select);
        if ($rows === false) {
            return array();
        }

        return $rows;
    }

    /**
     * @description 更新
     *
     * @param Update $update
     *
     * @return mixed
     */
    public function update(Update $update)
    {
        $sth = $this->prepare($update);

        if ($this->connection->affected_rows < 1) {
            throw new \Exception(
                sprintf('Update Fail, Effictive Rows: %s', $this->connection->affected_rows)
            );
        }
    }

    /**
     * @description 插入
     *
     * @param Insert $insert
     *
     * @return mixed
     */
    public function insert(Insert $insert)
    {
        $sth = $this->prepare($insert);

        if ($this->connection->affected_rows < 1) {
            throw new \Exception(
                sprintf('Insert Fail, Effictive Rows: %s', $this->connection->affected_rows)
            );
        }

        return $this->connection->insert_id;
    }

    /**
     * @description 准备SQL语句
     *
     * @param SqlInterface $sqlObj
     *
     * @return Swoole\Coroutine\MySQL\Statement
     */
    private function prepare(SqlInterface $sqlObj)
    {
        $sql = $sqlObj->getPrepareSql();
        if ($sql === false) {
            throw new \Exception('sql format is error');
        }

        if (!$this->connection->connected) {
            $this->connect();
        }

        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }

        $sth = $this->connection->prepare($sql);
        if (!$sth) {
            if ($this->isDisconneted()) {
                $this->connect();
                $sth = $this->connection->prepare($sql);
                if (!$sth) {
                    throw new \Exception('prepare sql fail: ' . $this->getError());
                }
            } else {
                throw new \Exception('prepare sql fail: ' . $this->getError());
            }
        }

        if (!$sth->execute($sqlObj->getBindData())) {
            if ($this->isDisconneted()) {
                $this->connect();
                $sth = $this->connection->prepare($sql);
                if (!$sth->execute($sqlObj->getBindData())) {
                    throw new \Exception('execute sql fail behand reconnect: ' . $this->getError());
                }
            } else {
                throw new \Exception('execute sql fail: ' . $this->getError());
            }
        }

        $end = microtime(true);
        if ($this->isDev) {
            DbLogger::write($sqlObj->toString(), $end - $begin);
        }

        return $sth;
    }

    /**
     * @description 查询
     *
     * @param Select $select
     *
     * @param int $type
     *
     * @return mixed
     */
    public function select(Select $select, $type = Select::ALL)
    {
        $sth = $this->prepare($select);

        if ($type == Select::SINGLE) {
            $row = false;
            while ($ret = $sth->fetch()) {
                $row = $ret;
            }

            return $row;
        }
        
        return $sth->fetchAll();
    }

    /**
     * @description 链接是否断开
     *
     * @return bool
     */
    private function isDisconneted()
    {
        return preg_match('/2006/', $this->getError()) || preg_match('/2013/', $this->getError()) || preg_match('/2002/', $this->getError());
    }

    /**
     * @description 关闭连接
     *
     * @return null
     */
    public function __destruct()
    {
        try {
            $this->connection->close();
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }
    }

    /**
     * @description 批量插入
     *
     * @param BatchInsert $batchInsert
     *
     * @return bool
     *
     * @throws Exception
     *
     */
    public function batchInsert(BatchInsert $batchInsert)
    {
        $sth = $this->prepare($batchInsert);
        if ($this->connection->affected_rows < 1) {
            throw new \Exception(
                sprintf('Batch Insert Fail, Effictive Rows: %s', $this->connection->affected_rows)
            );
        }

        return true;
    }

    /**
     * @description 删除
     *
     * @param Delete $delete
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(Delete $delete)
    {
        $sth = $this->prepare($delete);

        if ($this->connection->affected_rows < 1) {
            throw new \Exception(
                sprintf('Delete Fail, Effictive Rows: %s', $this->connection->affected_rows)
            );
        }

        return true;
    }
}
