<?php

class ModelModuleElModaqeqProduct extends Model
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
	 * Get modaqeq products via API
	 */
	public function getProducts()
	{
		$this->load->language('module/elmodaqeq');

		if( !empty($this->token) ) {
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => self::BASE_API_URL . '/Products',
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
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
			return $httpcode == 200 ? [ 'success' => 1 , 'products' => json_decode($response, true) ] : [ 'success' => 0 , 'message' => $this->language->get('error_something_went')];
		}
		else{
			return ['success' => 0 , 'message' => $this->language->get('error_login_failed')];
		}
	}

	public function addProduct($product)
	{
		$queryString = 'Name=' . $product['product_description'][1]['name'] .
		'&Barcode=' . ($product['barcode'] ?: $product['product_description'][1]['name'] ) . 
		'&Cost=' . $product['cost_price'] .
		'&Price=' . $product['price'] .
		'&IsTaxFound=' . ($product['tax_class_id'] > 0 ? 'true' : 'false');

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => self::BASE_API_URL . '/AddProduct?' . $queryString,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => array(),
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer ' . $this->token,
		    'Content-Type: application/json',
	    	'Accept: application/json',
		  ),
		));

		$response = curl_exec($curl);
      	$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		if( is_numeric($response) && $response > 0 && $httpcode == 200 )
			$this->db->query("INSERT IGNORE INTO `elmodaqeq_product` (`elmodaqeq_product_id`, `expandcart_product_id`) VALUES ( $response , $product_id );");

		return $httpcode == 200 ? true : false;
	}

	public function updateBulkProducts(array $products)
	{
		$product_ids = [];
		$categories = $this->db->query('SELECT * FROM `elmodaqeq_category`')->rows;
		$categories = array_column($categories, 'expandcart_category_id', 'elmodaqeq_category_id');
		try{
			$this->db->autocommit(FALSE);

			//update Product Prepared Statement
			$updateProductStatement = $this->db->prepare("UPDATE `product` SET `price` = ? ,`quantity` = ? , `barcode` = ? WHERE product_id = ? ");
			//update Product-Description Prepared Statement
			$updateProductDescStatement = $this->db->prepare("UPDATE `product_description` SET `name` = ? , `description` = ? WHERE product_id = ? AND language_id = ?");

			//update product-category
			$deleteProductCategoryStatement = $this->db->prepare("DELETE FROM `product_to_category` WHERE product_id = ?");
			$insertProductCategoryStatement = $this->db->prepare("INSERT INTO `product_to_category` (`product_id`, `category_id`) VALUES (?, ?)");

			//update in elmodaqeq_product
			$updateProductElmodaqeqStatement = $this->db->prepare("UPDATE `elmodaqeq_product` SET `barcode` = ? WHERE elmodaqeq_product_id = ?");

			foreach ($products as $product) {
				$product_id = $product['expandcart_product_id'];
				if($product_id) {
					$product = $this->formatProduct($product);
					$updateProductStatement->bind_param("disi", $product['product_data'][16], $product['product_data'][8], $product['product_data'][10],$product_id);
				    $updateProductStatement->execute();
				    //dispay errors if any
	    			// printf("Error updateProductStatement : %s.\n", $updateProductStatement->error);

				    //add product description..				    
				    foreach( $product['product_description'] as $language_id => $product_description){
						$updateProductDescStatement->bind_param("ssii", $product_description['name'],$product_description['description'], $product_id, $language_id );
				    	$updateProductDescStatement->execute();
	    				// printf("Error updateProductDescStatement : %s.\n", $updateProductDescStatement->error);
				    }
	    
				    // Get ExpandCart corresponding categories id
				    $expandCategoryId = $categories[$product['elmodaqeq_category_id']];
				    if($expandCategoryId) {
					    //Link product to these categories in product_to_category table
					    $deleteProductCategoryStatement->bind_param("i", $product_id);
					    $deleteProductCategoryStatement->execute();
	    				// printf("Error deleteProductCategoryStatement : %s.\n", $deleteProductCategoryStatement->error);

					    //foreach
						$insertProductCategoryStatement->bind_param("ii", $product_id, $expandCategoryId);
				    	$insertProductCategoryStatement->execute();
	    				// printf("Error insertProductCategoryStatement : %s.\n", $insertProductCategoryStatement->error);
				    }

				    //
				    $updateProductElmodaqeqStatement->bind_param('si', $product['product_data'][10], $product['elmodaqeq_product_id']);
				    $updateProductElmodaqeqStatement->execute();			    
				}	
			}

			$updateProductStatement->close();
			$updateProductDescStatement->close();
			$deleteProductCategoryStatement->close();
			$insertProductCategoryStatement->close();
			$this->db->autocommit(TRUE);
		} catch (Exception $e) {
			$this->db->rollback(); //remove all queries from queue if error (undo)
			throw $e;
		}
	}
	public function insertBulkProducts(array $products)
	{
		$product_ids = [];
		$categories = $this->db->query('SELECT * FROM `elmodaqeq_category`')->rows;
		$categories = array_column($categories, 'expandcart_category_id', 'elmodaqeq_category_id');
		try{
			$this->db->autocommit(FALSE);

			//Insert Product Prepared Statement
			$insertProductQueryFields = ['model', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location', 
			'quantity','minimum', 'barcode','stock_status_id', 'date_available','manufacturer_id','status', 
			'tax_class_id', 'price','image'];
			$insertProductQuery = "INSERT INTO `product` (" . implode(',', $insertProductQueryFields) . ") VALUES(" . str_repeat('?,', count($insertProductQueryFields) - 1) . "?)";
			$insertProductStatement = $this->db->prepare($insertProductQuery);

			//Insert Product-Description Prepared Statement
			$insertProductDescQueryFields = ['product_id', 'language_id', 'name', 'description', 'meta_description', 'tag','meta_keyword', 'slug'];			
			$insertProductDescQuery = "INSERT INTO `product_description` (" . implode(',', $insertProductDescQueryFields) . ") VALUES(" . str_repeat('?,', count($insertProductDescQueryFields) - 1) . "?)";
			$insertProductDescStatement = $this->db->prepare($insertProductDescQuery);

			//insert in elmodaqeq_product
			$insertProductElmodaqeqStatement = $this->db->prepare("INSERT INTO `elmodaqeq_product` (`elmodaqeq_product_id`, `expandcart_product_id`, `barcode`) VALUES (?, ?, ?)");

			//insert product-category
			$insertProductCategoryStatement = $this->db->prepare("INSERT INTO `product_to_category` (`product_id`, `category_id`) VALUES (?, ?)");

			//insert 
			$insertProductStoreStatement = $this->db->prepare("INSERT INTO `product_to_store` (`product_id`, `store_id`) VALUES (?, ?)");

			foreach ($products as $product) {
				$product = $this->formatProduct($product);
				$insertProductStatement->bind_param("ssssssssiisisiiids", ...$product['product_data']);
			    $insertProductStatement->execute();
			    $product_id = $this->db->getLastId();
			    //dispay errors if any
    			//printf("Error insertProductStatement : %s.\n", $insertProductStatement->error);

    			if( $product_id ) {
    				$product_ids [] = $product_id;
				    //add product description..				    
				    foreach( $product['product_description'] as $language_id => $product_description){
						$insertProductDescStatement->bind_param("iissssss", $product_id, $language_id, ...(array_values($product_description)) );
				    	$insertProductDescStatement->execute();
	    				//printf("Error insertProductDescStatement : %s.\n", $insertProductDescStatement->error);
				    }
	    
				    // Get ExpandCart corresponding categories id
				    $expandCategoryId = $categories[$product['elmodaqeq_category_id']];
				    if($expandCategoryId) {
					    //Link product to these categories in product_to_category table
						$insertProductCategoryStatement->bind_param("ii", $product_id, $expandCategoryId);
				    	$insertProductCategoryStatement->execute();
				    }

				    //Add product store relation
				    $insertProductStoreStatement->bind_param("ii", ...[$product_id, (int)$this->config->get('config_store_id')]);
					$insertProductStoreStatement->execute();
	    			//printf("Error insertProductStoreStatement : %s.\n", $insertProductStoreStatement->error);

				    $insertProductElmodaqeqStatement->bind_param("iis", ...[$product['elmodaqeq_product_id'] , $product_id, $product['barcode']]);
					$insertProductElmodaqeqStatement->execute();
	    			//printf("Error insertProductElmodaqeqStatement : %s.\n", $insertProductElmodaqeqStatement->error);
			    }	
			}

			$insertProductStatement->close();
			$insertProductDescStatement->close();
			$insertProductCategoryStatement->close();
			$insertProductStoreStatement->close();
			$insertProductElmodaqeqStatement->close();			
			$this->db->autocommit(TRUE);
		} catch (Exception $e) {
			$this->db->rollback(); //remove all queries from queue if error (undo)
			throw $e;
		}
	}

	public function formatProduct(array $product)
	{
		return [
			'elmodaqeq_product_id' => $product['Product_ID'],
			'barcode' => $product['Barcode'],
			'product_data' => [
				'', '', '', '', '', '', '', '', $product['QtyInStock'], 0, $product['Barcode'], 
				$this->config->get('config_stock_status_id'), (new \DateTime('NOW'))->format('Y-m-d'), 0, 1, 0, $product['Price'], '' /*image*/ 
			],
			'product_description' => [
				'1' => [
					'name' => $product['Name_En'] ?: $product['Name_Ar'],
					'description' => $product['Name_En'] ?: $product['Name_Ar'],
					'meta_description' => '',
					'tag' => '',
					'meta_keyword' => '',
					'slug' => '',
				],
				'2' => [
					'name' => $product['Name_Ar'],
					'description' => $product['Name_Ar'],
					'meta_description' => '',
					'tag' => '',
					'meta_keyword' => '',
					'slug' => '',
				]				
			],
			'elmodaqeq_category_id' => $product['CategoryID']
		];
	}

	public function deleteProducts()
	{
		$ids = array_column($this->db->query("SELECT expandcart_product_id FROM `elmodaqeq_product`")->rows, 'expandcart_product_id');
		$this->db->query("DELETE FROM product WHERE product_id IN (".implode(',', $ids).")");
        $this->db->query("DELETE FROM product_description WHERE product_id IN (".implode(',', $ids).")");
        $this->db->query("DELETE FROM product_to_category WHERE product_id IN (".implode(',', $ids).")");
        $this->db->query("DELETE FROM product_to_store WHERE product_id IN (".implode(',', $ids).")");
      
		$this->db->query("DELETE FROM elmodaqeq_product;");
		return $this->db->countAffected();
	}

	public function getExistedProducts($products)
	{
		$ids = array_column($products, 'Product_ID');
		$existedProducts = array_column($this->db->query("SELECT 
			* 
			FROM elmodaqeq_product 
			WHERE elmodaqeq_product_id IN(". implode(',', $ids).")")->rows, 'expandcart_product_id', 'elmodaqeq_product_id');

		array_walk($products, function(&$product, $key) use ($existedProducts, &$products) {
			if( !empty($existedProducts[$product['Product_ID']]) ) {
				$product['expandcart_product_id'] = $existedProducts[$product['Product_ID']];
			}else{
				unset($products[$key]);
			}
		});
		return $products;
		// return array_filter($products, function($product) use ($existedProducts) {
		// 	return !empty($existedProducts[$product['Product_ID']]);
		// });
	}

	public function getNewProducts($products)
	{
		$ids = array_column($products, 'Product_ID');

		$existedProducts = array_column($this->db->query("SELECT 
			elmodaqeq_product_id 
			FROM elmodaqeq_product 
			WHERE elmodaqeq_product_id IN(". implode(',', $ids).")")->rows, 'elmodaqeq_product_id');

		return array_filter($products, function($product) use ($existedProducts) {
		    return !in_array($product['Product_ID'], $existedProducts); 
		});
	}	
}
