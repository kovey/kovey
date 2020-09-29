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
	/**
	 * @description 构造函数
	 *
	 * @param RequestInterface $req
	 *
	 * @param ResponseInterface $res
	 *
	 * @param string $template
	 *
	 * @param Array $plugins
	 *
	 * @return ControllerInterface
	 */
	public function __construct(RequestInterface $req, ResponseInterface $res, string $template, Array $plugins);

	/**
	 * @description 设置VIEW
	 *
	 * @param ViewInterface $view
	 *
	 * @return null
	 */
	public function setView(ViewInterface $view);

	/**
	 * @description 渲染页面
	 *
	 * @return null
	 */
	public function render();

	/**
	 * @description 获取响应对象
	 *
	 * @return ResponseInterface
	 */
	public function getResponse();

	/**
	 * @description 获取请求对象
	 *
	 * @return RequestInterface
	 */
	public function getRequest();

	/**
	 * @description 初始化插件
	 *
	 * @return null
	 */
	public function initPlugins(Array $plugins);

	/**
	 * @description 获取插件
	 *
	 * @return Array
	 */
	public function getPlugins();

	/**
	 * @description 页面跳转
	 *
	 * @return null
	 */
	public function redirect($url);

	/**
	 * @description 禁用页面
	 *
	 * @return null
	 */
	public function disableView();

	/**
	 * @description 页面是否禁用
	 *
	 * @return bool
	 */
	public function isViewDisabled();

	/**
	 * @description 插件是否禁用
	 *
	 * @return bool
	 */
	public function isPluginDisabled();

	/**
	 * @description 禁用插件
	 *
	 * @return null
	 */
	public function disablePlugin();

	/**
	 * @description 设置头信息
	 *
	 * @return null
	 */
	public function setHeader($key, $val);
}