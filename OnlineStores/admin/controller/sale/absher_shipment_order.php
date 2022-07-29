<?php

class ControllerSaleAbsherShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
    parent::__construct($registry);

    if (!$this->user->hasPermission('modify', 'shipping/absher')) {
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }

    if( !\Extension::isInstalled('absher') ){
      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
    }
  }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/absher');
    // $this->load->model('shipping/absher');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}

		$this->data['order'] = $order;
    $this->data['default_currency'] = $this->config->get('config_currency');
    $this->data['shipper_address']      = $this->config->get('config_address')[$this->config->get('config_admin_language')];
    $this->data['shipper_title']        = $this->config->get('config_title')[$this->config->get('config_admin_language')];
    $this->data['shipper_telephone']    = $this->config->get('config_telephone');
    $this->data['cancel'] = $this->url->link('sale/order/info?order_id='.$this->request->get['order_id']);

		/*prepare absher/create.expand view data*/
	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/absher/shipment/create.expand';
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
          $this->load->language('shipping/absher');
          $this->load->model('shipping/absher');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $absher_email      = $this->config->get('absher_email');
          $absher_password   = $this->config->get('absher_password');

          $account_currency_code = $this->config->get('absher_account_currency');
          $total    = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);
          $weight   = $this->model_sale_order->getOrderTotalWeight($order_id) ?: 1;

          $data = [
            'sender_name'     => $this->request->post['sender_name'],
            'sender_city'     => $this->request->post['sender_city'],
            'sender_mobile'   => $this->request->post['sender_mobile'],
            'sender_address'  => $this->request->post['sender_address'],
            
            'productType'     => 'KVAIMI',
            'service'         => $this->request->post['service'],
            'password'        => $absher_password,
            'sender_email'    => $absher_email,

            'Receiver_name'    => $this->request->post['receiver_name'],
            'Receiver_email'   => $this->request->post['receiver_email'],
            'Receiver_address' => $this->request->post['receiver_address'],
            'Receiver_phone'   => $this->request->post['receiver_phone'],
            'Reciever_city'    => $this->request->post['reciever_city'],
            
            'Weight'           => $weight,            
            'Description'      => $this->request->post['description'],
            'NumberOfParcel'   => $this->request->post['number_of_parcels'],
            'BookingMode'      => $order['payment_code'] == 'cod' ? 'COD' : 'CC',
            'codValue'         => $order['payment_code'] == 'cod' ? $order['total'] : 0,
            'refrence_id'      => $order_id,
            'product_price'    => $order['total'],
          ];

          $response = $this->model_shipping_absher->createShipment($data);

          if( !empty($response['awb']) && !empty($response['awb_print_url']) ){  //succeeded
          	//update status & add history record
	    			if( !empty($this->config->get('absher_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'absher - Airway bill number (AWB): ' . $response['awb'] . 'Label Print URL: ' . $response['awb_print_url'],
				          'order_status_id'  => $this->config->get('absher_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://absher.fastcoo-solutions.com/lm/shipmentBookingApi_lm.php?awb_no=' . $response['awb']);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['awb']);

      			//Returning to Order page
      			$result_json['success_msg'] = $response['result']['message'];
      			$result_json['success']     = '1';

            //redirect
  					$result_json['redirect'] = '1';
            // $result_json['to'] = "sale/order/info?order_id=".$order_id;
  			    $result_json['to'] = $response['awb_print_url'];
          }
	    		else{
        		$result_json['success'] = '0';
					  $result_json['errors']  = 'ERROR: ' . implode(' - ', $response);
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
	        'href' => $this->url->link('shipping/absher', true)
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
		$this->load->language('shipping/absher');

		if (!$this->user->hasPermission('modify', 'shipping/absher')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}
  
    if ((utf8_strlen($this->request->post['description']) < 3) ) {
      $this->error['shipment_description'] = $this->language->get('error_shipment_description');
    }

    if( $this->request->post['number_of_parcels'] <= 0 ) {
        $this->error['number_of_parcels'] = $this->language->get('error_number_of_parcels');
    }

    if((utf8_strlen($this->request->post['sender_name']) < 3) ) {
        $this->error['sender_name'] = $this->language->get('error_sender_name');
    }


    if((utf8_strlen($this->request->post['sender_mobile']) < 9) ) {
        $this->error['sender_mobile'] = $this->language->get('error_sender_mobile');
    }

    if ((utf8_strlen($this->request->post['sender_address']) < 3) ) {
      $this->error['sender_address'] = $this->language->get('error_sender_address');
    }

    if((utf8_strlen($this->request->post['receiver_name']) < 3) ) {
        $this->error['receiver_name'] = $this->language->get('error_receiver_name');
    }

    if((utf8_strlen($this->request->post['receiver_email']) < 4) ) {
        $this->error['receiver_email'] = $this->language->get('error_receiver_email');
    }

    if ((utf8_strlen($this->request->post['receiver_phone']) < 5) ) {
      $this->error['receiver_phone'] = $this->language->get('error_receiver_phone');
    }

    if ((utf8_strlen($this->request->post['receiver_address']) < 3) ) {
      $this->error['receiver_address'] = $this->language->get('error_receiver_address');
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
