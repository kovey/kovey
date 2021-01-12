<?php
/**
 *
 * @description 简单的视图类
 *
 * @package     App\Mvc
 *
 * @time        Tue Sep 24 08:55:24 2019
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Mvc\View;

use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Web\App\Mvc\View\ViewInterface;

class Sample implements ViewInterface
{
    /**
     * @description 模板路径
     *
     * @var string
     */
    private $template;

    /**
     * @description 响应对象
     *
     * @var ResponseInterface
     */
    private $res;

    /**
     * @description 页面数据
     *
     * @var Array
     */
    private $data;

    /**
     * @description 构造
     *
     * @param ResponseInterface $res
     *
     * @param string $template
     *
     * @return ViewInterface
     */
    final public function __construct(ResponseInterface $res, string $template)
    {
        $this->res = $res;
        $this->template = $template;
        $this->data = array();
    }

    /**
     * @description 设置模板
     *
     * @param string $template
     *
     * @return null
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @description 设置变量值
     *
     * @param string $name
     *
     * @param string $val
     *
     * @return null
     */
    public function __set($name, $val)
    {
        $this->data[$name] = $val;
    }

    /**
     * @description 获取变量值
     *
     * @param string $name
     *
     * @return stringe
     */
    public function __get($name)
    {
        return $this->data[$name] ?? '';
    }

    /**
     * @description 页面渲染
     *
     * @return null
     */
    public function render()
    {
        ob_start();
        ob_implicit_flush(0);
        extract($this->data);
        require($this->template);
        $content = ob_get_clean();
        $this->res->setBody($content);
    }

    /**
     * @description 获取响应对象
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->res;
    }
}