<?php

class ControllerShippingBosta extends Controller{
   
   	/**
	* @var array the validation errors array.
	*/
	private $error = array();


	public function index(){
	    /*prepare bosta.expand view data*/
	    $this->load->language('shipping/bosta');
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'shipping/bosta.expand';
	    $this->children = array( 'common/header', 'common/footer');

	    //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

		//Form Buttons
		//save button - Ajax post request
		$this->data['action'] = $this->url->link('shipping/bosta/save', '' , 'SSL');
		$this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

	    /*Get form fields data*/
	    $this->data['bosta_api_key']      = $this->config->get('bosta_api_key');
	    $this->data['bosta_status']       = $this->config->get('bosta_status');
	    $this->data['bosta_live_mode']    = $this->config->get('bosta_live_mode');
	    $this->data['bosta_geo_zone_id']  = $this->config->get('bosta_geo_zone_id');
	    $this->data['bosta_tax_class_id'] = $this->config->get('bosta_tax_class_id');
       	$this->data['bosta_after_creation_status'] = $this->config->get('bosta_after_creation_status');
       	$this->data['bosta_statuses']     = $this->config->get('bosta_statuses');
       	$this->data['lang']               = $this->config->get('config_admin_language');
       	$this->data['bosta_price']        = $this->config->get('bosta_price');
       	

	    $this->load->model('localisation/geo_zone');
	    $this->data['geo_zones']  = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');
    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

	    //Managing session messages
	    if(isset($this->session->data['api_error'])) {
	      $this->data['api_error'] = $this->session->data['api_error'];
	      unset($this->session->data['api_error']);
	    }
	    if(isset($this->session->data['success'])){
	      $this->data['success'] = $this->session->data['success'];
	      unset($this->session->data['success']);
	    }

	    $this->response->setOutput($this->render());
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
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('extension/shipping', 'type=shipping', true)
			],
			[
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('shipping/bosta', true)
			]
		];
		return $breadcrumbs;
	}




	/**
	* Save form data and Enable Extension after data validation.
	*
	* @return void
	*/
	public function save(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

	  		//Validate form fields
			if ( ! $this->_validateForm() ){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			else{
				$this->load->model('setting/setting');
				$this->load->language('shipping/bosta');
	    		//Insert a record of Bosta gatway in the extension table in DB if not any
				$this->model_setting_setting->checkIfExtensionIsExists('shipping', 'bosta', TRUE);
	    		//Save bosta config data in settings table

                
            $this->tracking->updateGuideValue('SHIPPING');

                $this->model_setting_setting->insertUpdateSetting('bosta', $this->request->post);//, 'bosta_cities' => $this->config->get('bosta_cities')]);

				$result_json['success_msg'] = $this->language->get('text_success');

				$result_json['success']  = '1';
			    // $result_json['redirect'] = '1';
			    // $result_json['to'] = "shipping/bosta";
			}

			$this->response->setOutput(json_encode($result_json));
		}
		else{
			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
	}

	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){

		$this->load->language('shipping/bosta');

		if (!$this->user->hasPermission('modify', 'shipping/bosta')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['bosta_api_key']) < 3) ) {
			$this->error['bosta_api_key'] = $this->language->get('error_bosta_api_key');
		}

		if (!$this->request->post['bosta_price']['bosta_weight_rate_class_id'] || empty($this->request->post['bosta_price']['bosta_weight_rate_class_id']) ){
            $this->error['bosta_weight_rate_class_id'] = $this->language->get('error_bosta_weight_rate_class_id');
        }

		if($this->error && !isset($this->error['error']) ){
			$this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}


	private function _isAjax() {
		
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}


	public function install() {
		$this->load->model('shipping/bosta');
		$this->model_shipping_bosta->install();
	}

	public function uninstall(){
		$this->load->model('shipping/bosta');
		$this->model_shipping_bosta->uninstall();
	}

	public function create(){
		$order_id = $this->request->get['order_id'];
        $this->load->model('setting/setting');
		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($order_id);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->load->language('shipping/bosta');
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='.$order_id, '' , true));
		}

		$this->data['order'] = $order;

		$current_language = $this->config->get('config_admin_language');
        $bosta_cities=$this->config->get('bosta_cities');
		$read_sea_City = ['code'=>'EG-23' , 'zone'=>['en'=>'Red Sea' , 'ar'=>'البحر الأحمر']];
		if (!in_array($read_sea_City , $this->config->get('bosta_cities'))){
            $bosta_cities[] = $read_sea_City;
            $this->model_setting_setting->insertUpdateSetting( 'bosta', ['bosta_cities' => $bosta_cities]);
        }
		//Get cities according to current language
		$this->data['cities'] = array_map(function($var) use ($current_language) {
			$var['zone'] = $var['zone'][$current_language];
			return $var;
		}, $bosta_cities);

		/*prepare bosta.expand view data*/
	    $this->load->language('shipping/bosta');
	    $this->document->setTitle($this->language->get('create_heading_title'));

 		//Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
	    $this->data['cancel'] = $this->url->link('sale/order/info?order_id='.$order_id);
		$this->template = 'shipping/bosta/shipment/create.expand';
	    $this->children = array( 'common/header', 'common/footer');

        $this->response->setOutput($this->render_ecwig());
	}

	private function _validateShippingOrder(){	
		$this->load->language('shipping/bosta');

		if(utf8_strlen($this->request->post['receiver']['firstName']) < 3 
			|| utf8_strlen($this->request->post['receiver']['lastName']) < 3){
        	$this->error['firstName'] = $this->language->get('error_first_last_name');
		}
		if( !isset($this->request->post['receiver']['phone']) || !preg_match("/^01[0-5]{1}[0-9]{8}$/", $this->request->post['receiver']['phone']) ){
		    $this->error['phone'] = $this->language->get('error_phone');     
		}
		if( utf8_strlen($this->request->post['dropOffAddress']['firstLine']) < 5){
		    $this->error['firstLine'] = $this->language->get('error_first_line');     
		}
		if( $this->request->post['isSameDay'] == 1 && count(array_filter($this->request->post['dropOffAddress']['geoLocation']) ) < 2){
		    $this->error['geoLocation'] = $this->language->get('error_geolocation');     
		}

		if($this->error && !isset($this->error['error']) ){
	      $this->error['warning'] = $this->language->get('errors_heading');
	    }
		
		return $this->error;
	}


	public function store(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

			//Validate form fields
			if ( !empty($this->_validateShippingOrder()) ){
				$result_json['success'] = '0';
				$result_json['errors'] = $this->error;
			}
			else{
				$order_id = (int)$this->request->post['order_id'];
				$this->load->model('sale/order');
				$order = $this->model_sale_order->getOrder($order_id);

				$this->request->post['dropOffAddress']['geoLocation'] = array_map('floatval', $this->request->post['dropOffAddress']['geoLocation']);

				$data = [
					"receiver"         => array_filter($this->request->post['receiver']),
					"dropOffAddress"   => array_filter($this->request->post['dropOffAddress']),
					"notes"            => $this->request->post['notes']?:'',
					"cod"              => $order['payment_code'] === 'cod' ? floatval($order['total']) : 0,
					"type"             => 10, //Package Shipping & 15 Cash Collection
					"isSameDay"        => $this->request->post['isSameDay'],
					"businessReference"=> $order['order_id'],
					"webhookUrl"       => $this->config->get('bosta_callback'),
					"specs"            => [
						"packageDetails" => [
							"itemsCount" => $this->request->post['no_of_items'],
                            "description" => "
                            Name : {$order['shipping_firstname']} {$order['shipping_lastname']}
                            Address 1 : {$order['shipping_address_1']}
                            Address 2 : {$order['shipping_address_2']}
                            City : {$order['shipping_city']}
                            Area : {$order['shipping_area']}
                            Region / State Code : {$order['shipping_zone']}
                            Country : {$order['shipping_country']}
                            Note : ". strip_tags($order['comment']) ."
                            "
						]
					]
				];
				
	    		$this->load->language('shipping/bosta');
	    		$this->load->model('shipping/bosta');

	    		$response = $this->model_shipping_bosta->createShipment($data);

	    		if($response['status_code'] === 201){//succeeded 
	    			//update status & add history record
	    			if( !empty($this->config->get('bosta_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'Bosta.co - ' . $response['result']->message,
				          'order_status_id'  => $this->config->get('bosta_after_creation_status'),
				        ]);
					}

			        //Update Tracking Number & Tracking URL
			        $this->model_sale_order->updateShippingTrackingURL($order_id , "https://bosta.co/tracking-shipment/?lang=en&track_num=" . $response['result']->trackingNumber);
			        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']->trackingNumber);

	    			//Returning to Order page
	    			$result_json['success_msg'] = $this->language->get('text_creation_success');
					$result_json['success']  = '1';
					//redirect
					$result_json['redirect'] = '1';
			    	$result_json['to'] = "sale/order/info?order_id=".$order_id;	
	    		}
	    		elseif($response['status_code'] === 403 ){ //Forbidden
	    			$result_json['success'] = '0';
					$result_json['errors']  = $response['errors'];
	    		}
	    		else{
	    			$result_json['success'] = '0';
					$result_json['errors']  = $response['status_code'] . " - " . $response['result']->message;
	    		}
			}
			$this->response->setOutput(json_encode($result_json));
		}
		else{
			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
	}

}
