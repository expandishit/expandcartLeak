<?php

class ControllerShippingVanex extends Controller {

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
        $this->load->language('shipping/vanex');

        //Save vanex settings in settings table
        $this->model_setting_setting->insertUpdateSetting('vanex', $this->request->post );

        $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'SHIPPING', '1');

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

    $this->load->language('shipping/vanex');
    $this->load->model('sale/order');
    $this->load->model('shipping/vanex');

    $order = $this->model_sale_order->getOrder($order_id);
    //Check if store order has shipping order already
    if (!empty($order['tracking'])) {
      $this->session->data['error'] = $this->language->get('text_order_already_exist');
      $this->response->redirect($this->url->link('sale/order/info?order_id=' . $this->request->get['order_id'], '', true));
    }

    $this->data['order'] = $order;
    // cities from vanex
    $this->data['cities'] = $this->model_shipping_vanex->getCities();
    /*prepare vanex.expand view data*/
    $this->document->setTitle($this->language->get('create_heading_title'));

    //Breadcrumbs
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    $this->template = 'shipping/vanex/shipment/create.expand';
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
      else
      {
        $this->load->language('shipping/vanex');
        $this->load->model('shipping/vanex');
        $this->load->model('sale/order');
        $order_id = $this->request->post['order_id'];
        $order    = $this->model_sale_order->getOrder($order_id);
        $description = "";
        $totalQty = 0;
        foreach ($order['product'] as $product) {
          $totalQty += $product['quantity'];
          $description .= $product['quantity'].$this->language->get('text_items').$this->language->get('text_of')." ".$product['name']." + ";
        }
        $method = ($order['payment_code'] == "cod") ? "cash" : "epayment";
        $cities = explode('-',$this->request->post['city']);
        $main_city = $cities[0];
        $sub_city = ($cities[0] != $cities[1] ) ? $cities[1] : "";
        $loginResponse = $this->model_shipping_vanex->login();
        if($loginResponse['status_code'] != 200){
          $result_json['success'] = '0';
          $result_json['errors'][] = $this->language->get('error_credentials');
          $result_json['errors'][] = implode(', ', array_map(function ($entry) {
            return ($entry[key($entry)]);
          }, $loginResponse['errors']));
          $this->response->setOutput(json_encode($result_json));
          return;
        }
        $ship_data = 
        [
          "type"=>1,
          "reciever"=>$this->request->post['name'],
          "phone"=>$this->request->post['phone'],
          "phone_b"=>$this->request->post['phone'],
          "city"=>$main_city,
          "address"=>$this->request->post['address'],
          "price"=>$order['total'],
          "address_child"=>$sub_city,
          "payment_methode"=>$method,
          "paid_by"=>"market", // means that the sent price including shipping cost
          "commission_by"=>"customer", 
          "extra_size_by"=>"customer",
          "description"=>$description,
          "qty"=>$totalQty,
          "notes"=>$this->request->post['notes'],
          "leangh" =>35,
          "height"=>35,
          "width"=>35,
        ];  
        $response = $this->model_shipping_vanex->createShipment($loginResponse['data']['access_token'],$ship_data);
        if($response['status_code'] == 201){
          //succeeded
          //update status & add history record
          if( !empty($this->config->get('vanex_after_creation_status')) ){
              $this->model_sale_order->addOrderHistory($order['order_id'], [
                'comment'          => 'Vanex Tracking Code : ' . $response['package_code'],
                'order_status_id'  => $this->config->get('vanex_after_creation_status'),
              ]);
          }

          $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['package_code']);

          //Returning to Order page
          $result_json['success_msg'] = $this->language->get('text_success');
          $result_json['success']  = '1';
          //redirect
          $result_json['redirect'] = '1';
          $result_json['to'] = "sale/order/info?order_id=".$order_id;
        }
        else{
          $result_json['success'] = '0';
          
          if($response['errors']['reciever']){
            $this->load->language('shipping/vanex');
            $response['errors']['reciever'][0]= $this->language->get('error_reciever');
          }

          $result_json['errors'] = implode('<br> - ', array_map(function ($entry) {
            return ($entry[key($entry)]);
          }, $response['errors']));

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
    $this->template = 'shipping/vanex.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
  }

  private function _setViewData(){

    $this->load->language('shipping/vanex');
    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/vanex/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['vanex_city']      = $this->config->get('vanex_city');
    $this->data['vanex_email']  = $this->config->get('vanex_email');
    $this->data['vanex_password']  = $this->config->get('vanex_password');
    $this->data['vanex_after_creation_status'] = $this->config->get('vanex_after_creation_status');
    $this->data['vanex_display_name']  = $this->config->get('vanex_display_name');
    $this->data['vanex_status']       = $this->config->get('vanex_status');
    $this->data['vanex_price']        = $this->config->get('vanex_price');

    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses']    = $this->model_localisation_order_status->getOrderStatuses();

    $this->load->model('localisation/language');
    $this->data['languages'] = $this->model_localisation_language->getLanguages();
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
        'href' => $this->url->link('shipping/vanex', true)
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
    $this->load->language('shipping/vanex');

    if (!$this->user->hasPermission('modify', 'shipping/vanex')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('vanex') ){
      $this->error['vanex_not_installed'] = $this->language->get('error_not_installed');
    }

    if((utf8_strlen($this->request->post['vanex_password']) < 1) ) {
        $this->error['vanex_password'] = $this->language->get('error_vanex_password');
    }

    //$email_pattern_validation = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
    if((utf8_strlen($this->request->post['vanex_email']) < 3) || !filter_var($this->request->post['vanex_email'], FILTER_VALIDATE_EMAIL) ) {
        $this->error['vanex_email'] = $this->language->get('error_vanex_email');
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
      $this->load->language('shipping/vanex');

      if(utf8_strlen($this->request->post['name']) < 4){
            $this->error['customer_name'] = $this->language->get('error_customer_name');
      }
      if( !isset($this->request->post['phone']) || !preg_match("/^\+?\d+$/", $this->request->post['phone']) ){
          $this->error['customer_phone'] = $this->language->get('error_phone');
      }

      if( utf8_strlen($this->request->post['address']) < 2){
          $this->error['address_line_one'] = $this->language->get('error_address');
      }

      if( utf8_strlen($this->request->post['notes']) < 1){
          $this->error['notes'] = $this->language->get('error_notes');
      }

      if($this->error && !isset($this->error['error']) ){
          $this->error['warning'] = $this->language->get('errors_heading');
        }

      return $this->error;
    }

    public function install() {
      $this->load->model("shipping/vanex");
      $this->model_shipping_vanex->install();
    }

    public function uninstall() {
      $this->load->model("shipping/vanex");
      $this->model_shipping_vanex->uninstall();
    }
}
