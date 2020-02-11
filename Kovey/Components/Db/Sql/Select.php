<?php
/**
 *
 * @description 查询语句实现
 *
 * @package     Components\Db\Sql
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
    const SINGLE = 1;

    const ALL = 2;

    private $table;

    private $fields = array();

    const SQL_FORMAT = 'SELECT %s FROM %s';

    const INNER_JOIN_FORMAT = ' INNER JOIN %s AS %s ON %s ';

    const LEFT_JOIN_FORMAT = ' LEFT JOIN %s AS %s ON %s ';

    const RIGHT_JOIN_FORMAT = ' RIGHT JOIN %s AS %s ON %s ';

    const FIELD_FORMAT = '%s.%s as %s';

    const FIELD_NORMAL_FORMAT = '%s as %s';

    private $where;

    private $orWhere;

    private $joins = array();

    private $limit;

    private $order;

    private $group;

    private $tableAs;

    public function __construct($table, $as = false)
    {
        $this->tableAs = $as;
		$info = explode('.', $table);
		array_walk($info, function (&$row) {
			$row = $this->format($row);
		});

		$this->table = implode('.', $info);
    }

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

    private function join(Array $tableInfo, $on, $fileds, $type)
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

    public function innerJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::INNER_JOIN_FORMAT);
    }

    public function leftJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::LEFT_JOIN_FORMAT);
    }

    public function rightJoin(Array $tableInfo, $on, Array $fileds = array())
    {
        return $this->join($tableInfo, $on, $fileds, self::RIGHT_JOIN_FORMAT);
    }

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

    public function orWhere(Where $where)
    {
        $this->orWhere = $where;
        return $this;
    }

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

    public function limit($page, $size = 10)
    {
        $this->limit = sprintf(' LIMIT %s,%s', intval(($page - 1) * $size), intval($size));
        return $this;
    }

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

	public function toString()
	{
		$sql = $this->getPrepareSql();
		$data = $this->getBindData();

		if (count($data) < 1) {
			return $sql;
		}

		$pos = Util::strposall($sql, '?');
		foreach ($pos as $index => $p) {
			$sql = substr_replace($sql, $data[$index], $p, 1);
		}

		return $sql;
	}
}
