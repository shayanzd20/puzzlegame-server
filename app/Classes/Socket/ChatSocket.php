<?php



namespace App\Classes\Socket;

use App\Classes\Socket\Base\BaseSocket;
use Ratchet\ConnectionInterface;

require_once __DIR__ . "/config/systemConfig.php";
require_once __DIR__ . "/config/function.php";
require_once __DIR__ . "/config/gameFunctions.php";
require_once __DIR__ . "/api/api-sample-php/sms.class.php";


error_reporting(E_ALL);
ini_set('display_errors', 1);

class ChatSocket extends BaseSocket
{

    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // TODO: Implement onOpen() method.
        $this->clients->attach($conn);
        echo "new connection ! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // TODO: Implement onMessage() method.
//        $numRecv= count($this->clients) - 1;

                echo "\n"."from:"."\n";
                var_dump($from->resourceId);

                echo "\n"."msg:"."\n";
                $decode = json_decode($msg);
                var_dump($decode);
                echo "\n";

        switch ($decode->command) {
            case "login":
                include __DIR__ . "/api/login.php";
//                $this->subscriptions[$conn->resourceId] = $data->channel;
                break;

            case "validation":
                // validate disposable code
                include __DIR__ . "/api/validation.php";
                break;
            default:

        }

        }

    public function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
        $this->clients->detach($conn);
        echo "connection ! ({$conn->resourceId}) has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
        echo "An error has occurred: {$e->getMessage()} \n";

        $conn->close();
    }
}