<?php
/**
 *
 * @description 解析标记
 *
 * @package     Comment\Parse
 *
 * @time        2019-10-15 23:30:18
 *
 * @file  vendor/Kovey/Components/Parse/Comment.php
 *
 * @author      kovey
 */
namespace Kovey\Components\Parse;

class Comment
{
	/**
	 * @description 反射类
	 *
	 * @var ReflectionClass
	 */
	private $ref;

	/**
	 * @description 参数
	 *
	 * @var array
	 */
	private $ats;

	/**
	 * @description 需要过滤的关键字，这些关键字不初始化
	 * @package
	 * @author
	 * @var
	 * @param
	 * @return
	 * @throws
	 * @todo
	 * @name
	 */
	private static $excludes = array(
		'description' => 1,
		'package' => 1,
		'author' => 1,
		'var' => 1,
		'param' => 1,
		'return' => 1,
		'throws' => 1,
		'todo' => 1,
		'name' => 1,
	);

	public function __construct($class)
	{
		$this->ats = array();
		$this->ref = new \ReflectionClass($class);
	}

	/**
	 * @description 解析注解
	 *
	 * @param Object $obj
	 *
	 * @return null
	 */
	private function parse($obj)
	{
		$properties = $this->ref->getProperties();
		foreach ($properties as $property) {
			$comment = $property->getDocComment();
			if (empty($comment)) {
				continue;
			}

			$lines = explode("\n", $comment);
			foreach ($lines as $line) {
				if (!preg_match('/@(.*)/', $line, $match)) {
					continue;
				}

				if (count($match) !== 2) {
					continue;
				}

				$class = trim($match[1]);
				if (empty($class)
					|| isset(self::$excludes[strtolower($class)])
				) {
					continue;
				}

				$pro = new \ReflectionClass($match[1]);
				if ($property->isPrivate()
					|| $property->isProtected()
				) {
					$property->setAccessible(true);
				}

				$property->setValue($obj, $pro->newInstance());
			}
		}
	}

	/**
	 * @description 实例化对象
	 *
	 * @param ...mixed
	 *
	 * @return mixed
	 */
	public function newInstance(...$args)
	{
		$obj = $this->ref->newInstanceArgs($args);
		$this->parse($obj);

		return $obj;
	}
}
