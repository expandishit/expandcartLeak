<?php

interface HttpClient
{
    /**
     * Request implementation
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $data
     * @param array $headers
     * @param string $user
     * @param string $password
     * @param integer $timeout
     * @return HttpResponse
     */
    public function request(
        string $method,
        string $url,
        array $params = [],
        array $data = [],
        array $headers = [],
        string $user = null,
        string $password = null,
        int $timeout = null
    ): HttpResponse;
}
