<?php
/**
 *
 * @description 传输协议
 *
 * @package     Protocol
 *
 * @time        2019-11-16 18:14:53
 *
 * @author      kovey
 */
namespace Kovey\Rpc\Protocol;

use Kovey\Util\Util;
use Kovey\Rpc\Encryption\Encryption;

class Json implements ProtocolInterface
{
    /**
     * @description 路径
     *
     * @var string
     */
    private $path;

    /**
     * @description 方法
     *
     * @var string
     */
    private $method;

    /**
     * @description 参数
     *
     * @var Array
     */
    private $args;

    /**
     * @description 包体类容
     *
     * @var string
     */
    private $body;

    /**
     * @description 秘钥
     *
     * @var string
     */
    private $secretKey;

    /**
     * @description 明文
     *
     * @var string
     */
    private $clear;

    /**
     * @description 加密类型
     *
     * @var string
     */
    private $encryptType;

    /**
     * @description 是否公钥
     *
     * @var bool
     */
    private $isPub;

    /**
     * @description trace id
     *
     * @var string
     */
    private $traceId;

    /**
     * @description from
     */
    private $from;

    /**
     * @description 构造函数
     *
     * @param string $body
     *
     * @param string $key
     *
     * @param string $type
     *
     * @param bool $isPub
     *
     * @return Json
     */
    public function __construct(string $body, string $key, string $type = 'aes', bool $isPub = false)
    {
        $this->body = $body;
        $this->secretKey = $key;
        $this->encryptType = $type;
        $this->isPub = $isPub;
    }

    /**
     * @description 解析包
     *
     * @return bool
     */
    public function parse()
    {
        $this->clear = self::unpack($this->body, $this->secretKey, $this->encryptType, $this->isPub);
        if (empty($this->clear)) {
            return false;
        }

        if (!is_array($this->clear)) {
            return false;
        }

        if (!isset($this->clear['p'])
            || !isset($this->clear['m'])
            || empty($this->clear['p'])
            || empty($this->clear['m'])
        ) {
            return false;
        }

        if (isset($this->clear['a']) && !is_array($this->clear['a'])) {
            return false;
        }

        $this->path  = $this->clear['p'];
        $this->method = $this->clear['m'];
        $this->args = $this->clear['a'] ?? array();
        $this->traceId = $this->clear['t'] ?? '';
        $this->from = $this->clear['f'] ?? '';

        return true;
    }

    /**
     * @description 获取路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @description 获取方法
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @description 获取参数
     *
     * @return Array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @description 获取明文
     *
     * @return string
     */
    public function getClear()
    {
        return $this->clear;
    }

    /**
     * @description 获取TraceId
     *
     * @return string
     */
    public function getTraceId()
    {
        return $this->traceId;
    }

    /**
     * @description get from
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @description 打包
     *
     * @param Array $packet
     *
     * @param string $secretKey
     *
     * @param string $type
     *
     * @param bool $isPub
     *
     * @return string | bool
     */
    public static function pack(Array $packet, string $secretKey, $type = 'aes', $isPub = false)
    {
        $data = Encryption::encrypt(json_encode($packet), $secretKey, $type, $isPub);
        if (!$data) {
            return false;
        }

        return pack(self::PACK_TYPE, strlen($data)) . $data;
    }

    /**
     * @description 解包
     *
     * @param string $data
     *
     * @param string $secretKey
     *
     * @param string $type
     *
     * @param bool $isPub
     *
     * @return Array | bool
     */
    public static function unpack(string $data, string $secretKey, $type = 'aes', $isPub = false)
    {
        $info = unpack(self::PACK_TYPE, substr($data, self::LENGTH_OFFSET, self::HEADER_LENGTH));
        $length = $info[1] ?? 0;

        if (!Util::isNumber($length) || $length < 1) {
            return false;
        }

        $encrypt = substr($data, self::BODY_OFFSET, $length);
        $packet = Encryption::decrypt($encrypt, $secretKey, $type, $isPub);
        if (!$packet) {
            return false;
        }

        return json_decode($packet, true);
    }
}
