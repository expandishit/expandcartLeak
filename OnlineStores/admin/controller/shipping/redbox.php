<?php

class ControllerShippingRedbox extends Controller {

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
        $this->load->language('shipping/redbox');

        //Save redbox settings in settings table
        $this->model_setting_setting->insertUpdateSetting('redbox', $this->request->post );

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

    $this->load->language('shipping/redbox');
    $this->load->model('sale/order');
    $this->load->model('shipping/redbox');

    $order = $this->model_sale_order->getOrder($order_id);
    //Check if store order has shipping order already
    if (!empty($order['tracking'])) {
      $this->session->data['error'] = $this->language->get('text_order_already_exist');
      $this->response->redirect($this->url->link('shipping/redbox/trackShipment?order_id='.$order_id, '' , true));
    }

    $this->data['order'] = $order;
    /*prepare redbox.expand view data*/
    $this->document->setTitle($this->language->get('create_heading_title'));

    //Breadcrumbs
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    $this->template = 'shipping/redbox/shipment/create.expand';
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
        $this->load->language('shipping/redbox');
        $this->load->model('shipping/redbox');
        $this->load->model('sale/order');
        $order_id = $this->request->post['order_id'];
        $order    = $this->model_sale_order->getOrder($order_id);
        $lang = $this->config->get('config_admin_language');
        $products    = $this->model_sale_order->getOrderProducts($order_id);
        $order_total_in_sar = $order['total'];
        if( $order['currency_code'] != "SAR" && $order['payment_code'] == "cod"){
          $order_total_in_sar = $this->convertCurrncy($order['total'],$order['currency_code'],'SAR');
        }
        if($order['payment_code'] != "cod")
          $order_total_in_sar=0;

        $items = [];
        $i = 0;
        foreach ($products as $product) {
          $items[$i]['name'] = $product['name'];
          $items[$i]['quantity'] = $product['quantity'];
          $items[$i]['unitPrice'] = ($order['currency_code'] != "SAR") ? $this->convertCurrncy($product['price'],$order['currency_code'],'SAR') : $product['price'];
          $items[$i]['description'] = $this->request->post['notes'];
          $items[$i]['curency'] = "SAR";
          $i++;
        }
        $data = 
        [
          "business_id"=>$this->config->get('redbox_business_id'),
          "items"=>$items,
          "reference" => $order_id."_".time(),
          "sender_name"=>$this->config->get('config_name')[$lang],
          "sender_email"=>$this->config->get('config_email')[$lang],
          "sender_phone"=>$this->config->get('config_telephone'),
          "sender_address"=>$this->config->get('config_address')[$lang],
          "customer_name"=>$this->request->post['name'],
          "customer_email"=>$this->request->post['email'],
          "customer_phone"=>$this->request->post['phone'],
          "customer_address"=>$this->request->post['address'],
          "weight_unit"=>"kg",
          "weight_value"=>$this->request->post['weight'],
          "cod_currency"=>"SAR",
          "cod_amount"=>$order_total_in_sar,
          "from_platform"=>"expandCart",
        ];
        $response = $this->model_shipping_redbox->createShipment($data);
        if($response['success'] == 1){//succeeded
          //update status & add history record
          if( !empty($this->config->get('redbox_after_creation_status')) ){
              $this->model_sale_order->addOrderHistory($order['order_id'], [
                'comment'          => 'Redbox Shipment Id: ' .$response['shipment_id'].'<br> Tracking Number: '.$response['tracking_number'].'<br> Shipping Label URL: <br>'.$response['url_shipping_label'],
                'order_status_id'  => $this->config->get('redbox_after_creation_status'),
              ]);
          }
          $this->model_sale_order->updateShippingTrackingNumber($order_id , $response['shipment_id']);
          //Returning to Order page
          $result_json['success_msg'] = $this->language->get('text_success');;
          $result_json['success']  = '1';
          //redirect
          $result_json['redirect'] = '1';
          $result_json['to'] = "sale/order/info?order_id=".$order_id;

        }
        else{
          $result_json['success'] = '0';
          $result_json['errors']  = $response['msg'];
          
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
    $this->language->load('shipping/redbox');
    // set Page Title
    $this->document->setTitle($this->language->get('heading_title_redbox'));
    $this->load->model('sale/order');
    $this->load->model("shipping/redbox");
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    // get order id
    $order_id = $this->request->get['order_id'];
    $this->data['order_id'] = $order_id;
    $order = $this->model_sale_order->getOrder($order_id);
    $trackResponse = $this->model_shipping_redbox->trackShipment($order['tracking']);
    if($trackResponse['success'] == 1){
        $this->data['shipment'] = $trackResponse['shipment'];
    }else{
        $result_json['success'] = '0';
        $result_json['errors'] = $trackResponse['msg'];
        $result_json['errors']['warning'] = $this->language->get('redbox_error_warning');
        $this->response->setOutput(json_encode($result_json));
    }


    $this->template = 'shipping/redbox/shipment/track.expand';
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
    $this->template = 'shipping/redbox.expand';
    $this->children = ['common/header', 'common/footer'];
    $this->response->setOutput($this->render());
  }

