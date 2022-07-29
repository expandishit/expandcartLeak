<?php
/******************************************************
 * @package Flash Sales for Opencart 1.5.x
 * @version 1.0
 * @author ecomteck (http://ecomteck.com)
 * @copyright	Copyright (C) May 2013 ecomteck.com <@emai:ecomteck@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerModuleEcflashsale extends Controller {
	protected function index($setting) {
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'advanced_deals'");

        if(!$queryMultiseller->num_rows) {
            $this->redirect($this->url->link('common/home', '', 'SSL'));
            return;
        }

		static $module = 0;

		$current_store_id = $this->config->get('config_store_id');
		$stores = isset($setting['store_id'])?$setting['store_id']:array();
		if(!empty($stores) && !in_array($current_store_id, $stores)){
			return;
		}
		$this->language->load('module/ecflashsale');
		$this->load->model('ecflashsale/product');
		$this->load->model('ecflashsale/flashsale');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$setting = $this->defaultConfig($setting);

		$flashsale = array();

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecflashsale.css')) {
			$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css');
			$this->data["stylesheet"] = 'catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css';
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/ecflashsale.css');
			$this->data["stylesheet"] = 'catalog/view/theme/default/stylesheet/ecflashsale.css';
		}
		$this->document->addScript('catalog/view/javascript/ecflashsale/countdown.js');
	    $this->data["script"] = 'catalog/view/javascript/ecflashsale/countdown.js';
	    $this->data["popup_mode"] = false;

	    if(isset($setting['show_mode']) && $setting['show_mode'] == "popup") {

			$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');
			$this->data['popup_mode'] = true;
			$this->data['popup_width'] = isset($setting['popup_width'])?$setting['popup_width']:'60%';
	    }

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         	$this->data['base'] = $this->config->get('config_ssl');
	    } else {
	        $this->data['base'] = $this->config->get('config_url');
	    }

	    $this->data['module'] = $module++;

	    /*End Check module mode is mini mode, call mini deal html*/

	    $lang_id = $this->config->get('config_language_id');
	    $discount_setting = $this->config->get('ecflashsale_setting');

	    $discount_setting = $this->defaultConfig($discount_setting);
	    $ecflashsale_id = isset($setting['flashsale_id'])?(int)$setting['flashsale_id']:0;

	    /*Check module enabled*/
	    $enabled = isset($discount_setting["status"])?$discount_setting['status']: 1;
	    if(!$enabled)
	    	return;

	    /*Check product detail mode was called?*/
	    $route = isset($this->request->get['route'])?$this->request->get['route']:"";
	    $this->data['is_product_item'] = false;
	    if(isset($setting['product_id']) && !empty($setting['product_id'])){
	    	$this->data['is_product_item'] = true;
	    	$discount_setting['no_border'] = isset($setting['no_border'])?$setting['no_border']:$discount_setting['no_border'];
	    	$flashsale = $this->model_ecflashsale_flashsale->getFlashsaleByProduct( $setting['product_id'] );
	    	$is_flashsale = $this->model_ecflashsale_flashsale->checkProductFlashSale( $setting['product_id'], $flashsale );

	    	/*Check config mini mode was disable and product is no flash sale then return null*/
	    	if($setting['mini_mode'] != 2) {
		    	if( (!$setting['mini_mode'] && !$is_flashsale) || ($setting['show_deal_flashsale'] && !$is_flashsale) ){
		    		return;
		    	} elseif($setting['mini_mode'] && $is_flashsale && $route != "product/product" && $route != "product/category" && $route != "ecflashsale/sales"){
		    		$setting['mini_mode'] = 0;
		    	}
		    }
	    }else{    	
			$flashsale = $this->model_ecflashsale_flashsale->getFlashsale( $ecflashsale_id );
			if(empty($flashsale))
				return;
	    }

	   	/*Check module mode is mini mode, call mini deal html*/
	    $module_mode = isset($setting['mode'])?$setting['mode']:'block';
	    $this->data['module_id'] = isset($setting['module_id'])?$setting['module_id']:$module;

	    if($module_mode == "mini") {
	    	return $this->getMiniDeal( $setting );
	    }

	    $flashsale = $this->defaultConfig($flashsale);
	    /*Setting data for layout*/


	    $this->data['ecflashsale_id'] = isset($flashsale['ecflashsale_id'])?(int)$flashsale['ecflashsale_id']:0;
	    $this->data['date_end'] = $flashsale['date_end'];
	    $this->data['discount_percent'] = (float)$flashsale['percent'];
	    $this->data['discount_amount'] = (float)$flashsale['amount'];
	    $this->data['description_maxchars'] = isset($setting['description_maxchars'])?$setting['description_maxchars']:100;

	    $this->data['name'] = isset($flashsale['name'])?$flashsale['name']:"";
	    $this->data['description'] = isset($flashsale['description'])?$flashsale['description']:"";
	    $this->data['description'] = html_entity_decode($this->data['description'], ENT_QUOTES, 'UTF-8');
	    $this->data['description'] = isset($this->data['description'])?str_replace(array("&lt;","&gt;","&quot;"),array('<','>','"'),$this->data['description']):"";
	    if(!empty($this->data['description_maxchars'])){
	    	$this->data['description'] = utf8_substr(strip_tags($this->data['description']), 0, $this->data['description_maxchars']);
	    }

	    $this->data['image_width'] = $setting['image_width'];
	    $this->data['image_height'] = $setting['image_height'];

	    $this->data['show_image'] = $setting['show_image'];
	    $this->data['show_name'] = $setting['show_name'];
	    $this->data['show_sale_off'] = $setting['show_sale_off'];
	    $this->data['show_viewmore'] = isset($setting['show_viewmore'])?$setting['show_viewmore']:true;
	    $this->data['prefix'] = $setting['prefix'];
	    $this->data['enable_message'] = $setting['enable_message'];
	    $this->data['show_expire_date'] = $setting['show_expire_date'];
	    $this->data['show_stock_bar'] = $setting['show_stock_bar'];
	    $this->data['show_description'] = $setting['show_description'];
	    $this->data['text_show_detail'] = isset($setting['text_show_detail'])?$setting['text_show_detail']:$this->language->get("text_show_detail");
	    $this->data['text_discount'] = isset($setting['text_discount'])?$setting['text_discount']:$this->language->get("text_discount");
	    $this->data['no_border']	= $discount_setting['no_border'];

	    if(isset($setting['custom_url']) && !empty($setting['custom_url'])) {
	    	$this->data['flashsale_link'] = $setting['custom_url'];
	    } else {
	    	$this->data['flashsale_link'] = $this->url->link("ecflashsale/flashsale","ecflashsale_id=".(int)$this->data['ecflashsale_id']);
	    }

	    if(!empty($this->data['discount_percent']) && $this->data['discount_percent'] > 0){
	    	$discount_value = $this->data['discount_percent']."%";
	    }else{
	    	$discount_value = $this->currency->format( $this->data['discount_amount'] );
	    }

	    $this->data['text_discount'] = sprintf($this->data['text_discount'], $discount_value);

	    $date_diff = $this->dateDiff( $this->data['date_end'] );

	    $date_end_time = strtotime($this->data['date_end']);

	    $this->data["is_expired"] = ($date_diff <= 0 && $date_end_time !== false) ? true:false ;

	    if($setting["mini_mode"] && $this->data["is_product_item"]){

	    		$this->data['module_id'] = $setting['product_id'];
		    	$this->data['product_id'] = $setting['product_id'];
		    	if(isset($setting['product'])){
		    		$product = $setting['product'];
		    	}else{
		    		$product = $this->getItemDeal($setting['product_id'], $setting);
		    	}

		    	if((!isset($product['special'])) || (isset($product['special']) && !$product['special'])) {
		    		return ;
		    	}

		    	$date_diff = $this->dateDiff( $product['date_end'] );

	    		$date_end_time = strtotime($product['date_end']);

	    		$this->data["is_expired"] = ($date_diff <= 0 && $date_end_time !== false) ? true:false ;
	    		
		    	$this->data["deal"] = $product;

		    	$this->data['enable_deal_image'] = $setting['enable_deal_image'];
		    	$this->data['enable_deal_name'] = $setting['enable_deal_name'];
		    	$this->data['enable_deal_price'] = $setting['enable_deal_price'];
		    	$this->data['enable_buy_now'] = $setting['enable_buy_now'];
		    	$this->data['enable_detail_buynow'] = $setting['enable_detail_buynow'];
		    	$this->data['enable_discount'] = $setting['enable_discount'];
		    	$this->data['deal_image_width'] = $setting['deal_image_width'];
		    	$this->data['deal_image_height'] = $setting['deal_image_height'];
		    	$this->data['deal_block_width'] = $setting['deal_block_width'];
		    	$this->data['show_rating'] = $setting['show_rating'];
		    	$this->data['show_social'] = $setting['show_social'];
		    	$this->data['show_notify_message'] = $setting['show_notify_message'];
		    	$this->data['show_reward_point'] = $setting['show_reward_point'];

	    }

	    /*If expired time and Disable message, then return null, else show flash sale or expired message*/
	    if($this->data["is_expired"] && !$this->data['enable_message']){
	    	return ;
	    }else{
	    	/*Enable today deal mode?*/

		    if($setting["mini_mode"] && $this->data["is_product_item"]){

		    	if($route == "product/category" || $route == "ecflashsale/sales"){
		    		$deal_template = isset($setting['list_deal_template'])?$setting['list_deal_template']:'deal_on_listing';
		    	}
		    	else{
		    		$deal_template = isset($setting['deal_template'])?$setting['deal_template']:'deal';
		    	}
		    	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecflashsale/'.$deal_template.'.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/module/ecflashsale/'.$deal_template.'.tpl';
				} else {
					$this->template = 'default/template/module/ecflashsale/'.$deal_template.'.tpl';
				}
			}else{ //Run normal mode of flash sale
				if (isset($setting['image']) && !empty($setting['image']) && file_exists(DIR_IMAGE . $setting['image'])) {
					$image = $setting['image'];
				} elseif(isset($flashsale['image']) && !empty($flashsale['image']) && file_exists(DIR_IMAGE . $flashsale['image'])){
					$image = $flashsale['image'];
				} else {
					$image = 'no_image.jpg';
				}

			    $this->data['image'] = $image;
			    $this->data['thumb'] = $this->model_tool_image->resize($image, (int)$setting['image_width'], (int)$setting['image_height']);
		   

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecflashsale/flashsale.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/module/ecflashsale/flashsale.tpl';
				} else {
					$this->template = 'default/template/module/ecflashsale/flashsale.tpl';
				}
			}
	    	
	    	$output = $this->render();
		    
			return $output;
	    }
	}

	/*Call function to get a flash sale or a daily deal item with countdown timmer*/
	public function ecflashsale($args = null){
		$custom_settings = array();
		if(is_array($args)){
			$product_id = isset($args[0])?$args[0]:0;
			$custom_settings = isset($args[1])?$args[1]:array();

		}else{
			$product_id = (int)$args;
		}
		$setting = $this->config->get('ecflashsale_setting');
		
	    $setting = $this->defaultConfig($setting);
	    $setting['show_viewmore'] = false;
	    $setting['module_id'] = 9999;
	    $setting['show_name'] = false;
	    $route = isset($this->request->get['route'])?$this->request->get['route']:"";
		if($route == "product/product" || $route == "ecflashsale/sales" || (isset($custom_settings['mini_mode']) && $custom_settings['mini_mode'])){
			if(empty($product_id))
				$product_id = isset($this->request->get['product_id'])?$this->request->get['product_id']:"0";
			$setting['mini_mode'] = isset($custom_settings['mini_mode'])?$custom_settings['mini_mode']:$setting['mini_mode'];
			$setting['product_id'] = $product_id;
			$setting['show_image'] = false;
			$setting['show_description'] = false;
			$setting['prefix'] = "detail_flashsale ";
			$setting['show_viewmore'] = true;
			$setting['show_name'] = true;
		}
		if(isset($custom_settings['mode']) && $custom_settings['mode'] == "mini"){
			$modules = $this->config->get('ecflashsale_module');
			if ($modules) {
				$module_setting = array();
				foreach ($modules as $module) {  
					$module['custom_position'] = isset($module['custom_position'])?strtolower($module['custom_position']):'';
					if ( ($module['custom_position'] == 'minideal' || $module['custom_position'] == 'custom_deal' || $module['custom_position'] == 'mini_deal' || $module['custom_position'] == 'custom_position') && $module['status']) {
						$module_setting = $module;
						break;			
					}
				}
				$setting = array_merge($setting, $module_setting);
			}
		}
		$setting = array_merge($setting, $custom_settings);

		return $this->index( $setting );
	}

	public function getItemDeal($product_id = null, $setting = array()){

		$deal = $this->model_ecflashsale_product->getDeal($product_id);
		if(!$deal)
			return false;

		$order_status_id = isset($setting['order_status_id'])?$setting['order_status_id']:array(5);

		$bought = $this->model_ecflashsale_product->getTotalBought($deal['product_id'], $order_status_id );
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

		$date_diff = floor($date_diff/3600/24) - 1;

		//$date_diff = (int)$date_diff + 1;

		$date_diff = ($date_diff < 0)?0:$date_diff;
		
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
			'bought'	 => $bought,
			'thumb'   	 => $image,
			'name'    	 => $deal['name'],
			'quantity'	 => $deal['quantity'],
			'reward'	 => $deal['reward'],
			'price'   	 => $price,
			'special' 	 => $special,
			'rating'     => $rating,
			'save_price' => $save_price,
			'date_end_string' => $date_end_string,
			'date_end'	 => $date_end_string,
			'description'=> (html_entity_decode($deal['description'], ENT_QUOTES, 'UTF-8')),
			'reviews'    => sprintf($this->language->get('text_reviews'), (int)$deal['reviews']),
			'link'    	 => $this->url->link('product/product', 'product_id=' . $deal['product_id']),
			'notify_message' => $notify_message
		);
		return $product;
	}
	public function getMiniDeal( $setting = array() ) {
		$this->data['show_discount_percent'] = isset($setting['show_discount_percent'])?$setting['show_discount_percent']:1;
		$this->data['show_discount_price'] = isset($setting['show_discount_price'])?$setting['show_discount_price']:1;
		$this->data['show_quantity'] = isset($setting['show_quantity'])?$setting['show_quantity']:1;
		$route = isset($this->request->get['route'])?$this->request->get['route']:"";
		$product_id = isset($this->request->get['product_id'])?$this->request->get['product_id']:0;
		if($route == "product/product" && $product_id) {
			
			$this->data['deal'] = $this->getItemDeal($product_id, $setting);
			if(isset($this->data['deal']['discount']) && (float)$this->data['deal']['discount'] > 0) {
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecflashsale/mini_deal.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/module/ecflashsale/mini_deal.tpl';
				} else {
					$this->template = 'default/template/module/ecflashsale/mini_deal.tpl';
				}
				return $this->render();
			}
		}
		return ;
	}

	public function timer($args = null){
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

		$this->language->load('module/ecflashsale');
		$this->load->model('ecflashsale/product');
		$this->load->model('ecflashsale/flashsale');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$deal = $this->model_ecflashsale_product->getDeal($product_id);

		if(!$deal)
			return false;

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

		if ($deal['date_start'] > date('Y-m-d H:i:s')) {
			$is_upcomming = true;
		}

		$product = array(
			'product_id' => $deal['product_id'],
			'quantity'	 => $deal['quantity'],
			'is_expired' => $is_expired,
			'is_upcomming' => $is_upcomming,
			'date_start' => $deal['date_start'],
			'date_end_string' => $date_end_string,
			'date_end'	 => $date_end_string
		);

		$this->data['deal'] = $product;

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/stylesheet/ecflashsale.css')) {
			$this->document->addStyle('catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css');
			$this->data["stylesheet"] = 'catalog/view/theme/'.$this->config->get('config_template').'/stylesheet/ecflashsale.css';
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/ecflashsale.css');
			$this->data["stylesheet"] = 'catalog/view/theme/default/stylesheet/ecflashsale.css';
		}
		$this->document->addScript('catalog/view/javascript/ecflashsale/countdown.js');
	    $this->data["script"] = 'catalog/view/javascript/ecflashsale/countdown.js';

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         	$this->data['base'] = $this->config->get('config_ssl');
	    } else {
	        $this->data['base'] = $this->config->get('config_url');
	    }

	    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ecflashsale/timer.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/ecflashsale/timer.tpl';
		} else {
			$this->template = 'default/template/module/ecflashsale/timer.tpl';
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
		 $todays_date = date("Y-m-d H:i:s");
		 $today = strtotime($todays_date); 
		 $expiration_date = strtotime($date);
		 $diff = $expiration_date - $today;

		 return $diff;
	}
	protected function defaultConfig($setting = array()){

        $defaults = array('name'	=> 'Flash Sale',
            'description'				=> '',
            'no_border'			=> 0,
            'date_start'		=> '000-00-00',
            'enable_message'	=> '0',
            'date_end'	=> '000-00-00',
            'amount'		=> 0,
            'percent'			=> 0,
            'show_stock_bar'	=> 1,
            'apply_for'	=> 'category',
            'category_id'			=> '',
            'products'			=> '',
            'module_width' => '100%',
            'module_height' => '100%',
            'prefix'	=> '',
            'show_image'	=> 1,
            'show_name'		=> 1,
            'show_description' => 1,
            'show_sale_off' => 1,
            'show_expire_date' => 1,
            'mini_mode'			=> 0,
            'show_deal_flashsale' => 0,
            'order_status_id'	=> 5,
            'show_social'	=> 1,
            'show_rating'		=> 1,
            'show_notify_message' => 1,
            'min_quantity'	=> 10,
            'min_day'		=> 3,
            'show_only_flashsale' => 1,
            'show_reward_point' => 1,
            'enable_deal_image' => 1,
            'enable_deal_name' => 1,
            'enable_deal_price' => 1,
            'enable_detail_buynow' => 1,
            'enable_buy_now' => 1,
            'enable_discount' => 1,
            'deal_block_width' => '340px',
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
