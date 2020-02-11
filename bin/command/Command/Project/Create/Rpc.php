<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2020-01-18 21:28:26
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Project/Create/Rpc.php
 *
 * @author      kovey
 */
namespace Command\Project\Create;

use Util\Util;

class Rpc
{
	private $name;

	private $path;

	private $root;

	public function __construct($path, $name)
	{
		$this->path = $path;
		$this->name = $name;
		$this->root = $this->path . '/' . $this->name;
	}

	public function create()
	{
		if (!is_dir($this->path)) {
			mkdir($this->path, 0755, true);
		}

		mkdir($this->root, 0755, true);

		$this->createApplication()
			->createBin()
			->createConf()
			->createService()
			->createIndex()
			->createVendor();
	}

	private function createService()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/service', $this->root . '/service');
		$app = file_get_contents($this->root . '/service/kovey.service');
		$app = str_replace(array(
			'{pid_file}', 
			'{root}'
		), array(
			$this->root . '/run/kovey-rpc',
			$this->root
		), $app);
		file_put_contents($this->root . '/service/kovey.service', $app);
		return $this;
	}

	private function createIndex()
	{
		copy(KOVEY_TOOLS_BIN . '/template/rpc/index.php', $this->root . '/index.php');
		return $this;
	}

	private function createApplication()
	{
		$app = $this->root . '/application';
		Util::copy(KOVEY_TOOLS_BIN . '/template/Handler', $app . '/Handler');
		mkdir($app . '/library', 0755, true);

		copy(KOVEY_TOOLS_BIN . '/template/rpc/Bootstrap.php', $app . '/Bootstrap.php');
 
		return $this;
	}

	private function createBin()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/rpc/bin', $this->root . '/bin');
		chmod($this->root . '/bin/kovey', 0755);
		return $this;
	}

	private function createConf()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/rpc/conf', $this->root . '/conf');

		mkdir($this->root . '/run', 0755, true);

		$core = file_get_contents($this->root . '/conf/server.ini');
		$core = str_replace(array(
			'{log_file}',
			'{pid_file}',
		   	'{name}',	
			'{info}', 
			'{exception}', 
			'{error}', 
			'{warning}', 
			'{db}',
			'{monitor}',
			'{rpc-name}'
		), array(
			$this->root . '/logs/server/server.log',
			$this->root . '/run/kovey-rpc',
			$this->name,
			$this->root . '/logs/info',
			$this->root . '/logs/exception',
			$this->root . '/logs/error',
			$this->root . '/logs/warning',
			$this->root . '/logs/db',
			$this->root . '/monitor',
			$this->name
		), $core);
		file_put_contents($this->root . '/conf/server.ini', $core);
		return $this;
	}

	private function createVendor()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Components', $this->root . '/vendor/Kovey/Components');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Config', $this->root . '/vendor/Kovey/Config');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Util', $this->root . '/vendor/Kovey/Util');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Rpc', $this->root . '/vendor/Kovey/Rpc');
		copy(KOVEY_TOOLS_BIN . '/../kovey.php', $this->root. '/vendor/kovey.php');

		return $this;
	}
}
