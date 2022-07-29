<?php

namespace ExpandCart\Foundation\Network;

interface ConnectionInterface
{
    public function send();

    public function close();
}
