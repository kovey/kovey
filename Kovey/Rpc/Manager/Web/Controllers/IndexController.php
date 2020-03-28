<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-24 20:45:23
 *
 * @file kovey/Kovey/Rpc/Manager/Web/Controllers/Index.php
 *
 */
namespace Kovey\Rpc\Manager\Web\Controllers;

use Kovey\Rpc\Manager\Mvc\Controller;
use Kovey\Rpc\Manager\Web\Tools\Rf;
use Kovey\Util\Json;
use Kovey\Config\Manager;

class IndexController extends Controller
{
    public function indexAction()
    {
        $service = $this->data['s'] ?? '';
        $this->view->services = $this->getService($service);
    }

    private function getService($service)
    {
        $handler = Manager::get('server.rpc.handler');
        $services = array();
        if (!empty($service)) {
            $class = $handler . '\\' . ucfirst($service);
            $services[$service] = Rf::get($class);
            return $services;
        }

        $files = scandir(APPLICATION_PATH . '/application/' . str_replace('\\', '/', $handler));
        foreach ($files as $file) {
            if (substr($file, -3) !== 'php') {
                continue;
            }

            $service = substr($file, 0, strlen($file) - 4);
            $class = $handler . '\\' . ucfirst($service);
            $info = Rf::get($class);
            $services[$service] = $info;
        }

        return $services;
    }
}
