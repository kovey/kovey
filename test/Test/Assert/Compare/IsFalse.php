<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:12:07
 *
 * @file test/Assert/Compare/IsFalse.php
 *
 */
namespace Test\Assert\Compare;

class IsFalse
{
    public function compare(bool $a)
    {
        return $a === false;
    }
}
