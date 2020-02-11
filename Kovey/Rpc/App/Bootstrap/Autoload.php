<?php
/**
 *
 * @description 自动加载管理
 *
 * @package     App\Bootstrap
 *
 * @time        Tue Sep 24 08:59:43 2019
 *
 * @class       vendor/Kovey\Rpc/App/Bootstrap/Autoload.php
 *
 * @author      kovey
 */
namespace Kovey\Rpc\App\Bootstrap;

class Autoload
{
	private $customs;

	private $plugins;

	private $library;

	public function __construct()
	{
		$this->plugins = APPLICATION_PATH . '/application/plugins/';
		$this->library = APPLICATION_PATH . '/application/library/';

		$this->customs = array();
	}

	public function register()
	{
		spl_autoload_register(array($this, 'autoloadCore'));
		spl_autoload_register(array($this, 'autoloadPlugins'));
		spl_autoload_register(array($this, 'autoloadUserLib'));
		spl_autoload_register(array($this, 'autoloadLocal'));
	}

	public function autoloadCore($className)
	{
		try {
			$className = KOVEY_RPC_ROOT . '/' . str_replace('\\', '/', $className) . '.php';
			if (!is_file($className)) {
				return;
			}

			require_once $className;
		} catch (\Throwable $e) {
			echo $e->getMessage();
		}
	}

	public function autoloadPlugins($className)
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

	public function autoloadUserLib($className)
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
