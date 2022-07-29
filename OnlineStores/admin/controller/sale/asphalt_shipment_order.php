<?php

error_reporting(E_ALL);

class ControllerSaleAsphaltShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/asphalt')) {
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('asphalt') || !$this->config->get('asphalt')['status'] ){
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }
	}

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/asphalt');
		$this->load->model('shipping/asphalt');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);
		$order['total'] = $this->currency->convertUsingRatesAPI($order['total'],$this->config->get('config_currency'), 'EGP');
		$order['currency_code'] = 'EGP';
		$order['shipping_area'] = $this->model_shipping_asphalt->normalize_name($order['shipping_area']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}
		
	    $this->data['order']             = $order;
	    $this->data['cancel']            = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

	    $this->load->model('localisation/area');
	    $this->data['areas'] = $this->model_localisation_area->getAreasMuilteLangBasedOnZone($order['shipping_zone_id']);

	    $this->load->model('localisation/zone');
	    $this->data['zones'] = $this->model_localisation_zone->getZonesByCountryId($order['shipping_country_id']);
		$this->data['asphalt_zones'] = $this->config->get('asphalt_governments');

		$this->data['contents'] = $this->model_shipping_asphalt->getShipmentContentType();
		$this->data['packings'] = $this->model_shipping_asphalt->getShipmentPackagingType();
		$this->data['collect_methods'] = $this->model_shipping_asphalt->getShipmentCollectMethod();
		$this->data['collect_types']   = $this->model_shipping_asphalt->getShipmentAmountCollectType();
		$this->data['branches']        = $this->model_shipping_asphalt->getShipmentBranchIds();
		$this->data['delivery_types']  = $this->model_shipping_asphalt->getShipmentDeliveryType();
		$this->data['governments']  = $this->model_shipping_asphalt->getAllGovernments();

		/*prepare asphalt/create.expand view data*/
		$this->document->setTitle($this->language->get('create_heading_title'));
	 	
	 	//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/asphalt/shipment/create.expand';
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
				$this->load->language('shipping/asphalt');
				$this->load->model('shipping/asphalt');
				$this->load->model('sale/order');

				$settings = $this->config->get('asphalt');
  				$order_id = $this->request->post['order_id'];
  				$asphalt_shipment = $this->request->post['asphalt_order'];
  				$order    = $this->model_sale_order->getOrder($order_id);
				$weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;
		        $data = [
		            'api_key' => $settings['api_key'],
		            
		            'user'    => $asphalt_shipment['receiver']['name'],
		            'mobile'  => $asphalt_shipment['receiver']['mobile'],
		            'address' => $asphalt_shipment['receiver']['address'],		            
		            'gov_id'  => $asphalt_shipment['receiver']['gov_id'],		            
		            'zone'    => $asphalt_shipment['receiver']['zone'],
		            
		            'note'    => $asphalt_shipment['customer_note'],
		            'genral_note'  => $asphalt_shipment['general_note'],
		            'heavy'   => $weight,
		            'amount'  => $asphalt_shipment['amount'],
		            
		            'type'    => $asphalt_shipment['delivery_type'],
		            'content' => $asphalt_shipment['content'],
		            'packing' => $asphalt_shipment['packing'],
		            'collect_method' => $asphalt_shipment['collect_method'],
		            'collect_type' => $asphalt_shipment['collect_type'],
		            'branch_id'    => $asphalt_shipment['branch_id'],
		            'open_check'   => 'no',
		            'break_check'  => 'no',
		            'sender_amount'=> $asphalt_shipment['sender_amount'],
		            'items' => (string)(json_encode($this->getItems($order_id))),
		            'brand_name' => 'brand'
		        ];

				$response = $this->model_shipping_asphalt->createShipment(http_build_query($data));

				if( !empty($response) && $response['status_code'] == 200){  //succeeded trackingnumber
          			$trackingnumber = $response['invo'];
          			
          			//update status & add history record
	    			if( !empty($settings['after_creation_status']) ){
				        $this->model_sale_order->addOrderHistory($order_id, [
				          'comment'          => 'Asphalt - TrackingNumber: ' . $trackingnumber,
				          'order_status_id'  => $settings['after_creation_status'],
				        ]);
  				    }

		  	       //Update Tracking Number & Tracking URL
		  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://asphalt-eg.com/track?invo=' . $trackingnumber);
		  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $trackingnumber);
      	            $this->model_sale_order->updateShippingLabelURL($order_id , $response['print_url']);

	      			//Returning to Order page
	      			$result_json['success_msg'] = $this->language->get('text_shipment_created');
	      			$result_json['success']     = '1';

            		//redirect
  					$result_json['redirect'] = '1';
  					$result_json['to'] = $response['print_url'];
            		// $result_json['to'] = "sale/order/info?order_id=".$order_id;
          		}
	    		else{
	    		    $result_json['success'] = '0';
					$result_json['errors']  = 'ERROR: ' . $response['Description'];
                }
    		}

  			$this->response->setOutput(json_encode($result_json));
  		}
  		else{
  			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
  		}
	}

	public function getItems($order_id)
	{
		$this->load->model('sale/order');
		$products = $this->model_sale_order->getOrderProducts($order_id);
		$items = ['items'=>[]];
		foreach($products as $product){
			$items['items'][] = ['p_nameid' => $product['product_id'], 'p_qty' => $product['quantity']];
		}
		return $items;
	}

	public function getRegions()
	{
		$gov_id = $this->request->post['gov_id'];	    

		$this->load->model('shipping/asphalt');		
		$res =  !empty($gov_id) ? $this->model_shipping_asphalt->getAllRegions($gov_id) : [];
		return $this->response->setOutput(json_encode($res));
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
	        'href' => $this->url->link('shipping/asphalt', true)
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
		$this->load->language('shipping/asphalt');

		if (!$this->user->hasPermission('modify', 'shipping/asphalt')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		$asphalt_order = $this->request->post['asphalt_order'];
  		
  		if ((utf8_strlen($asphalt_order['general_note']) < 4) ) {
		  $this->error['error_shipment_description'] = $this->language->get('error_shipment_description');
		}

  		if ((utf8_strlen($asphalt_order['customer_note']) < 4) ) {
		  $this->error['error_customer_note'] = $this->language->get('error_customer_note');
		}
  		
  		if ((utf8_strlen($asphalt_order['receiver']['name']) < 4) ) {
		  $this->error['error_receiver_name'] = $this->language->get('error_receiver_name');
		}

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}


    	return $this->error;
	}
 
	private function _isAjax() {

		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}
}
