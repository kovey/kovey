<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:06:59
 *
 * @file /Users/kovey/Documents/workspace/project/test/Assert/Compare/IsTrue.php
 *
 */
namespace Test\Assert\Compare;

class IsTrue
{
    public function compare(bool $a)
    {
        return $a === true;
    }
}
