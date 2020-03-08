<?php
/**
 *
 * @description 帮助
 *
 * @package     Common\Common
 *
 * @time        2019-12-17 23:24:18
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Common/Help.php
 *
 * @author      kovey
 */
namespace Command\Common;

use Command\CommandInterface;
use Util\Logo;

class Help implements CommandInterface
{
	public function run()
	{
		Logo::show(Version::KOVEY_FRAMEWORK_VERSION);

		echo "Usage: kf.php [options] [--type] <type create del>" . PHP_EOL .
			"-h                    show help" . PHP_EOL .
			"--help                show help" . PHP_EOL .
			"-v                    show version" . PHP_EOL .
			"--version             show version" . PHP_EOL .
			"--project <name>      named project will be create or del" . PHP_EOL .
			"--handler <name>      named handler will be create or del" . PHP_EOL .
			"--service <name>      named service will be create or del" . PHP_EOL .
			"--controller <name>   named controller will be create or del" . PHP_EOL .
			"--path <path>         path to project" . PHP_EOL .
			"--ptype <name>        project type, only web or rpc or websocket" . PHP_EOL;
			"--logdir <path>       project logger directory" . PHP_EOL;
		exit;
	}
}
