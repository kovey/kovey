<?php
/**
 *
 * @description 创建web工程
 *
 * @package     
 *
 * @time        2019-12-26 00:03:07
 *
 * @author      kovey
 */
namespace Command\Project\Create;

use Util\Util;

class Web
{
    private $name;

    private $path;

    private $root;

    private $logdir;

    public function __construct($path, $name, $logdir)
    {
        $this->path = $path;
        $this->name = $name;
        $this->root = $this->path . '/' . $this->name;
        $this->logdir = $logdir;
    }

    public function create()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }

        $this->createApplication()
            ->createBin()
            ->createConf()
            ->createPublic()
            ->createService()
            ->createIndex();
    }

    private function createPublic()
    {
        mkdir($this->root . '/public/static', 0755, true);
        return $this;
    }

    private function createService()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/service', $this->root . '/service');
        $app = file_get_contents($this->root . '/service/kovey.service');
        $app = str_replace(array(
            '{pid_file}', 
            '{root}'
        ), array(
            $this->root . '/run/kovey-framework',
            $this->root
        ), $app);
        file_put_contents($this->root . '/service/kovey.service', $app);
        return $this;
    }

    private function createIndex()
    {
        copy(KOVEY_TOOLS_BIN . '/template/web/index.php', $this->root . '/index.php');
        return $this;
    }

    private function createApplication()
    {
        $app = $this->root . '/application';
        Util::copy(KOVEY_TOOLS_BIN . '/template/controllers', $app . '/controllers');
        Util::copy(KOVEY_TOOLS_BIN . '/template/layouts', $app . '/layouts');
        mkdir($app . '/library', 0755, true);
        mkdir($app . '/routers', 0755, true);

        Util::copy(KOVEY_TOOLS_BIN . '/template/plugins', $app . '/plugins');
        Util::copy(KOVEY_TOOLS_BIN . '/template/views', $app . '/views');

        copy(KOVEY_TOOLS_BIN . '/template/web/Bootstrap.php', $app . '/Bootstrap.php');
 
        return $this;
    }

    private function createBin()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/bin', $this->root . '/bin');
        chmod($this->root . '/bin/kovey', 0755);
        return $this;
    }

    private function createConf()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/web/conf', $this->root . '/conf');

        mkdir($this->root . '/run', 0755, true);
        if (empty($this->logdir)) {
            $this->logdir = $this->root . '/logs';
        } else if (!is_dir($this->logdir)) {
            mkdir($this->logdir, 0755, true);
        }

        $core = file_get_contents($this->root . '/conf/server.ini');
        $core = str_replace(array(
            '{pid_file}', 
            '{logger_info}', 
            '{logger_error}', 
            '{logger_warning}', 
            '{logger_exception}', 
            '{session_dir}',
            '{db}',
            '{monitor}',
            '{busi_exception}',
            '{project}',
            '{log_file}'
        ), array(
            $this->root . '/run/kovey-framework',
            $this->logdir . '/info',
            $this->logdir . '/error',
            $this->logdir . '/warning',
            $this->logdir . '/exception',
            $this->logdir . '/session',
            $this->logdir . '/db',
            $this->logdir . '/monitor',
            $this->logdir . '/busi-exception',
            $this->name,
            $this->logdir . '/server/server.log'
        ), $core);
        file_put_contents($this->root . '/conf/server.ini', $core);
        return $this;
    }
}
