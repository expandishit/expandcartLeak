<?php

class ControllerSaleParcelShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/parcel')) {
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('parcel') ){
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }
    }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
   		$this->load->language('shipping/parcel');
      $this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

  		$this->load->model('sale/order');
  		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

  		//Check if store order has shipping order already
      if( !empty($order['tracking']) ){
        $this->session->data['error'] = $this->language->get('text_order_already_exist');
        $this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
      }

		  $this->data['order'] = $order;
      $this->data['store_name']        = $this->config->get('config_title')[$this->config->get('config_admin_language')];
	    $this->data['shipper_address']   = $this->config->get('config_address')[$this->config->get('config_admin_language')];
      $this->data['shipper_telephone'] = $this->config->get('config_telephone');
      $this->data['shipper_lat']       = explode(',' , $this->config->get('config_location'))[0] ?? '';
      $this->data['shipper_long']      = trim(explode(',' , $this->config->get('config_location'))[1]) ?? '';
	    $this->data['shipper_email']     = $this->config->get('config_email');
	    $this->data['cancel'] = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

		  /*prepare parcel/create.expand view data*/
	  	$this->document->setTitle($this->language->get('create_heading_title'));
 		  //Breadcrumbs
	  	$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		  $this->template = 'shipping/parcel/shipment/create.expand';
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
          $this->load->language('shipping/parcel');
          $this->load->model('shipping/parcel');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $parcel_settings       = $this->config->get('parcel');

          // $total    = $this->currency->convertUsingRatesAPI($order['total'], $order['currency_code'], $account_currency_code);

          $data = [
            'job_description'     => $this->request->post['job_description'],
            'job_pickup_phone'    => $this->request->post['shipper_mobile'],
            'job_pickup_name'     => $this->request->post['store_name'],
            'job_pickup_email'    => $this->request->post['shipper_email'],
            'job_pickup_address'  => $this->request->post['shipper_address'],
            'job_pickup_latitude' => $this->request->post['shipper_lat'],
            'job_pickup_longitude'=> $this->request->post['shipper_lng'],
            'customer_email'       => $this->request->post['receiver_email'],
            'customer_username'    => $this->request->post['receiver_name'],
            'customer_phone'       => $this->request->post['receiver_mobile'],
            'customer_address'     => $this->request->post['receiver_address'],
            'latitude'             => $this->request->post['receiver_lat'],
            'longitude'            => $this->request->post['receiver_lng'],
            'job_pickup_datetime'  => $this->request->post['pickup_date'],
            'job_delivery_datetime'=> $this->request->post['delivery_date'],
            'Cash_needs_to_be_collected'   => $order['total'],
            'delivery_special_instruction' => $order['total'],
            'pickup_special_instruction'   => $order['total'],
          ];

          $response = $this->model_shipping_parcel->createShipment($data);

          if( $response['status_code'] == '200' && $response['result']['status'] == '200' ){  //succeeded
          	//update status & add history record
	    			if( !empty($parcel_settings['after_creation_status']) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'parcel - order_id => ' . $response['result']['data']['deliveries'][0]['order_id'],
				          'order_status_id'  => $parcel_settings['after_creation_status'],
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , $response['result']['data']['deliveries'][0]['result_tracking_link']);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['data']['deliveries'][0]['job_hash']);

      			//Returning to Order page
      			$result_json['success_msg'] = $response['result']['message'] ?? $this->language->get('text_shipment_created');
      			$result_json['success']     = '1';

            //redirect
  					$result_json['redirect'] = '1';
            $result_json['to'] = "sale/order/info?order_id=".$order_id;
          }
	    		else{
        		$result_json['success'] = '0';
					  $result_json['errors']  = $this->language->get('error_warning') . var_export($response, true);
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
	        'href' => $this->url->link('shipping/parcel', true)
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
		$this->load->language('shipping/parcel');

		if (!$this->user->hasPermission('modify', 'shipping/parcel')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

    //Shipper fields Validation...
    if ((utf8_strlen($this->request->post['store_name']) < 3) ) {
      $this->error['store_name'] = $this->language->get('error_store_name');
    }

    if( utf8_strlen($this->request->post['shipper_address']) < 3 ) {
        $this->error['shipper_address'] = $this->language->get('error_shipper_address');
    }

    // if( utf8_strlen($this->request->post['shipper_location']) < 3 ) {
    //     $this->error['shipper_location'] = $this->language->get('error_shipper_location');
    // }

    if( preg_match("^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$", $this->request->post['shipper_mobile']) ) {
        $this->error['shipper_mobile'] = $this->language->get('error_shipper_mobile');
    }


    //Reciever fields Validation...
    if( utf8_strlen($this->request->post['receiver_name']) < 3 ) {
        $this->error['receiver_name'] = $this->language->get('error_receiver_name');
    }

    if( utf8_strlen($this->request->post['receiver_address']) < 3 ) {
        $this->error['receiver_address'] = $this->language->get('error_receiver_address');
    }
    // if( utf8_strlen($this->request->post['receiver_email']) < 3 ) {
    //     $this->error['receiver_city'] = $this->language->get('error_receiver_city');
    // }

    if( preg_match("^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$", $this->request->post['receiver_mobile']) ) {
        $this->error['receiver_mobile'] = $this->language->get('error_receiver_mobile');
    }

    //Shipment date fields validations
    $pickup_date = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->post['pickup_date']);
    if( $pickup_date === FALSE){
      $this->error['pickup_date'] = $this->language->get('error_pickup_date');
    }

    $delivery_date = DateTime::createFromFormat('Y-m-d H:i:s', $this->request->post['delivery_date']);
    if( $delivery_date === FALSE){
      $this->error['delivery_date'] = $this->language->get('error_delivery_date');
    }

    if( !preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $this->request->post['shipper_lat']) ||
      empty($this->request->post['shipper_lat']) ) {
        $this->error['error_lat'] = $this->language->get('error_lat');
    }

    if( !preg_match('/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $this->request->post['shipper_lng']) ||
      empty($this->request->post['shipper_lng']) ) {
        $this->error['error_lng'] = $this->language->get('error_lng');
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
