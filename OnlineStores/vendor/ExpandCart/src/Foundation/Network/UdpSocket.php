<?php

namespace ExpandCart\Foundation\Network;

class UdpSocket/* implements SocketConnectionInterface*/
{
    protected $socket;
    protected $server;
    protected $port;

    public function connect($server, $port)
    {
        $this->server = $server;
        $this->port = $port;
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, 0);
    }

    public function send($message)
    {
        if (isset(EC_MONITORING['stores']) && isset(EC_MONITORING['stores'][STORECODE]) == false) {
            return false;
        }

        $message = (json_encode($message));
        $s = socket_sendto($this->socket, $message, strlen($message), 0, $this->server, $this->port);

        if (!$s) {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception($errormsg);
            die("Could not send data: [$errorcode] $errormsg \n");
        }
    }

    public function close()
    {
        socket_close($this->socket);
    }
}
