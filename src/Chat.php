<?php
namespace ChatServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class UserSocket
{
    public $connSocket;
    public $userId;
}

class MessageSocket
{
    public $type;
    public $user_send;
    public $user_reveice;
    public $message;
    public $avatar;
    public $name;
}

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "Started socket\n";
    }

    public function onOpen(ConnectionInterface $connSocket) {
        //get user id
        parse_str(parse_url($connSocket->httpRequest->getUri())['query'], $params);
        $userId = $params['userId'];

        $data = new UserSocket();
        $data->connSocket = $connSocket;
        $data->userId = $userId;
        $this->clients->attach($data);
        echo $connSocket->resourceId . " connected!\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = new UserSocket();
        $data = json_decode($msg);
        foreach ($this->clients as $client) {
            if ($client->userId === $data->user_reveice)
            {
                $client->connSocket->send(json_encode($data));
            }
        }
    }

    public function onClose(ConnectionInterface $connSocket) {
        foreach ($this->clients as $client) {
            if ($client->connSocket === $connSocket)
            {
                $this->clients->detach($client);
            }
        }
        echo $connSocket->resourceId . " disconnected!\n";
    }

    public function onError(ConnectionInterface $connSocket, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $connSocket->close();
    }
}