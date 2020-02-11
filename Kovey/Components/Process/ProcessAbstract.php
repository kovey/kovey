<?php
/**
 *
 * @description 用户自定义进程基类
 *
 * @package     Components\Process
 *
 * @time        Tue Sep 24 09:09:20 2019
 *
 * @class       vendor/Kovey/Components/Process/ProcessAbstract.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Process;
use Kovey\Config\Manager;

abstract class ProcessAbstract
{
    protected $process;

    protected $callBack;

    protected $server;

    protected $workerAtomic;

    protected $processName;

	protected $workNum = 0;

    final public function __construct()
    {
        $this->init();
        $this->process = new \Swoole\Process(array($this, 'callBack'));
    }

	public function setServer(\Swoole\Server $server)
	{
        $this->server = $server;
        $this->server->addProcess($this->process);
	}

	public function setWorkerAtomic(\Swoole\Atomic $workerAtomic)
	{
        $this->workerAtomic = $workerAtomic;
	}

	public function push($data)
	{
		return $this->process->write(serialize($data));
	}

    public function callBack($worker)
    {
        ko_change_process_name($this->processName);

        $this->busi();
    }

    protected function send($path, $method, $params = array())
    {
        $this->server->sendMessage(array(
            'p' => $path,
            'm' => $method,
            'a' => $params
        ), $this->getWorkerId());
    }
    
    protected function getWorkerId()
    {
        $id = $this->workerAtomic->get();
        if ($id >= $this->workNum) {
            $this->workerAtomic->set(0);
            $id = 0;
        }

        $this->workerAtomic->add();
        return $id;
    }

    abstract protected function init();

    abstract protected function busi();
}
