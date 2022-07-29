<?php

class HttpResponse
{
    /**
     * Response headers
     *
     * @var array|null
     */
    protected $headers;

    /**
     * Response content
     *
     * @var string|null
     */
    protected $content;

    /**
     * Response status code
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Constructor
     *
     * @param integer $statusCode
     * @param string|null $content
     * @param array|null $headers
     */
    public function __construct(int $statusCode, ?string $content, ?array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * Get content
     * 
     * @return mixed
     */
    public function getContent()
    {
        return \json_decode($this->content, true);
    }

    /**
     * Get status code
     *
     * @return integer
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Determine the request is successful or not 
     *
     * @return boolean
     */
    public function ok(): bool
    {
        return $this->getStatusCode() < 400;
    }

    /**
     * To debug request
     *
     * @return string
     */
    public function __toString(): string
    {
        return '[Response] HTTP ' . $this->getStatusCode() . ' ' . $this->content;
    }
}
