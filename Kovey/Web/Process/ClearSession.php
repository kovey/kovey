<?php
/**
 *
 * @description 清理session
 *
 * @package     Process
 *
 * @time        2019-10-13 01:19:20
 *
 * @file  /Users/kovey/Documents/php/kovey/vendor/Kovey\Web/Components/Process/ClearSession.php
 *
 * @author      kovey
 */
namespace Kovey\Web\Process;

use Kovey\Components\Logger\Logger;
use Kovey\Config\Manager;
use Kovey\Components\Process\ProcessAbstract;

class ClearSession extends ProcessAbstract
{
    protected function init()
    {
        $this->processName = Manager::get('app.process.name') . ' core clear session';
    }

    protected function busi()
    {
        while (true) {
			sleep(Manager::get('server.sleep.session'));
			$sessionPath = Manager::get('server.session.dir');
			foreach (scandir($sessionPath) as $path) {
				if ($path == '.' || $path == '..') {
					continue;
				}

				$file = $sessionPath . '/' . $path;
				clearstatcache(true, $file);

				$time = filemtime($file) + intval(Manager::get('server.session.expire'));
				if ($time > time()) {
					continue;
				}

				unlink($sessionPath . '/' . $path);
			}

			Logger::writeInfoLog(__LINE__, __FILE__, 'clear session expired');
        }
    }
}