  private function _setViewData(){

    $this->load->language('shipping/redbox');
    //Form Buttons
    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/redbox/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);

    /*Get form fields data*/
    $this->data['redbox_api_key']  = $this->config->get('redbox_api_key');
    $this->data['redbox_business_id']  = $this->config->get('redbox_business_id');
    $this->data['redbox_test']  = $this->config->get('redbox_test');
    $this->data['redbox_after_creation_status'] = $this->config->get('redbox_after_creation_status');
    $this->data['redbox_display_name']  = $this->config->get('redbox_display_name');
    $this->data['redbox_status']       = $this->config->get('redbox_status');
    $this->data['redbox_price']        = $this->config->get('redbox_price');

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
        'href' => $this->url->link('shipping/redbox', true)
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
    $this->load->language('shipping/redbox');

    if (!$this->user->hasPermission('modify', 'shipping/redbox')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('redbox') ){
      $this->error['redbox_not_installed'] = $this->language->get('error_not_installed');
    }

    if((utf8_strlen($this->request->post['redbox_api_key']) < 32) ) {
        $this->error['redbox_api_key'] = $this->language->get('error_redbox_api_key');
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
      $this->load->language('shipping/redbox');

      if(utf8_strlen($this->request->post['name']) < 4){
        $this->error['customer_name'] = $this->language->get('error_customer_name');
      }

      if(!$this->currency->has('SAR')){
        $this->error['currency'] = $this->language->get('error_currency');
      }

      // check phone entered to be Saudi Arabian number
      if( !isset($this->request->post['phone']) || !preg_match('/^(009665|9665|\+9665|05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/', $this->request->post['phone']) ){
        $this->error['customer_phone'] = $this->language->get('error_phone');
      }
      if( utf8_strlen($this->request->post['address']) < 2){
        $this->error['address_line_one'] = $this->language->get('error_address');
      }

      if( $this->request->post['weight'] < 0){
        $this->error['weight'] = $this->language->get('error_weight');
      }

      if($this->error && !isset($this->error['error']) ){
        $this->error['warning'] = $this->language->get('errors_heading');
      }

      return $this->error;
    }

    public function install() {
      $this->load->model("shipping/redbox");
      $this->model_shipping_redbox->install();
    }

    public function uninstall() {
      $this->load->model("shipping/redbox");
      $this->model_shipping_redbox->uninstall();
    }

    private function convertCurrncy($amount, $from, $to)
    {
      $order_total_in_usd = $this->currency->gatUSDRate($from) * $amount;
      $usd_to_sar_ratio = $this->currency->gatUSDRate($to);
      return round(($order_total_in_usd / $usd_to_sar_ratio),4);
    }
}
