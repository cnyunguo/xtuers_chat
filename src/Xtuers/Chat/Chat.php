<?php

namespace Xtuers\Chat;

use Evenement\EventEmitterInterface;
use Exception;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Tappleby\AuthToken\AuthTokenDriver;
use User;
use Xtuers\Chat\Exception\UnknownMessageTypeException;
use Xtuers\Chat\Request\BaseRequest;

class Chat
implements ChatInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected  $clients;

    protected $emitter;

    /**
     * @var \Tappleby\AuthToken\AuthTokenDriver
     */
    protected $driver;
    /**
     * @param ConnectionInterface $socket
     * @return Client
     */
    public function getClientBySocket(ConnectionInterface $socket)
    {
        foreach ($this->clients as $next)
        {
            if ($next->getSocket() === $socket)
            {
                return $next;
            }
        }

        return null;
    }

    /**
     * @param int $uid
     * @return Client
     */
    public function findClientByUid($uid){
        foreach($this->clients as $next){
            $user = $next->getUser();
            if($user!=null && $user->uid==$uid){
                return $next;
            }
        }
        return null;
    }

    public function getEmitter()
    {
        return $this->emitter;
    }

    public function setEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * @return SplObjectStorage
     */
    public function getClients()
    {
        return $this->clients;
    }

    public function __construct(EventEmitterInterface $emitter, AuthTokenDriver $driver)
    {
        $this->emitter = $emitter;
        $this->clients   = new SplObjectStorage();
        $this->driver = $driver;

    }

    public function onOpen(ConnectionInterface $socket)
    {
        $client = new Client();
        $client->setSocket($socket);
        $this->clients->attach($client);
        $this->emitter->emit("open", [$client]);
    }

    public function onMessage(ConnectionInterface $socket, $json)
    {
        echo $json."\n";
        $client = $this->getClientBySocket($socket);
        $message = new BaseRequest($json);
        if($message->validType()){
            $this->emitter->emit($message->getType(), [$client, $message->getTypedRequest()]);
        }else{
            $this->emitter->emit('error', [$client, new UnknownMessageTypeException("来自用户的未知类型消息:" .$message->getType())]);
        }
    }

    public function onClose(ConnectionInterface $socket)
    {
        $client = $this->getClientBySocket($socket);

        if ($client)
        {
            $this->clients->detach($client);
            $this->emitter->emit("close", [$client]);
        }
    }

    public function onError(ConnectionInterface $socket, Exception $exception)
    {
        $client = $this->getClientBySocket($socket);

        if ($client)
        {
            $client->getSocket()->close();
            $this->emitter->emit("error", [$client, $exception]);
        }
    }

    /**
     * @return AuthTokenDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }
}