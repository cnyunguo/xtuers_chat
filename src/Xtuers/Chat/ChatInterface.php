<?php

namespace Xtuers\Chat;

use Evenement\EventEmitterInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * 聊天的消息接口
 * @package Chat
 */
interface ChatInterface
extends MessageComponentInterface
{
    /**
     * @param ConnectionInterface $socket
     * @return Client
     */
    public function getClientBySocket(ConnectionInterface $socket);

    /**
     * @return EventEmitterInterface
     */
    public function getEmitter();

    /**
     * @param EventEmitterInterface $emitter
     * @return mixed
     */
    public function setEmitter(EventEmitterInterface $emitter);

    /**
     * @return \SplObjectStorage
     */
    public function getClients();

    /**
     * @return \Tappleby\AuthToken\AuthTokenDriver
     */
    public function getDriver();

    /**
     * @param int $uid
     * @return Client
     */
    public function findClientByUid($uid);
}