<?php
/**
 *
 * @description Mysql 客户端封装，基于Swoole\Coroutine\MySQL
 *
 * @package     Components\Db
 *
 * @time        Tue Sep 24 09:02:49 2019
 *
 * @class       vendor/Kovey/Components/Db/Mysql.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db;

use Kovey\Components\Db\Sql\Update;
use Kovey\Components\Db\Sql\Insert;
use Kovey\Components\Db\Sql\Select;
use Kovey\Components\Db\Sql\Where;
use Swoole\Coroutine\MySQL as SCD;
use Kovey\Components\Logger\Db as DbLogger;

class Mysql implements DbInterface
{
    private $dbname;

    private $host;

    private $username;

    private $password;

    private $connection;

    private $adapter;

    private $port;

	private $isDev = false;

    public function __construct(Array $config)
    {
        $this->dbname = isset($config['dbname']) ? $config['dbname'] : '';
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->adapter = strtolower($config['adapter']);
        $this->port = $config['port'];
		$dev = $config['dev'] ?? 'Off';
		$this->isDev = $dev === 'On';

		$this->connection = new SCD();
    }

    public function connect()
    {
		return $this->connection->connect(array(
			'host' => $this->host,
			'port' => $this->port,
			'user' => $this->username,
			'password' => $this->password,
			'database' => $this->dbname,
			'charset' => 'utf8',
			'fetch_mode' => true
		));
    }

	public function getError()
	{
		return $this->connection->errno . $this->connection->error . $this->connection->connect_errno . $this->connection->connect_error;
	}

    public function query($sql)
    {
		if (!$this->connection->connected) {
			$this->connect();
		}

		$begin = microtime(true);
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

    public function commit()
    {
        $this->connection->commit();
    }

    public function beginTransaction()
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

    public function rollBack()
    {
        $this->connection->rollback();
    }

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

                $where->equal($key, $val);
            }

            $select->where($where);
        }

        return $this->select($select, $select::SINGLE);
    }

    public function fetchAll($table, Array $condition = array(), Array $columns = array())
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

                $where->equal($key, $val);
            }

            $select->where($where);
        }
        return $this->select($select);
    }

    public function update(Update $update)
    {
		$sth = $this->prepare($update);

        if ($this->connection->affected_rows < 1) {
            throw new \Exception(
                sprintf('Update Fail, Effictive Rows: %s', $this->connection->affected_rows)
            );
        }
    }

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

	private function prepare(SqlInterface $sqlObj)
	{
        $sql = $sqlObj->getPrepareSql();
        if ($sql === false) {
            throw new \Exception('update sql format error');
        }

		if (!$this->connection->connected) {
			$this->connect();
		}

		$begin = microtime(true);

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

	private function isDisconneted()
	{
		return preg_match('/2006/', $this->getError()) || preg_match('/2013/', $this->getError());
	}

	public function __destruct()
	{
		try {
			$this->connection->close();
		} catch (\Throwable $e) {
		}
	}
}
