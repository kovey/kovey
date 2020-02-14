<?php
/**
 *
 * @description 数据验证
 *
 * @package     Util
 *
 * @time        Tue Sep 24 08:50:55 2019
 *
 * @class       vendor/Kovey/Util/Validate.php
 *
 * @author      kovey
 */
namespace Kovey\Util;

class Validator
{
    private $errors;

    private $data;

    private $rules;

    const PATTERN18  = '/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/';

	const PATTERN15 = '/^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}[0-9Xx]$/';

    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = array();
    }

    public function getErros()
    {
        return $this->errors;
    }

	/**
	 * 适用于同一个字段在其依赖字段的不同值的情况下的验证
	 */
	protected function inlineCondition($key, $conditions)
	{
		foreach ($conditions as $condition) {
			foreach ($condition as $field => $rules) {
				if (count($rules) != 3) {
					$this->errors[] = 'inline condition format error, example: "feild" => array("inlineCondition" => array("key" => array("opr", "val", array("rule", "rule1")))), opr:>,>=,===,==,<,<=,!=,!==,in_array';
					return false;
				}

				if (!in_array($rules[0], array('>','>=', '<', '<=', '==', '===', '!=', '!==', 'in_array'), true)) {
					$this->errors[] = 'opr is error, opr:>,>=,===,==,<,<=,!=,!==,in_array';
					return false;
				}

				if ($rules[0] === 'in_array') {
					if (!in_array($this->data[$field], $rules[1], true)) {
						continue;
					}
					if (!$this->validRule($key, $info[2])) {
						return false;
					}

					continue;
				}

				eval('$result=' . $this->data[$field] . $rules[0] . $rules[1] . ';');
				if (!$result) {
					continue;
				}

				if (!$this->validRule($key, $rules[2])) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * 适用于不同字段依赖于同一个字段时的验证
	 *
	 */
	protected function condition($key, $ruleInfo)
	{
		$condition = $ruleInfo['condition'];
		unset($ruleInfo['condition']);
		$needValid = true;
		foreach ($condition as $k => $info) {
			if (!isset($this->data[$k])) {
				$needValid = false;
				break;
			}

			if (count($info) !== 2) {
				$this->errors[] = 'condition format error, example: "feild" => array("condition" => array("key" => array("opr", "val"))), opr:>,>=,===,==,<,<=,!=,!==,in_array';
				return false;
			}

			if (!in_array($info[0], array('>','>=', '<', '<=', '==', '===', '!=', '!==', 'in_array'), true)) {
				$this->errors[] = 'opr is error, opr:>,>=,===,==,<,<=,!=,!==,in_array';
				return false;
			}

			if ($info[0] === 'in_array') {
				if (!in_array($this->data[$k], $info[1], true)) {
					$needValid = false;
					break;
				}
				continue;
			}

			eval('$result=' . $this->data[$k] . $info[0] . $info[1] . ';');
			if (!$result) {
				$needValid = false;
				break;
			}
		}

		if (!$needValid) {
			return true;
		}

		return $this->validRule($key, $ruleInfo);
	}

	protected function validRule($key, $ruleInfo)
	{
		if (in_array('required', $ruleInfo, true)) {
			if (!isset($this->data[$key])) {
				$this->errors[] = "$key is not exists";
				return false;
			}
		} else {
			if (!isset($this->data[$key])
				|| (!is_numeric($this->data[$key]) && empty($this->data[$key]))
			) {
				return true;
			}
		}

		if (in_array('canEmpty', $ruleInfo, true)) {
			if (strlen($this->data[$key]) === 0) {
				return true;
			}
		}

		foreach ($ruleInfo as $fun => $params) {
			if (is_numeric($fun)) {
				if ($params === 'canEmpty') {
					continue;
				}

				if (!$this->$params($this->data[$key])) {
					$this->errors[] = "$key validate fail with $params";
					return false;
				}

				continue;
			}

			if (!is_array($params)) {
				if (!$this->$fun($this->data[$key], $params)) {
					$this->errors[] = "$key validate fail with $fun limit with $params";
					return false;
				}

				continue;
			}

			if ($fun === 'inArray') {
				if (!$this->inArray($this->data[$key], $params)) {
					$this->errors[] = "$key validate fail with $fun limit with " . Json::encode($params);
					return false;
				}
				continue;
			}

			if (count($params) !== 2) {
				$this->errors[] = "params format error: " . Json::encode($params);
				return false;
			}

			if (!$this->$fun($this->data[$key], $params[0], $params[1])) {
				$this->errors[] = "$key validate fail with $fun limit with {$params[0]} and {$params[1]}";
				return false;
			}
		}

		return true;
	}

    public function run()
    {
        foreach ($this->rules as $key => $ruleInfo) {
			/**
			 * 内部条件验证
			 */
			if (isset($ruleInfo['inlineCondition'])) {
				if (!$this->inlineCondition($key, $ruleInfo['inlineCondition'])) {
					return false;
				}

				continue;
			}

			/**
			 * 条件验证
			 */
			if (isset($ruleInfo['condition'])) {
				if (!$this->condition($key, $ruleInfo)) {
					return false;
				}

				continue;
			}

			if (!$this->validRule($key, $ruleInfo)) {
				return false;
			}
        }

        return true;
    }

    public function maxlength($data, $length)
    {
        return strlen($data) <= $length;
    }

    public function minlength($data, $length)
    {
        return strlen($data) >= $length;
    }

    public function greaterThan($data, $val)
    {
        return $data > $val;
    }

    public function nessThan($data, $val)
    {
        return $data >= $val;
    }

    public function little($data, $val)
    {
        return $data < $val;
    }

    public function littleTo($data, $val)
    {
        return $data <= $val;
    }

    public function equalsLength($data, $length)
    {
        return strlen($data) === $length;
    }

    public function required($data)
    {
        return !is_null($data);
    }

    public function numberic($data)
    {
        return is_numeric($data);
    }

	public function money($data)
	{
		return (bool)preg_match('/^[0-9][0-9]*.[0-9]{2}$/', $data);
	}

	public function rate($data)
	{
		return (bool)preg_match('/^0.[0-9]{5}$/', $data);
	}

    public function number($data)
    {
        return ctype_digit($data) || is_int($data);
    }

    public function isArray($data)
    {
        return is_array($data);
    }

    public function inArray($data, Array $val)
    {
		if (is_array($data)) {
			foreach ($data as $v) {
				if (!in_array($v, $val, true)) {
					return false;
				}
			}

			return true;
		}

        return in_array($data, $val, true);
    }

    public function order($data)
    {
        return (bool)preg_match('/^[a-zA-Z0-9]+$/', $data);
    }

    public function wechat($data)
    {
        return (bool)preg_match('/^[a-zA-Z0-9_-]+$/', $data);
    }

    public function code($data)
    {
        return (bool)preg_match('/^[a-zA-Z]+$/', $data);
    }

    public function YmdHisDate($data)
    {
        return true;
    }

    public function url($data)
    {
        return (bool)preg_match('/^((ht|f)tps?):\/\/([\w\-]+(\.[\w\-]+)*\/)*[\w\-]+(\.[\w\-]+)*\/?(\?([\w\-\.,@?^=%&:\/~\+#]*)+)?/', $data);
    }

    public function ext($data)
    {
        return true;
    }

    public function name($data)
    {
        return (bool)preg_match('/^(?!_)[A-Za-z0-9_\-\x80-\xff]+$/', $data);
    }

	public function  mobile($data)
	{
		return (bool)preg_match('/1[3456789]{1}\d{9}$/', $data);
	}	

	public function email($data)
	{
		return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
	}

	public function date($date)
	{
        $date = explode('-', $date);
        if (count($date) != 3) {
            return false;
        }

		if (strlen($date[0]) != 4
			|| strlen($date[1]) != 2	
			|| strlen($date[2]) != 2	
		) {
			return false;
		}

		if (!ctype_digit($date[0]) 
			|| !ctype_digit($date[1])
			|| !ctype_digit($date[2])
		) {
            return false;
        }

        if (intval($date[1]) < 1 || intval($date[1]) > 12) {
            return false;
        }

        if (in_array($date[1], array('01', '03', '05', '07', '08', '10', '12'), true)) {
            if (intval($date[2]) < 1 || intval($date[2]) > 31) {
                return false;
            }
        } else if ($date[1] === '02') {
            if ($this->isLeapYear($date[0])) {
                if (intval($date[2]) > 29 || intval($date[2]) < 1) {
                    return false;
                }
            } else {
				if (intval($date[2]) > 28 || intval($date[2]) < 1) {
					return false;
				}
			}
		} else {
            if (intval($date[2]) > 30 || intval($date[2]) < 1) {
                return false;
            }
		}

        return true;
	}

    public function dateTime($dateTime)
    {
		$info = explode(' ', $dateTime);
		if (count($info) != 2) {
			return false;
		}

		$date = $info[0];
		if (!$this->date($date)) {
			return false;
		}

		$time = $info[1];
		return $this->time($time);
    }

    public function isLeapYear($year) 
    {
        if (strlen($year) != 4) {
            return false;
        }

        if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
            return true;
        }

        return false;
    }

    public function time($time) 
    {
		$time = explode(':', $time);
        if (count($time) != 3) {
            return false;
        }

		if (strlen($time[0]) != 2
			|| strlen($time[1]) != 2	
			|| strlen($time[2]) != 2	
		) {
			return false;
		}

        if (!ctype_digit($time[0]) || !ctype_digit($time[1]) || !ctype_digit($time[2])) {
            return false;
        }

        if (intval($time[0]) > 23 || intval($time[0]) < 0) {
            return false;
        }

        if (intval($time[1]) > 59 || intval($time[1]) < 0) {
            return false;
        }

        if (intval($time[2]) > 59 || intval($time[2]) < 0) {
            return false;
        }

        return true;
    }

	public function idCard($data)
	{
		return preg_match(self::PATTERN18, $data) || preg_match(self::PATTERN15, $data);
	}

	public function notEmpty($data)
	{
		if (is_array($data)) {
			return count($data) > 0;
		}

		return strlen($data) > 0;
	}

	public function className($data)
	{
		return (bool)preg_match('/^[A-Z][a-zA-Z]+$/', $data);
	}

	public function mobileCompany($data)
	{
		return (bool)preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]$/', $data);
	}

	public function equalCount($data, $count)
	{
		return count($data) == $count;
	}
}
