<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class GuideController extends Controller
{
    private $guide;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->guide = $this->container['guide'];
    }

    public function index(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $UserName = $parameters['UserName'];
        $Password = $parameters['Password'];
        $StoreCode = $parameters['StoreCode'];
        $Route = $parameters['Route'];

        return $response->withJson($this->guide->get($UserName, $Password, $StoreCode, $Route));
    }

    public function enable(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $UserName = $parameters['UserName'];
        $Password = $parameters['Password'];
        $StoreCode = $parameters['StoreCode'];
        $GuideKey = $parameters['GuideKey'];
        $Route = $parameters['Route'];

        $status = $this->guide->enable($UserName, $Password, $StoreCode, $GuideKey, $Route);
        return $response->withJson([
            'status' => $status
        ]);
    }

    public function disable(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $UserName = $parameters['UserName'];
        $Password = $parameters['Password'];
        $StoreCode = $parameters['StoreCode'];
        $GuideKey = $parameters['GuideKey'];
        $Route = $parameters['Route'];

        $status = $this->guide->disable($UserName, $Password, $StoreCode, $GuideKey, $Route);
        return $response->withJson([
            'status' => $status
        ]);
    }
}