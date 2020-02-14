<?php
/**
 *
 * @description 更新语句实现
 *
 * @package     Components\Db\Sql
 *
 * @time        Tue Sep 24 09:04:53 2019
 *
 * @class       vendor/Kovey/Components/Db/Sql/Update.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Components\Db\SqlInterface;
use Kovey\Util\Util;

class Update implements SqlInterface
{
    private $table;

    private $fields = array();

    private $data = array();

    const SQL_FORMAT = 'UPDATE %s SET %s';

    private $where;

    private $orignalData = array();

    private $addData = array();

    private $subData = array();

    private $equalData = array();

    public function __construct($table)
    {
        $this->where = new Where();
		$info = explode('.', $table);
		array_walk($info, function (&$row) {
			$row = $this->format($row);
		});

		$this->table = implode('.', $info);
    }

	private function format($name)
	{
		return sprintf('`%s`', $name);
	}

    public function __set($name, $val)
    {
        $this->orignalData[$name] = $val;
        $this->equalData[$name] = $val;
    }

    protected function parseData()
    {
        foreach ($this->equalData as $name => $val) {
            $this->fields[] = $this->format($name) . '=?';
            $this->data[] = $val;
        }

        foreach ($this->addData as $name => $val) {
            $this->fields[] = $this->format($name) . '= ' . $this->format($name) . ' + ?';
            $this->data[] = $val;
        }

        foreach ($this->subData as $name => $val) {
            $this->fields[] = $this->format($name) . '= ' . $this->format($name) . ' - ?';
            $this->data[] = $val;
        }

		return $this;
    }

    public function __get($name)
    {
        return $this->orignalData[$name] ?? '';
    }

    public function addSelf($name, $val)
    {
        $this->orignalData[$name] = $val;
        $this->addData[$name] = $val;
		return $this;
    }

    public function subSelf($name, $val)
    {
        $this->orignalData[$name] = $val;
        $this->subData[$name] = $val;
		return $this;
    }
    
    public function where(Array $condition)
    {
        foreach ($condition as $key => $val) {
            if (is_numeric($key)) {
                $this->where->statement($val);
                continue;
            }

            $this->where->equal($key, $val);
        }

		return $this;
    }

    public function getPrepareSql()
    {
        $this->parseData();

        if (count($this->fields) < 1 || count($this->data) < 1) {
            return false;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields)); 
        $whereSql = $this->where->getPrepareWhereSql();
        if ($whereSql !== false) {
            $sql .= $whereSql; 
        }

        return $sql;
    }

    public function getBindData()
    {
        $tmp = $this->data;
        foreach ($this->where->getBindData() as $val) {
            $tmp[] = $val;
        }

        return $tmp;
    }

	public function toString()
	{
		$sql = $this->getPrepareSql();
		$data = $this->getBindData();
		if (count($data) < 1) {
			return $sql;
		}

		foreach ($data as $needle) {
			$sql = substr_replace($sql, $needle, strpos($sql, '?'), 1);
		}

		return $sql;
	}
}
