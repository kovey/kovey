<?php
/**
 *
 * @description 路由接口
 *
 * @package     
 *
 * @time        2019-10-19 22:09:09
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Router;

use Kovey\Components\Middleware\MiddlewareInterface;

interface RouterInterface
{
    /**
     * @description 构造
     *
     * @param string $uri
     *
     * @param callable $fun
     *
     * @return Router
     */
    public function __construct(string $uri, $fun = null);

    /**
     * @description 获取action
     *
     * @return string
     */
    public function getAction();

    /**
     * @description 获取控制器
     *
     * @return string
     */
    public function getController();

    /**
     * @description 获取类路径
     *
     * @return string
     */
    public function getClassPath();

    /**
     * @description 添加中间件
     *
     * @param MiddlewareInterface $middleware
     *
     * @return Router
     */
    public function addMiddleware(MiddlewareInterface $middleware);

    /**
     * @description 获取中间件
     *
     * @return Array
     */
    public function getMiddlewares();

    /**
     * @description 是否有效
     *
     * @return bool
     */
    public function isValid();

    /**
     * @description 获取类名
     *
     * @return string
     */
    public function getClassName();

    /**
     * @description 获取action名称
     *
     * @return string
     */
    public function getActionName();

    /**
     * @description 获取页面路径
     *
     * @return string
     */
    public function getViewPath();

    /**
     * @description 获取回调
     *
     * @return callable
     */
    public function getCallable();

    /**
     * @description 获取URI
     *
     * @return string
     */
    public function getUri();
}    
