<?php

class ControllerShippingBowsala extends Controller {

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
        $this->load->language('shipping/bowsala');

        //Save bowsala settings in settings table
        $this->model_setting_setting->insertUpdateSetting('bowsala', $this->request->post );

        
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

      $this->load->language('shipping/bowsala');
  		$this->load->model('sale/order');
      $this->load->model('shipping/bowsala');

  		$order = $this->model_sale_order->getOrder($order_id);

  		//Check if store order has shipping order already
  		if( !empty($order['tracking']) ){
  			$this->response->redirect($this->url->link('shipping/bowsala/trackShipment?order_id='.$order_id, '' , true));
  		}

  		$this->data['order'] = $order;
      $this->data['weight'] = $this->model_sale_order->getOrderTotalWeight($order_id);
      // cities from bowsala
      $this->data['cities'] = $this->model_shipping_bowsala->getCities();

  		/*prepare bowsala.expand view data*/
  	  $this->document->setTitle($this->language->get('create_heading_title'));

   		//Breadcrumbs
  	  $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

  		$this->template = 'shipping/bowsala/shipment/create.expand';
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
        $this->load->language('shipping/bowsala');
        $this->load->model('shipping/bowsala');
        $this->load->model('sale/order');

        $lang = $this->config->get('config_admin_language');
				$order_id = $this->request->post['order_id'];
				$order    = $this->model_sale_order->getOrder($order_id);
        $payment_mode = ($order['payment_code'] == "cod") ? "Cash" : "";


        $order_total_in_sar = $order['total'];
        if( $order['currency_code'] != "SAR" && $order['payment_code'] == "cod"){
          $order_total_in_usd = $this->currency->gatUSDRate($order['currency_code']) * $order['total'];
          $usd_to_sar_ratio = $this->currency->gatUSDRate('SAR');
          $order_total_in_sar = round(($order_total_in_usd / $usd_to_sar_ratio),4);
        }

        if($order['payment_code'] != "cod")
          $order_total_in_sar="";

        $loginResponse = $this->model_shipping_bowsala->login();
        $ship_headers['access_token'] = $loginResponse['data']['access_token']['id'];
        $ship_headers['customer_id'] = $loginResponse['data']['Customer']['id'];
        $ship_headers['org_id'] = $loginResponse['data']['organisation_id'];
        $customer_code = $loginResponse['data']['Customer']['code'];
        $loginResponse = $this->model_shipping_bowsala->login();

        $srcCityName = $this->model_shipping_bowsala->getCityName($this->config->get('bowsala_city_pincode'));
        $dstCityName = $this->model_shipping_bowsala->getCityName($this->request->post['pincode']);

        $ship_data = 
        [
          "customerReferenceNumber"=>$order_id,
          "referenceNumber"=>NULL,
          "courierType"=>"NON-DOCUMENT",
          "consignmentType"=>"forward",
          "consignmentCategory"=>"domestic",
          "piecesDetail"=>array(
            array(
              "weight" =>"",
              "length" =>"",
              "height" =>"",
              "width" =>""
            )
          ),
          "allPiecesWithSameDimensions" => true,
          "shipmentPurpose"=>NULL,
          "srcAddress" => array(
            "pincode"=>$srcCityName,
            "name"=>$this->config->get('config_name')[$lang],
            "phone"=>$this->config->get('config_telephone'),
            "addressLine1"=>$this->config->get('config_address')[$lang],
            "cityName"=>$this->config->get('config_address')[$lang],
            "stateName"=>"",
            "isInternational"=>false,
            "id"=>""
          ),
          "dstAddress" => array(
            "pincode"=>$this->request->post['pincode'],
            "name"=>$this->request->post['name'],
            "phone"=>$this->request->post['phone'],
            "addressLine1"=>$this->request->post['address'],
            "cityName"=>$dstCityName,
            "stateName"=>"",
            "isInternational"=>false,
            "id"=>""
          ),
          "numberOfPieces"=>1,
          "weight"=>$this->request->post['weight'],
          "dimensions"=>array(
            "length"=>"",
            "width"=>"",
            "height"=>""            
          ),
          "serviceType"=>"PREMIUM",
          "declaredPrice"=>0,
          "isRiskSurchargeApplicable"=>false,
          "commodityId"=>"7",
          "codCollectionMode"=>$payment_mode,
          "codAmount"=>$order_total_in_sar,
          "description"=>$this->request->post['notes'],
          "ewayBill" =>array(),
          "childClient"=> array(
            "code"=>$customer_code,
            "id"=>$ship_headers['customer_id']
          )
        ];

        $response = $this->model_shipping_bowsala->createShipment($ship_headers,$ship_data);
	    	if($response['status'] == "OK"){//succeeded
    			//update status & add history record
    			if( !empty($this->config->get('bowsala_after_creation_status')) ){
			        $this->model_sale_order->addOrderHistory($order['order_id'], [
			          'comment'          => 'bowsala.referenceNumber: ' . $response['data']['referenceNumber'],
			          'order_status_id'  => $this->config->get('bowsala_after_creation_status'),
			        ]);
				   }

	        $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['data']['referenceNumber']);

