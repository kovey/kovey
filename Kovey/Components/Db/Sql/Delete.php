<?php
/**
 *
 * @description 删除
 *
 * @package     
 *
 * @time        2020-03-07 12:02:48
 *
 * @file  kovey/Kovey/Components/Db/Sql/Delete.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Components\Db\SqlInterface;
use Kovey\Util\Util;

class Delete implements SqlInterface
{
	/**
	 * @description 表名
	 *
	 * @var string
	 */
    private $table;

	/**
	 * @description 更新的字段
	 *
	 * @var Array
	 */
    private $fields = array();

	/**
	 * @description 字段的值
	 *
	 * @var Array
	 */
    private $data = array();

	/**
	 * @description 更新格式
	 *
	 * @var string
	 */
    const SQL_FORMAT = 'DELETE FROM %s';

	/**
	 * @description 条件
	 *
	 * @var Where
	 */
    private $where;

	/**
	 * @description 构造
	 *
	 * @var string $table
	 */
    public function __construct($table)
    {
        $this->where = new Where();
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
	 * @description 条件
	 *
	 * @param Array $condition
	 *
	 * @return Update
	 */
    public function where(Array $condition)
    {
        foreach ($condition as $key => $val) {
            if (is_numeric($key)) {
                $this->where->statement($val);
                continue;
            }

            if (is_array($val)) {
                $this->where->in($key, $val);
                continue;
            }

            $this->where->equal($key, $val);
        }

		return $this;
    }

	/**
	 * @description 准备SQL语句
	 *
	 * @return string | bool
	 */
    public function getPrepareSql()
    {
        $sql = sprintf(self::SQL_FORMAT, $this->table); 
        $whereSql = $this->where->getPrepareWhereSql();
        if ($whereSql !== false) {
            $sql .= $whereSql; 
        }

        return $sql;
    }

	/**
	 * @description 获取绑定数据
	 *
	 * @return Array
	 */
    public function getBindData()
    {
        return $this->where->getBindData();
    }

	/**
	 * @description 格式化SQL
	 *
	 * @return string
	 */
	public function toString()
	{
		$sql = $this->getPrepareSql();
		$data = $this->getBindData();
		if (count($data) < 1) {
			return $sql;
		}

		foreach ($data as $needle) {
			$sql = substr_replace($sql, '\'' . $needle . '\'', strpos($sql, '?'), 1);
		}

		return $sql;
	}
}
