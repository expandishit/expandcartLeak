<?php

namespace ExpandCart\Foundation\Exceptions;

class DatabaseException extends Exception
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
