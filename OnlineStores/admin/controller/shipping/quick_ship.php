<?php

class ControllerShippingQuickShip extends Controller{

  /**
   * @var array the validation errors array.
   */
  private $error = array();

  /**
   * @var string the API access token.
   */
  protected $access_token = null;

  /**
   * @var string the API refresh token.
   */
  protected $refresh_token = null;



  public function index(){
    /*prepare quick.expand view data*/
    $this->load->language('shipping/quick_ship');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/quick_ship.expand';
    $this->children = array( 'common/header', 'common/footer');

    //Breadcrumbs
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/quick_ship/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);
    $this->data['push']   = $this->url->link('shipping/quick_ship/push', '', 'SSL');
    $this->data['update_orders_status']   = $this->url->link('shipping/quick_ship/updateStatus', '', 'SSL');

    /*Get form fields data*/
    $this->data['quick_ship_username'] = $this->config->get('quick_ship_username');
    $this->data['quick_ship_password'] = $this->config->get('quick_ship_password');
    $this->data['quick_ship_payment_method_id']  = $this->config->get('quick_ship_payment_method_id');
    $this->data['quick_ship_content_type_id']    = $this->config->get('quick_ship_content_type_id');
    $this->data['quick_ship_added_services_ids'] = $this->config->get('quick_ship_added_services_ids');
    $this->data['quick_ship_ready_shipping_status'] = $this->config->get('quick_ship_ready_shipping_status');
    $this->data['quick_ship_after_creation_status'] = $this->config->get('quick_ship_after_creation_status');
    $this->data['quick_ship_status'] = $this->config->get('quick_ship_status');
    $this->data['quick_ship_geo_zone_id'] = $this->config->get('quick_ship_geo_zone_id');
    $this->data['quick_ship_tax_class_id'] = $this->config->get('quick_ship_tax_class_id');
    
    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    //Get all tax classes
    $this->load->model('localisation/tax_class');
    $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();



    //get current language
    $this->data['current_language'] = $this->config->get('config_admin_language');

    //get payment methods from  API
    $api_data = $this->_getAPIData();
    $this->data['payment_methods'] = $api_data['payment_methods'];
    $this->data['added_services']  = $api_data['added_services'];
    $this->data['content_types']   = $api_data['content_types'];


    //Managing session messages
    if(isset($this->session->data['api_error'])) {
      $this->data['api_error'] = $this->session->data['api_error'];
      unset($this->session->data['api_error']);
    }
    if(isset($this->session->data['success'])){
      $this->data['success'] = $this->session->data['success'];
      unset($this->session->data['success']);
    }


