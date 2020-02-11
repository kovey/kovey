<?php
/**
 *
 * @description 控制器接口
 *
 * @package     Controller
 *
 * @time        2019-10-17 23:51:21
 *
 * @file  vendor/Kovey\Web/App/Mvc/ControllerInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Mvc\Controller;

use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Plugin\PluginInterface;
use Kovey\Web\App\Mvc\View\ViewInterface;

interface  ControllerInterface
{
	public function __construct(RequestInterface $req, ResponseInterface $res, string $template, Array $plugins);

	public function setView(ViewInterface $view);

	public function render();

	public function getResponse();

	public function getRequest();

	public function initPlugins(Array $plugins);

	public function getPlugins();

	public function redirect($url);

	public function disableView();

	public function isViewDisabled();

	public function isPluginDisabled();

	public function disablePlugin();

	public function setHeader($key, $val);
}
