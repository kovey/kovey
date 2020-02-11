<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:26:06
 *
 * @file test/Assert/Compare/Equal/EqualArray.php
 *
 */
namespace Test\Assert\Compare\Equal;

class EqualArray
{
    public static function compare(Array $a, Array $b)
    {
        $res = array(
            'result' => true,
            'not' => array()
        );

        if (count($a) !== count($b)) {
            $res['result'] = false;
            return $res;
        }

        $result = true;
        $not = array();

        foreach ($a as $key => $val) {
            if (!isset($b[$key])) {
                $not[$key] = self::format($val, null);
                $result = false;
                continue;
            }

            if (!is_array($val)) {
                if ($b[$key] !== $val) {
                    $result = false;
                    $not[$key] = self::format($val, $b[$key]);
                }

                continue;
            }

            if (!is_array($b[$key])) {
                $result = false;
                $not[$key] = self::format('is array', 'not array');
                continue;
            }


            $cre = self::compare($val, $b[$key]);
            if (!$cre['result']) {
                $result = false;
                $res['not'] = array_merge($res['not'], $cre['not']);
                continue;
            }
        }

        $res['not'][] = $not;
        $res['result'] = $result;

        return $res;
    }

    public static function format($expect, $give)
    {
        if (is_null($give)) {
            return sprintf('g:%s,e:%s', 'null', $expect);
        }

        return sprintf('g:%s,e:%s', $give, $expect);
    }
}
