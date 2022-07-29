<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ManufacturerController extends Controller
{
    private $manufacturer,$registry;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->manufacturer = $this->container['manufacturer'];
		$this->registry = $container['registry'];
    }

    public function index(Request $request, Response $response)
    {
    }

    public function show(Request $request, Response $response, $args)
    {
    }

    public function store(Request $request, Response $response)
    {
    }

    public function update(Request $request, Response $response, $args)
    {
    }

    public function delete(Request $request, Response $response, $args)
    {
    }
	
	public function autocomplete (Request $request, Response $response){
		$params = $_GET;
		$data = array();
		
		if (isset($_GET['filter_name'])) {
			
			$filter_data = array(
				'filter_name' => trim($_GET['filter_name']),
				'start'       => 0,
				'limit'       => 20
			);

			$results = $this->manufacturer->getManufacturers($filter_data);
			
			if (isset($_GET['add_default']) && $_GET['add_default'] == 'y') {
                $data[] = array(
                    'manufacturer_id' => 0,
                   'name' => $this->registry->get('language')->get('text_none')
                );
            }
			
			foreach ($results as $result) {
				$data[] = array(
					'manufacturer_id' => $result['manufacturer_id'], 
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		
		$sort_order = array();

		foreach ($data as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data);
		
		return $response->withJson(['status' => 'ok', 'data' => $data]);
		  
    }
    
   ################ Get Manufacturer By Name ################
   public function getByName(Request $request, Response $response, $args)
   {
       $validator = $this->validator([
           'name' => $args['name']
       ], [
           'name' => 'string'
       ]);
       

       if (!$validator)
           return $response->withJson([
               'status' => 'error',
               'status_code' => $this->status[2],
               'error_description' => ''
           ]);

           
       $manufacturer = $this->manufacturer->getByName($args['name']);

       if ($manufacturer)
           return $response->withJson([
               'status' => $this->status[1],
               'data' => $manufacturer
           ]);


       return $response->withJson([
           'status' => 'error',
           'status_code' => $this->status[0],
           'error_description' => ''
       ]);
   }
	
}