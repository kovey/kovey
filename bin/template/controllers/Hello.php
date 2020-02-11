<?php
use Kovey\Web\App\Mvc\Controller\Controller;

class HelloController extends Controller
{
	public function worldAction()
	{
		$this->plugins['Layout']->title = 'Kovey Framwork';
		$this->view->name = 'Hello world!';
	}
}
