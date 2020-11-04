<?php
use Kovey\Web\App\Http\Plugin\PluginInterface;
use Kovey\Web\App\Mvc\View\Sample;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;

class Layout implements PluginInterface
{
	private $dir;

	private $vars;

	private $file;

	public function __construct($file = 'layout.phtml', $dir = null)
	{
		$this->file = $file;
		$this->dir = empty($dir) ? APPLICATION_PATH . '/application/layouts/' : $dir;
		$this->vars = array();
	}

	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}

	public function __get($name)
	{
		return $this->vars[$name] ?? '';
	}

	public function loopShutdown(RequestInterface $request, ResponseInterface $response)
	{
		$body = $response->getBody();
		$response->clearBody();
		$layout = new Sample($response, $this->dir . $this->file);
		$layout->content = $body;
		$layout->layout = $this->vars;
		$layout->render();
	}
}
