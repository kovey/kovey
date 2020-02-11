<?php
/**
 *
 * @description 控制器类
 *
 * @package     App\Mvc
 *
 * @time        Tue Sep 24 08:56:12 2019
 *
 * @class       vendor/Kovey\Web/App/Mvc/ControllerAbstract.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Mvc\Controller;

use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Plugin\PluginInterface;
use Kovey\Web\App\Mvc\View\ViewInterface;

class Controller implements ControllerInterface
{
	protected $view;

	protected $req;

	protected $plugins;

	protected $isViewDisabled;

	protected $isPluginDisabled;

	protected $res;

	final public function __construct(RequestInterface $req, ResponseInterface $res, string $template, Array $plugins)
	{
		$this->isViewDisabled = false;
		$this->isPluginDisabled = false;
		$this->req = $req;
		$this->res = $res;
		$this->plugins = array();
		$this->initPlugins($plugins);

		$this->init();
	}

	public function setView(ViewInterface $view)
	{
		$this->view = $view;
	}

	protected function init()
	{}

	public function render()
	{
		$this->view->render();
	}

	public function getResponse()
	{
		return $this->res;
	}

	public function getRequest()
	{
		return $this->req;
	}

	public function initPlugins(Array $plugins)
	{
		foreach ($plugins as $plugin) {
			$pclass = '\\' . $plugin;
			$pg = new $pclass(); 
			if (!$pg instanceof PluginInterface) {
				continue;
			}

			$this->plugins[$plugin] = $pg;
		}
	}

	public function getPlugins()
	{
		return $this->plugins;
	}

	public function redirect($url)
	{
		$this->getResponse()->redirect($url);
	}

	public function disableView()
	{
		$this->isViewDisabled = true;
	}

	public function isViewDisabled()
	{
		return $this->isViewDisabled;
	}

	public function isPluginDisabled()
	{
		return $this->isPluginDisabled;
	}

	public function disablePlugin()
	{
		$this->isPluginDisabled = true;
	}

	public function setHeader($key, $val)
	{
		$this->getResponse()->setHeader($key, $val);
	}
}
