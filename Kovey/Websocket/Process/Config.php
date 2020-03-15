<?php
/**
 *
 * @description 配置文件检测进程
 *
 * @package     Components\Process
 *
 * @time        Tue Sep 24 09:07:51 2019
 *
 * @class       vendor/Kovey\Websocket/Components/Process/Config.php
 *
 * @author      kovey
 */
namespace Kovey\Websocket\Process;
use Kovey\Components\Logger\Logger;
use Kovey\Config\Manager;
use Kovey\Components\Process\ProcessAbstract;

class Config extends ProcessAbstract
{
	/**
	 * @description 初始化
	 *
	 * @return null
	 */
    protected function init()
    {
        $this->processName = Manager::get('server.websocket.name') . ' config';
    }

	/**
	 * @description 业务逻辑处理
	 *
	 * @return null
	 */
    protected function busi()
    {
        while (true) {
			sleep(Manager::get('server.sleep.config'));
			Manager::parse();
			Logger::writeInfoLog(__LINE__, __FILE__, 'reload config');
        }
    }
}