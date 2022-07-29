<?php

namespace ExpandCart\Foundation\Inventory\Services\Odoo\XmlrpcClient\Transport;


use ExpandCart\Foundation\Inventory\Services\Odoo\XmlrpcClient\Exception\RequestException;

class TransportException extends RequestException
{
    public static function create(string $url, int $code = 0, \Throwable $previous = null): self
    {
        return new self(sprintf('Cannot access to URL "%s"', $url), $code, $previous);
    }
}
