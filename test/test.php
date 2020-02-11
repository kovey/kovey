<?php
/**
 * @description 集成测试脚本
 *
 * @package Test
 *
 * @author kovey
 *
 * @time 2019-10-10 15:21:59
 *
 * @file test/test.php
 *
 */
require_once __DIR__ . '/autoload.php';

$config = require_once __DIR__ . '/config.php';

use Test\Assert\Assert;
use Test\Parse\Container;

$options = getopt('c:p:m:h:a:');

function showHelp()
{
    echo "Usage:\n" .
        "   php test.php <-c category> <-p path> <-m method>\n" .
        "       -c category\n" .
        "       -p path\n" .
        "       -m method\n" .
        "       -a run all test\n" .
        "       -h help\n";
    exit;
}

if (isset($options['h'])) {
    showHelp();
}


$container = new Container();

try {
    Assert::setConfig();

    if (isset($options['a']) && $options['a'] === 'all') {
		$begin = microtime(true);
        runAllTest($config['root'], $config, $container);
		$end = microtime(true);
		echo "\nTest All Begin:          $begin\n";
		echo "Test All End:            $end\n";
		echo "Test All Spent:          " . round($end - $begin, 4) . "\n";
		echo "Test All Total Assert:   " . Assert::getTotalCount()  . "\n";
		echo "Test All Success Assert: " . Assert::getSuccCount()  . "\n";
		echo "Test All Failure Assert: " . Assert::getFailCount()  . "\n";
        exit;
    }

    if (!isset($options['p'])
        || !isset($options['m'])
        || !isset($options['c'])
    ) {
        showHelp();
    }

    $class = 'TestCase\\' . ucfirst($options['c']) . '\\' . ucfirst($options['p']) . '\\' . ucfirst($options['m']);
    $begin = microtime(true);
    $obj = $container->get($class);
    if (!$obj instanceof Test\TestInterface) {
        echo "$class is not implements TestInterface\n";
        exit;
    }

    $obj->test();
    $end = microtime(true);
    echo "\nTest $class Begin:          $begin\n";
    echo "Test $class End:            $end\n";
    echo "Test $class Spent:          " . round($end - $begin, 4) . "\n";
	echo "Test $class Total Assert:   " . Assert::getTotalCount()  . "\n";
	echo "Test $class Success Assert: " . Assert::getSuccCount()  . "\n";
	echo "Test $class Failure Assert: " . Assert::getFailCount()  . "\n";
} catch (\Throwable $e) {
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

function runAllTest($path, $config, $container)
{
    $files = scandir($path);
    foreach ($files as $dir) {
        if (substr($dir, 0, 1) === '.') {
            continue;
        }

        if (in_array($dir, $config['decludeDir'])
            || in_array($dir, $config['decludeFiles'])
        ) {
            continue;
        }

        if (is_dir($path . '/' . $dir)) {
            runAllTest($path . '/' . $dir, $config, $container);
            continue;
        }

        $info = explode($config['ns'], $path);
        $class = str_replace('/', '\\', $info[count($info) - 1]);
        $class = 'TestCase' . $class . '\\' . str_replace('.php', '', $dir);
        $obj = $container->get($class);
        if (!$obj instanceof Test\TestInterface) {
            echo "$class is not implements TestInterface\n";
            continue;
        }

        $obj->test();
    }
}
