<?php

class ControllerSalePostaPlusShipmentOrder extends Controller {

	private $error = [];

 	public function __construct($registry){
    parent::__construct($registry);

    if (!$this->user->hasPermission('modify', 'shipping/postaplus')) {
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }

    if( !\Extension::isInstalled('postaplus') ){
      $this->response->redirect($this->url->link('error/permission', '', 'SSL'));
    }
  }

	/**
	* Display the creation form to fill in by the user.
	*/
	public function create(){
 		$this->load->language('shipping/postaplus');
    // $this->load->model('shipping/postaplus');

		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($this->request->get['order_id']);

		//Check if store order has shipping order already
		if( !empty($order['tracking']) ){
			$this->session->data['error'] = $this->language->get('text_order_already_exist');
			$this->response->redirect($this->url->link('sale/order/info?order_id='. $this->request->get['order_id'] , '' , true));
		}

    $this->data['order'] = $order;
    $this->data['default_currency'] = $this->config->get('config_currency');
    $this->data['shipper_email']    = $this->config->get('config_email');
    $this->data['shipper_address']      = $this->config->get('config_address')[$this->config->get('config_admin_language')];
    $this->data['shipper_title']        = $this->config->get('config_title')[$this->config->get('config_admin_language')];
		$this->data['shipper_telephone']    = $this->config->get('config_telephone');
    $this->data['shipper_country_id']    = $this->config->get('config_country_id');

    $this->load->model('localisation/country');
    $this->data['countries'] = $this->model_localisation_country->getCountries();


		/*prepare postaplus/create.expand view data*/
	  $this->document->setTitle($this->language->get('create_heading_title'));
 		//Breadcrumbs
	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
		$this->template = 'shipping/postaplus/shipment/create.expand';
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
          $this->load->language('shipping/postaplus');
          $this->load->model('shipping/postaplus');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);
          $weight   = $this->model_sale_order->getOrderTotalWeight($order_id);

          $data = [
            'SHIPINFO' => [
              'CashOnDelivery'         => $order['total'],
              'CashOnDeliveryCurrency' => $order['currency_code'],
              
              'ClientInfo'  => [
                'CodeStation'    => $this->config->get('postaplus_station_code'),
                'Password'       => $this->config->get('postaplus_password'),
                'ShipperAccount' => $this->config->get('postaplus_shipper_account_id'),
                'UserName'       => $this->config->get('postaplus_username'),   
              ],
              
              'CodeCurrency' => $this->request->post['currency_code'],
              'CodeService'  => $this->request->post['service_code'],
              'CodeShippmentType' => $this->request->post['shipment_type_code'],
              
              'ConnoteContact'    => [
                'Email1'    => $this->request->post['sender_email'],
                'Email2'    => $this->request->post['receiver_email'],
                'TelHome'   => $this->request->post['receiver_telephone'],
                'TelMobile' => $this->request->post['receiver_mobile'],
                'WhatsAppNumber' => $this->request->post['receiver_whatsapp_number']?:'',
              ],

              'ConnoteDescription'     => $this->request->post['shipment_description'],
              'ConnotePieces'          => $this->request->post['number_of_packages'],
              'ConnoteProhibited'      => 'N',
              'ConnoteRef' => [
                'Reference1'  => $order_id,
                'Reference2'  => $order_id,
              ],

              'Consignee'  => [
                'Company'     => $this->request->post['receiver_name'],
                'FromAddress' => $this->request->post['sender_address'],
                'FromArea'    => $this->request->post['sender_area']?:'NA',
                'FromCity'    => $this->request->post['sender_city']?:'NA',
                'FromCodeCountry' => $this->request->post['sender_country_code'],
                'FromMobile'  => $this->request->post['sender_mobile'],
                'FromName'    => $this->request->post['sender_name'],
                'FromProvince'=> $this->request->post['sender_province']?:'NA',
                'FromTelphone'=> $this->request->post['sender_telephone'],

                'ToAddress'   => $this->request->post['receiver_address'],
                'ToArea'      => $this->request->post['receiver_area']?:'NA', 
                'ToCity'      => $this->request->post['receiver_city']?:'NA', 
                'ToCodeCountry'=> $this->request->post['receiver_country_code'],
                'ToCodeSector' => 'NA',
                'ToMobile'     => $this->request->post['receiver_mobile'], 
                'ToName'       => $this->request->post['receiver_name'],
                'ToProvince'   => $this->request->post['receiver_province']?:'NA',
                'ToTelPhone'   => $this->request->post['receiver_telephone'],
              ],

              'CostShipment' => $order['total'],
              'ItemDetails' => [
                'ITEMDETAILS' => [
                  'ConnoteHeight' => 0,
                  'ConnoteLength' => 0,
                  'ConnoteWeight' => $weight?:1,
                  'ConnoteWidth'  => 0,
                  'ScaleWeight'   => 0,
                ]
              ],

              'NeedPickUp'    => 'N',
              'NeedRoundTrip' => 'N',
            ]
          ];

