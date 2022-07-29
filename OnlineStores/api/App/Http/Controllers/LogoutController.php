<?php
namespace Api\Http\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutController extends Controller {
	
	
	
	
    public function index(Request $request, Response $response)
    {
			session_destroy();
		 return $response->withJson(['status' => 'ok', 'message' =>'logout success']);
    }

}