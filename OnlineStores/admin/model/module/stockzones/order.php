<?php
require_once('entity.php');

class ModelModuleStockzonesOrder extends ModelModuleStockzonesEntity {

	public function orderHasStockzonesProducts($order_id){
		$stockzones_products = $this->db->query("SELECT op.product_id 
			FROM `" . DB_PREFIX . "order_product` op 
			JOIN stockzones_product sp ON op.product_id = sp.expandcart_product_id 
			WHERE order_id = " . (int)$order_id . ";")->rows;
		return count($stockzones_products) > 0;
	}

	public function isStockzonesOrder($expandcart_order_id){
		return $this->db->query("SELECT stockzones_order_id 
			FROM `" . DB_PREFIX . "stockzones_order` 
			WHERE expandcart_order_id = " . (int)$expandcart_order_id)->row['stockzones_order_id'] ? TRUE : FALSE;
	}

	public function getStockzonesOrderData($expandcart_order_id){
		if($this->isStockzonesOrder($expandcart_order_id)){			
			return $this->db->query("SELECT * FROM `" . DB_PREFIX ."stockzones_order` WHERE expandcart_order_id = " . (int)$expandcart_order_id)->row;
		}	
	}

	public function addNewStockzonesOrder($order){
		//prepare required API data.. 	
		$data = [
    		"method_name"   => "saveOrder",
    		"data"          => $order,
    		"language_code" =>"en"
    	];
        return $this->connectAPI($data);
	}

	public function addNewOrderLocally($stockzones_order_id, $expandcart_order_id){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "stockzones_order` 
			SET stockzones_order_id = '" .$this->db->escape($stockzones_order_id) . "',
			expandcart_order_id = " . (int)$expandcart_order_id );
	}

	public function getProductsSlug($order_id){
		return $this->db->query("
			SELECT GROUP_CONCAT(pd.slug SEPARATOR ', ') AS slug 
			FROM product_description pd JOIN order_product op ON op.product_id = pd.product_id
			INNER JOIN stockzones_product sp ON sp.expandcart_product_id = op.product_id
			WHERE language_id = 1 AND order_id = " . (int)$order_id . ";")->row['slug'];
	}

	public function getStockzonesOrderDetails($order){
		//prepare required API data.. 	
		$data = [
    		"method_name" => "orderDetails",
    		"data" => $order
    	];

        $response = $this->connectAPI($data);
        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : ['error' => ($result['response']['message'] ?: 'Error: Something went wrong, Please try again.')];      
	}

	//get stockzones order products slugs & details....
	public function getOrderStockzonesProducts($order_id, $data){
		$stockzones_products = $this->db->query("
			SELECT mp.* , op.`quantity` as order_quantity
			FROM product p 
			JOIN product_description pd ON p.product_id = pd.product_id
			JOIN order_product op ON op.product_id = p.product_id
			INNER JOIN stockzones_product mp ON mp.expandcart_product_id = p.product_id
			WHERE pd.`language_id` = 1 and order_id = " . (int)$order_id)->rows;

		$filtered_stockzones_products['products'] = [];

		foreach ($stockzones_products as $key => $product) {
			$filtered_stockzones_products['products'][$key]['product_id'] = $product['parent_id'];			
			$filtered_stockzones_products['products'][$key]['variant_id'] = $product['stockzones_product_id'];			
			$filtered_stockzones_products['products'][$key]['orignal_variation_id'] = $product['orignal_variation_id'];			
			$filtered_stockzones_products['products'][$key]['product_name'] = $product['name'];
			$filtered_stockzones_products['products'][$key]['qty'] = $product['order_quantity'];			
			$filtered_stockzones_products['products'][$key]['slug'] = $product['slug'];
		}

		//call getOrderAddress
        $this->load->model('module/stockzones/address');
        $products = $this->model_module_stockzones_address->getOrderAddress($filtered_stockzones_products, $data)['product_detail'];

        foreach ($products as $key => $product) {        	
        	$products[$key]['shipping_name']             = $product['shipping'][0]['method'];
        	$products[$key]['shipping_service_provider'] = $product['shipping'][0]['Provider'];
        	$products[$key]['total_shipping_price']      = $product['shipping'][0]['TotalAmount']['Value'];
        	$products[$key]['shipping_vat_amount']       = 0;
        }
        return $products;
 	}
}
