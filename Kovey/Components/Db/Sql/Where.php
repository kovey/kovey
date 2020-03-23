<?php
/**
 *
 * @description where语句实现
 *
 * @package     Components\Db\Sql
 *
 * @time        Tue Sep 24 09:05:28 2019
 *
 * @class       vendor/Kovey/Components/Db/Sql/Where.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Util\Util;

class Where
{
	/**
	 * @description where语法格式
	 *
	 * @var string
	 */
    const SQL_FORMAT = ' WHERE %s';

	/**
	 * @description 条件数据
	 *
	 * @var Array
	 */
    private $data;

	/**
	 * @description 条件字段
	 *
	 * @var Array
	 */
    private $fields;

	/**
	 * @description 构造函数
	 */
    public function __construct()
    {
        $this->data = array();
        $this->fields = array();
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
		$info = explode('.', $name);
		$len = count($info);

		if ($len > 1) {
			$info[$len - 1] = sprintf('`%s`', $info[$len - 1]);
			return implode('.', $info);
		}

		return sprintf('`%s`', $name);
	}

	/**
	 * @description 设置条件值
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function __set($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '=?';
    }

	/**
	 * @description 大于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function large($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>?';
    }

	/**
	 * @description 不等于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function notEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<>?';
    }

	/**
	 * @description 大于等于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function largeEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>=?';
    }

	/**
	 * @description 小于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function little($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<?';
    }

	/**
	 * @description 小于等于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function littleEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<=?';
    }

	/**
	 * @description 等于
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function equal($name, $val)
    {
        $this->__set($name, $val);
    }

	/**
	 * @description IN
	 *
	 * @param string $name
	 *
	 * @param Array $val
	 *
	 * @return null
	 */
    public function in($name, Array $val)
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' IN(' . implode(',', $inVals). ')';
    }

	/**
	 * @description NOT IN
	 *
	 * @param string $name
	 *
	 * @param Array $val
	 *
	 * @return null
	 */
    public function notIn($name, Array $val)
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' NOT IN(' . implode(',', $inVals) . ')';
    }

	/**
	 * @description LIKE
	 *
	 * @param string $name
	 *
	 * @param mixed $val
	 *
	 * @return null
	 */
    public function like($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' LIKE ?';
    }

	/**
	 * @description BETWEEN
	 *
	 * @param string $name
	 *
	 * @param mixed $start
	 *
	 * @param mixed $end
	 *
	 * @return null
	 */
    public function between($name, $start, $end)
    {
        $this->data[] = $start;
        $this->data[] = $end;
        $this->fields[] = $this->format($name) . ' BETWEEN ? AND ?';
    }

	/**
	 * @description 语句
	 *
	 * @param string $statement
	 *
	 * @return null
	 */
    public function statement($statement)
    {
        $this->fields[] = $statement;
    }

	/**
	 * @description 准备SQL
	 *
	 * @return string
	 */
    public function getPrepareWhereSql()
    {
        if (count($this->fields) < 1) {
            return false;
        }

        return sprintf(self::SQL_FORMAT, implode(' AND ', $this->fields));
    }

	/**
	 * @description 准备OR WHERE
	 *
	 * @return string
	 */
    public function getPrepareOrWhereSql()
    {
        if (count($this->fields) < 1) {
            return false;
        }

        return sprintf(self::SQL_FORMAT, implode(' OR ', $this->fields));
    }

	/**
	 * @description 获取绑定数据
	 *
	 * @return Array
	 */
    public function getBindData()
    {
        return $this->data;
    }

	/**
	 * @description 格式化SQL语句
	 *
	 * @return string
	 */
	public function toString()
	{
		$sql = $this->getPrepareWhereSql();
		if (count($this->data) < 1) {
			return $sql;
		}

		foreach ($this->data as $needle) {
			$sql = substr_replace($sql, $needle, strpos($sql, '?'), 1);
		}
		return $sql;
	}
}
