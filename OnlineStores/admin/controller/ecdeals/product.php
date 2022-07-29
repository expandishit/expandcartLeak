<?php
/******************************************************
 * @package Flash Sale for Opencart 1.5.x
 * @version 1.0
 * @author EcomTeck (http://ecomteck.com)
 * @copyright	Copyright (C) May 2013 ecomteck.com <@emai:ecomteck@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerModuleEcdeals extends Controller {
	private $error = array();

	public function index() {
        $this->load->model('marketplace/common');

        $market_appservice_status = $this->model_marketplace_common->hasApp('advanced_deals');
        if (!$market_appservice_status['hasapp']) {
            $this->redirect($this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'));
            return;
        }

		$this->language->load('module/ecdeals');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('ecdeals/product');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$this->load->model("catalog/category");
		$this->load->model("sale/customer_group");

		$this->model_ecdeals_product->installSampleData();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$action = isset($this->request->post["action"])?$this->request->post["action"]:"";
			/*Store custom position and product specials on module config*/
			if(!empty($this->request->post['ecdeals_module'])){
				foreach($this->request->post['ecdeals_module'] as $key=>$module){
					
					$custom_position = isset($module['custom_position'])?$module["custom_position"]:"";
					if(!empty($custom_position)){
						$this->request->post['ecdeals_module'][$key]['position'] = $custom_position;	
					}
					
				}
				unset($custom_position);
				unset($product_specials);
			}

			$result = $this->model_ecdeals_product->storeFlashSale($this->request->post);
			if($result){
				$this->request->post = $result;
			}
			$this->model_setting_setting->editSetting('ecdeals', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			if($action == "save_stay"){
				$this->redirect($this->url->link('module/ecdeals', 'token=' . $this->session->data['token'], 'SSL'));
			}else{
				$this->redirect($this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'));
			}

		}
		$this->document->addStyle('view/stylesheet/ecdeals.css');
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['token'] = $this->session->data['token'];
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_header_top'] = $this->language->get('text_header_top');
		$this->data['text_header_bottom'] = $this->language->get('text_header_bottom');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
        $this->data['text_footer_top'] = $this->language->get('text_footer_top');
		$this->data['text_footer_bottom'] = $this->language->get('text_footer_bottom');
        $this->data['text_alllayout'] = $this->language->get('text_alllayout');
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['column_deal_status'] = $this->language->get('column_deal_status');

		$this->data['entry_content'] = $this->language->get('entry_content');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_custom_position'] = $this->language->get('entry_custom_position');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_save_stay'] = $this->language->get('button_save_stay');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_new_block'] = $this->language->get('button_add_new_block');
		$this->data['text_alllayout'] = $this->language->get('text_all_layout');


		$this->data['tab_block'] = $this->language->get('tab_block');

		$this->data['positions'] = array( 'header_top',
										  'header_bottom',
										  'content_top',
										  'content_bottom',
										  'column_left',
										  'column_right',
										  'footer_top',
										  'footer_bottom'
		);
    	$this->data['sources'] = array("product"=>$this->language->get("text_products"),
    									"category"=>$this->language->get("text_category")
    					);
    	$this->data['categories'] = array();
    	
    	$this->data['categories'] = $this->model_catalog_category->getCategories(array());
   		$this->data['yesno'] = array('1'=>$this->language->get("text_yes"),
   									 "0"=>$this->language->get("text_no"));
   		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups(array());
		$this->load->model('localisation/language');
   		$languages = $this->model_localisation_language->getLanguages();
		$this->data['languages'] = $languages;

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->data['error_dimension'] = $this->error['dimension'];
		} else {
			$this->data['error_dimension'] = array();
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/ecdeals', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('module/ecdeals', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('marketplace/home', 'token=' . $this->session->data['token'] . '&type=apps', 'SSL');


		$this->data['modules'] = array();
		$this->data['general'] = array();

		if (isset($this->request->post['ecdeals_module'])) {
			$this->data['modules'] = $this->request->post['ecdeals_module'];
		} elseif ($this->config->get('ecdeals_module')) {
			$this->data['modules'] = $this->config->get('ecdeals_module');
		}

		if (isset($this->request->post['ecdeals_setting'])) {
			$this->data['general'] = $this->request->post['ecdeals_setting'];
		} elseif ($this->config->get('ecdeals_setting')) {
			$this->data['general'] = $this->config->get('ecdeals_setting');
		}
		$this->data['general']['apply_for'] = isset($this->data['general']['apply_for'])?$this->data['general']['apply_for']:"category";
		$this->data['general']['status'] = isset($this->data['general']['status'])?$this->data['general']['status']:"1";
		if(!empty($this->data['general'])){
			$products = isset($this->data['general']['products'])?$this->data['general']['products']:array();
			
			if($products){
				$tmp_products = array();
				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);

					if ($product_info) {
						$tmp_products[] = array(
							'product_id' => $product_info['product_id'],
							'name'       => $product_info['name']
						);
					}
				}
				$this->data['general']['products'] = $tmp_products;
				unset($product_info);
				unset($product_id);

			}
			if (isset($this->data['general']['image']) && file_exists(DIR_IMAGE . $this->data['general']['image'])) {
				$image = $this->data['general']['image'];
			} else {
				$image = 'no_image.jpg';
			}
			$this->data['general']['image'] = $image;
			$this->data['general']['thumb'] = $this->model_tool_image->resize($image, 150, 150);
		}
		foreach ($this->data['modules'] as $key=>$module) {
			if (isset($module['image']) && file_exists(DIR_IMAGE . $module['image'])) {
				$image = $module['image'];
			} else {
				$image = 'no_image.jpg';
			}
			$this->data['modules'][$key]['image'] = $image;
			$this->data['modules'][$key]['thumb'] = $this->model_tool_image->resize($image, 150, 150);
		}
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 150, 150);
	    $this->data['default'] = $this->defaultConfig(array());
	    if( !empty($this->data['modules'])){
	        foreach($this->data['modules'] as $key=>$module){
	            $this->data['modules'][$key] = $this->defaultConfig($module);
	        }

	    }
	    $currency = $this->currency->getSymbolLeft($this->config->get('config_currency'));   
	    $this->data['currency'] = $currency;
		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		$file_path = DIR_DOWNLOAD."ecdeals/default_description.html";
		$this->data['default_description'] = $this->language->get("text_default_description");
		if(file_exists($file_path)){
			$this->data['default_description'] = file_get_contents($file_path);		
		}
		$this->data['default_description'] = preg_replace(array('/\r/', '/\n/'),'',$this->data['default_description']);
		$this->data['default_description'] = str_replace("'","\'",$this->data['default_description']);
		$this->data['default_description'] = html_entity_decode($this->data['default_description']);

		$this->template = 'module/ecdeals.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/ecdeals')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['ecdeals_module'])) {

		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
    
    protected function defaultConfig($setting = array()){
        $defaults = array('name'	=> 'Flash Sale',
            'description'				=> '',
            'date_start'		=> '000-00-00',
            'date_end'	=> '000-00-00',
            'discount_amount'		=> 0,
            'discount_percent'			=> 0,
            'apply_for'	=> 'category',
            'category_id'			=> '',
            'products'			=> '',
            'module_width' => '100%',
            'module_height' => '100%',
            'image_width'					=> '600',
            'show_image'	=> 1,
            'show_name'		=> 1,
            'show_description' => 1,
            'show_expire_date' => 1,
            'show_sale_off' => 1,
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
