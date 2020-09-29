<?php
/**
 * @description 视图
 *
 * @package Kovey\Rpc\Manager\Mvc
 *
 * @author kovey
 *
 * @time 2020-03-24 21:09:19
 *
 * @file kovey/Kovey/Rpc/Manager/Mvc/View.php
 *
 */
namespace Kovey\Rpc\Manager\Mvc;

class View
{
	/**
	 * @description 模板路径
	 *
	 * @var string
	 */
	private $template;

	/**
	 * @description 页面数据
	 *
	 * @var Array
	 */
	private $data;

	/**
	 * @description 构造
	 *
	 * @param ResponseInterface $res
	 *
	 * @param string $template
	 *
	 * @return ViewInterface
	 */
	final public function __construct()
	{
		$this->data = array();
	}

	/**
	 * @description 设置模板
	 *
	 * @param string $template
	 *
	 * @return null
	 */
	public function setTemplate(string $template)
	{
		$this->template = $template;
	}

	/**
	 * @description 设置变量值
	 *
	 * @param string $name
	 *
	 * @param string $val
	 *
	 * @return null
	 */
	public function __set($name, $val)
	{
		$this->data[$name] = $val;
	}

	/**
	 * @description 获取变量值
	 *
	 * @param string $name
	 *
	 * @return stringe
	 */
	public function __get($name)
	{
		return $this->data[$name] ?? '';
	}

	/**
	 * @description 页面渲染
	 *
	 * @return string
	 */
	public function render()
	{
		ob_start();
		ob_implicit_flush(0);
		extract($this->data);
		require($this->template);
		return ob_get_clean();
	}
}
