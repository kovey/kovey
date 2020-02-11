<?php
/**
 *
 * @description 创建web工程
 *
 * @package     
 *
 * @time        2019-12-26 00:03:07
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Project/Create/Web.php
 *
 * @author      kovey
 */
namespace Command\Project\Create;

use Util\Util;

class Web
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
			->createCron()
			->createConf()
			->createPublic()
			->createService()
			->createIndex()
			->createVendor();
	}

	private function createPublic()
	{
		mkdir($this->root . '/public/static', 0755, true);
		return $this;
	}

	private function createService()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/service', $this->root . '/service');
		$app = file_get_contents($this->root . '/service/kovey.service');
		$app = str_replace(array(
			'{pid_file}', 
			'{root}'
		), array(
			$this->root . '/run/kovey-framework',
			$this->root
		), $app);
		file_put_contents($this->root . '/service/kovey.service', $app);
		return $this;
	}

	private function createIndex()
	{
		copy(KOVEY_TOOLS_BIN . '/template/web/index.php', $this->root . '/index.php');
		return $this;
	}

	private function createApplication()
	{
		$app = $this->root . '/application';
		Util::copy(KOVEY_TOOLS_BIN . '/template/controllers', $app . '/controllers');
		Util::copy(KOVEY_TOOLS_BIN . '/template/layouts', $app . '/layouts');
		mkdir($app . '/library', 0755, true);
		mkdir($app . '/routers', 0755, true);

		Util::copy(KOVEY_TOOLS_BIN . '/template/plugins', $app . '/plugins');
		Util::copy(KOVEY_TOOLS_BIN . '/template/views', $app . '/views');

		copy(KOVEY_TOOLS_BIN . '/template/web/Bootstrap.php', $app . '/Bootstrap.php');
 
		return $this;
	}

	private function createBin()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/bin', $this->root . '/bin');
		chmod($this->root . '/bin/kovey', 0755);
		return $this;
	}

	private function createCron()
	{
		mkdir($this->root . '/cron', 0755, true);
		touch($this->root . '/cron/cron');
		return $this;
	}

	private function createConf()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/template/web/conf', $this->root . '/conf');

		$app = file_get_contents($this->root . '/conf/app.ini');
		$app = str_replace(array('{project}', '{cron-file}'), array($this->name, $this->root . '/cron/cron'), $app);
		file_put_contents($this->root . '/conf/app.ini', $app);

		mkdir($this->root . '/run', 0755, true);

		$core = file_get_contents($this->root . '/conf/server.ini');
		$core = str_replace(array(
			'{pid_file}', 
			'{logger_info}', 
			'{logger_error}', 
			'{logger_warning}', 
			'{logger_exception}', 
			'{session_dir}',
			'{db}',
			'{monitor}',
			'{project}',
			'{log_file}'
		), array(
			$this->root . '/run/kovey-framework',
			$this->root . '/logs/info',
			$this->root . '/logs/error',
			$this->root . '/logs/warning',
			$this->root . '/logs/exception',
			$this->root . '/session',
			$this->root . '/db',
			$this->root . '/monitor',
			$this->name,
			$this->root . '/logs/server/server.log'
		), $core);
		file_put_contents($this->root . '/conf/server.ini', $core);
		return $this;
	}

	private function createVendor()
	{
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Components', $this->root . '/vendor/Kovey/Components');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Config', $this->root . '/vendor/Kovey/Config');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Util', $this->root . '/vendor/Kovey/Util');
		Util::copy(KOVEY_TOOLS_BIN . '/../Kovey/Web', $this->root . '/vendor/Kovey/Web');
		copy(KOVEY_TOOLS_BIN . '/../kovey.php', $this->root. '/vendor/kovey.php');

		return $this;
	}
}
