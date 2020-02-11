<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-10-11 10:22:16
 *
 * @file test/assert/assert.php
 *
 */
namespace Test\Assert;

use Test\Assert\Compare;

class Assert
{
	private static $testCount = 0;

	private static $failCount = 0;

	private static $succCount = 0;
	public static function getTotalCount()
	{
		return self::$testCount;
	}

	public static function getFailCount()
	{
		return self::$failCount;
	}

	public static function getSuccCount()
	{
		return self::$succCount;
	}

    public static function setConfig()
    {
		if (ini_get('zend.assertions') != 1) {
			echo "please change zend.assertions value to 1 in php.ini\n";
			exit;
		}

        assert_options(ASSERT_ACTIVE, true);
        assert_options(ASSERT_WARNING,  false);
        //assert_options(ASSERT_CALLBACK, array('Test\Assert\Assert', 'assertFail'));
    }

    public static function assertFail($script, $line, $message, $desc = '') {
        echo "$script: $line: $desc\n";
    }

    private static function assert($assertion, $expect, $gived)
	{
		self::$testCount ++;
        if (assert($assertion)) {
			echo '.';
			self::$succCount ++;
            return true;
		}
		self::$failCount ++;
        $info = debug_backtrace();
        if (count($info) < 4) {
            return false;
        }
        
        $testCase = $info[2];

        echo sprintf(
            "Failure Test %s, %s is failed in %s on line %s, %s\n", 
            self::getTestClass($info[3]['class']), $testCase['function'], $testCase['file'], $testCase['line'], ''
        );

        Show::showDiff(array('value' => $expect), array('value' => $gived));

        return false;
    }

    private static function assertArray($assertion, $expect, $gived, $not)
    {
        if (assert($assertion)) {
            echo '.';
            return true;
        }
        $info = debug_backtrace();
        if (count($info) < 4) {
            return false;
        }
        
        $testCase = $info[2];
        echo sprintf(
            "Failure Test %s, %s is failed in %s on line %s, %s\n", 
            self::getTestClass($info[3]['class']), $testCase['function'], $testCase['file'], $testCase['line'], ''
        );

        Show::showDiff($expect, $gived, $not);
        return false;
    }

    private static function getTestClass($class)
    {
        $info = explode('\\', $class);
        return $info[count($info) - 1];
    }

    private static function jsonEncode($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function formatMsg($expect, $gived)
    {
        if (!is_array($expect) && !is_object($gived)) {
            return sprintf('expect: "%s", gived: "%s"', $expect, $gived);
        }

        return sprintf('expect: "%s", gived: "%s"', self::jsonEncode($expect), self::jsonEncode($gived));
    }

    public static function isTrue(bool $assertion)
    {
        return self::assert(Compare\IsTrue::compare($assertion), 'true', 'false');
    }

    public static function isFalse(bool $assertion)
    {
        return self::assert(Compare\IsFalse::compare($assertion), 'false', 'true');
    }

    public static function equal($expect, $gived)
    {
        return self::assert(Compare\Equal\Equal::compare($expect, $gived), $expect, $gived);
    }

    public static function notEqual($expect, $gived)
    {
        return self::assert(Compare\NotEqual\NotEqual::compare($expect, $gived), $expect, $gived);
    }

    public static function equalArray(Array $expect, Array $gived)
    {
        $result = Compare\Equal\EqualArray::compare($expect, $gived);
        return self::assertArray($result['result'], $expect, $gived, $result['not']);
    }

    public static function notEqualArray(Array $expect, Array $gived)
    {
        $result = Compare\NotEqual\NotEqualArray::compare($expect, $gived);
        return self::assertArray($result['result'], $expect, $gived, $result['not']);
    }
}
