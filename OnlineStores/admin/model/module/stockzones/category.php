<?php
require_once 'entity.php';

class ModelModuleStockzonesCategory extends ModelModuleStockzonesEntity {
    
    /**
	* [POST] Get stockzones Categories from stockzones API
	*
	* @return 
	*/
    public function getstockzonesCategories(){ 
        //prepare required API data..  	
		$data = [
    		"method_name" => "getCategories",
    		"data" => [],
    		"language_code" => "en"
    	];

        //connect API to get categories list
        $response = $this->connectAPI($data);

        return  $response['data']['status'] == 'success' ?  $response['data']['result'] : ['error' => $result['response']['message'] ?: 'Error: Something went wrong, Please try again.'];      
    }


    /**
     * Add a stockzones category to:
     * 1- Expandcart category tables 
     * 2- stockzones_category table
     */
    public function addCategory($data){
        //1- Expandcart category tables
        $this->load->model('catalog/category');
        $expandcart_category_id = $this->model_catalog_category->addCategory($data);

        //2- stockzones_category table
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "stockzones_category` 
            SET 
            `expandcart_category_id` = " . (int)$expandcart_category_id . ",
            `stockzones_category_id` = '" . $this->db->escape($data['stockzones_category_id']) . "',
            `name` = '" . $this->db->escape($data['stockzones_name']) . "',
            `slug` = '" . $this->db->escape($data['stockzones_slug']) . "',
            `parent_id` = " . (int)$data['stockzones_parent_id'] . ",
            `created`   = '" . $this->db->escape($data['stockzones_created']) . "';
            ");
    }

    public function findBystockzonesId($stockzones_category_id){
        return $this->db->query("
            SELECT expandcart_category_id 
            FROM `" . DB_PREFIX . "stockzones_category`
            WHERE stockzones_category_id = '" . $this->db->escape($stockzones_category_id) . "'
            ")->row['expandcart_category_id'] ?: 0;
    }

    public function findByExpandCartId($expandcart_category_id){
        return $this->db->query("
            SELECT stockzones_category_id 
            FROM `" . DB_PREFIX . "stockzones_category`
            WHERE expandcart_category_id = " . (int)$expandcart_category_id . "
            ")->row['stockzones_category_id'] ?: 0;
    }
 
}
