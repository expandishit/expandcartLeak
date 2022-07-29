<?php

class ModelModuleElModaqeqCategory extends Model
{
	const BASE_API_URL = 'http://bmfcarapi.auditorerp.cloud';
	
	private $token = '';

	public function __construct ($registry){
		parent::__construct($registry);
		
		// echo 'elmodaqeq expandish model __construct<br/>';
    	$this->load->model('module/elmodaqeq/authentication');
    	$response = $this->model_module_elmodaqeq_authentication->login();
    	if($response['StatusCode'] == 0)
    		$this->token = $response['ResponseKey'];
	}
	/**
	 * Get modaqeq Categories via API
	 */
	public function getCategories()
	{
		$this->load->language('module/elmodaqeq');

		if( !empty($this->token) ) {
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => self::BASE_API_URL . '/Categories',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
			    'Authorization: Bearer ' . $this->token,
			    'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);
	      	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			curl_close($curl);
			// return $httpcode == 200 ? json_decode($response, true) : -1;
			return $httpcode == 200 ? [ 'success' => 1 , 'categories' => json_decode($response, true) ] : [ 'success' => 0 , 'message' => $this->language->get('error_something_went')];
		}
		else{
			return ['success' => 0 , 'message' => $this->language->get('error_login_failed')];
		}
	}

	public function addCategory($category_id , $category)
	{
		$queryString = 'Name=' . urlencode($category['category_description'][1]['name']) ;
		$url = self::BASE_API_URL . '/AddCategory?' . $queryString;
		$response = $this->curl($url, 'POST', []);
		
		if( $response == 0 ){
			//Category name is already exist
			$response = $this->curl(self::BASE_API_URL . '/EditCategory?' . $queryString, 'POST', []);
		}

		$this->db->query("INSERT IGNORE INTO `elmodaqeq_category` (`elmodaqeq_category_id`, `expandcart_category_id`) VALUES ( $response , $category_id );");

		return $httpcode == 200 ? true : false;
	}

	public function insertBulkCategories(array $categories)
	{
		try{
			$this->db->autocommit(FALSE);

			//Insert category Prepared Statement
			$insertCategoryQueryFields = ['`parent_id`', '`top`', '`column`', '`status`', '`date_modified`'];			
			$insertCategoryQuery = "INSERT INTO `category` (" . implode(',', $insertCategoryQueryFields) . ") VALUES(" . str_repeat('?,', count($insertCategoryQueryFields) - 1) . "?)";
			$insertCategoryStatement = $this->db->prepare($insertCategoryQuery);

			//Insert Category-Description Prepared Statement
			$insertCategoryDescQueryFields = ['`category_id`', '`language_id`', '`name`', '`description`', '`meta_description`','`meta_keyword`', '`slug`'];			
			$insertCategoryDescQuery = "INSERT INTO `category_description` (" . implode(',', $insertCategoryDescQueryFields) . ") VALUES(" . str_repeat('?,', count($insertCategoryDescQueryFields) - 1) . "?)";
			$insertCategoryDescStatement = $this->db->prepare($insertCategoryDescQuery);

			$insertCategoryPathStatement  = $this->db->prepare("INSERT INTO `category_path` (`category_id`, `path_id`, `level`) VALUES (?, ?, ?)");
			$insertCategoryStoreStatement = $this->db->prepare("INSERT INTO `category_to_store` (`category_id`, `store_id`) VALUES (?, ?)");

			//insert in elmodaqeq_product
			$insertCategoryElmodaqeqStatement = $this->db->prepare("INSERT INTO `elmodaqeq_category` (`elmodaqeq_category_id`, `expandcart_category_id`) VALUES (?, ?)");

			//Get names, ids of all already existed categories
			$existedCategories = $this->db->query("SELECT DISTINCT `name`, category_id FROM category_description WHERE language_id = 1 AND `name` IN ('" . implode("','", array_column($categories, 'name'))  ."') GROUP BY `name`")->rows;
			$existedCategories = array_column($existedCategories, 'name', 'category_id');

			//loop on categories to be added
			$newCategories = [];
			foreach( $categories as $category ){
				$category = $this->formatCategory($category);

				//Check if the category name is already exist,
				if(!($category_id = array_search($category['name'], $existedCategories)) ){
	    			$insertCategoryStatement->bind_param("iiiis", ...$category['category_data']);
				    $insertCategoryStatement->execute();
				    $category_id = $this->db->getLastId();

				    foreach( $category['category_description'] as $language_id => $category_description){
					    $insertCategoryDescStatement->bind_param("iisssss", $category_id, $language_id, ...(array_values($category_description)) );
					    $insertCategoryDescStatement->execute();
					}

					$insertCategoryPathStatement->bind_param("iii", ...[$category_id, $category_id, 0]);
					$insertCategoryPathStatement->execute();

					$insertCategoryStoreStatement->bind_param("ii", ...[$category_id, (int)$this->config->get('config_store_id')]);
					$insertCategoryStoreStatement->execute();
					
					$insertCategoryElmodaqeqStatement->bind_param("ii", ...[$category['elmodaqeq_category_id'], $category_id]);
					$insertCategoryElmodaqeqStatement->execute();
    			// printf("Error insertCategoryElmodaqeqStatement : %s.\n", $insertCategoryElmodaqeqStatement->error);
										
				}  		
			}
			$insertCategoryStatement->close();
			$insertCategoryDescStatement->close();
			$insertCategoryElmodaqeqStatement->close();
			$insertCategoryPathStatement->close();
			$insertCategoryStoreStatement->close();
			$this->db->autocommit(TRUE);
		} catch (Exception $e) {
			$this->db->rollback(); //remove all queries from queue if error (undo)
			throw $e;
		}
	}

	public function formatCategory(array $category)
	{
		return [
			'elmodaqeq_category_id' => $category['Id'],
			'category_data' => [ 0 , 0 , 0 , 1 , (new DateTime('NOW'))->format('Y-m-d H:i:s')],
		    'category_description' => [
	            '1' => [
                    'name' => $category['Name'],
                    'description' => $category['Name'],
                    'meta_description' => $category['Name'],
                    'meta_keyword' => $category['Name'],
                    'slug'=> str_replace(' ', '-', $category['Name']) 
	            ],
	            '2' => [
                    'name' => $category['Name'],
                    'description' => $category['Name'],
                    'meta_description' => $category['Name'],
                    'meta_keyword' => $category['Name'],
                    'slug'=> str_replace(' ', '-', $category['Name']) 
	            ]
		    ]
	    ];
	}

	private function curl($url, $method, $data)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => $method,
		  CURLOPT_POSTFIELDS => $data,
		  CURLOPT_HTTPHEADER => array(
		    'Accept: application/json',
		  ),
		));

		$response = (int) curl_exec($curl);
      	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return $response;
	}

	public function getNewCategories($categories)
	{
		$ids = array_column($categories, 'Id');

		if(!empty($ids)){
			$existedCategories = array_column($this->db->query("SELECT 
			elmodaqeq_category_id 
			FROM elmodaqeq_category 
			WHERE elmodaqeq_category_id IN(". implode(',', $ids).")")->rows, 'elmodaqeq_category_id');

			return array_filter($categories, function($category) use ($existedCategories) {
			    return !in_array($category['Id'], $existedCategories); 
			});
		}
		
	}
}
