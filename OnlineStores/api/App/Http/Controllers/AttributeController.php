<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AttributeController extends Controller
{
    private $attribute;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->attribute = $this->container['attribute'];
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
	
	public function getAll (Request $request, Response $response){
		
		$data = array();
		$results = $this->attribute->groupedAutocomplete();

            foreach ($results as $result) {
                $attribute = [];

                $attribute['text'] = $result['group_name'];

                foreach ($result['options'] as $option) {
                    $attribute['children'][] = [
                        'id' => $option['attribute_id'],
                        'text' => $option['attribute_name']
                    ];
                }

                $data[] = $attribute;
            }
		
		
		return $response->withJson(['status' => 'ok', 'data' => $data]);
		  
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

			$results = $this->attribute->groupedAutocomplete($filter_data);

			
            foreach ($results as $result) {
                $attribute = [];

                $attribute['text'] = $result['group_name'];

                foreach ($result['options'] as $option) {
                    $attribute['children'][] = [
                        'id' => $option['attribute_id'],
                        'text' => $option['attribute_name']
                    ];
                }

                $data[] = $attribute;
            }
		}
		/*
		$sort_order = array();
		
		foreach ($data as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data);
		*/
		return $response->withJson(['status' => 'ok', 'data' => $data]);
		  
	}
	
	
}