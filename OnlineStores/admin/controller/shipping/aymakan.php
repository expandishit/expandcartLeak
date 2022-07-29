
<?php

class ControllerShippingAyMakan extends Controller{
  /**
   * @var array the validation errors array.
   */
  private $error = [];

  public function index(){

    $this->load->language('shipping/aymakan');

    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/aymakan/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);


    /*Get form fields data*/
    $this->data['aymakan_api_key']        = $this->config->get('aymakan_api_key');
    $this->data['aymakan_status']         = $this->config->get('aymakan_status');
    $this->data['aymakan_debugging_mode'] = $this->config->get('aymakan_debugging_mode');
    $this->data['aymakan_geo_zone_id']    = $this->config->get('aymakan_geo_zone_id');
    $this->data['aymakan_tax_class_id']   = $this->config->get('aymakan_tax_class_id');
    $this->data['aymakan_price']          = $this->config->get('aymakan_price');    
    $this->data['aymakan_statuses']       = $this->config->get('aymakan_statuses');
    $this->data['lang']                   = $this->config->get('config_admin_language');
    $this->data['aymakan_statuses_added'] = $this->config->get('aymakan_statuses_added');
    $this->data['aymakan_after_creation_status'] = $this->config->get('aymakan_after_creation_status');
    $this->data['aymakan_gateway_display_name']  = $this->config->get('aymakan_gateway_display_name');
    $this->data['aymakan_account_currency']      = $this->config->get('aymakan_account_currency');

    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones']  = $this->model_localisation_geo_zone->getGeoZones();

    $this->load->model('localisation/order_status');
    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    $this->load->model('localisation/tax_class');
    $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

    $this->load->model('localisation/language');
    $this->data['languages'] = $this->model_localisation_language->getLanguages();


    //Breadcrumbs
    $this->data['breadcrumbs'] = $this->_createBreadcrumbs();

    /*prepare aymakan.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/aymakan/settings.expand';
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
        $this->load->language('shipping/aymakan');

        //Save aymakan config data in settings table
        $this->model_setting_setting->insertUpdateSetting('aymakan', $this->request->post);

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

  public function install(){
    $this->load->model('shipping/aymakan');
    $this->model_shipping_aymakan->install();
  }

  public function addAllStatuses(){
    if(!$this->config->get('aymakan_statuses_added')){
        $aymakan_statuses = $this->config->get('aymakan_statuses');

        //add them to status table
        $this->load->model('localisation/order_status');
        $this->load->model('setting/setting');

        foreach($aymakan_statuses as $key => $status){
            $inserted_status_id = $this->model_localisation_order_status->addOrderStatus(
              [
                "order_status" => [
                  1 => ['name' => $status['status'] ],
                  2 => ['name' => $status['status_ar'] ]
                ]
              ]);
            $aymakan_statuses[$key]['expandcartid'] = $inserted_status_id;
        }

        //update setting
        $this->model_setting_setting->insertUpdateSetting('aymakan', ['aymakan_statuses' => $aymakan_statuses]);
        $this->model_setting_setting->insertUpdateSetting('aymakan', ['aymakan_statuses_added' => '1']);
    }
    $this->response->redirect($this->url->link('extension/shipping/activate?code=aymakan', '', 'SSL'));
  }

  /** HELPER METHODS **/

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

    if( !\Extension::isInstalled('aymakan') ){
      $this->error['aymakan_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['aymakan_api_key']) < 172) ) {
      $this->error['aymakan_api_key'] = $this->language->get('error_aymakan_api_key');
    }

    if( $this->request->post['aymakan_price']['aymakan_weight_general_rate'] <= 0){
      $this->error['aymakan_price'] = $this->language->get('error_aymakan_price');
    }

    if($this->error && !isset($this->error['error']) ){
      $this->error['warning'] = $this->language->get('error_warning');
    }
    return !$this->error;
  }


  private function _isAjax() {

    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
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
        'href' => $this->url->link('shipping/aymakan', true)
      ]
    ];
    return $breadcrumbs;
  }

}
