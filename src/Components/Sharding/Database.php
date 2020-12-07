<?php
/**
 *
 * @description 根据ID获取分库名称
 *
 * @package     Components\Sharding
 *
 * @time        Tue Oct  1 00:22:54 2019
 *
 * @author      kovey
 */
namespace Kovey\Components\Sharding;

use Kovey\Util\Util;

class Database
{
    /**
     * @description 最大值
     *
     * @var int
     */
    private $maxCount;

    /**
     * @description 构造函数
     *
     * @return Database
     */
    public function __construct($maxCount = 128)
    {
        $this->maxCount = $maxCount;
    }

    /**
     * @description 获取KEY
     *
     * @param mixed $id
     *
     * @return int
     */
    public function getShardingKey($id)
    {
        if (!Util::isNumber($id)) {
            $id = hexdec(hash('crc32', $id));
        } else {
            $id = intval($id);
        }

        return $id % $this->maxCount;
    }
}
