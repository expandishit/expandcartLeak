<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Respect\Validation\Validator as vld;

use Api\Models\User;

class UserProfileController extends Controller
{
	
	
	
   public function index(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!filter_var($args['id'], FILTER_VALIDATE_INT))
        {
            return $response->withJson(['status' => 'validation_error', 'error_description' => 'ID Should be integer']);
        }

        $user = (new User)->setContainer($this->container);

        $user_data = $user->profile($id);
        if($user_data)
            return $response->withJson(['status' => 'ok', 'data' => $user_data]);

        return $response->withJson(['status' => 'error', 'error_description' => 'User not found']);
    }


   

}