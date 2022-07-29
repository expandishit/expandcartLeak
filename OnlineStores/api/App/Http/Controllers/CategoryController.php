<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CategoryController extends Controller
{
    private $category;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->category = $this->container['category'];
    }

    public function index(Request $request, Response $response)
    {
        return $response->withJson($this->category->getAll());
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

			$results = $this->category->getCategories($filter_data);

			foreach ($results as $result) {
				$data[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'image' => \Filesystem::getUrl('image/' . $result['image']),
					//'image' => 'image/' . $result['image'],
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

	################ Get Category ################
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

			
        $category = $this->category->getByName($args['name']);

        if ($category)
            return $response->withJson([
                'status' => $this->status[1],
                'data' => $category
            ]);


        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[0],
            'error_description' => ''
        ]);
    }
	
}