    			//Returning to Order page
    			$result_json['success_msg'] = $this->language->get('text_success');;
    			$result_json['success']  = '1';
  				//redirect
					$result_json['redirect'] = '1';
			    $result_json['to'] = "sale/order/info?order_id=".$order_id;

        }
    		else{
    			$result_json['success'] = '0';
          if(!empty($loginResponse['error'])) {
            $result_json['errors']  = $loginResponse['error']['message'].": Status Code ".$loginResponse["error"]["statusCode"]." ,Reason: ".$loginResponse["error"]["reason"].$this->language->get('error_credentials');
          }
          else{
            $result_json['errors']  = $response['error']['message'];
          }
    		}
  		}
			$this->response->setOutput(json_encode($result_json));
		}
		else{
			$this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
  }

  public function trackShipment()
  {
    // load Language File
    $this->language->load('shipping/bowsala');
    // set Page Title
    $this->document->setTitle($this->language->get('heading_title_bowsala'));
    $this->load->model('sale/order');
    $this->load->model("shipping/bowsala");
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    // get order id
    $order_id = $this->request->get['order_id'];
    $this->data['order_id'] = $order_id;
    $order = $this->model_sale_order->getOrder($order_id);
    $loginResponse = $this->model_shipping_bowsala->login();
    $ship_headers['access_token'] = $loginResponse['data']['access_token']['id'];
    $ship_headers['customer_id'] = $loginResponse['data']['Customer']['id'];
    $ship_headers['org_id'] = $loginResponse['data']['organisation_id'];

    $trackResponse = $this->model_shipping_bowsala->trackShipment($ship_headers,$order['tracking']);
    if($trackResponse['status']=="OK"){
        $this->data['trackingEvents'] = $trackResponse['data']['events'];
    }else{
        $result_json['success'] = '0';
        $result_json['errors'] = $trackResponse['error']['message'];
        $result_json['errors']['warning'] = $this->language->get('bowsala_error_warning');
        $this->response->setOutput(json_encode($result_json));
    }


    $this->template = 'shipping/bowsala/shipment/track.expand';
    $this->children = array(
        'common/footer',
        'common/header'
    );
    $this->response->setOutput($this->render_ecwig());
    return;
  }

  /* Private Methods */
  private function _renderView(){
    /*prepare quick.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/bowsala.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
  }

  private function _setViewData(){

    $this->load->language('shipping/bowsala');

    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/bowsala/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['bowsala_city_pincode']      = $this->config->get('bowsala_city_pincode');
    $this->data['bowsala_user_name']  = $this->config->get('bowsala_user_name');
    $this->data['bowsala_password']  = $this->config->get('bowsala_password');
    $this->data['bowsala_after_creation_status'] = $this->config->get('bowsala_after_creation_status');
    $this->data['bowsala_status']       = $this->config->get('bowsala_status');
    $this->data['bowsala_price']        = $this->config->get('bowsala_price');

    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses']    = $this->model_localisation_order_status->getOrderStatuses();

    $this->load->model('shipping/bowsala');
    $this->data['cities'] = $this->model_shipping_bowsala->getCities();

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
        'href' => $this->url->link('shipping/bowsala', true)
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
    $this->load->language('shipping/bowsala');

    if (!$this->user->hasPermission('modify', 'shipping/bowsala')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('bowsala') ){
      $this->error['bowsala_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['bowsala_city_pincode']) < 2) ) {
      $this->error['bowsala_city_pincode'] = $this->language->get('error_city');
    }

    if((utf8_strlen($this->request->post['bowsala_password']) < 1) ) {
        $this->error['bowsala_password'] = $this->language->get('error_bowsala_password');
    }

    if((utf8_strlen($this->request->post['bowsala_user_name']) < 1) ) {
        $this->error['bowsala_user_name'] = $this->language->get('error_bowsala_user_name');
    }

    if (!$this->request->post['bowsala_price']['bowsala_weight_rate_class_id'] || empty($this->request->post['bowsala_price']['bowsala_weight_rate_class_id']) ){
            $this->error['bowsala_weight_rate_class_id'] = $this->language->get('error_bowsala_weight_rate_class_id');
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
      $this->load->language('shipping/bowsala');

      if(utf8_strlen($this->request->post['name']) < 3){
            $this->error['customer_name'] = $this->language->get('error_customer_name');
      }
      if( !isset($this->request->post['phone']) || !preg_match("/^\+?\d+$/", $this->request->post['phone']) ){
          $this->error['customer_phone'] = $this->language->get('error_phone');
      }

      if( utf8_strlen($this->request->post['address']) < 4){
          $this->error['address_line_one'] = $this->language->get('error_address');
      }
      if( utf8_strlen($this->request->post['weight']) < 0){
          $this->error['weight'] = $this->language->get('error_weight');
      }

      if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('errors_heading');
        }

      return $this->error;
    }

    public function install() {
      $this->load->model("shipping/bowsala");
      $this->model_shipping_bowsala->install();
    }

    public function uninstall() {
      $this->load->model("shipping/bowsala");
      $this->model_shipping_bowsala->uninstall();
    }
}
