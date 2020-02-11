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
    const SQL_FORMAT = ' WHERE %s';

    private $data;

    private $fields;

    public function __construct()
    {
        $this->data = array();
        $this->fields = array();
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

    public function __set($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '=?';
    }

    public function large($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>?';
    }

    public function notEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<>?';
    }

    public function largeEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>=?';
    }

    public function little($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<?';
    }

    public function littleEqual($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<=?';
    }

    public function equal($name, $val)
    {
        $this->__set($name, $val);
    }

    public function in($name, Array $val)
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' IN(' . implode(',', $inVals). ')';
    }

    public function notIn($name, Array $val)
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' NOT IN(' . implode(',', $inVals) . ')';
    }

    public function like($name, $val)
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' LIKE ?';
    }

    public function between($name, $start, $end)
    {
        $this->data[] = $start;
        $this->data[] = $end;
        $this->fields[] = $this->format($name) . ' BETWEEN ? AND ?';
    }

    public function statement($statement)
    {
        $this->fields[] = $statement;
    }

    public function getPrepareWhereSql()
    {
        if (count($this->fields) < 1) {
            return false;
        }

        return sprintf(self::SQL_FORMAT, implode(' AND ', $this->fields));
    }

    public function getPrepareOrWhereSql()
    {
        if (count($this->fields) < 1) {
            return false;
        }

        return sprintf(self::SQL_FORMAT, implode(' OR ', $this->fields));
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
