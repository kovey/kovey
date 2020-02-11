<?php
/**
 *
 * @description 版本 
 *
 * @package     
 *
 * @time        2019-12-17 23:40:19
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Common/Version.php
 *
 * @author      kovey
 */
namespace Command\Common;

use Command\CommandInterface;
use Util\Logo;

class Version implements CommandInterface
{
	const KOVEY_FRAMEWORK_VERSION = '1.0';

	public function run()
	{
		Logo::show(self::KOVEY_FRAMEWORK_VERSION);
		exit;
	}
}