          $response = $this->model_shipping_postaplus->createShipment($data);

          //faild response
          if( is_array($response) && !empty($response['error']) ){
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $response['error'];
          }
          else if( strpos($response->Shipment_CreationResult , '(FALSE RESPONSE)') === 0  ){            
            $result_json['success'] = '0';
            $result_json['errors']  = 'ERROR: ' . $response->Shipment_CreationResult;
          }else{
            $new_air_waybill_number = $response->Shipment_CreationResult;
          	
            //update status & add history record
	    			if( !empty($this->config->get('postaplus_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'postaplus - shipment_id: ' . $new_air_waybill_number,
				          'order_status_id'  => $this->config->get('postaplus_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://www.postaplus.com/?trackid=' . $new_air_waybill_number .'&trackby=awb');
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $new_air_waybill_number);

      			//Returning to Order page
      			$result_json['success_msg'] = $this->language->get('text_shipment_created_successfully');
      			$result_json['success']     = '1';
    				
            //redirect
  					$result_json['redirect'] = '1';
  			    $result_json['to'] = "sale/order/info?order_id=".$order_id;
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
	        'href' => $this->url->link('shipping/postaplus', true)
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
		$this->load->language('shipping/postaplus');

		if (!$this->user->hasPermission('modify', 'shipping/postaplus')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['shipment_description']) < 3) ) {
		  $this->error['shipment_description'] = $this->language->get('error_shipment_description');
		}

		if( $this->request->post['number_of_packages'] <= 0 ) {
		    $this->error['number_of_packages'] = $this->language->get('error_number_of_packages');
		}

		if((utf8_strlen($this->request->post['sender_name']) < 3) ) {
		    $this->error['sender_name'] = $this->language->get('error_sender_name');
		}

    // if ((utf8_strlen($this->request->post['sender_email']) < 3) ) {
    //   $this->error['sender_email'] = $this->language->get('error_sender_email');
    // }

    if((utf8_strlen($this->request->post['sender_telephone']) < 6) ) {
        $this->error['sender_telephone'] = $this->language->get('error_sender_telephone');
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

    // if((utf8_strlen($this->request->post['receiver_email']) < 4) ) {
    //     $this->error['receiver_email'] = $this->language->get('error_receiver_email');
    // }

    if ((utf8_strlen($this->request->post['receiver_telephone']) < 5) ) {
      $this->error['receiver_telephone'] = $this->language->get('error_receiver_telephone');
    }

    if((utf8_strlen($this->request->post['receiver_mobile']) < 9) ) {
        $this->error['receiver_mobile'] = $this->language->get('error_receiver_mobile');
    }

    // if((utf8_strlen($this->request->post['receiver_whatsapp_number']) < 3) ) {
    //     $this->error['receiver_whatsapp_number'] = $this->language->get('error_drop_street');
    // }

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
