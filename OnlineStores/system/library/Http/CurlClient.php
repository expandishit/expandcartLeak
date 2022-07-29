<?php

require_once(__DIR__ . '/HttpResponse.php');
require_once(__DIR__ . '/HttpClient.php');

/**
 * cURL client
 */
class CurlClient implements HttpClient
{
    /**
     * The maximum number of seconds to allow cURL functions to execute.
     * @const int
     */
    public const DEFAULT_TIMEOUT = 60;

    /**
     * The registry instance
     *
     * @var Registry
     */
    protected $registry;

    /**
     * cURL options
     *
     * @var array
     */
    protected $defaultRequestOptions = [];

    /**
     * Last executed request
     *
     * @var array
     */
    protected $request;

    /**
     * Last cURL Response
     *
     * @var HttpResponse
     */
    protected $response;

    /**
     * Constructor
     *
     * @param Registry $registry
     * @param array $request
     */
    public function __construct($registry, array $defaultRequestOptions = [])
    {
        $this->registry = $registry;
        $this->defaultRequestOptions = $defaultRequestOptions;
    }

    /**
     * Create cURL request
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
    ): HttpResponse {
        $request = $this->mergeRequestOptions(
            $method,
            $url,
            $params,
            $data,
            $headers,
            $user,
            $password,
            $timeout
        );

        $this->request = $request;
        $this->response = null;

        try {
            if (!$curl = \curl_init()) {
                throw new \Exception('Unable to initialize cURL');
            }

            if (!\curl_setopt_array($curl, $request)) {
                throw new \Exception(\curl_error($curl));
            }

            if (!$response = \curl_exec($curl)) {
                throw new \Exception(\curl_error($curl));
            }

            $parts = \explode("\r\n\r\n", $response, 3);

            list($head, $body) = (\preg_match('/\AHTTP\/1.\d 100 Continue\Z/', $parts[0])
                || \preg_match('/\AHTTP\/1.\d 200 Connection established\Z/', $parts[0])
                || \preg_match('/\AHTTP\/1.\d 200 Tunnel established\Z/', $parts[0]))
                ? array($parts[1], $parts[2])
                : array($parts[0], $parts[1]);

            $statusCode = \curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $responseHeaders = [];
            $headerLines = \explode("\r\n", $head);
            \array_shift($headerLines);
            foreach ($headerLines as $line) {
                list($key, $value) = \explode(':', $line, 2);
                if (isset($responseHeaders[$key])) {
                    $responseHeaders[$key][] = $value;
                } else {
                    $responseHeaders[$key] = [$value];
                }
            }
            \curl_close($curl);

            if (isset($request[CURLOPT_INFILE]) && \is_resource($request[CURLOPT_INFILE])) {
                \fclose($request[CURLOPT_INFILE]);
            }

            $this->response = new HttpResponse($statusCode, $body, $responseHeaders);

            return $this->response;
        } catch (\ErrorException $e) {
            if (isset($curl) && \is_resource($curl)) {
                \curl_close($curl);
            }

            if (isset($request[CURLOPT_INFILE]) && \is_resource($request[CURLOPT_INFILE])) {
                \fclose($request[CURLOPT_INFILE]);
            }

            throw $e;
        }
    }

    /**
     * Prepare cURL options
     *
     * @param string $method
     * @param string $url
     * @param array $params
     * @param array $data
     * @param array $headers
     * @param string $user
     * @param string $password
     * @param integer $timeout
     * @return array
     */
    public function mergeRequestOptions(
        string $method,
        string $url,
        array $params = [],
        array $data = [],
        array $headers = [],
        string $user = null,
        string $password = null,
        int $timeout = null
    ): array {
        $timeout = $timeout ?? self::DEFAULT_TIMEOUT;
        $request = $this->defaultRequestOptions + [
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_INFILESIZE => Null,
            CURLOPT_HTTPHEADER => [],
            CURLOPT_TIMEOUT => $timeout,
        ];

        foreach ($headers as $key => $value) {
            $request[CURLOPT_HTTPHEADER][] = "$key: $value";
        }

        if ($user && $password) {
            $request[CURLOPT_HTTPHEADER][] = 'Authorization: Basic ' . \base64_encode("$user:$password");
        }

        $body = $this->buildQuery($params);
        if ($body) {
            $request[CURLOPT_URL] .= '?' . $body;
        }

        switch (\strtolower(\trim($method))) {
            case 'get':
                $request[CURLOPT_HTTPGET] = true;
                break;
            case 'post':
                $request[CURLOPT_POST] = true;
                $request[CURLOPT_POSTFIELDS] = $this->buildRequestData($data, $headers);
                break;
            case 'put':
                $request[CURLOPT_PUT] = true;
                if ($data) {
                    if ($buffer = \fopen('php://memory', 'w+')) {
                        $dataString = $this->buildRequestData($data, $headers);
                        \fwrite($buffer, $dataString);
                        \fseek($buffer, 0);
                        $request[CURLOPT_INFILE] = $buffer;
                        $request[CURLOPT_INFILESIZE] = \strlen($dataString);
                    } else {
                        throw new \Exception('Unable to open a temporary file');
                    }
                }
                break;
            case 'head':
                $request[CURLOPT_NOBODY] = true;
                break;
            default:
                $request[CURLOPT_CUSTOMREQUEST] = \strtoupper($method);
        }

        return $request;
    }

    /**
     * build request data
     *
     * @param array $data
     * @param array $headers
     * @return string
     */
    public function buildRequestData(array $data, array $headers): string
    {
        foreach ($headers as $key => $value) if (strtolower(trim($key)) === 'content-type' && strtolower(trim($value)) === 'application/json')
            return $this->buildJson($data);

        return $this->buildQuery($data);
    }

    /**
     * Encode url params
     *
     * @param array|null $params
     * @return string
     */
    public function buildQuery(?array $params): string
    {
        $parts = [];
        $params = $params ?: [];

        foreach ($params as $key => $value) {
            if (\is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = \urlencode((string)$key) . '=' . \urlencode((string)$item);
                }
            } else {
                $parts[] = \urlencode((string)$key) . '=' . \urlencode((string)$value);
            }
        }

        return \implode('&', $parts);
    }

    /**
     * Encode json params
     *
     * @param array|null $params
     * @return string
     */
    public function buildJson(?array $params): string
    {
        return json_encode($params ?: []);
    }

    /**
     * Get the last request
     *
     * @return array $request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * The last response
     *
     * @return HttpResponse $response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
