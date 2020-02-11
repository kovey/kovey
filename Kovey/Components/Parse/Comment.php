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
	private $ref;

	private $ats;

	public function __construct($class)
	{
		$this->ats = array();
		$this->ref = new \ReflectionClass($class);
	}

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

	public function newInstance(...$args)
	{
		$obj = $this->ref->newInstanceArgs($args);
		$this->parse($obj);

		return $obj;
	}
}
