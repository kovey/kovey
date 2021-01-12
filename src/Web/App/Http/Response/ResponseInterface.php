<?php
/**
 *
 * @description 响应接口
 *
 * @package     Response
 *
 * @time        2019-10-17 23:30:36
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Response;

interface ResponseInterface
{
    /**
     * @description 设置状态码
     *
     * @param int $code
     *
     * @return ResponseInterface
     */
    public function status($code);

    /**
     * @description 跳转
     *
     * @param string $url
     *
     * @return ResponseInterface
     */
    public function redirect($url);

    /**
     * @description 设置头信息
     *
     * @param string $key
     *
     * @param string $val
     *
     * @return ResponseInterface
     */
    public function setHeader($key, $value);

    /**
     * @description 设置cookie
     *
     * @param string $name
     *
     * @param string $value
     *
     * @param string $expire
     *
     * @param string $path
     *
     * @param string $domain
     *
     * @param string $secure
     *
     * @param bool $httponly
     *
     * @return ResponseInterface
     */
    public function setCookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null);

    /**
     * @description 添加头
     *
     * @param Array $header
     *
     * @return ResponseInterface
     */
    public function addHeaders(array $header);

    /**
     * @description 获取头信息
     *
     * @param bool $fastcgi
     *
     * @return string
     */
    public function getHeader($fastcgi = false);

    /**
     * @description 是否cache
     *
     * @return null
     */
    public function noCache();

    /**
     * @description 获取BODY
     *
     * @return string
     */
    public function getBody();

    /**
     * @description 获取HEAD
     *
     * @return Array
     */
    public function getHead();

    /**
     * @description 获取COOKIE
     *
     * @return Array
     */
    public function getCookie();

    /**
     * @description 设置BODY
     *
     * @param string $body
     *
     * @return null
     */
    public function setBody($body);

    /**
     * @description 转换成数组
     *
     * @return Array
     */
    public function toArray() : array;

    /**
     * @description 清理BODY
     *
     * @return null
     */
    public function clearBody();

    /**
     * @description 获取状态码Y
     *
     * @return int
     */
    public function getHttpCode();
}