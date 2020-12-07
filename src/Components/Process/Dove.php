<?php
/**
 * @description 配置下发
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-04-20 18:51:37
 *
 */
namespace Kovey\Components\Process;

use Kovey\Config\Manager;
use Kovey\Rpc\Client\Client;
use Kovey\Components\Logger\Logger;
use Kovey\Util\Json;
use Swoole\Coroutine\System;
use Swoole\Timer;

class Dove extends ProcessAbstract
{
    const PROCESS_NAME = 'dove';

    private $confTemplates = array();

    private $confPath;

    /**
     * @description 初始化
     *
     * @return null
     */
    protected function init()
    {
        $this->processName = 'kovey framework dove';
        $this->confPath = APPLICATION_PATH . '/conf';

        $files = scandir($this->confPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || strtolower(substr($file, -6)) !== 'schema') {
                continue;
            }

            $this->confTemplates[substr($file, 0, -7)] = $this->confPath . '/' . $file;
        }
    }

    /**
     * @description 业务处理
     *
     * @return null
     */
    protected function busi()
    {
        $this->listen(function ($pipe) {});

        Timer::tick(30000, function () {
            $this->getConfigFromCenter();
        });

        $this->getConfigFromCenter();
    }

    /**
     * @description 从配置中心拉取配置
     *
     * @return null
     */
    protected function getConfigFromCenter()
    {
        foreach ($this->confTemplates as $file => $template) {
            $this->parseConfig($file, $template);
        }
    }

    /**
     * @description 配置解析，写入配置文件
     *
     * @param string $file
     *
     * @param string $template
     */
    protected function parseConfig($file, $filePath)
    {
        go(function ($file, $filePath) {
            $template = System::readFile($filePath);
            preg_match_all('/#{(.*)}/', $template, $matches);
            if (count($matches) != 2) {
                return;
            }

            $cli = new Client(Manager::get('dove.config'));
            if (!$cli->connect()) {
                Logger::writeWarningLog(__LINE__, __FILE__, $cli->getError());
                return;
            }

            if (!$cli->send(array(
                'p' => 'Config',
                'm' => 'get',
                'a' => array($matches[1])
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

            if (empty($result['result'])) {
                return;
            }

            foreach ($result['result'] as $field => $val) {
                $template = str_replace('#{' . $field . '}', $val, $template);
            }

            System::writeFile($this->confPath . '/' . $file, $template);
        }, $file, $filePath);
    }
}
