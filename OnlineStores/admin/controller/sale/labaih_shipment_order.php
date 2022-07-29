<?php

class ControllerSaleLabaihShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
	    parent::__construct($registry);

	    if (!$this->user->hasPermission('modify', 'shipping/labaih')) {
	      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
	    }

	    if( !\Extension::isInstalled('labaih') ){
	      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
	    }
    }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
   		$this->load->language('shipping/labaih');
      	// $this->load->model('shipping/labaih');

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
      $this->data['shipper_district']  = explode(',', $this->data['shipper_address'] )[1];
      $this->data['shipper_city']      = explode(',', $this->data['shipper_address'] )[2];
      $this->data['shipper_telephone'] = $this->config->get('config_telephone');
      $this->data['shipper_location']  = $this->config->get('config_location');
	    $this->data['shipper_email']     = $this->config->get('config_email');
	    $this->data['cancel'] = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

		  /*prepare labaih/create.expand view data*/
	  	$this->document->setTitle($this->language->get('create_heading_title'));
 		  //Breadcrumbs
	  	$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		  $this->template = 'shipping/labaih/shipment/create.expand';
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
          $this->load->language('shipping/labaih');
          $this->load->model('shipping/labaih');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $labaih_settings     = $this->config->get('labaih');

          $account_currency_code = $labaih_settings['account_currency'];
          $total    = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);
          $weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;

          $data = [
            'api_key'          => $labaih_settings['api_key'],
            'pickupDate'       => $this->request->post['pickup_date'],
            'deliveryDate'     => $this->request->post['delivery_date'],
            
            'consigneeName'    => $this->request->post['receiver_name'],
            'consigneeMobile'  => $this->request->post['receiver_mobile'],
            'consigneeAddress' => $this->request->post['receiver_address'],
            'consigneeCity'    => $this->request->post['receiver_city'],

            'store'            => $this->request->post['store_name'],
            'shipperName'      => $this->request->post['shipper_name'],
            'shipperMobile'    => $this->request->post['shipper_mobile'],
            'shipperEmail'     => $this->request->post['shipper_email'],
            'shipperCity'      => $this->request->post['shipper_city'],
            'shipperDistrict'  => $this->request->post['shipper_district'],
            'shipperAddress'   => $this->request->post['shipper_address'],
            'shipperLatLong'   => $this->request->post['shipper_location'],
            'paymentMethod'    => $order['payment_code'] == 'cod' ? 'cod' : 'prepaid', //cod/prepaid
            'paymentAmount'    => $total,
          ];

          $response = $this->model_shipping_labaih->createShipment($data);

          if( $response['status'] == '200' && $response['message'] == 'Success' ){  //succeeded
          	//update status & add history record
	    			if( !empty($labaih_settings['after_creation_status']) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'labaih - consignmentNo: ' . $response['consignmentNo'] . 'Label Print URL: ' . $response['shipmentLabel'],
				          'order_status_id'  => $labaih_settings['after_creation_status'],
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , $response['liveTrackingLink']);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['consignmentNo']);
            $this->model_sale_order->updateShippingLabelURL($order_id , $response['shipmentLabel']);

      			//Returning to Order page
      			$result_json['success_msg'] = $response['message'];
      			$result_json['success']     = '1';

            //redirect
  					$result_json['redirect'] = '1';
            // $result_json['to'] = "sale/order/info?order_id=".$order_id;
  			    $result_json['to'] = $response['shipmentLabel'];
          }
	    		else{
        		$result_json['success'] = '0';
					  $result_json['errors']  = 'ERROR: ' . var_export($response, true);
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
	        'href' => $this->url->link('shipping/labaih', true)
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
		$this->load->language('shipping/labaih');

		if (!$this->user->hasPermission('modify', 'shipping/labaih')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}
  

    //Shipper fields Validation...
    if ((utf8_strlen($this->request->post['store_name']) < 3) ) {
      $this->error['store_name'] = $this->language->get('error_store_name');
    }

    if( utf8_strlen($this->request->post['shipper_name']) < 3 ) {
        $this->error['shipper_name'] = $this->language->get('error_shipper_name');
    }

    if( utf8_strlen($this->request->post['shipper_district']) < 3 ) {
        $this->error['shipper_district'] = $this->language->get('error_shipper_district');
    }

    if( utf8_strlen($this->request->post['shipper_city']) < 3 ) {
        $this->error['shipper_city'] = $this->language->get('error_shipper_city');
    }

    if( utf8_strlen($this->request->post['shipper_address']) < 3 ) {
        $this->error['shipper_address'] = $this->language->get('error_shipper_address');
    }

    if( utf8_strlen($this->request->post['shipper_location']) < 3 ) {
        $this->error['shipper_location'] = $this->language->get('error_shipper_location');
    }

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
    if( utf8_strlen($this->request->post['receiver_city']) < 3 ) {
        $this->error['receiver_city'] = $this->language->get('error_receiver_city');
    }

    if( preg_match("^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$", $this->request->post['receiver_mobile']) ) {
        $this->error['receiver_mobile'] = $this->language->get('error_receiver_mobile');
    }

    //Shipment date fields validations
    $pickup_date = DateTime::createFromFormat('Y-m-d', $this->request->post['pickup_date']);
    if( $pickup_date === FALSE){
      $this->error['pickup_date'] = $this->language->get('error_pickup_date');            
    }

    $delivery_date = DateTime::createFromFormat('Y-m-d', $this->request->post['delivery_date']);
    if( $delivery_date === FALSE){
      $this->error['delivery_date'] = $this->language->get('error_delivery_date');            
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

}


