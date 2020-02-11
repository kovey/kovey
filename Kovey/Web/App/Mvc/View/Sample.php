<?php
/**
 *
 * @description 简单的视图类
 *
 * @package     App\Mvc
 *
 * @time        Tue Sep 24 08:55:24 2019
 *
 * @class       vendor/Kovey\Web/App/Mvc/View.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Mvc\View;

use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Web\App\Mvc\View\ViewInterface;

class Sample implements ViewInterface
{
	private $template;

	private $res;

	private $data;

	final public function __construct(ResponseInterface $res, string $template)
	{
		$this->res = $res;
		$this->template = $template;
		$this->data = array();
	}

	public function setTemplate($template)
	{
		$this->template = $template;
	}

	public function __set($name, $val)
	{
		$this->data[$name] = $val;
	}

	public function __get($name)
	{
		return $this->data[$name] ?? '';
	}

	public function render()
	{
		ob_start();
		ob_implicit_flush(0);
		extract($this->data);
		require($this->template);
		$content = ob_get_clean();
		$this->res->setBody($content);
	}

	public function getResponse()
	{
		return $this->res;
	}	
}
