<?php
/**
 * @description 反射类
 *
 * @package Kovey\Rpc\Manager\Web\Tools
 *
 * @author kovey
 *
 * @time 2020-03-24 21:59:53
 *
 * @file kovey/Kovey/Rpc/Manager/Web/Tools/Rf.php
 *
 */
namespace Kovey\Rpc\Manager\Web\Tools;

class Rf
{
    /**
     * @description 获取接口信息和注视
     *
     * @param mixed $class
     *
     * @return Array
     */
    public static function get($class)
    {
        $rf = new \ReflectionClass($class);
        $methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
        if (empty($methods)) {
            return array();
        }

        $result = array();
        foreach ($methods as $method) {
            if ($method->getName() === '__construct'
                || $method->getName() === '__destruct'
            ) {
                continue;
            }

            $info = array(
                'doc' => $method->getDocComment()
            );

            $params = $method->getParameters();
            $info['args'] = array();
            if (!empty($params)) {
                foreach ($params as $param) {
                    preg_match('/>(.*)]/', $param->__toString(), $match);
                    $p = trim($match[1]);
                    $ainfo = explode(' ', $p);
                    $len = count($ainfo);
                    if ($len > 1) {
                        $info['args'][$param->getPosition()] = array(
                            'type' => $ainfo[0],
                            'param' => $ainfo[$len - 1]
                        );
                        continue;
                    }

                    $info['args'][$param->getPosition()] = array(
                        'type' => '',
                        'param' => $p
                    );
                }

                ksort($info['args']);
            }

            $result[$method->getName()] = $info;
        }

        ksort($result); 
        return $result;
    }
}
