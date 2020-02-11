<?php
/**
 *
 * @description 插件接口
 *
 * @package     App\Http\Plugin
 *
 * @time        Tue Sep 24 08:59:02 2019
 *
 * @class       vendor/Kovey\Web/App/Http/Plugin/PluginInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Plugin;
use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;

interface PluginInterface
{
	public function loopShutdown(RequestInterface $request, ResponseInterface $response);
}
