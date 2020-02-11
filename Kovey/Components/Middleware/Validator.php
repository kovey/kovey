<?php
/**
 *
 * @description 数据验证中间件
 *
 * @package     Middleware
 *
 * @time        2019-11-06 23:48:01
 *
 * @file  admin/vendor/Kovey\Web/Components/Middleware/Validator.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Middleware;

use Kovey\Web\App\Http\Request\RequestInterface;
use Kovey\Web\App\Http\Response\ResponseInterface;
use Kovey\Util\Validator as Valid;
use Kovey\Util\Json;

class Validator implements MiddlewareInterface
{
	private $rules = array();

	public function setRules(Array $rules)
	{
		$this->rules = $rules;
		return $this;
	}

	public function handle(RequestInterface $req, ResponseInterface $res, callable $next)
	{
		if (empty($this->rules)) {
			return $next($req, $res);
		}

		$data = null;
		switch (strtolower($req->getMethod())) {
			case 'get':
				$data = $req->getQuery();
				break;
			case 'post':
				$data = $req->getPost();
				break;
			case 'put':
				$data = $req->getPut();
				break;
			case 'delete':
				$data = $req->getDelete();
				break;
			default:
				$data = array();
		}

		$valid = new Valid($data, $this->rules);
		if (!$valid->run()) {
			$res->status(200);
			$res->setHeader('content-type', 'application/json');
			$res->setBody(Json::encode(array(
				'code' => 1000,
				'msg' => implode(';', $valid->getErros())
			)));
			return $res;
		}

		return $next($req, $res);
	}
}
