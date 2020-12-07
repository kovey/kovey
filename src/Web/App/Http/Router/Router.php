<?php
/**
 *
 * @description 路由对象
 *
 * @package     Router
 *
 * @time        2019-10-19 21:34:55
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

use Kovey\Components\Middleware\MiddlewareInterface;

class Router implements RouterInterface
{
    /**
     * @description URI
     *
     * @var string
     */
    private $uri;

    /**
     * @description 中间件
     *
     * @var Array
     */
    private $middlewares;

    /**
     * @description Action
     *
     * @var string
     */
    private $action;

    /**
     * @description controller
     *
     * @var string
     */
    private $controller;

    /**
     * @description 雷鸣路径
     *
     * @var string
     */
    private $classPath;

    /**
     * @description 是否有效
     *
     * @var bool
     */
    private $isValid;

    /**
     * @description 雷命
     *
     * @var string
     */
    private $className;

    /**
     * @description 页面路径
     *
     * @var string
     */
    private $viewPath;

    /**
     * @description action 名称
     *
     * @var string
     */
    private $actionName;

    /**
     * @description 回调
     *
     * @var string
     */
    private $callable;

    /**
     * @description 构造
     *
     * @param string $uri
     *
     * @param callable $fun
     *
     * @return Router
     */
    public function __construct(string $uri, $fun = null)
    {
        $this->uri = str_replace('//', '/', $uri);
        $this->middlewares = array();
        $this->classPath = '';
        $this->isValid = true;
        if (is_callable($fun)) {
            $this->callable = $fun;
            return;
        }

        if (!empty($fun)) {
            $info = explode('@', $fun);
            if (count($info) != 2) {
                $this->isValid = false;
                return;
            }

            $this->uri = '/' . $info[1] . '/' . $info[0];
        }

        $this->callable = null;

        $this->parseRoute();
        $this->className = str_replace('/', '\\', $this->classPath) . '\\' . ucfirst($this->controller) . 'Controller';
        $this->viewPath = strtolower($this->classPath) . '/' . strtolower($this->controller) . '/' . strtolower($this->action);
        $this->classPath = $this->classPath . '/' . ucfirst($this->controller) . '.php';
        $this->actionName = $this->action . 'Action';
    }

    /**
     * @description 路由解析
     *
     * @return null
     */
    private function parseRoute()
    {
        if ($this->uri === '/') {
            $this->controller = 'index';
            $this->action = 'index';
            return;
        }

        if (!$this->isUri($this->uri)) {
            $this->isValid = false;
            return;
        }

        $info = explode('/', $this->uri);
        $count = count($info);
        if ($count < 2) {
            $this->controller = 'index';
            $this->action = 'index';
            return;
        }

        if ($count == 2) {
            if (empty($info[1])) {
                $this->controller = 'index';
            } else {
                $this->controller = $info[1];
            }

            $this->action = 'index';

            return;
        }

        if ($count == 3) {
            $this->controller = $info[1];
            $this->action = $info[2];
            return;
        }

        $this->classPath = '/' . ucfirst($info[1]);
        $this->controller = $info[2];
        $this->action = $info[3];
    }

    /**
     * @description 获取action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @description 获取控制器
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @description 获取类路径
     *
     * @return string
     */
    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * @description 添加中间件
     *
     * @param MiddlewareInterface $middleware
     *
     * @return Router
     */
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * @description 获取中间件
     *
     * @return Array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @description URI是否合法
     *
     * @param string $uri
     *
     * @return bool
     */
    private function isUri($uri)
    {
        return (bool)preg_match('/^\/[a-zA-Z]+(\/[a-zA-Z][a-zA-Z0-9]+){0,3}(\/.+){0,1}$/', $uri);
    }

    /**
     * @description 是否有效
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @description 获取类名
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @description 获取action名称
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @description 获取页面路径
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->viewPath;
    }

    /**
     * @description 获取回调
     *
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @description 获取URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
