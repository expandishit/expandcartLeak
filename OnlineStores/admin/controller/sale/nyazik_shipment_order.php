<?php

class ControllerSaleNyazikShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/nyazik')) {
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('nyazik') || !$this->config->get('nyazik')['status'] ){
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/nyazik');
    	$this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		$order['total'] = $this->currency->convertUsingRatesAPI($order['total'], $this->config->get('config_currency'), 'SAR');
		$order['currency_code'] = $this->config->get('config_admin_language') == 'ar'? 'رس' : 'SAR';
		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}
		
	    $this->data['order']             = $order;
	    $this->data['cancel']            = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);
	    $this->data['shipment_description'] = $this->model_sale_order->getOrderShipmentDescription($this->request->get['order_id']);

	    $this->load->model('localisation/country');
	    $this->data['countries'] = $this->model_localisation_country->getCountries();

		/*prepare nyazik/create.expand view data*/
		$this->document->setTitle($this->language->get('create_heading_title'));
	 	
	 	//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/nyazik/shipment/create.expand';
		$this->children = ['common/header', 'common/footer'];
	    $this->response->setOutput($this->render_ecwig());
	}


	/**
	* Store/save the order data in the shipping gateway DB via external APIs.
	*/
	public function store(){
		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {
        	//Validate form fields
  			if ( !empty($this->_validateForm()) ){
  				$result_json['success'] = '0';
  				$result_json['errors']  = $this->error;
  			}
  			else{
				$this->load->language('shipping/nyazik');
				$this->load->model('shipping/nyazik');
				$this->load->model('sale/order');

				$settings = $this->config->get('nyazik');
  				$order_id = $this->request->post['order_id'];
  				$nyazik_shipment = $this->request->post['nyazik_order'];
  				$order    = $this->model_sale_order->getOrder($order_id);
				$weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;
				$nyazik_order = $this->request->post['nyazik_order'];
				$products = $this->model_sale_order->getOrderProducts($order_id);

				$data = [					
					"task_id"         => $nyazik_order['task_id'],
					"task_group"      => "DOM",
					"task_type"       => "PPX",
					"task_payment"    => "PAC",
					"task_service"    => "",
					"task_cod_amount" => $nyazik_order['cod'],
					//Shipper
					"from_name"       => $nyazik_order['shipper']['name'],
					"from_mobile"     => $nyazik_order['shipper']['phone'],
					"from_email"      => $nyazik_order['shipper']['email'],
					"from_address1"   => $nyazik_order['shipper']['addressline1'],
					"from_city"       => $nyazik_order['shipper']['city'],
					"from_country"    => $nyazik_order['shipper']['country_code'],
					//Receiver
					"to_name"         => $nyazik_order['receiver']['name'],
					"to_mobile"       => $nyazik_order['receiver']['phone'],
					"to_phone"        => $nyazik_order['receiver']['phone'],
					"to_email"        => $nyazik_order['receiver']['email'],
					"to_address1"     => $nyazik_order['receiver']['addressline1'],
					"to_address2"     => $nyazik_order['receiver']['addressline2'],
					"to_city"         => $nyazik_order['receiver']['city'],
					"to_country"      => $nyazik_order['receiver']['country_code'],
					"item_description"=> $nyazik_order['shipment_description'] ?: $this->model_sale_order->getOrderShipmentDescription($order_id),
					"item_pieces"     => count($products),
					"item_value"      => $nyazik_order['cod'],
					"item_weight"     => $weight,
					"item_weight_unit"=> "KG"
				];
				$response = $this->model_shipping_nyazik->createShipment($data);
				
				$result = $response['result'][0];

				if( $result['result'] == 'success' ){  //succeeded trackingnumber
          			$trackingnumber = $result['hawb'];
          			
          			//update status & add history record
	    			if( !empty($settings['after_creation_status']) ){
				        $this->model_sale_order->addOrderHistory($order_id, [
				          'comment'          => 'Nyazik - TrackingNumber: ' . $trackingnumber,
				          'order_status_id'  => $settings['after_creation_status'],
				        ]);
  				    }

  				    $langCode = $this->config->get('config_admin_language') == 'ar' ? 'ar' : 'en';

		  	        //Update Tracking Number & Tracking URL
		  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://www.nyazik.com/'.$langCode.'/track?task=' . $trackingnumber);
		  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $trackingnumber);
            	    $this->model_sale_order->updateShippingLabelURL($order_id , $result['print_url']);

	      			//Returning to Order page
	      			$result_json['success_msg'] = $this->language->get('text_shipment_created');
	      			$result_json['success']     = '1';

            		//redirect
  					$result_json['redirect'] = '1';
            		$result_json['to'] = "sale/order/info?order_id=".$order_id;
          		}
	    		else{
				$result_json['success'] = '0';
				$nyazik_error = [];
				foreach($result as $k => $v)
					if($k != 'result')
						$nyazik_error[] = $k.":  ".$v;
				$nyazik_error = implode("<br>", $nyazik_error);
				$result_json['errors']  = $result['message'] ?? $nyazik_error;
                }
    		}

  			$this->response->setOutput(json_encode($result_json));
  		}
  		else{
  			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
  		}
	}

	/** Helper methods **/

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
	        'text' => $this->language->get('create_heading_title'),
	        'href' => $this->url->link('shipping/nyazik', true)
	      ]
	    ];
	    return $breadcrumbs;
	}


	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm(){
		$this->load->language('shipping/nyazik');

		if (!$this->user->hasPermission('modify', 'shipping/nyazik')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		$nyazik_order = $this->request->post['nyazik_order'];

		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $nyazik_order['shipper']['phone']) ) {
        	$this->error['error_shipper_phone'] = $this->language->get('error_shipper_phone');
    	}

		if( !preg_match('/^(\+\d{1,5}|0)(\d{10})$/', $nyazik_order['receiver']['phone']) ) {
        	$this->error['error_receiver_phone'] = $this->language->get('error_receiver_phone');
    	}

    	if( utf8_strlen($nyazik_order['task_id']) <= 0)
    		$this->error['error_task_id'] = $this->language->get('error_task_id');

    	if( utf8_strlen($nyazik_order['shipper']['name']) < 2)
    		$this->error['error_shipper_name'] = $this->language->get('error_shipper_name');

    	if( utf8_strlen($nyazik_order['receiver']['name']) < 2)
    		$this->error['error_receiver_name'] = $this->language->get('error_receiver_name');

    	if( !preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $nyazik_order['cod']) )
    		$this->error['error_cod'] = $this->language->get('error_cod');

    	if( !filter_var($nyazik_order['shipper']['email'], FILTER_VALIDATE_EMAIL))
    		$this->error['error_shipper_email'] = $this->language->get('error_shipper_email');

    	if( !filter_var($nyazik_order['receiver']['email'], FILTER_VALIDATE_EMAIL))
    		$this->error['error_receiver_email'] = $this->language->get('error_receiver_email');

    	if( utf8_strlen($nyazik_order['shipper']['addressline1']) < 2)
    		$this->error['error_shipper_addressline'] = $this->language->get('error_shipper_addressline');

    	if( utf8_strlen($nyazik_order['receiver']['addressline1']) < 2)
    		$this->error['error_receiver_addressline1'] = $this->language->get('error_receiver_addressline1');

    	if( utf8_strlen($nyazik_order['receiver']['addressline2']) < 2)
    		$this->error['error_receiver_addressline2'] = $this->language->get('error_receiver_addressline2');

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

    	return $this->error;
	}
 
	private function _isAjax() {

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	public function getCities()
	{
	      //If Country id is missing return empty array...
	      if( empty($this->request->post['country_id']) ) {
	        $this->response->setOutput(json_encode([]));
	        return;
	      }

	      $this->load->model('localisation/zone');
	      $cities = $this->model_localisation_zone->getZonesByCountryId($this->request->post['country_id']);
	      $this->response->setOutput(json_encode($cities));
    }
}
