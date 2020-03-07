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
	/**
	 * @description 依赖缓存
	 *
	 * @var Array
	 */
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
	 * @category
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
		'category' => 1
	);

	/**
	 * @description 构造函数
	 */
    public function __construct()
    {
        $this->instances = array();
    }

	/**
	 * @description 根据类名获取实例
	 *
	 * @param string $class
	 *
	 * @param ...mixed $args
	 *
	 * @return mixed
	 *
	 * @throws Throwable
	 */
    public function get(string $class, ...$args)
    {
        $class = new \ReflectionClass($class);

        if (isset($this->instances[$class->getName()])) {
            return $this->bind($class, $this->instances[$class->getName()], $args);
        }

        $this->resolve($class);
        return $this->bind($class, $this->instances[$class->getName()], $args);
    }

	/**
	 * @description 绑定依赖
	 *
	 * @param ReflectionClass $class
	 *
	 * @param Array $dependencies
	 *
	 * @param Array $args
	 *
	 * @return mixed
	 */
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

	/**
	 * @description 缓存依赖
	 *
	 * @param ReflectionClass $class
	 *
	 * @return null
	 */
    private function resolve(\ReflectionClass $class)
    {
        $this->instances[$class->getName()] = $this->getAts($class);
        foreach ($this->instances[$class->getName()] as $deps) {
            $this->resolve($deps['class']);
        }
    }

	/**
	 * @description 获取所有依赖
	 *
	 * @param ReflectionClass $ref
	 *
	 * @return Array
	 */
    private function getAts(\ReflectionClass $ref)
    {
        $properties = $ref->getProperties();
        $ats = array();
        foreach ($properties as $property) {
			$comment = $property->getDocComment();
			if (empty($comment)) {
				continue;
			}

			if (!preg_match_all('/(?=@(.*)\n)/', $comment, $matches)) {
				continue;
			}

			if (count($matches) != 2
				|| empty($matches[1])
			) {
				continue;
			}

			foreach ($matches[1] as $match) {
				$class = trim($match);
				if (empty($class)) {
					continue;
				}
				if ($this->isExcludes($class)) {
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

				break;
			}
        }

        return $ats;
    }

	/**
	 * @description 判断是否是关键字
	 *
	 * @return bool
	 */
	private function isExcludes($class)
	{
		$info = explode(' ', $class);
		return isset(self::$excludes[strtolower($info[0])]);
	}
}
