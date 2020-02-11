<?php
/**
 *
 * @description 创建项目
 *
 * @package     Command\Project
 *
 * @time        2019-12-25 23:50:34
 *
 * @file  /Users/kovey/Documents/php/kovey/bin/command/Command/Project/Create.php
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

	private $types = array(
		'web' => 'Web',
		'rpc' => 'Rpc'
	);

	public function __construct($path, $name, $type)
	{
		$this->path = $path;
		$this->name = $name;
		$this->type = $type;
	}

	public function run()
	{
		if (!isset($this->types[$this->type])) {
			Show::show('ptype is only "web" or "rpc"');
			exit;
		}

		if (file_exists($this->path . '/' . $this->name)) {
			Show::showFormat('project %s is exists', $this->name);
			exit;
		}

		$this->create();
	}

	private function create()
	{
		try {
			$class = '\Command\Project\Create\\' . $this->types[$this->type];
			$obj = new $class($this->path, $this->name);
			$obj->create();
			Show::showFormat('create %s in %s success', $this->name, $this->path);
		} catch (\Throwable $e) {
			Show::show('create ' . $this->name . ' is fail');
		} catch (\Exception $e) {
			Show::show($e->getMessage());
		}
	}
}