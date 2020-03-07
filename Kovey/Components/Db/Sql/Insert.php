<?php
/**
 *
 * @description 插入语句实现
 *
 * @package     Kovey\Components\Db\Sql
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
	/**
	 * @description 表名
	 *
	 * @var string
	 */
    private $table;

	/**
	 * @description 插入的字段
	 *
	 * @var string
	 */
    private $fields = array();

	/**
	 * @description 占位符
	 *
	 * @var string
	 */
    private $values = array();

	/**
	 * @description 插入的值
	 *
	 * @var string
	 */
    private $data = array();

	/**
	 * @description SQL语法格式
	 *
	 * @var string
	 */
    const SQL_FORMAT = 'INSERT INTO %s (%s) VALUES (%s)';

	/**
	 * @description 原始数据
	 *
	 * @var Array
	 */
    private $orignalData = array();

	/**
	 * @description 是否解析过的标志,防止多次解析，导致sql语法出错
	 *
	 * @var bool
	 */
	private $isParsed = false;

	/**
	 * @description 构造函数
	 *
	 * @param string $table
	 */
    public function __construct($table)
    {
		$info = explode('.', $table);
		array_walk($info, function (&$row) {
			$row = $this->format($row);
		});

		$this->table = implode('.', $info);
    }

	/**
	 * @description 格式化字段
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	private function format($name)
	{
		return sprintf('`%s`', $name);
	}

	/**
	 * @description 设置字段值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function __set($name, $val)
    {
        $this->orignalData[$name] = $val;
    }

	/**
	 * @description 获取字段的值
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
    public function __get($name)
    {
        return $this->orignalData[$name] ?? '';
    }

	/**
	 * @description 解析占位符和值
	 *
	 * @return null
	 */
    public function parseData()
    {
		if ($this->isParsed) {
			return;
		}

		$this->isParsed = true;
        foreach ($this->orignalData as $name => $val) {
            $this->fields[] = $this->format($name);
            $this->data[] = $val;
            $this->values[] = '?';
        }
	}

	/**
	 * @description 获取字段
	 *
	 * @return Array
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @description 获取占位符
	 *
	 * @return Array
	 */
	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @description 准备SQL
	 *
	 * @return string | bool
	 */
    public function getPrepareSql()
    {
        $this->parseData();

        if (count($this->fields) < 1 || count($this->data) < 1) {
            return false;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields), implode(',', $this->values)); 

        return $sql;
    }

	/**
	 * @description 获取绑定的值
	 *
	 * @return Array
	 */
    public function getBindData()
    {
        return $this->data;
    }

	/**
	 * @description 格式化SQL
	 *
	 * @return string
	 */
	public function toString()
	{
		$sql = $this->getPrepareSql();
		if (count($this->data) < 1) {
			return $sql;
		}

		foreach ($this->data as $needle) {
			$sql = substr_replace($sql, $needle, strpos($sql, '?'), 1);
		}

		return $sql;
	}
}
