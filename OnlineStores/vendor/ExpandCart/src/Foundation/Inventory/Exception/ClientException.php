<?php

namespace ExpandCart\Foundation\Inventory\Exception;

class ClientException extends \Exception
{
    public function debug()
    {
        return [
            'type' => __CLASS__,
            'message' => $this->getMessage(),
            'line' => $this->getLine(),
            'file' => $this->getFile(),
            'trace' => $this->getTrace()
        ];
    }
}
