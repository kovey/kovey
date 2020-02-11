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

		echo "Usage: kf.php [options] [--type] <type create del>\n" .
			"-h                    show help\n" .
			"--help                show help\n" .
			"-v                    show version\n" .
			"--version             show version\n" .
			"--project <name>      named project will be create or del\n" .
			"--handler <name>      named handler will be create or del\n" .
			"--service <name>      named service will be create or del\n" .
			"--controller <name>   named controller will be create or del\n" .
			"--path <path>         path to project\n" .
			"--ptype <name>        project type, only web or rpc\n";
		exit;
	}
}
