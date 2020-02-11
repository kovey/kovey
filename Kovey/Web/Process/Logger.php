<?php
/**
 *
 * @description 日志进程
 *
 * @package     Components\Process
 *
 * @time        Tue Sep 24 09:08:57 2019
 *
 * @class       vendor/Kovey\Web/Components/Process/Logger.php
 *
 * @author      kovey
 */
namespace Kovey\Web\Process;

use Kovey\Components\Logger\Logger as Lg;
use Kovey\Config\Manager;
use Kovey\Components\Process\ProcessAbstract;

class Logger extends ProcessAbstract
{
    protected function init()
    {
        $this->processName = Manager::get('app.process.name') . ' core record log';
    }

    protected function busi()
    {
		swoole_event_add($this->process->pipe, function ($pipe) {
			$logger = unserialize($this->process->read());
			if (!is_array($logger)) {
				return;
			}

			switch ($logger['type']) {
			case 'info':
				Lg::writeInfoLog($logger['line'], $logger['file'], $logger['msg']);
				break;
			case 'error':
				Lg::writeErrorLog($logger['line'], $logger['file'], $logger['msg']);
				break;
			case 'warning':
				Lg::writeWarningLog($logger['line'], $logger['file'], $logger['msg']);
				break;
			case 'exception':
				Lg::writeExceptionLog($logger['line'], $logger['file'], $logger['e']);
				break;
			}

		});
    }
}
