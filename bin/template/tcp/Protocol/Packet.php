<?php
/**
 * @description
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-05-06 14:43:53
 *
 */
namespace Protocol;

use Kovey\Tcp\Protocol\ProtocolInterface;

class Packet implements ProtocolInterface
{
    private $action;

    private $message;

    public function __construct(string $body, int $action)
    {
        $this->action = $action;
        $this->message = $body;
    }

    public function getAction() : string
    {
        return $this->action;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}
