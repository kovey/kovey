<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        Mon Sep 30 09:54:42 2019
 *
 * @class-file  Kovey\Web/App/Mvc/ViewInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Mvc\View;
use Kovey\Web\App\Http\Response\ResponseInterface;

interface ViewInterface
{
	public function __construct(ResponseInterface $res, string $template);

	public function setTemplate($template);

	public function render();

	public function getResponse();
}
