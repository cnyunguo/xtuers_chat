<?php
namespace Xtuers\Chat;
use Ratchet\ConnectionInterface;
use User;

/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-17
 * Time: 下午3:22
 */
class Client implements ClientInterface{
    private $user;
    private $socket;
    private $connetcTime;

    /**
     * @return ConnectionInterface
     */
    function getSocket()
    {
        return $this->socket;
    }

    function setSocket(ConnectionInterface $socket)
    {
        $this->socket = $socket;
    }

    function getUser()
    {
        return $this->user;
    }

    function setUser(User $user)
    {
        $this->user = $user;
    }
}