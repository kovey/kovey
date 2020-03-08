<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: Hello.proto

namespace Protobuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>protobuf.Hello</code>
 */
class Hello extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string handler = 1;</code>
     */
    protected $handler = '';
    /**
     * Generated from protobuf field <code>string handlerMethod = 2;</code>
     */
    protected $handlerMethod = '';
    /**
     * Generated from protobuf field <code>string hello = 3;</code>
     */
    protected $hello = '';
    /**
     * Generated from protobuf field <code>int32 type = 4;</code>
     */
    protected $type = 0;
    /**
     * Generated from protobuf field <code>string world = 5;</code>
     */
    protected $world = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $handler
     *     @type string $handlerMethod
     *     @type string $hello
     *     @type int $type
     *     @type string $world
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Hello::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string handler = 1;</code>
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Generated from protobuf field <code>string handler = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setHandler($var)
    {
        GPBUtil::checkString($var, True);
        $this->handler = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string handlerMethod = 2;</code>
     * @return string
     */
    public function getHandlerMethod()
    {
        return $this->handlerMethod;
    }

    /**
     * Generated from protobuf field <code>string handlerMethod = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setHandlerMethod($var)
    {
        GPBUtil::checkString($var, True);
        $this->handlerMethod = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string hello = 3;</code>
     * @return string
     */
    public function getHello()
    {
        return $this->hello;
    }

    /**
     * Generated from protobuf field <code>string hello = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setHello($var)
    {
        GPBUtil::checkString($var, True);
        $this->hello = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 type = 4;</code>
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Generated from protobuf field <code>int32 type = 4;</code>
     * @param int $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkInt32($var);
        $this->type = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string world = 5;</code>
     * @return string
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * Generated from protobuf field <code>string world = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setWorld($var)
    {
        GPBUtil::checkString($var, True);
        $this->world = $var;

        return $this;
    }

}

