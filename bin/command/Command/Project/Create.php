<?php
/**
 *
 * @description 创建项目
 *
 * @package     Command\Project
 *
 * @time        2019-12-25 23:50:34
 *
 * @author      kovey
 */
namespace Command\Project;
use Command\CommandInterface;
use Util\Show;

class Create implements CommandInterface
{
    private $path;

    private $name;

    private $type;

    private $logdir;

    private $types = array(
        'web' => 'Web',
        'rpc' => 'Rpc',
        'websocket' => 'Websocket',
        'tcp' => 'Tcp'
    );

    public function __construct($path, $name, $type, $logdir)
    {
        $this->path = $path;
        $this->name = $name;
        $this->type = $type;
        $this->logdir = $logdir;
    }

    public function run()
    {
        if (!isset($this->types[$this->type])) {
            Show::show('ptype is only "web" or "rpc" or "websocket"');
            exit;
        }

        if (is_file($this->path . '/' . $this->name . '/index.php')) {
            Show::showFormat('project %s is exists', $this->name);
            exit;
        }

        $this->create();
    }

    private function create()
    {
        try {
            $class = '\Command\Project\Create\\' . $this->types[$this->type];
            $obj = new $class($this->path, $this->name, $this->logdir);
            $obj->create();
            Show::showFormat('create %s in %s success', $this->name, $this->path);
        } catch (\Throwable $e) {
            Show::show('create ' . $this->name . ' is fail');
        } catch (\Exception $e) {
            Show::show($e->getMessage());
        }
    }
}
