<?php
/**
 *
 * @description 命令入口
 *
 * @package     
 *
 * @time        2019-12-17 23:18:41
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Command.php
 *
 * @author      kovey
 */
namespace Command;

class Command
{
	public static function run($command, $category, ...$args)
	{
		try {
			$class = '\Command\\' . ucfirst($category) . '\\' . ucfirst($command);
			$obj = new $class(...$args);
			$obj->run();
		} catch (\Throwable $e) {
			echo "command $command is unkown" . PHP_EOL;
		} catch (\Exception $e) {
			echo $e->getMessage() , "" . PHP_EOL;
		}
	}
}
