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
	/**
	 * @description 自定义的加载路径
	 *
	 * @var Array
	 */
	private $customs;

	/**
	 * @description 插件加载目录
	 *
	 * @var string
	 */
	private $plugins;

	/**
	 * @description 库目录
	 *
	 * @var string
	 */
	private $library;

	/**
	 * @description 构造函数
	 *
	 * @return Autoload
	 */
	public function __construct()
	{
		$this->plugins = APPLICATION_PATH . '/application/plugins/';
		$this->library = APPLICATION_PATH . '/application/library/';

		$this->customs = array();
	}

	/**
	 * @description 注册自动加载的路径
	 *
	 * @return null
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'autoloadPlugins'));
		spl_autoload_register(array($this, 'autoloadUserLib'));
		spl_autoload_register(array($this, 'autoloadLocal'));
	}

	/**
	 * @description 插件库路径
	 *
	 * @return null
	 */
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

	/**
	 * @description 用户库目录
	 *
	 * @return null
	 */
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

	/**
	 * @description 自定义加载路径
	 *
	 * @return null
	 */
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

	/**
	 * @description 添加自定义加载路径
	 *
	 * @param string $path
	 *
	 * @return Autoload
	 */
	public function addLocalPath($path)
	{
		if (!is_dir($path)) {
			return $this;
		}
		$this->customs[] = $path;
		return $this;
	}
}
