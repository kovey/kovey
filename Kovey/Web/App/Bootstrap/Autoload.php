<?php
/**
 *
 * @description 自动加载管理
 *
 * @package     App\Bootstrap
 *
 * @time        Tue Sep 24 08:59:43 2019
 *
 * @class       vendor/Kovey\Web/App/Bootstrap/Autoload.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Bootstrap;

class Autoload
{
	private $customs;

	private $controllers;

	private $plugins;

	private $library;

	public function __construct()
	{
		$this->controllers = APPLICATION_PATH . '/application/controllers/';
		$this->plugins = APPLICATION_PATH . '/application/plugins/';
		$this->library = APPLICATION_PATH . '/application/library/';

		$this->customs = array();
	}

	public function register()
	{
		spl_autoload_register(array($this, 'autoloadCore'));
		spl_autoload_register(array($this, 'autoloadController'));
		spl_autoload_register(array($this, 'autoloadPlugins'));
		spl_autoload_register(array($this, 'autoloadUserLib'));
		spl_autoload_register(array($this, 'autoloadLocal'));
	}

	function autoloadCore($className)
	{
		try {
			$className = KOVEY_FRAMEWORK_PATH . '/' . str_replace('\\', '/', $className) . '.php';
			if (!is_file($className)) {
				return;
			}

			require_once $className;
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}

	function autoloadController($className)
	{
		try {
			$className = str_replace('Controller', '', $className);
			$className = $this->controllers . str_replace('\\', '/', $className) . '.php';
			$className = str_replace('//', '/', $className);
			if (!is_file($className)) {
				return;
			}
			require_once $className;
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}

	function autoloadPlugins($className)
	{
		try {
			$className = $this->plugins . str_replace('\\', '/', $className) . '.php';
			$className = str_replace('//', '/', $className);
			if (!is_file($className)) {
				return;
			}

			require_once $className;
		} catch (\Throwable $e) {	
			echo $e->getMessage();
		}
	}

	function autoloadUserLib($className)
	{
		try {
			$className = $this->library . str_replace('\\', '/', $className) . '.php';
			$className = str_replace('//', '/', $className);
			if (!is_file($className)) {
				return;
			}

			require_once $className;
		} catch (\Throwable $e) {	
			echo $e->getMessage();
		}
	}

	public function autoloadLocal($className)
	{
		foreach ($this->customs as $path) {
			try {
				$className = $path . '/' . str_replace('\\', '/', $className) . '.php';
				if (!is_file($className)) {
					continue;
				}

				require_once $className;
				break;
			} catch (\Throwable $e) {
				echo $e->getMessage();
			}
		}
	}

	public function addLocalPath($path)
	{
		if (!is_dir($path)) {
			return $this;
		}
		$this->customs[] = $path;
		return $this;
	}
}
