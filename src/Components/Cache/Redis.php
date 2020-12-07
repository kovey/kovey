<?php
/**
 *
 * @description Redis 客户端封装，基于\Swoole\Coroutine\Redis
 *
 * @package     Components\Cache
 *
 * @time        Tue Sep 24 09:01:39 2019
 *
 * @author      kovey
 */
namespace Kovey\Components\Cache;

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
        $this->connection = new \Swoole\Coroutine\Redis();
        $this->connection->setOptions(array(
            'compatibility_mode' => true
        ));
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
        if (!$this->connection->connected) {
            $this->connect();
        }

        return $this->connection->$name(...$params);
    }

    /**
     * @description 获取错误
     *
     * @return string
     */
    public function getError()
    {
        return sprintf('[%s]: %s', $this->connection->errCode, $this->connection->errMsg);
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}
