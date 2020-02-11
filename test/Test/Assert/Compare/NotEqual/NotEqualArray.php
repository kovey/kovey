<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:34:50
 *
 * @file test/Assert/Compare/NotEqual/NotEqualArray.php
 *
 */
namespace Test\Assert\Compare\NotEqual;

class NotEqualArray
{
    public static function compare(Array $a, Array $b)
    {
        $res = array(
            'result' => false,
            'not' => array()
        );

        if (count($a) !== count($b)) {
            $res['result'] = true;
            return $res;
        }

        $not = array();

        foreach ($a as $key => $val) {
            if (!isset($b[$key])) {
                $res['result'] = true;
                return $res;
            }

            if (!is_array($val)) {
                if ($b[$key] !== $val) {
                    $res['result'] = true;
                    return $res;
                }

                $not[$key] = self::format($val, $b[$key]);
                continue;
            }

            if (!is_array($b[$key])) {
                $res['result'] = true;
                return $res;
            }

            $cre = self::compare($val, $b[$key]);
            if ($cre['result']) {
                $res['result'] = true;
                return $res;
            }

            $res['not'] = array_merge($res['not'], $cre['not']);
        }

        $res['not'][] = $not;

        return $res;
    }

    public static function format($expect, $give)
    {
        if (is_null($give)) {
            return sprintf('e:%s,g:%s', $expect, 'null');
        }

        return sprintf('g:%s,e:%s', $expect, $give);
    }
}
