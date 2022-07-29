<?php

class ControllerShippingDiggiPacks extends Controller {

  /**
   * @var array the validation errors array.
   */
  private $error = [];

  public function index(){
    $this->load->language('shipping/diggi_packs');
    $this->load->model('shipping/diggi_packs');
    $this->load->model('localisation/zone');

    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/diggi_packs/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['diggi_packs_secret_key']  = $this->config->get('diggi_packs_secret_key');
    $this->data['diggi_packs_uid_number']  = $this->config->get('diggi_packs_uid_number');

    $this->data['diggi_packs_sender_name'] = $this->config->get('diggi_packs_sender_name');
    $this->data['diggi_packs_cost'] = $this->config->get('diggi_packs_cost');
    $this->data['diggi_packs_sender_email'] = $this->config->get('diggi_packs_sender_email');
    $this->data['diggi_packs_sender_city'] = $this->config->get('diggi_packs_sender_city');
    $this->data['diggi_packs_sender_address'] =$this->config->get('diggi_packs_sender_address');
    $this->data['diggi_packs_sender_phone']= $this->config->get('diggi_packs_sender_phone');
    $this->data['diggi_packs_after_creation_status'] = $this->config->get('diggi_packs_after_creation_status');
    $this->data['diggi_packs_status']       = $this->config->get('diggi_packs_status');
    $this->data['diggi_packs_geo_zone_id']  = $this->config->get('diggi_packs_geo_zone_id');
    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses']    = $this->model_localisation_order_status->getOrderStatuses();
    // get Saudi Arabia cities
    $this->data['cities'] = $this->model_localisation_zone->getZonesByCountryIdAndLanguageId('184',1);
    //Breadcrumbs
    $this->data['breadcrumbs']       = $this->_createBreadcrumbs();
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/diggi_packs.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
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
        $this->load->language('shipping/diggi_packs');

        //Save diggi settings in settings table
        $this->model_setting_setting->insertUpdateSetting('diggi_packs', $this->request->post );

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

      $this->load->language('shipping/diggi_packs');
  		$this->load->model('sale/order');
      $this->load->model('shipping/diggi_packs');
      $this->load->model('localisation/zone');

  		$order = $this->model_sale_order->getOrder($order_id);

  		//Check if store order has shipping order already
  		if( !empty($order['tracking']) ){
  			$this->session->data['error'] = $this->language->get('text_order_already_exist');
  			$this->response->redirect($this->url->link('sale/order/info?order_id='.$order_id, '' , true));
  		}

  		$this->data['order'] = $order;
      $this->data['order']['weight'] = $this->model_sale_order->getOrderTotalWeight($order_id);
      // get Saudi Arabia cities
      $this->data['cities'] = $this->model_localisation_zone->getZonesByCountryIdAndLanguageId('184',1);

  	  $this->document->setTitle($this->language->get('create_heading_title'));

   		//Breadcrumbs
  	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

  		$this->template = 'shipping/diggi_packs/shipment/create.expand';
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
        $this->load->language('shipping/diggi_packs');
        $this->load->model('shipping/diggi_packs');
        $this->load->model('sale/order');
				$order_id = $this->request->post['order_id'];
				$order    = $this->model_sale_order->getOrder($order_id);
        $booking_mode = ($order['payment_code'] == "cod") ? "COD" : "CC";
        $weight = $this->request->post['weight'];
        $order_total_in_sar = $order['total'];
        if( $order['currency_code'] != "SAR" ){
          $order_total_in_usd = $this->currency->gatUSDRate($order['currency_code']) * $order['total'];
          $usd_to_sar_ratio = $this->currency->gatUSDRate('SAR');
          $order_total_in_sar = round(($order_total_in_usd / $usd_to_sar_ratio),4);
        }

        $reference_id = time()."_".$order_id;
        $param =
        [
          "sender_name"     => $this->config->get('diggi_packs_sender_name'),
          "sender_email"    => $this->config->get('diggi_packs_sender_email'),
          "origin"          => $this->config->get('diggi_packs_sender_city'),
          "sender_phone"    => $this->config->get('diggi_packs_sender_phone'),
          "sender_address"  => $this->config->get('diggi_packs_sender_address'),
          "receiver_name"   => $this->request->post['receiver_name'],
          "receiver_phone"  => $this->request->post['receiver_phone'],
          "destination"     => $this->request->post['receiver_city'],
          "BookingMode"     => $booking_mode,
          "receiver_address"=> $this->request->post['receiver_address'],
          "reference_id"    => $reference_id,
          "codValue"        => ($booking_mode == "COD") ?  $order_total_in_sar : 0,
          "productType"     => "parcel",
          "service"         => 3,
          "skudetails"      => array(
                                 array(
                                  'sku'         => "",
                                  'description' => "",
                                  'cod'         => ($booking_mode == "cod") ?  $order_total_in_sar : 0,
                                  'piece'       => "",
                                  'weight'         => $weight
                                ),
                              )
                                  
        ];
        $sign = $this->getCreateSign($param);
	    	$response = $this->model_shipping_diggi_packs->createShipment($sign,$param);
	    	if($response['status_code'] == 200 && isset($response['result']['order_id'])){//succeeded
    			//update status & add history record
    			if( !empty($this->config->get('diggi_packs_after_creation_status')) ){
			        $this->model_sale_order->addOrderHistory($order['order_id'], [
			          'comment'          => 'DiggiPacks . order reference: ' . $reference_id . ' & DiggiPacks tracking number: ' . $response['result']['awb_no'],
			          'order_status_id'  => $this->config->get('diggi_packs_after_creation_status'),
			        ]);
				   }

	        //Update Tracking Number & Tracking URL
	        $this->model_sale_order->updateShippingTrackingURL($order_id , 'https://client.diggipacks.com/track-shipment-'.$response['result']['awb_no']);
	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['result']['awb_no']);

    			//Returning to Order page
    			$result_json['success_msg'] = $response['result']['Success'];
    			$result_json['success']  = '1';
  					//redirect
					$result_json['redirect'] = '1';
			    $result_json['to'] = "sale/order/info?order_id=".$order_id;

        }
    		else{
    			$result_json['success'] = '0';
				  $result_json['errors']  = 'error: ' .implode(',', $response['result']);
    		}
  		}
			$this->response->setOutput(json_encode($result_json));
		}
		else{
			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
  }


  /* Private Methods */
  private function getCreateSign($param){
    $sign = $this->config->get('diggi_packs_secret_key')."customerId".$this->config->get('diggi_packs_uid_number')."formatjson"."methodcreateOrder"."signMethodmd5".json_encode($param).$this->config->get('diggi_packs_secret_key');
    return strtoupper(md5($sign));
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
        'href' => $this->url->link('shipping/diggi_packs', true)
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
    $this->load->language('shipping/diggi_packs');

    if (!$this->user->hasPermission('modify', 'shipping/diggi_packs')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('diggi_packs') ){
      $this->error['diggi_packs_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['diggi_packs_secret_key']) > 38) ) {
      $this->error['diggi_packs_secret_key'] = $this->language->get('error_secret_key');
    }

    if((utf8_strlen($this->request->post['diggi_packs_uid_number']) > 20) ) {
        $this->error['diggi_packs_uid_number'] = $this->language->get('error_uid_number');
    }

    if(utf8_strlen($this->request->post['diggi_packs_sender_name']) < 3){
      $this->error['diggi_packs_sender_name'] = $this->language->get('error_name');
    }
    if(utf8_strlen($this->request->post['diggi_packs_sender_city']) < 2){
      $this->error['diggi_packs_sender_city'] = $this->language->get('error_city');
    }
    if(utf8_strlen($this->request->post['diggi_packs_cost']) < 0){
      $this->error['diggi_packs_cost'] = $this->language->get('error_cost');
    }

    if( !isset($this->request->post['diggi_packs_sender_phone']) || !preg_match("/^\+?\d+$/", $this->request->post['diggi_packs_sender_phone']) ){
      $this->error['diggi_packs_sender_phone'] = $this->language->get('error_phone');
    }

    if( utf8_strlen($this->request->post['diggi_packs_sender_address']) < 4){
      $this->error['diggi_packs_sender_address'] = $this->language->get('error_address');
    }

    if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['diggi_packs_sender_email'])){
      $this->error['diggi_packs_sender_email'] = $this->language->get('error_email');
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
    $this->load->language('shipping/diggi_packs');

    if(utf8_strlen($this->request->post['receiver_name']) < 3){
      $this->error['receiver_name'] = $this->language->get('error_name');
    }
    if( !isset($this->request->post['receiver_phone']) || !preg_match("/^\+?\d+$/", $this->request->post['receiver_phone']) ){
      $this->error['receiver_phone'] = $this->language->get('error_phone');
    }

    if( utf8_strlen($this->request->post['receiver_address']) < 4){
      $this->error['receiver_address'] = $this->language->get('error_address');
    }

    if( utf8_strlen($this->request->post['receiver_city']) < 2){
      $this->error['receiver_city'] = $this->language->get('error_city');
    }
    if( utf8_strlen($this->request->post['wieght']) < 0){
      $this->error['wieght'] = $this->language->get('error_wieght');
    }

    if( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/", $this->request->post['receiver_email'])){
      $this->error['receiver_email'] = $this->language->get('error_email');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('errors_heading');
    }

    return $this->error;
  }


}
