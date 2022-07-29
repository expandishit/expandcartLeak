<?php

class ControllerSaleWagonShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
    parent::__construct($registry);

    if (!$this->user->hasPermission('modify', 'shipping/wagon')) {
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }

    if( !\Extension::isInstalled('wagon') ){
      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
    }
  }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/wagon');
    // $this->load->model('shipping/wagon');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}

		$this->data['order'] = $order;

		/*prepare wagon/create.expand view data*/
	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/wagon/shipment/create.expand';
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
          $this->load->language('shipping/wagon');
          $this->load->model('shipping/wagon');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $wagon_email      = $this->config->get('wagon_email');
          $wagon_password   = $this->config->get('wagon_password');
          $wagon_api_secret = $this->config->get('wagon_api_secret');
          $account_currency_code = $this->config->get('wagon_account_currency');
          $total = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);

          $data = [
            'email'         => $wagon_email,
            'password'      => $wagon_password,
            'secret_key'    => $wagon_api_secret,

            'pickup_area'      => $this->request->post['pickup_area'],
            'pickup_block'     => $this->request->post['pickup_block'],
            'pickup_street'    => $this->request->post['pickup_street'],
            'pickup_address'   => $this->request->post['pickup_address'],
            'pickup_latitude'  => $this->request->post['pickup_latitude'],
            'pickup_longitude' => $this->request->post['pickup_longitude'],

            'drop_area'      => $this->request->post['drop_area'],
            'drop_block'     => $this->request->post['drop_block'],
            'drop_street'    => $this->request->post['drop_street'],
            'drop_address'   => $this->request->post['drop_address'],
            'drop_latitude'  => $this->request->post['drop_latitude'],
            'drop_longitude' => $this->request->post['drop_longitude'],

            'receiver_name'  => $this->request->post['receiver_name'],
            'receiver_phone' => $this->request->post['receiver_phone'],

            'shipment_package_name'  => STORECODE . ' API, Created By ExpandCart',
            'shipment_package_value' => $total,
            'invoice_no'             => $order_id,

            'scheduled_date' => $this->request->post['scheduled_date'],
            'scheduled_time' => $this->request->post['scheduled_time'],
          ];
  	    	
          $response = $this->model_shipping_wagon->createShipment($data);

          if($response['result']['status'] == 1 ){  //succeeded && isset($response['result']['​data']['shipment_id'])
          	//update status & add history record
	    			if( !empty($this->config->get('wagon_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'wagon - shipment_id: ' . $response['result']['data']['shipment_id'],
				          'order_status_id'  => $this->config->get('wagon_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        // $this->model_sale_order->updateShippingTrackingURL($order_id , '');
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['data']['shipment_id']);

      			//Returning to Order page
      			$result_json['success_msg'] = $response['result']['message'];
      			$result_json['success']     = '1';
    				
            //redirect
  					$result_json['redirect'] = '1';
  			    $result_json['to'] = "sale/order/info?order_id=".$order_id;
          }
	    		else{
        		$result_json['success'] = '0';
					  $result_json['errors']  = 'ERROR: ' . ($response['result']['message'] ?: '');
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
	        'href' => $this->url->link('shipping/wagon', true)
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
		$this->load->language('shipping/wagon');

		if (!$this->user->hasPermission('modify', 'shipping/wagon')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['receiver_name']) < 3) ) {
		  $this->error['receiver_name'] = $this->language->get('error_receiver_name');
		}

		if((utf8_strlen($this->request->post['receiver_phone']) < 8) ) {
		    $this->error['receiver_phone'] = $this->language->get('error_receiver_phone');
		}

		if((utf8_strlen($this->request->post['pickup_address']) < 3) ) {
		    $this->error['pickup_address'] = $this->language->get('error_pickup_address');
		}

    if ((utf8_strlen($this->request->post['pickup_area']) < 3) ) {
      $this->error['pickup_area'] = $this->language->get('error_pickup_area');
    }

    if((utf8_strlen($this->request->post['pickup_block']) < 1) ) {
        $this->error['pickup_block'] = $this->language->get('error_pickup_block');
    }

    if((utf8_strlen($this->request->post['pickup_street']) < 3) ) {
        $this->error['pickup_street'] = $this->language->get('error_pickup_street');
    }

    if ((utf8_strlen($this->request->post['pickup_latitude']) < 1) ) {
      $this->error['pickup_latitude'] = $this->language->get('error_pickup_latitude');
    }

    if((utf8_strlen($this->request->post['pickup_longitude']) < 1) ) {
        $this->error['pickup_longitude'] = $this->language->get('error_pickup_longitude');
    }

    if((utf8_strlen($this->request->post['drop_address']) < 3) ) {
        $this->error['drop_address'] = $this->language->get('error_drop_address');
    }

    if ((utf8_strlen($this->request->post['drop_area']) < 3) ) {
      $this->error['drop_area'] = $this->language->get('error_drop_area');
    }

    if((utf8_strlen($this->request->post['drop_block']) < 1) ) {
        $this->error['drop_block'] = $this->language->get('error_drop_block');
    }

    if((utf8_strlen($this->request->post['drop_street']) < 3) ) {
        $this->error['drop_street'] = $this->language->get('error_drop_street');
    }

    if ((utf8_strlen($this->request->post['drop_latitude']) < 1) ) {
      $this->error['drop_latitude'] = $this->language->get('error_drop_latitude');
    }

    if((utf8_strlen($this->request->post['drop_longitude']) < 1) ) {
        $this->error['drop_longitude'] = $this->language->get('error_drop_longitude');
    }

    if (!isset($this->error['pickup_latitude']) || empty($this->error['pickup_latitude'])){
        if (!$this->_validateLatitude($this->request->post['pickup_latitude']))
            $this->error['pickup_latitude'] = $this->language->get('error_pickup_latitude_format');
    }
    if (!isset($this->error['drop_latitude']) || empty($this->error['drop_latitude'])){
        if(!$this->_validateLatitude($this->request->post['drop_latitude']))
            $this->error['drop_latitude'] = $this->language->get('error_drop_latitude_format');
    }

    if (!isset($this->error['pickup_longitude']) || empty($this->error['pickup_longitude'])){
        if (!$this->_validateLongitude($this->request->post['pickup_longitude']))
            $this->error['pickup_longitude'] = $this->language->get('error_pickup_longitude_format');
    }
    if (!isset($this->error['drop_longitude']) || empty($this->error['drop_longitude'])){
        if (!$this->_validateLongitude($this->request->post['drop_longitude']))
            $this->error['drop_longitude'] = $this->language->get('error_drop_longitude_format');
    }

    if (!isset($this->error['receiver_phone']) || empty($this->error['receiver_phone'])){
        if (!$this->_validateGeneralPhoneNumberFormat($this->request->post['receiver_phone']))
            $this->error['receiver_phone'] = $this->language->get('error_receiver_phone_format');
    }



		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}
		
    return $this->error;
	}


  private function _convertAmountToAccountCurrency($total, $currency_code, $account_currency_code){

    if( !$total || !$currency_code ) return 0;

    $currency_code = strtoupper($currency_code);

    if( $currency_code === $account_currency_code ){
      return round($total, 2);
    }
    elseif ( $currency_code !== 'USD' ) {
      $currenty_rate     = $this->currency->gatUSDRate($currency_code);
      $amount_in_dollars = $currenty_rate * $total;

      $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
      $final_amount         = $amount_in_dollars / $target_currency_rate;
      return round($final_amount, 2);
    }
    //If USD convert it directly to Account Currency
    else{
      $target_currency_rate = $this->currency->gatUSDRate($account_currency_code);
      $final_amount         = $total / $target_currency_rate;
      return round($final_amount, 2);
    }
  }

  private function _isAjax() {
    
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }


    // The latitude must be a number between -90 and 90
    private function _validateLatitude($lat) {
        return preg_match('/^(\+|-)?(?:90(?:(?:\.0{1,20})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,20})?))$/', $lat);
    }
    // the longitude must be a number between -180 and 180
    private function _validateLongitude($long) {
        return preg_match('/^(\+|-)?(?:180(?:(?:\.0{1,20})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,20})?))$/', $long);
    }

    // validate phone before send to api
    private function _validateGeneralPhoneNumberFormat($number){
        $replace = array( ' ', '-', '/', '(', ')', ',', '.'  , '+');
        $number = str_replace( $replace, '', trim($number) );
        $english = array('0','1','2','3','4','5','6','7','8','9');
        $arabic = array('٠','١','٢','٣','٤','٥','٦','٧','٨','٩');
        $number =  str_replace($arabic, $english, $number);
        return (is_numeric($number));
    }

}
