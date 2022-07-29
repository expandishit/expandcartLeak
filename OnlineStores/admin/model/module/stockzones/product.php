<?php
require_once('entity.php');

class ModelModulestockzonesProduct extends ModelModuleStockzonesEntity {


    /**
	 * [POST] Get stockzones Products from their API
	 *
	 * @return 
	 */
    public function getstockzonesProducts(){    	
    	$data = [
    		"method_name" => "getProductList",
    		"data" => [ "page" => "1" , "sort_by"  => "asc/desc" ],
    		"language_code" => "en"
    	];
        //connect API to get categories list
        $response = $this->connectAPI($data);
        
        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : ['error' => $result['response']['message'] ?: 'Error: Something went wrong, Please try again.'];      
 
    }

    /**
     * Add a stockzones product to:
     * 1- Expandcart product tables 
     * 2- stockzones_product table
     */
    public function addProduct($data){
        //1- Expandcart product tables
        $this->load->model('catalog/product');
        $expandcart_product_id = $this->model_catalog_product->addproduct($data);

        //2- stockzones_product table
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "stockzones_product` 
            SET 
            `expandcart_product_id` = " . (int)$expandcart_product_id . ",
            `stockzones_product_id` = '" . $this->db->escape($data['stockzones_product_id']) . "',
            `parent_id` = '" . $this->db->escape($data['parent_id']) . "',
            `orignal_variation_id` = '" . $this->db->escape($data['orignal_variation_id']) . "',
            `name` = '" . $this->db->escape($data['name']) . "',
            `slug` = '" . $this->db->escape($data['slug']) . "',
            `wholesaler_id` = '" . $this->db->escape($data['wholesaler_id']) . "'
            ");
    }

    public function deleteStockzonesProducts(){
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product`
            WHERE stockzones_product_id IN (SELECT expandcart_product_id FROM `".DB_PREFIX."stockzones_product`)");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_description`
            WHERE stockzones_product_id IN (SELECT expandcart_product_id FROM `".DB_PREFIX."stockzones_product`)");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "stockzones_product`;");
    }

    public function findBystockzonesId($stockzones_product_id){
        return $this->db->query("
            SELECT expandcart_product_id 
            FROM `" . DB_PREFIX . "stockzones_product`
            WHERE stockzones_product_id = '" . $this->db->escape($stockzones_product_id) . "'
            ")->row['expandcart_product_id'] ?: 0;
    }

 
}
