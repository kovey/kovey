<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:13:14
 *
 * @file test/Assert/Compare/NotEqaul/NotEqaul.php
 *
 */
namespace Test\Assert\Compare\NotEqual;

class NotEqual
{
    public function compare($a, $b)
    {
        return $a !== $b;
    }
}
