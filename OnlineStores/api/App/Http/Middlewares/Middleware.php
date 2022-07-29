<?php

namespace Api\Http\Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Middleware
{
    /**
     * The response object
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * The request object
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * The credentials array
     *
     * @var array
     */
    protected $credentials;

    /**
     * The Container object
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * construct the Middleware object
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {

        $this->container = $container;

        if (isset($this->container['credentials'])) {
            $this->setCredentials($this->container['credentials']);
        }

    }

    /**
     * setUp the required logic to pass to the next layer
     *
     * @return bool
     */
    abstract protected function setUp();

    /**
     * performing any required cleanup & shutdowning for the middleware
     *
     * @return void
     */
    abstract protected function shutDown();

    /**
     * return credentials array
     *
     * @return array
     */
    protected function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * set the credentials array
     *
     * @param $credentials
     *
     * @return array
     */
    protected function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * get the request object
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * set the request object
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Api\Http\Middlewares\MiddlewareInterface
     */
    protected function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * get the response object
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * set the response object
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Api\Http\Middlewares\MiddlewareInterface
     */
    protected function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $this->setRequest($request);

        $this->setResponse($response);

        $response = $this->setUp();

        if ($response && true !== $response) {
            return $response;
        }

        $this->response = $next($this->request, $this->response);

        $this->shutDown();

        return $this->response;
    }
}
