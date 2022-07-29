<?php

class ControllerSaleSubscription extends Controller {

	private $error = [];

 	public function __construct($registry){
        parent::__construct($registry);

        if (!$this->user->hasPermission('modify', 'module/buyer_subscription_plan')) {
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
        
        if( !\Extension::isInstalled('buyer_subscription_plan') ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
    }

	public function index(){
		
		$this->load->model('module/buyer_subscription_plan/subscription');		
        $this->data['subscriptions']  = $this->model_module_buyer_subscription_plan_subscription->get();
        
		$this->load->language('module/buyer_subscription_plan');
		$this->document->setTitle($this->language->get('heading_title3'));

        $this->_renderView('module/buyer_subscription_plan/subscription/index.expand');
	}

	public function create(){
		$this->load->language('module/buyer_subscription_plan');
		$this->document->setTitle($this->language->get('heading_title1'));

		$this->data['action'] = $this->url->link('sale/subscription/store', '', 'SSL');

    	$this->_getForm();
	}
	public function store(){

        if ( ! $this->_validateForm() ){
            $result_json['success'] = '0';
            $result_json['errors']  = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->load->model('module/buyer_subscription_plan/subscription');
		$subscription_id = $this->model_module_buyer_subscription_plan_subscription->add($this->request->post['subscription']);

		if($subscription_id){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/subscription','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = 'Errors: ';
	  		$result_json['success'] = '0';
      		$result_json['errors']  = $this->error;
	  	}
    	$this->response->setOutput(json_encode($result_json));
	}


	//Edit Subscription
	public function edit(){
		$this->load->language('module/buyer_subscription_plan');

		$this->document->setTitle($this->language->get('heading_title2'));
		
		$this->data['action'] = $this->url->link('sale/subscription/update', 'subscription_id=' . $this->request->get['subscription_id'], 'SSL');		
        
        $this->load->model('module/buyer_subscription_plan/subscription');		
		$this->data['subscription'] = $this->model_module_buyer_subscription_plan_subscription->get($this->request->get['subscription_id']);

    	$this->_getForm();
	}
	public function update(){
        if ( ! $this->_validateForm() ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;

            $this->response->setOutput(json_encode($result_json));

            return;
        }
		$this->request->post['subscription']['subscription_id'] = $this->request->get['subscription_id'];

        $this->load->model('module/buyer_subscription_plan/subscription');
		$is_updated = $this->model_module_buyer_subscription_plan_subscription->update($this->request->post['subscription']);

		if($is_updated){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/subscription','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = 'Errors: ';
	  		$result_json['success'] = '0';
      		$result_json['errors']  = $this->error;
	  	}

    	$this->response->setOutput(json_encode($result_json));
	}


	public function delete(){
		$this->load->model('module/buyer_subscription_plan/subscription');
		$is_deleted = $this->model_module_buyer_subscription_plan_subscription->delete($this->request->post['selected_ids']);

		if($is_deleted){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/subscription','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = 'Errors: ';
	  		$result_json['success'] = '0';
      		$result_json['error']   = $this->language->get('error_delete');
	  	}
    	
    	$this->response->setOutput(json_encode($result_json));
	}



	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('module/buyer_subscription_plan');

		//Permission Validation
		if (!$this->user->hasPermission('modify', 'module/buyer_subscription_plan/subscription')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		//Coupons Validation
		if( empty($this->request->post['subscription']['coupons']) 
			|| !is_array($this->request->post['subscription']['coupons']) ){
		    $this->error['coupons'] = $this->language->get('error_coupons');
		}
		
		//Title & Description Validation
		foreach ($this->request->post['subscription']['translations'] as $language_id => $translations) {
			$title = ltrim($translations['title'] ," ");
			$description = ltrim(strip_tags($translations['description'])," ");

			if ( utf8_strlen($title) < 3 || utf8_strlen($title) > 64 ) {
        		$this->error['title_' . $language_id] = $this->language->get('error_title');
      		}

      		if ( utf8_strlen($description) < 3 ) {
        		$this->error['s_description_' . $language_id] = $this->language->get('error_description');
      		}
		}

		//Price Validation
        if (!isset($this->request->post['subscription']['price']) 
        	|| $this->request->post['subscription']['price'] <= 0) {
            $this->error['price'] = $this->language->get('error_price');
        }

		//Validity Validation
        if (!isset($this->request->post['subscription']['validity_period']) 
        	|| $this->request->post['subscription']['validity_period'] <= 0
        	/*|| $this->request->post['subscription']['validity_period'] >= 1000*/) {
            $this->error['validity_period'] = $this->language->get('error_validity_period');
        }

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	/**
	* Form the breadcrumbs array.
	*
	* @return Array $breadcrumbs
	*/
	private function _createBreadcrumbs(){

		$breadcrumbs = [
		  [
		    'text' => $this->language->get('text_home'),
		    'href' => $this->url->link('common/dashboard', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('text_module'),
		    'href' => $this->url->link('marketplace/home', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('module/buyer_subscription_plan', '', 'SSL')
		  ]
		];

		return $breadcrumbs;
	}

	private function _getForm() {
		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$this->data['error_name']    = isset($this->error['name']) ? $this->error['name'] : [];

		$this->data['cancel'] = $this->url->link('sale/subscription', '', 'SSL');

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->data['current_store_currency'] = $this->config->get('config_currency');

		$this->load->model('sale/coupon');
		$this->data['coupons'] = $this->model_sale_coupon->getCoupons();
		
		$this->_renderView('module/buyer_subscription_plan/subscription/form.expand');


		// if (isset($this->request->post['attribute_description'])) {
		// 	$this->data['attribute_description'] = $this->request->post['attribute_description'];
		// } elseif (isset($this->request->get['advanced_attribute_id'])) {
		// 	$this->data['attribute_description'] = $this->model_module_advanced_product_attributes_attribute->getAttributeDescriptions($this->request->get['advanced_attribute_id']);
		// } else {
		// 	$this->data['attribute_description'] = [];
		// }

		// if (isset($this->request->post['attribute_group_id'])) {
		// 	$this->data['attribute_group_id'] = $this->request->post['attribute_group_id'];
		// } elseif (!empty($attribute_info)) {
		// 	$this->data['attribute_group_id'] = $attribute_info['attribute_group_id'];
		// } else {
		// 	$this->data['attribute_group_id'] = '';
		// }

		// if (isset($this->request->post['sort_order'])) {
		// 	$this->data['sort_order'] = $this->request->post['sort_order'];
		// } elseif (!empty($attribute_info)) {
		// 	$this->data['sort_order'] = $attribute_info['sort_order'];
		// } else {
		// 	$this->data['sort_order'] = '';
		// }

		// if (isset($this->request->post['attribute_type'])) {
		// 	$this->data['attribute_type'] = $this->request->post['attribute_type'];
		// } elseif (!empty($attribute_info)) {
		// 	$this->data['attribute_type'] = $attribute_info['type'];
		// } else {
		// 	$this->data['attribute_type'] = 'text';
		// }


		// if (isset($this->request->post['glyphicon'])) {
		// 	$this->data['glyphicon'] = $this->request->post['glyphicon'];
		// } elseif (!empty($attribute_info)) {
		// 	$this->data['glyphicon'] = $attribute_info['glyphicon'];
		// } else {
		// 	$this->data['glyphicon'] = 'fa fa-check-circle';
		// }


		// if (isset($this->request->post['attribute_values'])) {
		// 	$this->data['attribute_values'] = $this->request->post['attribute_values'];
		// } elseif (!empty($attribute_info)) {
		// 	$this->data['attribute_values'] = $attribute_info['values'];
		// } else {
		// 	$this->data['attribute_values'] = [];
		// }
  	}

  	private function _renderView($name){
  		$this->load->language('module/buyer_subscription_plan');

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

		//render view template
		$this->template = $name;
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
  	}
}
