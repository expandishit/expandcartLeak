<?php

class ModelSellerSeller extends Model
{
	/**
	* return the current Singular seller title whether custom or default
	*/
	public function getSellerTitle(){

		//Get current seller title in DB, setting table
		$msconf_seller_title = $this->config->get('msconf_seller_title');

		//Get current store language
		$current_lng_id = $this->config->get('config_language_id');

		// //if null or empty throw exception
		// if( !$msconf_seller_title || empty($msconf_seller_title)){
		// 	throw new Exception("Seller title can't be NULL or EMPTY");
		// }

		// //check if default value - Admin didn't change the default title
		// if( is_string($msconf_seller_title) && strcasecmp($msconf_seller_title , 'seller') == 0 ){
		// 	return $this->language->get('ms_seller_title');
		// }

		//if custom title
		if( isset($msconf_seller_title[$current_lng_id]['single']) ){

			if( !empty($msconf_seller_title[$current_lng_id]['single']) )
				return $msconf_seller_title[$current_lng_id]['single'];
			else
				return $this->language->get('ms_seller_title');
		}
		else
			return $this->language->get('ms_seller_title');
			
		// throw new Exception("Array key doesn't exist");
	}

	/**
	* return the current Plural seller title whether custom or default
	*/
	public function getSellersTitle(){

		//Get current seller title in DB, setting table
		$msconf_seller_title = $this->config->get('msconf_seller_title');

		//Get current store language
		$current_lng_id = $this->config->get('config_language_id');

		// //if null or empty throw exception
		// if( !$msconf_seller_title || empty($msconf_seller_title)){
		// 	throw new Exception("Seller title can't be NULL or EMPTY");
		// }

		// //check if default value - Admin didn't change the default title
		// if( is_string($msconf_seller_title) && strcasecmp($msconf_seller_title , 'seller') == 0 ){
		// 	return $this->language->get('ms_sellers_title');
		// }

		//if custom title
		if( isset($msconf_seller_title[$current_lng_id]['multi']) ){

			if( !empty($msconf_seller_title[$current_lng_id]['multi']) )
				return $msconf_seller_title[$current_lng_id]['multi'];
			else
				return $this->language->get('ms_sellers_title');
		}
		else
			return $this->language->get('ms_sellers_title');

		// throw new Exception("Array key doesn't exist");
	}

	/**
	* return the current Singular product title whether custom or default
	*/
	public function getProductTitle(){
        $this->language->load_json('multiseller/multiseller');
        //Get current product title in DB, setting table
		$msconf_product_title = $this->config->get('msconf_product_title');

		//Get current store language
		$current_lng_id = $this->config->get('config_language_id');

		// //if null or empty throw exception
		// if( !$msconf_product_title || empty($msconf_product_title)){
		// 	throw new Exception("Product title can't be NULL or EMPTY");
		// }

		// //check if default value - Admin didn't change the default title
		// if( is_string($msconf_product_title) && strcasecmp($msconf_product_title , 'products') == 0 ){
		// 	return $this->language->get('ms_product_title');
		// }

		//if custom title
		if( isset($msconf_product_title[$current_lng_id]['single']) ){

			if( !empty($msconf_product_title[$current_lng_id]['single']) )
				return $msconf_product_title[$current_lng_id]['single'];
			else
				return $this->language->get('ms_product_title');
		}
		else
			return $this->language->get('ms_product_title');

		// throw new Exception("Array key doesn't exist");
	}

	/**
	* return the current Plural product title whether custom or default
	*/
	public function getProductsTitle(){
		//Get current product title in DB, setting table
		$msconf_product_title = $this->config->get('msconf_product_title');

		//Get current store language
		$current_lng_id = $this->config->get('config_language_id');

		// //if null or empty throw exception
		// if( !$msconf_product_title || empty($msconf_product_title)){
		// 	throw new Exception("Product title can't be NULL or EMPTY");
		// }

		// //check if default value - Admin didn't change the default title
		// if( is_string($msconf_product_title) && strcasecmp($msconf_product_title , 'products') == 0 ){
		// 	return $this->language->get('ms_products_title');
		// }

		$this->language->load_json('multiseller/multiseller');

		//if custom title
		if( isset($msconf_product_title[$current_lng_id]['multi']) ){
						
			if( !empty($msconf_product_title[$current_lng_id]['multi']) )
				return $msconf_product_title[$current_lng_id]['multi'];
			else
				return $this->language->get('ms_products_title');
		}
		else
			return $this->language->get('ms_products_title');

		// throw new Exception("Array key doesn't exist");
	}


	public function getSellerGroupId($seller_id = 0){
		return $this->db->query("
			SELECT seller_group 
			FROM `" . DB_PREFIX . "ms_seller` WHERE seller_id = " . (int)$seller_id
		)->row['seller_group'];
	}
}
