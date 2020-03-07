<?php
/**
 *
 * @description 查询语句实现
 *
 * @package     Kovey\Components\Db\Sql
 *
 * @time        Tue Sep 24 09:04:25 2019
 *
 * @class       vendor/Kovey/Components/Db/Sql/Select.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Db\Sql;

use Kovey\Components\Db\SqlInterface;
use Kovey\Util\Util;

class Select implements SqlInterface
{
	/**
	 * @description 单条查询
	 *
	 * @var int
	 */
    const SINGLE = 1;

	/**
	 * @description 查询全部
	 *
	 * @var int
	 */
    const ALL = 2;

	/**
	 * @description 表名
	 *
	 * @var string
	 */
    private $table;

	/**
	 * @description 查询字段
	 *
	 * @var Array
	 */
    private $fields = array();

	/**
	 * @description SQL格式
	 *
	 * @var string
	 */
    const SQL_FORMAT = 'SELECT %s FROM %s';

	/**
	 * @description 内联语法
	 *
	 * @var string
	 */
    const INNER_JOIN_FORMAT = ' INNER JOIN %s AS %s ON %s ';

	/**
	 * @description 左联语法
	 *
	 * @var string
	 */
    const LEFT_JOIN_FORMAT = ' LEFT JOIN %s AS %s ON %s ';

	/**
	 * @description 右联语法
	 *
	 * @var string
	 */
    const RIGHT_JOIN_FORMAT = ' RIGHT JOIN %s AS %s ON %s ';

	/**
	 * @description 字段格式化语法
	 *
	 * @var string
	 */
    const FIELD_FORMAT = '%s.%s as %s';

	/**
	 * @description 字段常规模式语法
	 *
	 * @var string
	 */
    const FIELD_NORMAL_FORMAT = '%s as %s';

	/**
	 * @description wher条件
	 *
	 * @var Where
	 */
    private $where;

	/**
	 * @description OR Where条件
	 *
	 * @var Where
	 */
    private $orWhere;

	/**
	 * @description join数据
	 *
	 * @var Array
	 */
    private $joins = array();

	/**
	 * @description limit
	 *
	 * @var string
	 */
    private $limit;

	/**
	 * @description 排序
	 *
	 * @var string
	 */
    private $order;

	/**
	 * @description 分组
	 *
	 * @var string
	 */
    private $group;

	/**
	 * @description 表别名
	 *
	 * @var string
	 */
    private $tableAs;

	/**
	 * @description 构造函数
	 *
	 * @param string $table
	 *
	 * @param string | bool $as
	 */
    public function __construct($table, $as = false)
    {
        $this->tableAs = $as;
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
		$info = explode('.', $name);
		$len = count($info);

		if ($len > 1) {
			$info[$len - 1] = sprintf('`%s`', $info[$len - 1]);
			return implode('.', $info);
		}

		return sprintf('`%s`', $name);
	}

	/**
	 * @description 查询列
	 *
	 * @param Array $columns
	 *
	 * @param string | bool $tableName
	 *
	 * @return Select
	 */
    public function columns(Array $columns, $tableName = false)
    {
        $finalTable = $this->table;
        if ($tableName === false || !is_string($tableName)) {
            if ($this->tableAs !== false && is_string($this->tableAs)) {
                $finalTable = $this->tableAs;
            }
        } else {
            $finalTable = $tableName;
        }

        foreach ($columns as $key => $val) {
            if (is_numeric($key)) {
                $key = $val;
            }

            if (preg_match('/\(/', $val)) {
				$info = explode('(', $val);
				if (strtoupper(trim($info[0])) == 'COUNT') {
					$this->fields[] = sprintf(self::FIELD_NORMAL_FORMAT, $val, $key);
					continue;
				}

				$val = str_replace(array('(', ')'), array('(' . $finalTable . '.`', '`)'), $val);
                $this->fields[] = sprintf(self::FIELD_NORMAL_FORMAT, $val, $key);
                continue;
            }

            $this->fields[] = sprintf(self::FIELD_FORMAT, $finalTable, $this->format($val), $key);
        }

        return $this;
    }

	/**
	 * @description 关联
	 *
	 * @param Array $tableInfo
	 *
	 * @param string $on
	 *
	 * @param Array $fields
	 *
	 * @param int $type
	 *
	 * @return Select
	 */
    private function join(Array $tableInfo, $on, Array $fileds, $type)
    {
		$on = $this->formatOn($on);

        $as = '';
        $table = '';
        foreach ($tableInfo as $key => $val) {
            $table = $this->format($val);
            if (is_numeric($key)) {
                $as = $val;
            } else {
                $as = $key;
            }
            break;
        }

        $this->columns($fileds, $as);

        $this->joins[] = sprintf($type, $table, $as, $on);

        return $this;
    }

	/**
	 * @description 内联
	 *
	 * @param Array $tableInfo
	 *
	 * @param string $on
	 *
	 * @param Array $fileds
	 *
	 * @return Select
	 */
    public function innerJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::INNER_JOIN_FORMAT);
    }

	/**
	 * @description 左联
	 *
	 * @param Array $tableInfo
	 *
	 * @param string $on
	 *
	 * @param Array $fileds
	 *
	 * @return Select
	 */
    public function leftJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::LEFT_JOIN_FORMAT);
    }

	/**
	 * @description 右联
	 *
	 * @param Array $tableInfo
	 *
	 * @param string $on
	 *
	 * @param Array $fileds
	 *
	 * @return Select
	 */
    public function rightJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::RIGHT_JOIN_FORMAT);
    }

	/**
	 * @description 查询条件
	 *
	 * @param Where | Array $where
	 *
	 * @return Select
	 */
    public function where($where)
	{
		if ($where instanceof Where) {
        	$this->where = $where;
		} else if (is_array($where)) {
			$this->where = new Where();
			foreach ($where as $key => $val) {
				if (is_numeric($key)) {
					$this->where->statement($val);
					continue;
				}

				$this->where->equal($key, $val);
			}
		} else {
			$this->where = new Where();
			$this->where->statement($where);
		}

		return $this;
    }

	/**
	 * @description 或条件
	 *
	 * @param Where
	 *
	 * @return Select
	 */
    public function orWhere(Where $where)
    {
        $this->orWhere = $where;
        return $this;
    }

	/**
	 * @description 处理表的别名
	 *
	 * @return string
	 */
    private function processAsTable()
    {
        if ($this->tableAs === false) {
            return $this->table;
        }

        if (!is_string($this->tableAs)) {
            return $this->table;
        }

        return sprintf('%s AS %s', $this->table, $this->tableAs);
    }

	/**
	 * @description 准备语句
	 *
	 * @return string
	 */
    public function getPrepareSql()
    {
        $finalTable = $this->processAsTable();

        $sql = '';
        if (count($this->fields) < 1) {
            $sql = sprintf(self::SQL_FORMAT, '*', $finalTable); 
        } else {
            $sql = sprintf(self::SQL_FORMAT, implode(',', $this->fields), $finalTable);
        }

        if (count($this->joins) > 0) {
            $sql .= implode('', $this->joins);
        }

        $whereSql = $this->getPrepareWhere();
        if (!empty($whereSql)) {
            $sql .= $whereSql;
        }

        if (!empty($this->group)) {
            $sql .= $this->group;
        }

        if (!empty($this->order)) {
            $sql .= $this->order;
        }

        if (!empty($this->limit)) {
            $sql .= $this->limit;
        }

        return $sql;
    }

	/**
	 * @description 准备查询条件
	 *
	 * @return string
	 */
    private function getPrepareWhere()
    {
        $sql = null;
        if (!empty($this->where)) {
            $sql = $this->where->getPrepareWhereSql();
        }

        if (!empty($this->orWhere)) {
            if (empty($sql)) {
                $sql = $this->orWhere->getPrepareOrWhereSql();
            } else {
                $sql .= str_replace('WHERE', 'AND', $this->orWhere->getPrepareOrWhereSql());
            }
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
        $tmp = array();
        if (!empty($this->where)) {
            foreach ($this->where->getBindData() as $val) {
                $tmp[] = $val;
            }
        }

        if (!empty($this->orWhere)) {
            foreach ($this->orWhere->getBindData() as $val) {
                $tmp[] = $val;
            }
        }

        return $tmp;
    }

	/**
	 * @description 条数限制
	 *
	 * @param int $page
	 *
	 * @param int $size
	 *
	 * @return Select
	 */
    public function limit($page, $size = 0)
	{
		if ($size <= 0) {
			$this->limit = sprintf(' LIMIT %s', $page);
			return $this;
		}

        $this->limit = sprintf(' LIMIT %s,%s', intval(($page - 1) * $size), intval($size));
        return $this;
    }

	/**
	 * @description 排序
	 *
	 * @param string $order
	 *
	 * @return Select
	 */
    public function order($order)
    {
		if (!is_array($order)) {
			$order = array($order);
		}

		array_walk($order, function (&$row) {
			$tmp = explode(' ', trim($row));
			$tmp[0] = $this->format($tmp[0]);
			$row = implode(' ', $tmp);
		});

        $this->order = sprintf(' ORDER BY %s', implode(',', $order));
        return $this;
    }

	/**
	 * @description 分组
	 *
	 * @param string | Array $group
	 *
	 * @return Select
	 */
    public function group($group)
    {
        if (!is_array($group)) {
            $group = array($group);
        }

		array_walk($group, function (&$row) {
			$row = $this->format($row);
		});

        $this->group = sprintf(' GROUP BY %s', implode(',', $group));
        return $this;
    }

	/**
	 * @description 格式化ON条件
	 *
	 * @param string $on
	 *
	 * @return string
	 */
	private function formatOn($on)
	{
		$info = explode(' ', $on);
		array_walk($info, function (&$row) {
			if (empty(trim($row))) {
				return;
			}

			if (in_array(strtoupper(trim($row)), array('AND', 'OR'))) {
				return;
			}

			$tmp = explode('=', $row);
			if (count($tmp) != 2) {
				return;
			}

			array_walk($tmp, function (&$r) {
				$tt = explode('.', trim($r));
				$len = count($tt);
				if ($len < 2) {
					return;
				}

				$tt[$len - 1] = sprintf('`%s`', $tt[$len - 1]);

				$r = implode('.', $tt);
			});

			$row = implode('=', $tmp);
		});

		return implode(' ', $info);
	}

	/**
	 * @description 格式化SQL语句
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
			$sql = substr_replace($sql, $needle, strpos($sql, '?'), 1);
		}

		return $sql;
	}
}
