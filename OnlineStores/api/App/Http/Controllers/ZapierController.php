<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Api\Models\Token;
use Api\Models\User;

class ZapierController extends Controller
{

    private $zapier;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->zapier = $this->container['zapier'];
    }



    public function makeAuth(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        if (!(Token::validateTokenCredentials($parameters))) {
            return $response->withJson([
                'status' => 'error',
                'error_code' => 'invalid_credentials',
                'error_description' => 'Invalid credentials, please checkout your credentials'
            ], 401);
        }

        $user = (new User)->setClient($this->container->db);

        $user = $user->getUser($parameters['client_secret'], $parameters['client_id']);

        if (!$user) {
            return $response->withJson([
                'status' => 'error',
                'error_code' => 'invalid_credentials',
                'error_description' => 'Invalid credentials, please checkout your credentials'
            ], 401);
        }

        return $response->withJson(['status' => 'ok'], 200);
    }

    /***************** Subscribe hook *****************/
    public function subscribeHook(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $hook_id = $this->zapier->subscribeHook($parameters['type'],$parameters['url']);
        return $response->withJson(['status' => 'ok','id'=>$hook_id], 200);
    }

    /***************** UnSubscribe hook *****************/
    public function unSubscribeHook(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        $this->zapier->unSubscribeHook($parameters['id']);
        return $response->withJson(['status' => 'ok','id'=>$parameters['id']], 200);
    }
}