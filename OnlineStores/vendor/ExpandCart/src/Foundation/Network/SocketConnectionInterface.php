<?php

namespace ExpandCart\Foundation\Network;

interface SocketConnectionInterface extends ConnectionInterface
{
    public function connect();
}