    $this->response->setOutput($this->render());
  }

  public function updateStatus(){
    //clean session variable
    unset($this->session->data['api_error']);
    unset($this->session->data['success']);
    
    $mylog1 = new Log('quick_orders_updateStatus' . date('Ymd') . '.log');

    // get all orders id - only quick statuses ids
    $this->load->model('shipping/quick_ship');

    $orders = $this->model_shipping_quick_ship->getOrdersToTrack();
 
    //get username & password from DB, setting table
    $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
    $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

    //Get an access token
    $response      = $this->model_shipping_quick_ship->getAccessToken($username, $password);
    $access_token  = $response->resultData->access_token;
  
    $response = $this->model_shipping_quick_ship->getTrackingInfo($access_token, array_column($orders, 'tracking') );
    $mylog1->write('getTrackingInfo() :   ' . json_encode($response));

    //get last status and check if != current status then update order status
    $this->load->language('shipping/quick_ship');
    $this->load->model('sale/order');

    if($response->httpStatusCode == 200){

      foreach( $response->resultData as $shipment_order){

        if( $shipment_order->shipmentStatusList ){

          $statuses = json_decode(json_encode($shipment_order->shipmentStatusList), true);
          usort($statuses, 'date_compare');
          
          //get status id in our DB
          $new_status_id = $this->model_shipping_quick_ship->getOrderStatusId((int)$statuses[0]['id']);
          
          if( !empty($new_status_id) ){
            //compare current order status with this if != change if = do nothing
            $key = array_search($shipment_order->shipmentId, array_column($orders, 'tracking'));

            if($key !== FALSE && $orders[$key]['order_status_id'] != $new_status_id){           
              //update status & add history record
              $this->model_sale_order->addOrderHistory($orders[$key]['order_id'], 
                [
                  'comment'          => 'Tracking number: ' . $shipment_order->shipmentId ,
                  'order_status_id'  => $new_status_id,
                ]);
            }
          }
        }
      }
      $this->session->data['success'] = $this->language->get('text_orders_status_updated');
    }
    else{
      //return error occured during process then print $response->httpStatusCode & messageAr/messageEn
      $this->session->data['api_error'] = $this->config->get('config_admin_language') == 'ar' ? $response->messageAr : $response->messageEn;
    }
      //$this->redirect($this->url->link('shipping/quick_ship'));
      $this->redirect($this->url->link('extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1'));
  }

 /**
  * Push orders to quick gateway, creates multiple shipping orders at once
  *
  * @return Array
  */
  public function push(){
    //clean session variable
    unset($this->session->data['api_error']);
    unset($this->session->data['success']);

    //Load required models
    $this->load->language('shipping/quick_ship');

    //Validate Settings (Username,password, Currency SAR, ..
    $validation_result = $this->_validateSettings();

    if( !empty($validation_result) ){
        $this->session->data['api_error'] = $validation_result;
        //$this->redirect($this->url->link('shipping/quick_ship'));
        $this->redirect($this->url->link('extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1'));
    }
    //Orders
    //Get orders to be shipped
    //Load API model
    $this->load->model('shipping/quick_ship');
    $orders = $this->model_shipping_quick_ship->getOrdersToPush();

    //if there is no order return error
    if( !$orders ){
      $this->session->data['api_error'] = $this->language->get('error_api_no_orders');
      //$this->redirect($this->url->link('shipping/quick_ship'));
        $this->redirect($this->url->link('extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1'));
    }

    //Keep track of orders status
    $orders_succeeded = [];
    $orders_failed    = [];

    //Get coutries supported by Quick API for shipping. format ('country_iso_code_2' => 'id' )
    $supported_countries = $this->model_shipping_quick_ship->getSupportedCountries();
    $this->load->model('sale/order');

    //Create new log to track responses
    $mylog1 = new Log('quick_orders_push' . date('Ymd') . '.log');

    //user & password
    $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
    $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

    foreach($orders as $order){
      $mylog1->write('Order: #'.$order['order_id'] );
      $mylog1->write('Order Country: ' .$order['country_code']);
      
      //Check if country code supported
      if( !$supported_countries[$order['country_code']] ){
        $orders_failed[] = [
          'order_id'      => $order['order_id'],
          'message' => 'Shipping country is not supported by Quick'
        ];
        continue;
      }

      //Validate Order Data..
      $validation_result = $this->_validateOrderData($order);
      if( !empty($validation_result) ){
         $orders_failed[] = [
          'order_id'      => $order['order_id'],
          'message'       => implode(' , ', $validation_result),
        ];
        continue;
      }

      /* Step 1:  Create shipment order data array */
      $data = [
        'SandboxMode'    => false,
        'CustomerName'   => $order['firstname'] . ' ' . $order['lastname'],
        'CustomerPhoneNumber' => $order['telephone'],
        // 'PreferredReceiptTime'=> "2018/10/24 18:00",
        // 'PreferredDeliveryTime'=> "2018/10/24 23:00",
        'NotesFromStore'=> $this->_formatComment($order['comment']),
        'PaymentMethodId'=> $this->config->get('quick_ship_payment_method_id'),
        'ShipmentContentValueSAR'=> $order['currency_code'] == 'SAR' ? $order['total'] : round( $this->currency->convert($order['total'], $order['currency_code'], 'SAR') , 4) ,
        'ShipmentContentTypeId'=> $this->config->get('quick_ship_content_type_id'), //Breakable Item
        'AddedServicesIds'=> $this->config->get('quick_ship_added_services_ids')?:[],
        'CustomerLocation'=> [
          'Desciption'  => $order['shipping_address_1']?: $order['payment_address_1'],
          'Longitude'   => "",
          'Latitude'    => "",
          'GoogleMapsFullLink' => "",
          'CountryId'   => $supported_countries[$order['country_code']],
          'CityAsString'=> $order['shipping_city']?: $order['payment_city']
        ],
        'ExternalStoreShipmentIdRef'=> '',
        'API_Call_Source'=>"Expand Cart",
        'Currency'=>"SAR",
      ];

      $mylog1->write('data: ' .json_encode($data));
      $mylog1->write('Calling getAccessToken API');

      /* Step 2: call Quick Api to create new shipment order*/
      //Get an access token
      $response      = $this->model_shipping_quick_ship->getAccessToken($username, $password);
      $access_token  = $response->resultData->access_token;
      
      $mylog1->write('Calling createShipment API');

      $response      = $this->model_shipping_quick_ship->createShipment($response->resultData->access_token , $data);

      $mylog1->write('Order: response =>'.json_encode($response));

      /* Step 3: validate response */
      if( $response->httpStatusCode == 201 ){
        //update status & add history record
        $this->model_sale_order->addOrderHistory($order['order_id'], [
          'comment'          => $response->resultData->trackShipmentUrl,
          'order_status_id'  => $this->config->get('quick_ship_after_creation_status'),
        ]);

        //Save Tracking URL & tracking number
        $this->model_shipping_quick_ship->saveTrackingURL(
          $response->resultData->trackShipmentUrl, 
          $response->resultData->id , 
          (int)$order['order_id']
        );

        //
        $orders_succeeded[] = [
          'order_id' => $order['order_id'],
          'message'  => $this->config->get('config_admin_language') == 'ar' ? $response->MessageAr : $response->MessageEn,
        ];
      }
      else{
        $orders_failed[] = [
          'order_id' => $order['order_id'] ,
          'message'  => $this->config->get('config_admin_language') == 'ar' ? $response->MessageAr : $response->MessageEn,
        ];
      }
    }

    if( count($orders_succeeded) == count($orders) ){
      $this->session->data['success'] = $this->language->get('text_orders_created');
    }
    else if( !empty($orders_failed) ){
      $this->session->data['api_error'] = $this->language->get('text_failed_orders') .' :'. count($orders_failed) . $this->language->get('text_orders') .'<br/>';
      foreach ($orders_failed as $order) {
        $this->session->data['api_error'] .= $this->language->get('text_order_id') . $order['order_id'] .'<br/>' . $order['message'] . '<br/><br/>';
      }
      // if( !empty($orders_succeeded) ){
      //   $this->session->data['success'] = $this->language->get('text_orders_created') . ' ' . implode(' , #', $orders_succeeded ) ;
      // }
    }

      //$this->redirect($this->url->link('shipping/quick_ship'));
      $this->redirect($this->url->link('extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1'));
 }

  /**
  * Get list of payment_methods, added_services & shipping_content_types supported by Quick API system.
  *
  * @return Array
  */
  private function _getAPIData(){
    //Load API model
    $this->load->model('shipping/quick_ship');

    //get username & password from DB, setting table
    $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
    $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

    //Get an access token
    $response      = $this->model_shipping_quick_ship->getAccessToken($username, $password);
    $access_token  = $response->resultData->access_token;

    //Call get Consistent data API to get quick_shipGateway server data
    $response = $this->model_shipping_quick_ship->getConsistentData($access_token);

    return [
      'payment_methods' => $response->resultData->paymentMethodList,
      'added_services' => $response->resultData->servicesList,
      'content_types' => $response->resultData->shipmentContentTypeList,
    ];
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
        'href' => $this->url->link('shipping/quick_ship', true)
      ]
    ];
    return $breadcrumbs;
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
        $this->load->language('shipping/quick_ship');
        //Insert a record of quick gatway in the extension table in DB if not any
        $this->model_setting_setting->checkIfExtensionIsExists('shipping', 'quick_ship', TRUE);
        //Save quick user & password in settings table
        $this->model_setting_setting->editSetting('quick_ship', $this->request->post );

          
            $this->tracking->updateGuideValue('SHIPPING');

          $result_json['success_msg'] = $this->language->get('text_success');

        $result_json['success']  = '1';
        $result_json['redirect'] = '1';
        //$this->redirect($this->url->link('extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1'));
        $result_json['to'] = "extension/shipping/activate?code=quick_ship&activated=1&delivery_company=1";
      }

      $this->response->setOutput(json_encode($result_json));
    }
    else{
      $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
    }
  }

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/quick_ship');

    if (!$this->user->hasPermission('modify', 'shipping/quick_ship')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }
    if ((utf8_strlen($this->request->post['quick_ship_username']) < 3) ) {
        $this->error['quick_ship_username'] = $this->language->get('error_quick_username');
    }
    if($this->request->post['quick_ship_password']){
      if ((utf8_strlen($this->request->post['quick_ship_password']) < 6) ) {
        $this->error['quick_ship_password'] = $this->language->get('error_quick_password');
      }
    }
    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return !$this->error;
  }

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateOrderData($order){

    $validation_errors = [];

    if( !$order['firstname'] && !$order['lastname']) {
        $validation_errors['customer_name'] = $this->language->get('error_customer_name');
    }
    if( !$order['telephone'] || preg_match("^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$", $order['telephone']) ){
        $validation_errors['customer_telephone'] = $this->language->get('error_customer_telephone');     
    }
    if( !$order['shipping_address_1'] && !$order['payment_address_1'] ){
        $validation_errors['customer_address'] = $this->language->get('error_customer_address');
    }
    if( !$order['country_code'] ){
        $validation_errors['country_code'] = $this->language->get('error_country_code');
    }
    if( !$order['shipping_city'] && !$order['payment_city'] ){
        $validation_errors['customer_shipping_city'] = $this->language->get('error_customer_shipping_city');
    }    
    return $validation_errors;
  }

  private function _validateSettings(){
    $this->load->language('shipping/quick_ship');

    
    if( !$this->config->get('quick_ship_username') || !$this->config->get('quick_ship_password') ) {
      return $this->language->get('error_api_invalid_username_or_password');
    }
    
    if( !$this->config->get('quick_ship_payment_method_id') ){
      return $this->language->get('error_quick_ship_payment_method_missing');
    }
    
    if( !$this->config->get('quick_ship_content_type_id') ){
      return $this->language->get('error_quick_ship_content_type_missing');
    }

    $this->load->model('localisation/currency');
    $currencey = $this->model_localisation_currency->getCurrencyByCode('SAR');

    if( !$currencey){
      return $this->language->get('error_SAR_currency_not_found');
    }
    // if( $this->currency->has('SAR') ){
    //   return $this->language->get('error_SAR_currency_not_found');  
    // }
    return $errors;
  }

  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
  }


  public function install() {
    $this->load->model('shipping/quick_ship');
    $this->model_shipping_quick_ship->install();
  }

  public function uninstall(){
    $this->load->model('shipping/quick_ship');
    $this->model_shipping_quick_ship->uninstall();
  }

