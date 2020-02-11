<?php
/**
 * @description 解析标签
 *
 * @package Test\Parse
 *
 * @author kovey
 *
 * @time 2019-10-15 20:16:38
 *
 * @file test/Test/Parse/Comment.php
 *
 */
namespace Test\Parse;

class Comment
{
    public static function getAts(\ReflectionClass $ref)
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
