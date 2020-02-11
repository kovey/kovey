<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:19:36
 *
 * @file test/Assert/Compare/Eqaul/Equal.php
 *
 */
namespace Test\Assert\Compare\Equal;

class Equal
{
    public function compare($a, $b)
    {
        return $a === $b;
    }
}