/**
  * Push order to quick gateway, create ONE shipping order.
  *
  * @return Array
  */
  public function pushOneOrder(){
    $order_id = (isset($this->request->get['order_id']) ? $this->request->get['order_id'] : null);

    if(!$order_id){
      $result_json['error'] = $this->language->get('error_invalid_order_id');
      $this->response->setOutput( json_encode($result_json) );
      return;
    }
    
    //Orders
    //Get orders to be shipped
    //Load API model
    $this->load->model('shipping/quick_ship');
    $order = $this->model_shipping_quick_ship->getOrderToPush($order_id);

    if(!$order){
      $result_json['error'] = $this->language->get('error_invalid_order_id');
      $this->response->setOutput( json_encode($result_json) );
      return;
    }

    //Load required models
    $this->load->language('shipping/quick_ship');

    //Validate Settings (Username,password, Currency SAR, ..
    $validation_result = $this->_validateSettings();

    if( !empty($validation_result) ){
      $result_json['error'] = $validation_result;
      $this->response->setOutput( json_encode($result_json) );
    }

     //Get coutries supported by Quick API for shipping. format ('country_iso_code_2' => 'id' )
    $supported_countries = $this->model_shipping_quick_ship->getSupportedCountries();

    //Create new log to track responses
    $mylog1 = new Log('quick_order_push' . date('Ymd') . '.log');
    $mylog1->write('$supported_countries =>'.json_encode($supported_countries));

    //user & password
    $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
    $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));


    //Check if country code supported
    if( !$supported_countries[$order['country_code']] ){
      $result_json['error'] = 'Shipping country is not supported by Quick';
      $this->response->setOutput( json_encode($result_json) );
    }

    //Validate Order Data..
    $validation_result = $this->_validateOrderData($order);
    if( !empty($validation_result) ){
      $result_json['error'] = $validation_result;
      $this->response->setOutput( json_encode($result_json) );
    }

    /* Step 1:  Create shipment order data array */
    $data = [
      'SandboxMode'    => false,
      'CustomerName'   => $order['firstname'] . ' ' . $order['lastname'],
      'CustomerPhoneNumber' => $order['telephone'],
      // 'PreferredReceiptTime'=> "2018/10/24 18:00",
      // 'PreferredDeliveryTime'=> "2018/10/24 23:00",
      'NotesFromStore'=> $this->_formatComment($order['comment']),
      'PaymentMethodId'=> $this->config->get('quick_ship_payment_method_id'),
      'ShipmentContentValueSAR'=> $order['currency_code'] == 'SAR' ? $order['total'] : round( $this->currency->convert($order['total'], $order['currency_code'], 'SAR') , 4) ,
      'ShipmentContentTypeId'=> $this->config->get('quick_ship_content_type_id'), //Breakable Item
      'AddedServicesIds'=> $this->config->get('quick_ship_added_services_ids')?:[],
      'CustomerLocation'=> [
        'Desciption'  => $order['shipping_address_1']?: $order['payment_address_1'],
        'Longitude'   => "",
        'Latitude'    => "",
        'GoogleMapsFullLink' => "",
        'CountryId'   => $supported_countries[$order['country_code']],
        'CityAsString'=> $order['shipping_city']?: $order['payment_city']
      ],
      'ExternalStoreShipmentIdRef'=> '',
      'API_Call_Source'=>"Expand Cart",
      'Currency'=>"SAR",
    ];

    $mylog1->write('data: ' .json_encode($data));
    $mylog1->write('Calling getAccessToken API');

    /* Step 2: call Quick Api to create new shipment order*/
    //Get an access token
    $response      = $this->model_shipping_quick_ship->getAccessToken($username, $password);
    $access_token  = $response->resultData->access_token;
    
    $mylog1->write('Calling createShipment API');

    $response      = $this->model_shipping_quick_ship->createShipment($response->resultData->access_token , $data);

    $mylog1->write('Order: response =>'.json_encode($response));

    /* Step 3: validate response */
    if( $response->httpStatusCode == 201 ){
      //update status & add history record
      $this->load->model('sale/order');
      $this->model_sale_order->addOrderHistory($order['order_id'], [
        'comment'          => $response->resultData->trackShipmentUrl,
        'order_status_id'  => $this->config->get('quick_ship_after_creation_status'),
      ]);

      //Save Tracking URL & tracking number
      $this->model_shipping_quick_ship->saveTrackingURL(
        $response->resultData->trackShipmentUrl, 
        $response->resultData->id , 
        (int)$order['order_id']
      );

      //returning success
      $result_json['success_msg'] = $this->language->get('text_order_created');
      $this->response->setOutput( json_encode($result_json) );
      return;
    }else{
        $result_json['error'] = $this->language->get('text_failed_order');
        if (isset($response->messageAr) && !empty($response->messageAr) && $this->config->get('config_admin_language') === 'ar'){
            $result_json['error'] .= ' ' .$response->messageAr;
        }else{
            if (isset($response->messageEn) && !empty($response->messageEn)){
                $result_json['error'] .= ' ' .$response->messageEn;
            }
        }
    }

    $this->response->setOutput( json_encode($result_json) );
    return;
  }

  public function getShippingLabelFile(){
    $trancking_number = $this->request->get['trancking_number'];
    
    if (isset($trancking_number)) {
      $this->load->model('shipping/quick_ship');
      
      //get username & password from DB, setting table
      $username = htmlspecialchars_decode($this->config->get('quick_ship_username'));
      $password = htmlspecialchars_decode($this->config->get('quick_ship_password'));

      //Get an access token
      $response      = $this->model_shipping_quick_ship->getAccessToken($username, $password);
      $access_token  = $response->resultData->access_token;

      $result = $this->model_shipping_quick_ship->getShippingLabelAsPDF($access_token, $trancking_number);
      if( !$result ){
        //redirect back
        if (isset($_SERVER["HTTP_REFERER"])) {
          header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
      }
    }
  }

  private function _date_compare($a, $b){
    $t1 = strtotime($a['created_at']);
    $t2 = strtotime($b['created_at']);
    return $t1 - $t2;
  } 

  private function _formatComment($comment){
    return str_ireplace(["<br />","<br>","<br/>"] , "\r\n", html_entity_decode($comment) );
  }
}
