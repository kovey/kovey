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
	private $procs;

    public function __construct($workerNum)
    {
        $this->workerAtomic = new \Swoole\Atomic($workerNum);
		$this->procs = array();
    }

	public function addProcess($name, ProcessAbstract $process)
	{
		$process->setWorkerAtomic($this->workerAtomic);
		$this->procs[$name] = $process;
	}

    public function push($name, $data)
	{
		if (!isset($this->procs[$name])) {
			return false;
		}

        return $this->procs[$name]->push($data);
    }
}
