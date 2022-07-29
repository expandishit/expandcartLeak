<?php

namespace Api\Handlers;

use Monolog\Logger;

class Error {
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    public function __invoke($request, $response, $exception) {
        $errorCode = bin2hex(random_bytes(5));
        $this->logger->critical($exception->getMessage(), ['file' => $exception->getFile(), 'line' => $exception->getLine(), 'errorCode' => $errorCode]);
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write("<html lang='en'><head><title>ErrorCode : {$errorCode}</title></head><h1>Something went wrong! <br />ErrorCode : {$errorCode} </h1></html>");
    }
}