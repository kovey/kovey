<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-09-16 15:50:13
 *
 */
namespace Kovey\Components\Redis;

class Redis
{
    /**
     * @description REDIS链接
     *
     * @var \Swoole\Coroutine\Redis
     */
    private $connection;

    /**
     * @description 配置
     *
     * @var Array
     */
    private $config;

    public function __construct(Array $config)
    {
        $this->config = $config;
        $this->connection = new \Redis();
    }

    /**
     * @description 连接REDIS服务器
     *
     * @return bool
     */
    public function connect()
    {
        if (!$this->connection->connect($this->config['host'], $this->config['port'])) {
            return false;
        }

        return $this->connection->select($this->config['db']);
    }

    /**
     * @description 调用REDIS方法, 参考PHPREDIS扩展
     *
     * @param string $name
     *
     * @param Array $params
     *
     * @return mixed
     */
    public function __call($name, $params)
    {
        return $this->connection->$name(...$params);
    }

    /**
     * @description 获取错误
     *
     * @return string
     */
    public function getError()
    {
        return $this->connection->getLastError();
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}
