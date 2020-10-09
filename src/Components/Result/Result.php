<?php
/**
 *
 * @description 接口返回值基类
 *
 * @package     Components\Result
 *
 * @time        Tue Sep 24 09:12:05 2019
 *
 * @class       vendor/Kovey/Components/Result/Result.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Result;
use Kovey\Util\Json;
use Kovey\Config\Manager;

class Result
{
	/**
	 * @description 错误码
	 *
	 * @var mixed
	 */
    protected $code;

	/**
	 * @description 错误消息
	 *
	 * @var mixed
	 */
    protected $msg;

	/**
	 * @description 返回数据
	 *
	 * @var mixed
	 */
    protected $data;

	/**
	 * @description 是否开发模式
	 *
	 * @var bool
	 */
	protected $isDev;

	/**
	 * @description 构造结果
	 *
	 * @param mixed $code
	 *
	 * @param mixed $msg
	 *
	 * @param mixed $data
	 *
	 * @return Result
	 */
    public function __construct($code, $msg, $data)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
		$this->isDev = true;
    }

	/**
	 * @description 转为数组
	 *
	 * @return Array
	 */
    protected function toArray()
    {
        return array(
            'code' => $this->code,
            'msg' => $this->isDev ? $this->msg : '',
            'data' => $this->data
        );
    }

	/**
	 * @description 转为JSON
	 *
	 * @return string
	 */
    protected function toJson()
    {
        return Json::encode($this->toArray());
    }
}
