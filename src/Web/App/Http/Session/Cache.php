<?php
/**
 *
 * @description 放入Redis缓存
 *
 * @package     Session
 *
 * @time        2019-10-12 23:33:43
 *
 * @author      kovey
 */
namespace Kovey\Web\App\Http\Session;
use Kovey\Util\Json;
use Kovey\Components\Pool\PoolInterface;

class Cache implements SessionInterface
{
    /**
     * @description 内容
     *
     * @var string
     */
    private $content;

    /**
     * @description 文件
     *
     * @var string
     */
    private $file;

    /**
     * @description 连接池
     *
     * @var PoolInterface
     */
    private $pool;

    /**
     * @description 缓存见
     *
     * @var string
     */
    const SESSION_KEY = 'kovey_session_';

    /**
     * @description 构造
     *
     * @param PoolInterface $pool
     *
     * @param string $sessionId
     *
     * @return Cache
     */
    public function __construct(PoolInterface $pool, $sessionId)
    {
        $this->file = $sessionId;
        $this->pool = $pool;
        $this->content = array();

        $this->init();
    }

    /**
     * @description 初始化
     *
     * @return null
     */
    private function init()
    {
        $redis = $this->pool->getDatabase();
        if (!$redis) {
            return;
        }
        $file = $redis->get(self::SESSION_KEY . $this->file);
        $this->pool->put($redis);
        if ($file === false) {
            $this->newSessionId();
            return;
        }

        $info = Json::decode($file);
        if (empty($info) || !is_array($info)) {
            return;
        }

        $this->content = $info;
    }

    /**
     * @description 获取值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->content[$name] ?? '';
    }

    /**
     * @description 设置值
     *
     * @param string $name
     *
     * @param mixed $val
     *
     * @return null
     */
    public function set($name, $val)
    {
        $this->content[$name] = $val;
    }

    /**
     * @description 保存到REDIS
     *
     * @return null
     */
    private function saveToRedis()
    {
        go (function () {
            $redis = $this->pool->getDatabase();
            if (!$redis) {
                return;
            }

            $redis->set(self::SESSION_KEY . $this->file, Json::encode($this->content));
            $this->pool->put($redis);
        });
    }

    /**
     * @description 获取值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @description 设置值
     *
     * @param string $name
     *
     * @param mixed $val
     *
     * @return null
     */
    public function __set($name, $val)
    {
        $this->set($name, $val);
    }

    /**
     * @description 删除
     *
     * @param string $name
     *
     * @return bool
     */
    public function del($name)
    {
        if (!isset($this->content[$name])) {
            return false;
        }

        $this->set($name, null);
        return true;
    }

    /**
     * @description 获取sessionID
     *
     * @return string
     */
    public function getSessionId()
    {
        if (!empty($this->file)) {
            return $this->file;
        }

        $this->newSessionId();

        return $this->file;
    }

    /**
     * @description 创建sessionID
     *
     * @return null
     */
    private function newSessionId()
    {
        $this->file = password_hash(uniqid('session', true) . random_int(1000000, 9999999), PASSWORD_DEFAULT);
    }

    /**
     * @description 清除
     *
     * @return null
     */
    public function clear()
    {
        $this->content = array();
    }

    /**
     * @description 一些处理
     *
     * @return null
     */
    public function __destruct()
    {
        $this->saveToRedis();
    }
}
