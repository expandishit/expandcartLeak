<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DownloadController extends Controller
{
    private $download;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->download = $this->container['download'];
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

			$results = $this->download->getDownloads($filter_data);

			foreach ($results as $result) {
				$data[] = array(
					'download_id' => $result['download_id'], 
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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
	
}