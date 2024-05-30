<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require '../vendor/autoload.php';

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $users;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Новий зв'язок! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($data['type']) && $data['type'] === 'join') {
                $this->users[$from->resourceId] = $data['user'];
                $this->broadcastUserList();
            } else {
                $response = [
                    'user' => $data['user'],
                    'message' => $data['message'],
                    'timestamp' => date('Y-m-d H:i:s'),
                    'recipient' => $data['recipient']
                ];

                if ($data['recipient']) {
                    // Приватне повідомлення
                    foreach ($this->clients as $client) {
                        if ($from !== $client && $this->users[$client->resourceId] === $data['recipient']) {
                            $client->send(json_encode($response));
                        }
                    }
                } else {
                    // Публічне повідомлення
                    foreach ($this->clients as $client) {
                        if ($from !== $client) {
                            $client->send(json_encode($response));
                        }
                    }
                }
            }
        } else {
            echo "Невірний формат повідомлення\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);
        $this->broadcastUserList();
        echo "Зв'язок {$conn->resourceId} закрито\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Помилка: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcastUserList()
    {
        $userList = array_values($this->users);
        $response = [
            'type' => 'userlist',
            'users' => $userList
        ];

        foreach ($this->clients as $client) {
            $client->send(json_encode($response));
        }
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run();
