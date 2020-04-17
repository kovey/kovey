<?php
/**
 *
 * @description 定时任务进程
 *
 * @package     Components\Process
 *
 * @time        Tue Sep 24 09:08:23 2019
 *
 * @class       vendor/Kovey\Web/Components/Process/Cron.php
 *
 * @author      kovey
 */
namespace Kovey\Web\Process;
use Kovey\Components\Logger\Logger;
use Kovey\Config\Manager;
use Swoole\Coroutine\System;
use Kovey\Util\Json;
use Kovey\Components\Process\ProcessAbstract;

class Cron extends ProcessAbstract
{
	/**
	 * @description 文件修改时间
	 *
	 * @var int
	 */
    protected $fileTime = 0;

	/**
	 * @description 缓存
	 *
	 * @var Array
	 */
    protected $cache = array();

	/**
	 * @description 任务个数
	 *
	 * @var int
	 */
    protected $cfgCount = 0;

	/**
	 * @description 当前秒数
	 *
	 * @var int
	 */
    protected $seconds;

	/**
	 * @description 初始化
	 *
	 * @return null
	 */
    protected function init()
    {
        $this->processName = Manager::get('app.process.name') . ' core crontab';
    }

	/**
	 * @description 业务处理
	 *
	 * @return null
	 */
    protected function busi()
    {
        while (true) {
            $this->seconds = intval(date('s'));
            $this->parseConfig();
            $this->runCron();

            sleep(Manager::get('server.sleep.crontab'));
        }
    }

	/**
	 * @description 解析crontab文件
	 *
	 * @return null
	 */
    protected function parseConfig()
    {
        $file = Manager::get('app.cron.file');
        if (!is_file($file)) {
            Logger::writeErrorLog(__LINE__, __FILE__, 'cron file: ' . $file . ' not found.');
            return;
        }

        clearstatcache(true, $file);

        $time = filemtime($file);
        if ($time <= $this->fileTime) {
            return;
        }


        $content = file_get_contents($file);
        if (!$content) {
            return;
        }

        $this->fileTime = $time;

        $this->cache = array();
        $info = explode("\n", $content);
        foreach ($info as $line) {
            if (substr($line, 0, 1) === '#') {
                continue;
            }

            $line = str_replace(array(';', '；'), '', trim($line));
            $cfg = explode(' ', $line);
            if (count($cfg) < 7) {
                continue;
            }

            $config = array(
                'sec' => $cfg[0],
                'min' => $cfg[1],
                'hour' => $cfg[2],
                'day' => $cfg[3],
                'mon' => $cfg[4],
                'week' => $cfg[5],
                'shell' => $this->getShell($cfg),
                'nextRunTime' => time(),
                'isLoop' => false,
                'isRunned' => false
            );

            $res = $this->getSleep($config);
            $config['nextRunTime'] = $res['nextRunTime'];
            $config['isLoop'] = $res['isLoop'];
            $config['runTime'] = 0;

            $this->cache[] = $config;
        }

        $this->cfgCount = count($this->cache);
        Logger::writeInfoLog(__LINE__,__FILE__, 'Parse crontab file success, config: ' . json_encode($this->cache, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

	/**
	 * @description 获取执行的shell
	 *
	 * @param Array $cfg
	 *
	 * @return null
	 */
    protected function getShell(Array $cfg)
    {
        if (!isset($cfg[6])) {
            return false;
        }

        $params = array();
        $count = count($cfg);
        for ($i = 6; $i < $count; $i ++) {
            $params[] = $cfg[$i];
        }

        return implode(' ', $params);
    }

	/**
	 * @description 执行shell
	 *
	 * @return null
	 */
    protected function runCron()
    {
        for ($i = 0; $i < $this->cfgCount;  $i ++) {
            if (!$this->cache[$i]['isLoop']) {
                if ($this->cache[$i]['isRunned']
                    && $this->cache[$i]['runTime'] > (time() - 30)
                ) {
                    continue;
                }

                $this->cache[$i]['isRunned'] = false;
                $this->cache[$i]['nextRunTime'] = $this->parseCanRun($this->cache[$i]);
            }

            if ($this->cache[$i]['nextRunTime'] > time()) {
                continue;
            }

			go (function () use ($i) {
				$result = System::exec($this->cache[$i]);
				Logger::writeInfoLog(__LINE__, __FILE__, sprintf('run [%s], result: %s', $this->cache[$i]['shell'], Json::encode($result)));
			});

            $this->cache[$i]['isRunned'] = true;
            $this->cache[$i]['runTime'] = time();
            if ($this->cache[$i]['isLoop']) {
                $this->cache[$i]['nextRunTime'] = $this->getSleep($this->cache[$i])['nextRunTime'];
                continue;
            }

            $this->cache[$i]['nextRunTime'] = time() + 999;
        }
    }

	/**
	 * @description 获取睡眠时间
	 *
	 * @param Array $config
	 *
	 * @return null
	 */
    protected function getSleep(Array $config)
    {
        if (strpos($config['sec'], '/') !== false) {
            return array(
                'nextRunTime' => str_replace('/', '', $config['sec']) + $config['nextRunTime'],
                'isLoop' => true
            );
        }

        if (strpos($config['min'], '/') !== false) {
            return array(
                'nextRunTime' => str_replace('/', '', $config['min']) * 60 + $config['nextRunTime'],
                'isLoop' => true
            );
        }

        if (strpos($config['hour'], '/') !== false) {
            return array(
                'nextRunTime' => str_replace('/', '', $config['hour']) * 3600 + $config['nextRunTime'],
                'isLoop' => true,
            );
        }

        if (strpos($config['day'], '/') !== false) {
            return array(
                'nextRunTime' => str_replace('/', '', $config['day']) * 3600 * 24 + $config['nextRunTime'],
                'isLoop' => true
            );
        }

        return array(
            'nextRunTime' => $this->parseCanRun($config),
            'isLoop' => false
        );
    }

	/**
	 * @description 解析是否能执行
	 *
	 * @param Array $config
	 *
	 * @return null
	 */
    protected function parseCanRun($config)
    {
        if ($config['mon'] !== '*') {
            if ($config['mon'] != intval(date('m'))) {
                return time() + 999;
            }
        }

        if ($config['week'] !== '*') {
            if ($config['week'] != date('w')) {
                return time() + 999;
            }
        }

        if ($config['hour'] !== '*') {
            if ($config['hour'] != intval(date('H'))) {
                return time() + 999;
            }
        }

        if ($config['min'] !== '*') {
            if ($config['min'] != intval(date('i'))) {
                return time() + 999;
            }
        }

        if ($config['sec'] !== '*') {
            if ($config['sec'] > $this->seconds
                || $config['sec'] < ($this->seconds - 20)
            ) {
                return time() + 999;
            }
        }

        return time();
    }
}
