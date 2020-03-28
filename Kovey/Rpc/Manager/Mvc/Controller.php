<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-24 21:11:37
 *
 * @file kovey/Kovey/Rpc/Manager/Mvc/Controller.php
 *
 */
namespace Kovey\Rpc\Manager\Mvc;

class Controller
{
    protected $view;

    protected $viewStatus = false;

    final public function __construct()
    {
        $this->view = new View();
    }

    public function setTemplate($template)
    {
        $this->view->setTemplate($template);
    }

    public function render()
    {
        return $this->view->render();
    }

    public function isDisableView()
    {
        return $this->viewStatus;
    }

    public function disableView()
    {
        $this->viewStatus = true;
    }
}
