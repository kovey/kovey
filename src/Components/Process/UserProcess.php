<?php
/**
 *
 * @description 自定义进程管理,托管到swoole的process manager
 *
 * @package     Server
 *
 * @time        Tue Sep 24 08:53:06 2019
 *
 * @class       vendor/Kovey/Server/UserProcess.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Process;

class UserProcess
{
	/**
	 * @description 所有的自定义进程
	 *
	 * @var Array
	 */
	private $procs;

	/**
	 * @description 构造函数
	 *
	 * @param int $workerNum;
	 */
    public function __construct($workerNum)
    {
        $this->workerAtomic = new \Swoole\Atomic($workerNum);
		$this->procs = array();
    }

	/**
	 * @description 添加用户自定的进程
	 *
	 * @param string $name
	 *
	 * @param ProcessAbstract $process
	 *
	 * @return null
	 */
	public function addProcess($name, ProcessAbstract $process)
	{
		$process->setWorkerAtomic($this->workerAtomic);
		$this->procs[$name] = $process;
	}

	/**
	 * @description 向指定的进程管道写入数据
	 *
	 * @param string $name
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 */
    public function push($name, $data)
	{
		if (!isset($this->procs[$name])) {
			return false;
		}

        return $this->procs[$name]->push($data);
    }
}