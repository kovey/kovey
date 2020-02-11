<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 11:46:03
 *
 * @file test/Test/TestBase.php
 *
 */
namespace Test;

use Test\Assert\Assert;

abstract class TestBase implements TestInterface
{
    final public function __construct()
    {
        $this->init();
    }

    public function assertEqual($except, $gived)
    {
        return Assert::equal($except, $gived);
    }

    public function assertNotEqual($except, $gived)
    {
        return Assert::notEqual($except, $gived);
    }

    public function assertEqualArray($except, $gived)
    {
        return Assert::equalArray($except, $gived);
    }

    public function assertNotEqualArray($except, $gived)
    {
        return Assert::notEqualArray($except, $gived);
    }

    public function assertTrue($gived)
    {
        return Assert::isTrue($gived);
    }

    public function assertFalse($gived)
    {
        return Assert::isFalse($gived);
    }

    public function test()
    {
        $this->setUp();
        $this->run();
        $this->tearDown();
    }

    protected function init()
    {
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    abstract protected function run();
}
