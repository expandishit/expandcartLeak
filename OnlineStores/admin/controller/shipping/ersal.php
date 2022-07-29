<?php

class ControllerShippingErsal extends Controller {

  /**
   * @var array the validation errors array.
   */
  private $error = [];



  public function index(){


    $this->_setViewData();


    $this->_renderView();
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
        $this->load->language('shipping/ersal');

        //Save ersal settings in settings table
        $this->model_setting_setting->insertUpdateSetting('ersal', $this->request->post );

          
            $this->tracking->updateGuideValue('SHIPPING');

          $result_json['success_msg'] = $this->language->get('text_success');

        $result_json['success']  = '1';
      }

      $this->response->setOutput(json_encode($result_json));
    }
    else{
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }
  }


  public function create(){
  		$order_id = $this->request->get['order_id'];

      $this->load->language('shipping/ersal');
  		$this->load->model('sale/order');
      $this->load->model('shipping/ersal');

  		$order = $this->model_sale_order->getOrder($order_id);

  		//Check if store order has shipping order already
  		if( !empty($order['tracking']) ){
  			$this->session->data['error'] = $this->language->get('text_order_already_exist');
  			$this->response->redirect($this->url->link('sale/order/info?order_id='.$order_id, '' , true));
  		}

  		$this->data['order'] = $order;
      $this->data['order']['weight'] = $this->model_sale_order->getOrderTotalWeight($order_id);

      //Cities from fetchr
      $this->data['cities'] = $this->model_shipping_ersal->getCities($order['shipping_iso_code_2'])['data']['results'];

  		/*prepare ersal.expand view data*/
  	  $this->document->setTitle($this->language->get('create_heading_title'));

   		//Breadcrumbs
  	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

  		$this->template = 'shipping/ersal/shipment/create.expand';
  	  $this->children = ['common/header', 'common/footer'];

      $this->response->setOutput($this->render_ecwig());
  	}


  public function store(){
  		if( $this->_isAjax() && $this->request->server['REQUEST_METHOD'] == 'POST' ) {

  			//Validate form fields
  			if ( !empty($this->_validateShippingOrder()) ){
  				$result_json['success'] = '0';
  				$result_json['errors'] = $this->error;
  			}
  			else{
          $this->load->language('shipping/ersal');
          $this->load->model('shipping/ersal');
          $this->load->model('sale/order');

  				$order_id = $this->request->post['order_id'];
  				$order    = $this->model_sale_order->getOrder($order_id);

  				$data =
          [
            "orders" => [
              [
            		 "order_type"        => $this->request->post['order_type'] ?: 'forward-logistics',
            		 "commodity_value"   => $order['total'],
            		 "order_value"       => $order['total'],
            		 "order_value_currency"  => $order['currency_code'], // Order currency
            		 // "category_of_goods"     => "",
            		 "description"           => "FMLS - Qty: 1, ",
            		 "client_order_reference"=> $order_id . '-' . time(), //Order id
            		 // "bag_count"         => 1,
            		 // "dimensions"        =>[
              	// 		 "height" => "0",
              	// 		 "width"  => "0",
              	// 		 "breadth"=> "0",
              	// 		 "uom"    => "CM"
               //   ],
            		 "weight" => $this->request->post['weight'],
            		 "payment"  => [
            			 "collectible" => [
            				 "transaction_type" => "COD",
            				 "amount"           => $order['total'],
            				 "currency"         =>  $order['currency_code']
            			 ]
            		 ],
            		 "pickup" => [
            			 "address_id" => $this->config->get('ersal_pickup_address_id'),
            			 "comments"   => [""]
            		 ],
            		 "drop" => $this->request->post['customer']
            	 ],
            ]
          ];

  	    	$response = $this->model_shipping_ersal->createShipment($data);

  	    	if($response['status_code'] == 200 && $response['result']['success'] == 1){//succeeded
	    			//update status & add history record
	    			if( !empty($this->config->get('ersal_after_creation_status')) ){
				        $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment'          => 'ersal.us - so_no: ' . $response['result']['data']['orders'][0]['so_no'] . ' & client_order_reference: ' . $response['result']['data']['orders'][0]['client_order_reference'] . '  &  scheduling_link: '.$response['result']['data']['orders'][0]['scheduling_link'],
				          'order_status_id'  => $this->config->get('ersal_after_creation_status'),
				        ]);
  				   }

  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://tracking.ersal.om/track/'.$response['result']['data']['orders'][0]['tracking_number']);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['data']['orders'][0]['tracking_number']);

      			//Returning to Order page
      			$result_json['success_msg'] = $response['result']['message'];
      			$result_json['success']  = '1';
    					//redirect
  					$result_json['redirect'] = '1';
  			    $result_json['to'] = "sale/order/info?order_id=".$order_id;

          }
	    		else{
	    			$result_json['success'] = '0';
					  $result_json['errors']  = 'error: ' . ($response['result']['message'] ?: '') . '   - details : ' . var_export($response['result']['data']['orders'][0], true);
	    		}
    		}
  			$this->response->setOutput(json_encode($result_json));
  		}
  		else{
  			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
  		}
  	}


  /* Private Methods */
  private function _renderView(){
    /*prepare quick.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/ersal.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
  }

  private function _setViewData(){

    $this->load->language('shipping/ersal');

    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/ersal/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['ersal_api_key']      = $this->config->get('ersal_api_key');
    $this->data['ersal_client_id']    = $this->config->get('ersal_client_id');
    $this->data['ersal_after_creation_status'] = $this->config->get('ersal_after_creation_status');
    $this->data['ersal_status']       = $this->config->get('ersal_status');
    $this->data['ersal_geo_zone_id']  = $this->config->get('ersal_geo_zone_id');
    $this->data['ersal_pickup_address_id']  = $this->config->get('ersal_pickup_address_id');

    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses']    = $this->model_localisation_order_status->getOrderStatuses();

    //Breadcrumbs
    $this->data['breadcrumbs']       = $this->_createBreadcrumbs();
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
        'href' => $this->url->link('shipping/ersal', true)
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
    $this->load->language('shipping/ersal');

    if (!$this->user->hasPermission('modify', 'shipping/ersal')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('ersal') ){
      $this->error['ersal_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['ersal_api_key']) < 40) ) {
      $this->error['ersal_api_key'] = $this->language->get('error_api_key');
    }

    if((utf8_strlen($this->request->post['ersal_client_id']) < 36) ) {
        $this->error['ersal_client_id'] = $this->language->get('error_client_id');
    }

    if((utf8_strlen($this->request->post['ersal_pickup_address_id']) < 45) ) {
        $this->error['ersal_pickup_address_id'] = $this->language->get('error_ersal_pickup_address_id');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return !$this->error;
  }

  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }



  private function _validateShippingOrder(){
      $this->load->language('shipping/ersal');

      if(utf8_strlen($this->request->post['customer']['name']) < 3){
            $this->error['customer_name'] = $this->language->get('error_customer_name');
      }
      if( !isset($this->request->post['customer']['phone']) || !preg_match("/^\+?\d+$/", $this->request->post['customer']['phone']) ){
          $this->error['customer_phone'] = $this->language->get('error_phone');
      }

      if( utf8_strlen($this->request->post['customer']['address']['address_line_one']) < 4){
          $this->error['address_line_one'] = $this->language->get('error_address_line_one');
      }

      if( utf8_strlen($this->request->post['customer']['address']['city_code']) < 4){
          $this->error['city'] = $this->language->get('error_city');
      }

      if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['customer']['email'])){
          $this->error['email'] = $this->language->get('error_email');
      }

      if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('errors_heading');
        }

      return $this->error;
    }

}
