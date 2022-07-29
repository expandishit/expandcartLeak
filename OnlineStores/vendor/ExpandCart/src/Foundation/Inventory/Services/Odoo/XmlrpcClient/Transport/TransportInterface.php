<?php

namespace ExpandCart\Foundation\Inventory\Services\Odoo\XmlrpcClient\Transport;

interface TransportInterface
{
    /**
     * @throws TransportException when posting failed
     */
    public function post(string $url, string $xmlRequest): Response;
}
