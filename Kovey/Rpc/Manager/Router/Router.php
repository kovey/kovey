<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-24 20:58:19
 *
 * @file kovey/Kovey/Rpc/Manager/Router/Router.php
 *
 */
namespace Kovey\Rpc\Manager\Router;

class Router
{
    private $controller;

    private $action;

    private $rootLib = 'Kovey\Rpc\Manager\Web\Controllers\\';

    private $template;

    public function __construct($path)
    {
        if ($path === '/') {
            $this->controller = $this->rootLib . 'IndexController';
            $this->action = 'indexAction';
            $this->template = KOVEY_RPC_ROOT . '/Kovey/Rpc/Manager/Web/Views/Index/Index.phtml';
            return;
        }

        $info = explode('/', $path);
        if (count($info) > 1) {
            $this->controller = $this->rootLib . ucfirst($info[1]) . 'Controller';
        }

        if (count($info) > 2) {
            $this->action = $info[2] . 'Action';
            $this->template = KOVEY_RPC_ROOT . '/Kovey/Rpc/Manager/Web/Views/' . ucfirst($info[1]) . '/' . ucfirst($info[2]) . '.phtml';
        } else {
            $this->action = 'indexAction';
            $this->template = KOVEY_RPC_ROOT . '/Kovey/Rpc/Manager/Web/Views/' . ucfirst($info[1]) . '/Index.phtml';
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getTemplate()
    {
        return $this->template;
    }
}
