<?php
/**
 * @description 依赖注入容器
 *
 * @package Parse
 *
 * @author kovey
 *
 * @time 2019-10-16 10:36:36
 *
 * @file Kovey/Components/Parse/Container.php
 *
 */
namespace Kovey\Components\Parse;

class Container implements ContainerInterface
{
    private $instances;

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
		'@description',
		'@package',
		'@author',
		'@var',
		'@param',
		'@return',
		'@throws',
		'@todo',
		'@name',
	);

    public function __construct()
    {
        $this->instances = array();
    }

    public function get(string $class, ...$args)
    {
        $class = new \ReflectionClass($class);

        if (isset($this->instances[$class->getName()])) {
            return $this->bind($class, $this->instances[$class->getName()], $args);
        }

        $this->resolve($class);
        return $this->bind($class, $this->instances[$class->getName()], $args);
    }

    private function bind(\ReflectionClass $class, Array $dependencies, Array $args = array())
    {
		$obj = null;
		if (count($args) > 0) {
			$obj = $class->newInstanceArgs($args);
		} else {
			$obj = $class->newInstance();
		}
        if (count($dependencies) < 1) {
            return $obj;
        }

        foreach ($dependencies as $dependency) {
            $dep = $this->bind($dependency['class'], $this->instances[$dependency['class']->getName()] ?? array());
            $dependency['property']->setValue($obj, $dep);
        }

        return $obj;
    }

    private function resolve(\ReflectionClass $class)
    {
        $this->instances[$class->getName()] = $this->getAts($class);
        foreach ($this->instances[$class->getName()] as $deps) {
            $this->resolve($deps['class']);
        }
    }

    private function getAts(\ReflectionClass $ref)
    {
        $properties = $ref->getProperties();
        $ats = array();
        foreach ($properties as $property) {
			$comment = $property->getDocComment();
			if (empty($comment)) {
				continue;
			}

			$lines = explode("\n", $comment);
			foreach ($lines as $line) {
				if ($this->isExcludes($line)) {
					continue;
				}

				if (!preg_match('/@(.*)/', $line, $match)) {
					continue;
				}

				if (count($match) !== 2) {
					continue;
				}

				$class = trim($match[1]);
				if (empty($class)) {
					continue;
				}

				$pro = new \ReflectionClass($class);
				if ($property->isPrivate()
					|| $property->isProtected()
				) {
					$property->setAccessible(true);
				}

				$ats[$property->getName()] = array(
					'class' => new \ReflectionClass($class),
					'property' => $property
				);
			}
        }

        return $ats;
    }

	private function isExcludes($line)
	{
		$line = strtolower($line);

		foreach (self::$excludes as $exclude) {
			if (strpos($line, $exclude) !== false) {
				return true;
			}
		}

		return false;
	}
}
