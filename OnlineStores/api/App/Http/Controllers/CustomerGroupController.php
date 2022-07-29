<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Respect\Validation\Validator as vld;

use Api\Models\CustomerGroup;

class CustomerGroupController extends Controller
{

    
	/**
     * get customer groups API
     *
     * @param int id 
     *
     * @return json
     * */
    public function index(Request $request, Response $response)
    {
       
			
			$customerGroup = (new customerGroup)->setContainer($this->container);
           
		    $customer_groups = $customerGroup->getCustomerGroups();
		   // $customer_groups = array();
			

            
            return $response->withJson([
                'status' => 'ok',
                'data' => $customer_groups
            ]);
     


	}


}