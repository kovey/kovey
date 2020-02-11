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
 * @file test/Test/Parse/Container.php
 *
 */
namespace Test\Parse;

class Container
{
    private $instances;

    public function __construct()
    {
        $this->instances = array();
    }

    public function get(string $class)
    {
        $class = new \ReflectionClass($class);

        if (isset($this->instances[$class->getName()])) {
            return $this->bind($class, $this->instances[$class->getName()]);
        }

        $this->resolve($class);
        return $this->bind($class, $this->instances[$class->getName()]);
    }

    private function bind(\ReflectionClass $class, Array $dependencies)
    {
        $obj = $class->newInstance();
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
            $info = $property->getDocComment();
            if (!preg_match('/@(.*)\n/', $info, $match)) {
                continue;
            }

            $property->setAccessible(true);

            $ats[$property->getName()] = array(
                'class' => new \ReflectionClass($match[1]),
                'property' => $property
            );
        }

        return $ats;
    }
}
