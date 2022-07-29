<?php

class ControllerSaleAyMakanShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
    parent::__construct($registry);

    if (!$this->user->hasPermission('modify', 'shipping/aymakan')) {
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }

    if( !\Extension::isInstalled('aymakan') ){
      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
    }
  }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/aymakan');
    // $this->load->model('shipping/aymakan');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}

		$this->data['order'] = $order;
    $this->data['shipper_email']      = $this->config->get('config_email');
    $this->data['shipper_address']    = $this->config->get('config_address')[$this->config->get('config_admin_language')];
    $this->data['shipper_title']      = $this->config->get('config_title')[$this->config->get('config_admin_language')];
		$this->data['shipper_telephone']  = $this->config->get('config_telephone');
    $this->data['shipper_country_id'] = $this->config->get('config_country_id');

		$this->load->model('shipping/aymakan');
    $this->data['cities']             = $this->model_shipping_aymakan->getCities();
		$this->data['neighbourhoods']     = $this->model_shipping_aymakan->getNeighbourhoods();

    $this->load->model('localisation/country');
    $this->data['countries'] = $this->model_localisation_country->getCountries();


		/*prepare aymakan/create.expand view data*/
	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/aymakan/shipment/create.expand';
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
          $this->load->language('shipping/aymakan');
          $this->load->model('shipping/aymakan');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

          $aymakan_email      = $this->config->get('aymakan_email');
          $aymakan_password   = $this->config->get('aymakan_password');
          $aymakan_api_secret = $this->config->get('aymakan_api_secret');
          $account_currency_code = $this->config->get('aymakan_account_currency');
          $total = $this->_convertAmountToAccountCurrency($order['total'], $order['currency_code'], $account_currency_code);

          $data = [
            'requested_by'    => (string)$this->request->post['requested_by'],
            'declared_value'  => (float)$total,
            'declared_value_currency' => (string)$account_currency_code,
            'reference'       => (string)$order_id,
            'is_cod'          => $order['payment_code'] == 'cod' ? 1 : $order['payment_method'],
            'currency'        => (string)$account_currency_code,
            'delivery_name'   => (string)$this->request->post['receiver_name'],
            'delivery_email'  => (string)$this->request->post['receiver_email'],
            'delivery_city'   => (string)$this->request->post['receiver_city'],
            'delivery_address'=> (string)$this->request->post['receiver_address'],
            'delivery_country'=> (string)$this->request->post['receiver_country'],
            'delivery_phone'  => $this->request->post['receiver_phone'],
            'delivery_neighbourhood' => (string)$this->request->post['receiver_neighbourhood'],
            'collection_name'    => (string)$this->request->post['sender_name'],
            'collection_email'   => (string)$this->request->post['sender_email'],
            'collection_city'    => (string)$this->request->post['sender_city'],
            'collection_address' => (string)$this->request->post['sender_address'],
            'collection_neighbourhood' => (string)$this->request->post['sender_neighbourhood'],
            'collection_country' => (string)$this->request->post['sender_country'],
            'collection_phone'   => $this->request->post['sender_phone'],
            'pieces'             => (int)$this->request->post['number_of_packages'],
            'items_count'        => count($this->model_sale_order->getOrderProducts($order_id ))
          ];

          if($data['is_cod']){
            $data['cod_amount']= (float)$total;
          }elseif($data['is_cod'] == 0){
            $data['delivery_description'] = (float)$total;
          }
          $response = $this->model_shipping_aymakan->createShipment($data);

          $error    = $response['error'];
          $response = $response['result'];
          
          if($error){
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $error;          
          }
          else if($response['success'] == TRUE && isset($response['data']['shipping']['tracking_number']) ){  //succeeded

            $tracking_number = $response['data']['shipping']['tracking_number'];
            $tracking_url    = $this->config->get('aymakan_debugging_mode') ? 'https://dev.aymakan.com.sa/en/tracking/' . $tracking_number : 'https://aymakan.com.sa/en/tracking/' . $tracking_number;
            $pdf_label_url   = $response['data']['shipping']['pdf_label'];

            //update status & add history record
	    			if( !empty($this->config->get('aymakan_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'aymakan - shipment_id: ' . $tracking_number . '  PDF_LABEL: ' . $pdf_label_url,
				          'order_status_id'  => $this->config->get('aymakan_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
						$this->model_sale_order->updateShippingTrackingURL($order_id , $tracking_url);
  	        $this->model_sale_order->updateShippingLabelURL($order_id , $pdf_label_url);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $tracking_number);

      			//Returning to Order page
      			$result_json['success_msg'] = $this->language->get('text_shipment_created_successfully');
      			$result_json['success']     = '1';

            //redirect
  					$result_json['redirect'] = '1';
            $result_json['to'] = $pdf_label_url;
            // $result_json['to'] = "sale/order/info?order_id=".$order_id;
          }
	    		else{
        		$result_json['success'] = '0';
					  $result_json['errors']  = 'ERROR: ' . $response['message'] . ' - details: ' . var_export($response['errors'], true);
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
	        'href' => $this->url->link('shipping/aymakan', true)
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
		$this->load->language('shipping/aymakan');

		if (!$this->user->hasPermission('modify', 'shipping/aymakan')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

    if ((utf8_strlen($this->request->post['requested_by']) < 1) ) {
		  $this->error['receiver_name'] = $this->language->get('error_receiver_name');
		}

    if ((utf8_strlen($this->request->post['receiver_name']) < 1) ) {
		  $this->error['receiver_name'] = $this->language->get('error_receiver_name');
		}

    if ((utf8_strlen($this->request->post['receiver_email']) < 4)  || !filter_var($this->request->post['receiver_email'], FILTER_VALIDATE_EMAIL) ) {
		  $this->error['receiver_email'] = $this->language->get('error_receiver_email');
		}

    // if ((utf8_strlen($this->request->post['receiver_city']) < 3) ) {
		//   $this->error['receiver_city'] = $this->language->get('error_receiver_city');
		// }

		if ((utf8_strlen($this->request->post['receiver_address']) < 3) ) {
		  $this->error['receiver_address'] = $this->language->get('error_receiver_address');
		}

		// if((utf8_strlen($this->request->post['receiver_country']) < 8) ) {
		//     $this->error['receiver_country'] = $this->language->get('error_receiver_country');
		// }

		if((utf8_strlen($this->request->post['receiver_phone']) < 5) ) {
		    $this->error['receiver_phone'] = $this->language->get('error_receiver_phone');
		}

    if ((utf8_strlen($this->request->post['sender_name']) < 1) ) {
      $this->error['sender_name'] = $this->language->get('error_sender_name');
    }

    if((utf8_strlen($this->request->post['sender_email']) < 4) || !filter_var($this->request->post['sender_email'], FILTER_VALIDATE_EMAIL) ) {
        $this->error['sender_email'] = $this->language->get('error_sender_email');
    }

    // if((utf8_strlen($this->request->post['sender_city']) < 3) ) {
    //     $this->error['sender_city'] = $this->language->get('error_sender_city');
    // }

    if ((utf8_strlen($this->request->post['sender_address']) < 3) ) {
      $this->error['sender_address'] = $this->language->get('error_sender_address');
    }

    if((utf8_strlen($this->request->post['sender_neighbourhood']) < 1) ) {
        $this->error['sender_neighbourhood'] = $this->language->get('error_neighbourhood');
    }

    if((utf8_strlen($this->request->post['receiver_neighbourhood']) < 1) ) {
        $this->error['receiver_neighbourhood'] = $this->language->get('error_neighbourhood');
    }

    if((utf8_strlen($this->request->post['sender_phone']) < 5) ) {
        $this->error['sender_phone'] = $this->language->get('error_sender_phone');
    }

    if ($this->request->post['number_of_packages'] < 1 ) {
      $this->error['number_of_packages'] = $this->language->get('error_number_of_packages');
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
