<?php

class ControllerShippingBarq extends Controller {

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
        $this->load->language('shipping/barq');

        //Save barq settings in settings table
        $this->model_setting_setting->insertUpdateSetting('barq', $this->request->post );

          
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

      $this->load->language('shipping/barq');
  		$this->load->model('sale/order');
      $this->load->model('shipping/barq');

  		$order = $this->model_sale_order->getOrder($order_id);
  		//check if store order has shipping order already
  		if(!empty($order['tracking'])){
  			$this->session->data['error'] = $this->language->get('text_order_already_exist');
  			$this->response->redirect($this->url->link('sale/order/info?order_id='.$order_id, '' , true));
  		}
      $token = $this->model_shipping_barq->login();
      // cities from barq
      $this->data['cities'] = $this->model_shipping_barq->getCities($token);
      // hubs from barq
      $this->data['hubs'] = $this->model_shipping_barq->getHubs($token);

  		$this->data['order'] = $order;
      $this->data['order']['weight'] = $this->model_sale_order->getOrderTotalWeight($order_id);


  		/*prepare barq.expand view data*/
  	  $this->document->setTitle($this->language->get('create_heading_title'));

   		//Breadcrumbs
  	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

  		$this->template = 'shipping/barq/shipment/create.expand';
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
          $this->load->language('shipping/barq');
          $this->load->model('shipping/barq');
          $this->load->model('sale/order');
          $order_id = $this->request->post['order_id'];
          $order    = $this->model_sale_order->getOrder($order_id);
          $token = $this->model_shipping_barq->login();

          $order_total_in_sar = $order['total'];
          if( $order['currency_code'] != "SAR" ){
            $order_total_in_usd = $this->currency->gatUSDRate($order['currency_code']) * $order['total'];
            $usd_to_sar_ratio = $this->currency->gatUSDRate('SAR');
            $order_total_in_sar = round(($order_total_in_usd / $usd_to_sar_ratio),4);
          }
          $cordinates = $this->model_shipping_barq->getGeocode($this->request->post['customer']['address']);
          $data = 
            [
              "payment_type" => ($order['payment_code'] == "cod") ? 1 : 0,
              "shipment_type" => 0,
              "hub_id" => '',
              "hub_code" => $this->config->get('barq_default_hub_code'),
              "merchant_order_id" => $order_id.time(),
              "invoice_total" => "$order_total_in_sar",
              "customer_details" => array(
                "first_name"=>$this->request->post['customer']['first_name'],
                "last_name"=>$this->request->post['customer']['last_name'],
                "country"=>"Saudi Arabia",
                "city"=> $this->request->post['city'],
                "mobile"=>$this->request->post['customer']['phone'],
                "address"=>$this->request->post['customer']['address']
              ),
              "products" => array(
                array(
                  "sku"=>"",
                  "serial_no"=>"",
                  "name"=>"",
                  "color"=>"",
                  "brand"=>"",
                  "price"=>$order_total_in_sar,
                  "weight_kg"=>$this->request->post['weight'],
                  "qty"=>1
                )
              ),
              "destination" => array(
                "latitude"=>$cordinates['lat'],
                "longitude"=>$cordinates['lng']
              ),
            ];

  	    	$response = $this->model_shipping_barq->createShipment($data,$token);
  	    	if(!empty($response['tracking_no'] )) {
            //succeeded
	    			//update status & add history record
	    			if( !empty($this->config->get('barq_after_creation_status')) ){
				      $this->model_sale_order->addOrderHistory($order['order_id'], [
				          'comment' => 'barq.shipment_id: ' . $response['id'] . ' & tracking_no: ' . $response['tracking_no'].' & merchant_order_id: ' . $response['merchant_order_id']. ' & delivery_hub: ' . $response['hub']['code'],
				          'order_status_id'  => $this->config->get('barq_after_creation_status'),
				        ]);
  				  }
            $test_mode = $this->config->get('barq_test_mode');
            if(!$test_mode)
              $tracking_url = "https://barqfleet.com/merchants/orders/".$response['id'];
            else
              $tracking_url = "https://staging.barqfleet.com/merchants/orders/".$response['id'];
  	        //Update Tracking Number & Tracking URL
  	        $this->model_sale_order->updateShippingTrackingURL($order_id , $tracking_url);
  	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $tracking_url);

      			//Returning to Order page
      			$result_json['success_msg'] = $this->language->get('text_success');
      			$result_json['success']  = '1';
    					//redirect
  					$result_json['redirect'] = '1';
  			    $result_json['to'] = "sale/order/info?order_id=".$order_id;

          }
	    		else{
	    			$result_json['success'] = '0';
					  $result_json['errors']  = 'error code : '.$response['code'].", ".$response['message'];
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
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/barq.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
  }

  private function _setViewData(){
    $this->load->language('shipping/barq');
    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/barq/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['barq_email']      = $this->config->get('barq_email');
    $this->data['barq_password']    = $this->config->get('barq_password');
    $this->data['barq_after_creation_status'] = $this->config->get('barq_after_creation_status');
    $this->data['barq_status']       = $this->config->get('barq_status');
    $this->data['barq_test_mode']       = $this->config->get('barq_test_mode');
    $this->data['barq_geo_zone_id']  = $this->config->get('barq_geo_zone_id');
    $this->data['barq_google_api_key']  = $this->config->get('barq_google_api_key');
    $this->data['barq_hubs']          = $this->config->get('barq_hubs'); 

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
        'href' => $this->url->link('shipping/barq', true)
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
    $this->load->language('shipping/barq');

    if (!$this->user->hasPermission('modify', 'shipping/barq')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('barq') ){
      $this->error['barq_not_installed'] = $this->language->get('error_not_installed');
    }

    if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['barq_email'])){
        $this->error['barq_email'] = $this->language->get('error_email');
    }

    if((utf8_strlen($this->request->post['barq_password']) < 2) ) {
        $this->error['barq_password'] = $this->language->get('barq_password');
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
    $this->load->language('shipping/barq');

    if(utf8_strlen($this->request->post['customer']['first_name']) < 3){
          $this->error['customer_name'] = $this->language->get('error_customer_name');
    }
    if(utf8_strlen($this->request->post['customer']['last_name']) < 3){
          $this->error['customer_name'] = $this->language->get('error_customer_name');
    }  

    if( !isset($this->request->post['customer']['phone']) || !preg_match("/^\+?\d+$/", $this->request->post['customer']['phone']) ){
        $this->error['customer_phone'] = $this->language->get('error_phone');
    }

    if( utf8_strlen($this->request->post['customer']['address']) < 4){
        $this->error['address'] = $this->language->get('error_address');
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

