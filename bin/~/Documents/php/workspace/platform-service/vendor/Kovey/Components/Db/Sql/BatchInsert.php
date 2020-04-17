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
	/**
	 * @description 表名
	 *
	 * @var string
	 */
    private $table;

	/**
	 * @description 插入的字段名称
	 *
	 * @var Array
	 */
    private $fields = array();

	/**
	 * @description 插入的值
	 *
	 * @var Array
	 */
    private $values = array();

	/**
	 * @description 最终合并的数据
	 *
	 * @var Array
	 */
    private $data = array();

	/**
	 * @description SQL语法
	 *
	 * @var string
	 */
    const SQL_FORMAT = 'INSERT INTO %s (%s) VALUES %s';

	/**
	 * @description 构造函数
	 *
	 * @var string $table
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
	 * @description 添加插入语句
	 *
	 * @param Insert $insert
	 *
	 * @return BatchInsert
	 */
	public function add(Insert $insert)
	{
		$insert->parseData();

		$this->data = array_merge($this->data, $insert->getBindData());
		$this->values[] = sprintf('(%s)', implode(',', $insert->getValues()));
		if (empty($this->fields)) {
			$this->fields = $insert->getFields();
		}

		return $this;
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
	 * @description 获取sql
	 *
	 * @return string | bool
	 */
    public function getPrepareSql()
    {
        if (count($this->fields) < 1 || count($this->data) < 1) {
            return false;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields), implode(',', $this->values)); 

        return $sql;
    }

	/**
	 * @description 获取绑定的数据
	 *
	 * @return Array
	 */
    public function getBindData()
    {
        return $this->data;
    }

	/**
	 * @description 将对象转换成SQL语句
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
