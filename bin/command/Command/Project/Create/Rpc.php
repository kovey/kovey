<?php
/**
 *
 * @description 
 *
 * @package     
 *
 * @time        2020-01-18 21:28:26
 *
 * @author      kovey
 */
namespace Command\Project\Create;

use Util\Util;

class Rpc
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
            ->createService()
            ->createIndex();
    }

    private function createService()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/service', $this->root . '/service');
        $app = file_get_contents($this->root . '/service/kovey.service');
        $app = str_replace(array(
            '{pid_file}', 
            '{root}'
        ), array(
            $this->root . '/run/kovey-rpc',
            $this->root
        ), $app);
        file_put_contents($this->root . '/service/kovey.service', $app);
        return $this;
    }

    private function createIndex()
    {
        copy(KOVEY_TOOLS_BIN . '/template/rpc/index.php', $this->root . '/index.php');
        return $this;
    }

    private function createApplication()
    {
        $app = $this->root . '/application';
        Util::copy(KOVEY_TOOLS_BIN . '/template/Handler', $app . '/Handler');
        mkdir($app . '/library', 0755, true);

        copy(KOVEY_TOOLS_BIN . '/template/rpc/Bootstrap.php', $app . '/Bootstrap.php');
 
        return $this;
    }

    private function createBin()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/rpc/bin', $this->root . '/bin');
        chmod($this->root . '/bin/kovey', 0755);
        return $this;
    }

    private function createConf()
    {
        Util::copy(KOVEY_TOOLS_BIN . '/template/rpc/conf', $this->root . '/conf');

        mkdir($this->root . '/run', 0755, true);

        if (empty($this->logdir)) {
            $this->logdir = $this->root . '/logs';
        } else if (!is_dir($this->logdir)) {
            mkdir($this->logdir, 0755, true);
        }

        $core = file_get_contents($this->root . '/conf/server.ini');
        $core = str_replace(array(
            '{log_file}',
            '{pid_file}',
            '{name}',    
            '{info}', 
            '{exception}', 
            '{error}', 
            '{warning}', 
            '{db}',
            '{monitor}',
            '{busi_exception}',
            '{rpc-name}'
        ), array(
            $this->logdir . '/server/server.log',
            $this->root . '/run/kovey-rpc',
            $this->name,
            $this->logdir . '/info',
            $this->logdir . '/exception',
            $this->logdir . '/error',
            $this->logdir . '/warning',
            $this->logdir . '/db',
            $this->logdir . '/monitor',
            $this->logdir . '/busi-exception',
            $this->name
        ), $core);
        file_put_contents($this->root . '/conf/server.ini', $core);
        return $this;
    }
}
