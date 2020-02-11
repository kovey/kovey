<?php
/**
 *
 * @description 系统监控
 *
 * @package     Kovey\Components\Process
 *
 * @time        2020-01-19 14:52:47
 *
 * @file  /Users/kovey/Documents/php/kovey/Kovey/Components/Process/Monitor.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Process;

use Kovey\Config\Manager;
use Kovey\Rpc\Client\Client;
use Kovey\Components\Logger\Logger;
use Kovey\Util\Json;

class Monitor extends ProcessAbstract
{
    protected function init()
    {
        $this->processName = 'kovey framework cluster monitor';
    }

    protected function busi()
    {
		swoole_event_add($this->process->pipe, function ($pipe) {
			$logger = unserialize($this->process->read());
			if (!is_array($logger)) {
				return;
			}

			$this->sendToMonitor(Json::encode($logger));
		});
    }

	protected function sendToMonitor($buffer)
	{
		go(function () use ($buffer) {
			$cli = new Client(Manager::get('rpc.monitor'));
			if (!$cli->connect()) {
				Logger::writeWarningLog(__LINE__, __FILE__, $cli->getError());
				return;
			}

			if (!$cli->send(array(
				'p' => 'Monitor',
				'm' => 'save',
				'a' => array($buffer)
			))) {
				$cli->close();
				Logger::writeWarningLog(__LINE__, __FILE__, $cli->getError());
				return;
			}

			$result = $cli->recv();
			$cli->close();

			if (empty($result)) {
				Logger::writeWarningLog(__LINE__, __FILE__, 'response error');
				return;
			}

			if ($result['code'] > 0) {
				if ($result['type'] != 'success') {
					Logger::writeWarningLog(__LINE__, __FILE__, $result['err']);
				}
			}

			if (!$result['result']) {
				Logger::writeWarningLog(__LINE__, __FILE__, 'save fail, logger: ' . $buffer);
			}
		});
	}
}
