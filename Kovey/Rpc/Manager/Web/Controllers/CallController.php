<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-03-24 21:25:42
 *
 * @file kovey/Kovey/Rpc/Manager/Web/Controllers/Call.php
 *
 */
namespace Kovey\Rpc\Manager\Web\Controllers;

use Kovey\Rpc\Manager\Mvc\Controller;
use Kovey\Rpc\Manager\Web\Tools\Code;
use Kovey\Util\Json;

class CallController extends Controller
{
    public function serviceAction($app)
    {
        $this->disableView();

        $service = $this->data['service'] ?? '';
        $method = $this->data['method'] ?? '';
        $args = $this->data['args'] ?? array();

        if (empty($service) || empty($method)) {
            return 'service or method is empty.';
        }

        $obj = $app->getContainer()->get('Handler\\' . $service);
        $params = array();
        foreach ($args as $arg) {
            if ($arg['type'] != 'array') {
                $params[] = $arg['value'];
                continue;
            }

            $params[] = Json::decode($arg['value']);
        }

        return Code::dump($obj->$method(...$params));
    }

    public function indexAction()
    {
        $this->view->args = empty($this->data['a']) ? array() : Json::decode($this->data['a']);
        $this->view->service = $this->data['s'] ?? '';
        $this->view->method = $this->data['m'] ?? '';
        $this->view->argsType = array(
            'other' => 'other',
            'boolean' => 'boolean',
            'array' => 'array'
        );
    }
}