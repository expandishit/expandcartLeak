<?php
/******************************************************
 * @package Flash Sales for Opencart 1.5.x
 * @version 1.0
 * @author ecomteck (http://ecomteck.com)
 * @copyright	Copyright (C) May 2013 ecomteck.com <@emai:ecomteck@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerModuleEcdeals extends Controller {
	protected function index($setting) {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		static $module = 0;
		if(is_numeric($setting)) {
			$setting = $this->getModuleSettings( $setting );

		}
		$current_store_id = $this->config->get('config_store_id');
		$stores = isset($setting['store_id'])?$setting['store_id']:array();
		if(!empty($stores) && !in_array($current_store_id, $stores)){
			return;
		}
		/*Return deals carousel*/

		if(!isset($setting['product_id']) && !isset($setting['product']) ){
			return $this->dealCarousel($module, $setting);
		}
		$this->language->load('module/ecdeals');
		$this->load->model('ecdeals/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$setting = $this->defaultConfig($setting);


		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecdeals.css')) {
			$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css');
			$this->data["stylesheet"] = 'catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css';
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/ecdeals.css');
			$this->data["stylesheet"] = 'catalog/view/theme/default/stylesheet/ecdeals.css';
		}
		$this->document->addScript('catalog/view/javascript/ecdeals/countdown.js');
	    $this->data["script"] = 'catalog/view/javascript/ecdeals/countdown.js';

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         	$this->data['base'] = $this->config->get('config_ssl');
	    } else {
	        $this->data['base'] = $this->config->get('config_url');
	    }

	    $this->data['module'] = $module++;

	    $lang_id = $this->config->get('config_language_id');
    	if(isset($setting['product'])){
    		$product = $setting['product'];
    		$this->data['module_id'] = $product['product_id'];
    		$this->data['product_id'] = $product['product_id'];
    	}else{
    		$product = $this->getItemDeal($setting['product_id'], $setting, true);
    		$this->data['module_id'] = $setting['product_id'];
    		$this->data['product_id'] = $setting['product_id'];
    	}

    	if(isset($product['is_expired'])) {
    		$this->data["is_expired"] = $product['is_expired'];
    	} else {
    		$date_diff = $this->dateDiff( $product['date_end'] );
			$this->data["is_expired"] = ($date_diff <= 0) ? true:false ;
    	}
    	
		
    	$this->data["deal"] = $product;

    	$this->data['date_format'] = $setting['date_format'];
    	$this->data['enable_deal_image'] = $setting['enable_deal_image'];
    	$this->data['show_expired_deal'] = $setting['show_expired_deal'];
    	$this->data['allow_buy'] = $setting['allow_buy'];
    	$this->data['enable_deal_name'] = $setting['enable_deal_name'];
    	$this->data['enable_deal_price'] = $setting['enable_deal_price'];
    	$this->data['enable_buy_now'] = $setting['enable_buy_now'];
    	$this->data['enable_discount'] = $setting['enable_discount'];
    	$this->data['deal_image_width'] = $setting['deal_image_width'];
    	$this->data['deal_image_height'] = $setting['deal_image_height'];
    	$this->data['show_rating'] = $setting['show_rating'];
    	$this->data['deal_block_width'] = $setting['deal_block_width'];
    	$this->data['show_social'] = $setting['show_social'];
    	$this->data['show_notify_message'] = $setting['show_notify_message'];
    	$this->data['show_reward_point'] = $setting['show_reward_point'];
    	$this->data['show_stock_bar'] = isset($setting['show_stock_bar'])?$setting['show_stock_bar']:1;
    	$this->data['cols'] = $setting['item_cols'];
    	$this->data['prefix'] = $setting['prefix'];
    	$title = isset( $setting['title'][$this->config->get('config_language_id')] ) ? $setting['title'][$this->config->get('config_language_id')]:"";
    	$description = isset( $setting['description'][$this->config->get('config_language_id')] ) ? $setting['description'][$this->config->get('config_language_id')]:"";

    	$this->data['heading_title'] = "";
		if($setting['show_title']){
			$this->data['heading_title'] = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
		}
		$this->data['module_description'] = "";
		if($setting['show_module_description']){
			$this->data['module_description'] = html_entity_decode( $description, ENT_QUOTES, 'UTF-8' );
		}
		


    	/*Enable today deal mode?*/
    	$route = isset($this->request->get['route'])?$this->request->get['route']:"";

    	if($route == "ecdeals/sales" && isset($this->request->get['status']) && $this->request->get['status'] == "past") {
    		$this->data['show_expired_deal'] = 1;
    	}

    	if($route == "product/category" || $route == "ecdeals/sales"){
    		$deal_template = isset($setting['list_deal_template'])?$setting['list_deal_template']:'deal_on_listing';
    	}
    	else{
    		$deal_template = isset($setting['deal_template'])?$setting['deal_template']:'deal';
    		$this->data['is_product_item'] = 1;
    	}
    	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecdeals/'.$deal_template.'.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/ecdeals/'.$deal_template.'.tpl';
		} else {
			$this->template = 'default/template/module/ecdeals/'.$deal_template.'.tpl';
		}

    	$output = $this->render();
	    
		return $output;
	 
	}
	protected function dealCarousel($module = 0, $setting = array()){

		$this->language->load('module/ecdeals');
		$this->load->model('ecdeals/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$setting = $this->defaultConfig($setting);
		$setting['enable_carousel'] = isset($setting['enable_carousel'])?$setting['enable_carousel']:1; /*carousel | deals*/

		if($setting['enable_carousel']){
			$this->document->addScript('catalog/view/javascript/ecdeals/jquery.carouFredSel-6.2.1.js');
			/* optionally include helper plugins */
			$this->document->addScript('catalog/view/javascript/ecdeals/helper-plugins/jquery.mousewheel.min.js');
			$this->document->addScript('catalog/view/javascript/ecdeals/helper-plugins/jquery.touchSwipe.min.js');
		}
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecdeals.css')) {
			$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/ecdeals.css');
		}
		$this->document->addScript('catalog/view/javascript/ecdeals/countdown.js');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         	$this->data['base'] = $this->config->get('config_ssl');
	    } else {
	        $this->data['base'] = $this->config->get('config_url');
	    }

	    $title = isset( $setting['title'][$this->config->get('config_language_id')] ) ? $setting['title'][$this->config->get('config_language_id')]:"";
    	$description = isset( $setting['description'][$this->config->get('config_language_id')] ) ? $setting['description'][$this->config->get('config_language_id')]:"";

    	$this->data['heading_title'] = "";
		if($setting['show_module_title']){
			$this->data['heading_title'] = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );
		}
		$this->data['module_description'] = "";
		if($setting['show_module_description']){
			$this->data['module_description'] = html_entity_decode( $description, ENT_QUOTES, 'UTF-8' );
		}
		

	    $this->data['enable_deal_image'] = $setting['enable_deal_image'];
    	$this->data['enable_deal_name'] = $setting['enable_deal_name'];
    	$this->data['enable_deal_price'] = $setting['enable_deal_price'];
    	$this->data['enable_buy_now'] = $setting['enable_buy_now'];
    	$this->data['enable_discount'] = $setting['enable_discount'];
    	$this->data['deal_image_width'] = $setting['deal_image_width'];
    	$this->data['deal_image_height'] = $setting['deal_image_height'];
    	$this->data['show_rating'] = $setting['show_rating'];
    	$this->data['show_social'] = $setting['show_social'];
    	$this->data['show_notify_message'] = $setting['show_notify_message'];
    	$this->data['show_reward_point'] = $setting['show_reward_point'];
    	$this->data['cols'] = $setting['item_cols'];
    	$this->data['cols_small'] = $setting['cols_small'];
    	$this->data['cols_mini'] = $setting['cols_mini'];
    	$this->data['number_scroll'] = $setting['number_scroll'];
    	$this->data['prefix'] = $setting['prefix'];

    	/*Setting carousel*/
		$this->data['carousel_auto'] = $setting['carousel_auto'];
		$this->data['carousel_mousewhell'] = $setting['carousel_mousewhell'];
		$this->data['carousel_responsive'] = $setting['carousel_responsive'];
		$this->data['carousel_item_width'] = $setting['carousel_item_width'];
		$this->data['carousel_min_items'] = $setting['carousel_min_items'];
		$this->data['carousel_max_items'] = $setting['carousel_max_items'];

		$this->data['duration'] = isset($setting['duration'])?$setting['duration']:1000;
	    $this->data['scroll_effect'] = isset($setting['scroll_effect'])?$setting['scroll_effect']:'scroll';

	    $this->data['module'] = $module++;

	    $lang_id = $this->config->get('config_language_id');
	    $general = $this->config->get('ecdeals_setting');
	    $general = is_array($general)?$general: unserialize($general);
	    
	    $general = array_merge( $setting, $general);
	    $route = isset($this->request->get['route'])?$this->request->get['route']:"";

	    $limit = isset($setting['limit_deals'])?(int)$setting['limit_deals']: 12;
	    $show_featured = isset($setting['show_featured'])?(int)$setting['show_featured']: 2;
	    $category_mode = isset($setting['category_mode'])?(int)$setting['category_mode']: 0;
	    $deal_mode = isset($setting['deal_mode'])?$setting['deal_mode']: "";

	    $categories = isset($setting['category_id'])?$setting['category_id']:array();
	    if(count($categories) == 1 && empty($categories[0])){
	    	$categories = array();
	    }

	    if($category_mode && $route == "product/category"){
	    	$categories = array();
	    	if (isset($this->request->get['path'])) {					
				$parts = explode('_', (string)$this->request->get['path']);
				$categories[] = (int)array_pop($parts);
			}
	    }

	    $sort = 'ps.date_end';
	    $order = 'ASC';

	    if($deal_mode == "new") {
	    	$sort = 'p.date_added';
	    	$order = 'DESC';
	    }

	    $data = array(	
	    				"filter_category_id" => $categories,
	    				"filter_featured" => $show_featured,
	    				"filter_deal_status" => $deal_mode,
	    				"sort" =>  $sort,
	    				"order" => $order,
	    				"start" => 0,
	    				"limit" => $limit);
	    $deals = $this->model_ecdeals_product->getProductSpecials( $data );
	    $this->data['deals'] = array();
	    if($deals){
	    	foreach($deals as $deal){
	    		$this->data['deals'][] = $this->getItemDeal( $deal['product_id'], $general, false );
	    	}
	    }

	    if($setting['enable_carousel']){
	    	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecdeals/carousel.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/ecdeals/carousel.tpl';
			} else {
				$this->template = 'default/template/module/ecdeals/carousel.tpl';
			}
	    }else{
	    	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecdeals/deals.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/ecdeals/deals.tpl';
			} else {
				$this->template = 'default/template/module/ecdeals/deals.tpl';
			}
	    }
	   

    	$output = $this->render();
	    
		return $output;
	}
	/*Call function to get a flash sale or a daily deal item with countdown timmer*/
	public function deal($args = null){
		$custom_settings = array();
		if(is_array($args)){
			$product_id = isset($args[0])?$args[0]:0;
			$custom_settings = isset($args[1])?$args[1]:array();

		}else{
			$product_id = (int)$args;
		}
		$setting = $this->config->get('ecdeals_setting');
		$setting = is_array($setting)?$setting:unserialize($setting);
		
	    $setting = $this->defaultConfig($setting);
	    $setting['show_viewmore'] = false;
	    $setting['module_id'] = 9999;
	    $setting['show_name'] = false;
	    $route = isset($this->request->get['route'])?$this->request->get['route']:"";

		if(empty($product_id))
			$product_id = isset($this->request->get['product_id'])?$this->request->get['product_id']:"0";
		$setting['mini_mode'] = isset($custom_settings['mini_mode'])?$custom_settings['mini_mode']:$setting['mini_mode'];
		$setting['product_id'] = $product_id;
		$setting['show_image'] = false;
		$setting['show_description'] = false;
		$setting['prefix'] = "detail_flashsale ";
		$setting['show_viewmore'] = true;
		$setting['show_name'] = true;
		if($setting['mini_mode']){
			$setting = array_merge($setting, $custom_settings);
		
			return $this->index( $setting );	
		}
		return ;
	}

	public function getItemDeal($product_id = null, $setting = array(), $check_category = true){
		$category_id = array();
		if($check_category) {
			$category_id = isset($setting['show_category_id'])?$setting['show_category_id']:array();
		}
		$show_expired_deal = isset($setting['show_expired_deal'])?$setting['show_expired_deal']: 0;
		$deal = $this->model_ecdeals_product->getDeal($product_id, $category_id, $show_expired_deal);

		if(!$deal)
			return false;

		$order_status_id = isset($setting['order_status_id'])?$setting['order_status_id']:5;
		$bought = $this->model_ecdeals_product->getTotalBought($deal['product_id'], $order_status_id );
		$bought = empty($bought)?0:$bought;
		$save_price = (float)$deal['price'] - (float)$deal['special'];
		$discount = round(($save_price/$deal['price'])*100);

		$save_price = $this->currency->format($this->tax->calculate($save_price, $deal['tax_class_id'], $this->config->get('config_tax')));

		if ($deal['image'] && isset($setting['deal_image_width']) && $setting['deal_image_height']) {
			$image = $this->model_tool_image->resize($deal['image'], $setting['deal_image_width'], $setting['deal_image_height']);
		} else {
			$image = false;
		}
					
		if ($this->customer->isCustomerAllowedToViewPrice()) {
			$price = $this->currency->format($this->tax->calculate($deal['price'], $deal['tax_class_id'], $this->config->get('config_tax')));
		} else {
			$price = false;
		}
				
		if ((float)$deal['special']) {
			$special = $this->currency->format($this->tax->calculate($deal['special'], $deal['tax_class_id'], $this->config->get('config_tax')));
		} else {
			$special = false;
		}

		if ($this->config->get('config_review_status')) {
			$rating = $deal['rating'];
		} else {
			$rating = false;
		}
		$date_end_string = isset($deal['date_end'])?$deal['date_end']:"";

		$date_end_time = strtotime($deal['date_end']);

		$date_diff = $this->dateDiff( $deal['date_end'] );


		$is_expired = ($date_diff <= 0) ? true:false ;

		if($deal['date_end'] == "0000-00-00" || $deal['date_end'] == "0000-00-00 00:00:00") {
			$is_expired = false;
		}

		$date_diff = floor($date_diff/3600/24) ;//- 1;

		$date_diff = ($date_diff >= 0)?$date_diff:0;

		$is_upcomming = false;
		$current_time = time() + 25200;
		if ($deal['date_start'] > date('Y-m-d H:i:s', $current_time)) {
			$is_upcomming = true;
		}

		$notify_message = array();

		if($deal['quantity'] <= $setting['min_quantity']){
			$tmp_notify = $this->language->get("text_notify_quantity");
			$notify_message[] = sprintf($tmp_notify, $deal['quantity']);
		}
		if($date_diff <= $setting['min_day'] && $date_end_time !== false){
			$tmp_notify = $this->language->get("text_notify_day");
			$notify_message[] = sprintf($tmp_notify, $date_diff);
		}

		$product = array(
			'product_id' => $deal['product_id'],
			'discount' 	 => $discount,
			'is_expired' => $is_expired,
			'is_upcomming' => $is_upcomming,
			'bought'	 => $bought,
			'thumb'   	 => $image,
			'name'    	 => $deal['name'],
			'quantity'	 => $deal['quantity'],
			'reward'	 => $deal['reward'],
			'price'   	 => $price,
			'special' 	 => $special,
			'rating'     => $rating,
			'save_price' => $save_price,
			'date_start' => $deal['date_start'],
			'date_end_string' => $date_end_string,
			'date_end'	 => $date_end_string,
			'description'=> (html_entity_decode($deal['description'], ENT_QUOTES, 'UTF-8')),
			'reviews'    => sprintf($this->language->get('text_reviews'), (int)$deal['reviews']),
			'link'    	 => $this->url->link('product/product', 'product_id=' . $deal['product_id']),
			'notify_message' => $notify_message
		);

		return $product;
	}

	public function timer($args = null){
		$setting = $this->config->get('ecdeals_setting');
		$setting = is_array($setting)?$setting:unserialize($setting);
		
	    $setting = $this->defaultConfig($setting);
	    if(!$setting['mini_mode']){
			return false;
	    }
		$check_category = true;
		$category_id = array();
		if($check_category) {
			$category_id = isset($setting['show_category_id'])?$setting['show_category_id']:array();
		}

		$custom_settings = array();
		if(is_array($args)){
			$product_id = isset($args[0])?$args[0]:0;
			$custom_settings = isset($args[1])?$args[1]:array();

		}else{
			$product_id = (int)$args;
		}

		$this->language->load('module/ecdeals');
		$this->load->model('ecdeals/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$deal = $this->model_ecdeals_product->getDeal($product_id, $category_id, false);

		if(!$deal)
			return false;

		$save_price = (float)$deal['price'] - (float)$deal['special'];
		$discount = round(($save_price/$deal['price'])*100);

		if ($this->customer->isCustomerAllowedToViewPrice()) {
			$price = $this->currency->format($this->tax->calculate($deal['price'], $deal['tax_class_id'], $this->config->get('config_tax')));
		} else {
			$price = false;
		}
				
		if ((float)$deal['special']) {
			$special = $this->currency->format($this->tax->calculate($deal['special'], $deal['tax_class_id'], $this->config->get('config_tax')));
		} else {
			$special = false;
		}

		$date_end_string = isset($deal['date_end'])?$deal['date_end']:"";

		$date_end_time = strtotime($deal['date_end']);

		$date_diff = $this->dateDiff( $deal['date_end'] );


		$is_expired = ($date_diff <= 0) ? true:false ;

		if($deal['date_end'] == "0000-00-00" || $deal['date_end'] == "0000-00-00 00:00:00") {
			$is_expired = false;
		}

		if($is_expired) //If expired date, return null
			return;

		$date_diff = floor($date_diff/3600/24) ;//- 1;

		$date_diff = ($date_diff >= 0)?$date_diff:0;

		$is_upcomming = false;
		$current_time = time() + 25200;
		if ($deal['date_start'] > date('Y-m-d H:i:s', $current_time)) {
			$is_upcomming = true;
		}

		$product = array(
			'product_id' => $deal['product_id'],
			'quantity'	 => $deal['quantity'],
			'is_expired' => $is_expired,
			'is_upcomming' => $is_upcomming,
			'date_start' => $deal['date_start'],
			'date_end_string' => $date_end_string,
			'date_end'	 => $date_end_string,
			'price'   	 => $price,
			'special' 	 => $special,
			'discount'     => $discount,
			'save_price' => $save_price
		);

		$this->data['deal'] = $product;

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecdeals.css')) {
			$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css');
			$this->data["stylesheet"] = 'catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecdeals.css';
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/ecdeals.css');
			$this->data["stylesheet"] = 'catalog/view/theme/default/stylesheet/ecdeals.css';
		}
		$this->document->addScript('catalog/view/javascript/ecdeals/countdown.js');
	    $this->data["script"] = 'catalog/view/javascript/ecdeals/countdown.js';

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         	$this->data['base'] = $this->config->get('config_ssl');
	    } else {
	        $this->data['base'] = $this->config->get('config_url');
	    }

	    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecdeals/timer.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/ecdeals/timer.tpl';
		} else {
			$this->template = 'default/template/module/ecdeals/timer.tpl';
		}
    	$output = $this->render();
	    
		return $output;
	}

	public function getBarClass($percent = 0){
		$class = "";
		if((float)$percent <= 50) {
			$class = "w40";
		} else if((float)$percent < 100 && (float)$percent > 50) {
			$class = "w50";
		} else if((float)$percent == 100) {
			$class = "w100";
		}

		return $class;
	}

	protected function dateDiff( $date ){
		$time = time() + 25200;
		$todays_date = date("Y-m-d H:i:s", $time);
		$today = strtotime($todays_date); 
		$expiration_date = strtotime($date);
		$diff = $expiration_date - $today;
		return $diff;
	}

	protected function getModuleSettings( $module = null, $store_id = 0) {
		$setting = array();
		if($module != null) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `group` = 'ecdeals' AND `store_id`=".(int)$store_id);
			if($query->num_rows > 0) {
				foreach ($query->rows as $row) {
					if(strpos($row['key'], "ecdeals_".$module."_") !== false) {
						$key = str_replace("ecdeals_".$module."_","", $row['key']);
						$setting[ $key ] = $this->maybe_unserialize( $row['value'] );
					}
				}
			}
			return $setting;
		}
		return false;
	}

	protected function maybe_unserialize( $original ) {
	        if ( $this->is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
	                return @unserialize( $original );
	        return $original;
	}
	/**
	 * Check value to find if it was serialized.
	 *
	 * If $data is not an string, then returned value will always be false.
	 * Serialized data is always a string.
	 *
	 * @since 2.0.5
	 *
	 * @param mixed $data Value to check to see if was serialized.
	 * @param bool $strict Optional. Whether to be strict about the end of the string. Defaults true.
	 * @return bool False if not serialized and true if it was.
	 */
	protected function is_serialized( $data, $strict = true ) {
	        // if it isn't a string, it isn't serialized
	        if ( ! is_string( $data ) )
	                return false;
	        $data = trim( $data );
	        if ( 'N;' == $data )
	                return true;
	        $length = strlen( $data );
	        if ( $length < 4 )
	                return false;
	        if ( ':' !== $data[1] )
	                return false;
	        if ( $strict ) {
	                $lastc = $data[ $length - 1 ];
	                if ( ';' !== $lastc && '}' !== $lastc )
	                        return false;
	        } else {
	                $semicolon = strpos( $data, ';' );
	                $brace     = strpos( $data, '}' );
	                // Either ; or } must exist.
	                if ( false === $semicolon && false === $brace )
	                        return false;
	                // But neither must be in the first X characters.
	                if ( false !== $semicolon && $semicolon < 3 )
	                        return false;
	                if ( false !== $brace && $brace < 4 )
	                        return false;
	        }
	        $token = $data[0];
	        switch ( $token ) {
	                case 's' :
	                        if ( $strict ) {
	                                if ( '"' !== $data[ $length - 2 ] )
	                                        return false;
	                        } elseif ( false === strpos( $data, '"' ) ) {
	                                return false;
	                        }
	                        // or else fall through
	                case 'a' :
	                case 'O' :
	                        return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
	                case 'b' :
	                case 'i' :
	                case 'd' :
	                        $end = $strict ? '$' : '';
	                        return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	        }
	        return false;
	}
	protected function defaultConfig($setting = array()){
        $defaults = array('name'	=> 'Deal Item',
        	'product_id' 	=> 0,
        	'show_expired_deal' => 0,
        	'allow_buy' => 1,
        	'min_quantity' => 6,
        	'min_day' => 3,
            'description'				=> '',
            'no_border'			=> 0,
            'date_start'		=> '000-00-00',
            'enable_message'	=> '0',
            'date_end'	=> '000-00-00',
            'item_cols'	=> 3,
            'cols_small' => 2,
            'cols_mini' => 1,
            'amount'		=> 0,
            'percent'			=> 0,
            'apply_for'	=> 'category',
            'category_id'			=> '',
            'products'			=> '',
            'module_width' => '100%',
            'module_height' => '100%',
            'prefix'	=> '',
            'deal_mode' => '',
            'number_scroll' => 1,
            'carousel_auto' => 0,
            'carousel_mousewhell' => 1,
            'carousel_responsive' => 1,
            'carousel_item_width' => 400,
            'carousel_min_items' => 1,
            'carousel_max_items' => 6,
            'show_module_description' => 1,
            'show_title'	=> 1,
            'show_image'	=> 1,
            'show_name'		=> 1,
            'show_description' => 1,
            'show_sale_off' => 1,
            'show_expire_date' => 1,
            'mini_mode'			=> 0,
            'order_status_id'	=> 5,
            'show_social'	=> 1,
            'show_rating'		=> 1,
            'show_notify_message' => 1,
            'min_quantity'	=> 10,
            'min_day'		=> 3,
            'deal_block_width' => '340px',
            'show_reward_point' => 1,
            'enable_deal_image' => 1,
            'enable_deal_name' => 1,
            'enable_deal_price' => 1,
            'enable_buy_now' => 1,
            'enable_discount' => 1,
            'date_format' => 'Y-m-d H:i:s',
            'deal_image_width' => "150",
            'deal_image_height' => "200",
            'image_width'					=> '600',
            'image_height'				=> '400'
        );
        if(!empty($setting)){
            return array_merge($defaults, $setting);
        }
        else{
            return $defaults;
        }
    }
}
?>
