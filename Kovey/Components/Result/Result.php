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
    protected $code;

    protected $msg;

    protected $data;

	protected $isDev;

    public function __construct($code, $msg, $data)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
		$this->isDev = true;
    }

    protected function toArray()
    {
        return array(
            'code' => $this->code,
            'msg' => $this->isDev ? $this->msg : '',
            'data' => $this->data
        );
    }

    protected function toJson()
    {
        return Json::encode($this->toArray());
    }
}
