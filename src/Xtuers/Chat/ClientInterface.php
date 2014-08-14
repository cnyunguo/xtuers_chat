<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 14-7-17
 * Time: 下午3:23
 */

namespace Xtuers\Chat;



use Ratchet\ConnectionInterface;
use User;

interface ClientInterface {
    /**
     * @return ConnectionInterface
     */
    function getSocket();

    function setSocket(ConnectionInterface $socket);

    /**
     * @return User
     */
    function getUser();
    function setUser(User $user);
}