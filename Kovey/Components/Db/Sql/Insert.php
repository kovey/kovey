<?php
/**
 *
 * @description 插入语句实现
 *
 * @package     Components\Db\Sql
 *
 * @time        Tue Sep 24 09:03:58 2019
 *
 * @class       vendor/Kovey/Components/Db/Sql/Insert.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Components\Db\SqlInterface;
use Kovey\Util\Util;

class Insert implements SqlInterface
{
    private $table;

    private $fields = array();

    private $values = array();

    private $data = array();

    const SQL_FORMAT = 'INSERT INTO %s (%s) VALUES (%s)';

    private $orignalData = array();

    public function __construct($table)
    {
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
    }

    public function __get($name)
    {
        return $this->orignalData[$name] ?? '';
    }

    public function parseData()
    {
        foreach ($this->orignalData as $name => $val) {
            $this->fields[] = $this->format($name);
            $this->data[] = $val;
            $this->values[] = '?';
        }
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getValues()
	{
		return $this->values;
	}

    public function getPrepareSql()
    {
        $this->parseData();

        if (count($this->fields) < 1 || count($this->data) < 1) {
            return false;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields), implode(',', $this->values)); 

        return $sql;
    }

    public function getBindData()
    {
        return $this->data;
    }

	public function toString()
	{
		$sql = $this->getPrepareSql();
		if (count($this->data) < 1) {
			return $sql;
		}

		$pos = Util::strposall($sql, '?');
		foreach ($pos as $i => $p) {
			$sql = substr_replace($sql, $this->data[$i], $p, 1);
		}

		return $sql;
	}
}
