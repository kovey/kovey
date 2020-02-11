<?php
/**
 *
 * @description 批量插入
 *
 * @package     
 *
 * @time        2019-12-10 23:15:33
 *
 * @file  /Users/kovey/Documents/php/kovey/web/Kovey/Components/Db/Sql/BatchInsert.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Components\Db\SqlInterface;
use Kovey\Util\Util;

class BatchInsert implements SqlInterface
{
    private $table;

    private $fields = array();

    private $values = array();

    private $data = array();

    const SQL_FORMAT = 'INSERT INTO %s (%s) VALUES %s';

    public function __construct($table)
    {
		$info = explode('.', $table);
		array_walk($info, function (&$row) {
			$row = $this->format($row);
		});

		$this->table = implode('.', $info);
    }

	public function add(Insert $insert)
	{
		$insert->parseData();

		$this->data = array_merge($insert->getBindData());
		$this->values[] = sprintf('(%s)', implode(',', $insert->getValues()));
		if (empty($this->fields)) {
			$this->fields = $insert->getFields();
		}
	}

	private function format($name)
	{
		return sprintf('`%s`', $name);
	}

    public function getPrepareSql()
    {
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
