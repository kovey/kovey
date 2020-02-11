<?php
/**
 *
 * @description 响应接口
 *
 * @package     Response
 *
 * @time        2019-10-17 23:30:36
 *
 * @file  vendor/Kovey\Web/App/Http/Response/ResponseInterface.php
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Response;

interface ResponseInterface
{
    public function status($code);

	public function redirect($url);

    public function setHeader($key, $value);

    public function setCookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null, $httponly = null);

    public function addHeaders(array $header);

    public function getHeader($fastcgi = false);

    public function noCache();

	public function getBody();

	public function getHead();

	public function getCookie();

	public function setBody($body);

	public function toArray() : array;

	public function clearBody();

	public function getHttpCode();
}
