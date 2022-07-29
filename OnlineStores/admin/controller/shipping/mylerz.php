
<?php

class ControllerShippingMylerz extends Controller{
  /**
   * @var array the validation errors array.
   */
  private $error = [];

  public function index(){

    $this->load->language('shipping/mylerz');
    $this->document->addStyle('view/stylesheet/anytime_custome_theme.css');

    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/mylerz/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);
    $this->data['push']   = $this->url->link('sale/mylerz_shipment_order/storeMultiple', '', 'SSL');


    /*Get form fields data*/
    $this->data['mylerz_username']       = $this->config->get('mylerz_username');
    $this->data['mylerz_password']       = $this->config->get('mylerz_password');

    $this->data['mylerz_status']         = $this->config->get('mylerz_status');
    $this->data['mylerz_admin_status']   = $this->config->get('mylerz_admin_status');
    $this->data['mylerz_debugging_mode'] = $this->config->get('mylerz_debugging_mode');
    $this->data['mylerz_geo_zone_id']    = $this->config->get('mylerz_geo_zone_id');
    $this->data['mylerz_tax_class_id']   = $this->config->get('mylerz_tax_class_id');
    $this->data['mylerz_display_name']   = $this->config->get('mylerz_display_name');
    $this->data['mylerz_price']          = $this->config->get('mylerz_price');
    $this->data['mylerz_account_currency']      = $this->config->get('mylerz_account_currency');
    $this->data['mylerz_after_creation_status'] = $this->config->get('mylerz_after_creation_status');
    $this->data['mylerz_ready_shipping_status'] = $this->config->get('mylerz_ready_shipping_status');
    $this->data['mylerz_debugging_mode'] = $this->config->get('mylerz_debugging_mode');
    $this->data['mylerz_pickup_due_date']= $this->config->get('mylerz_pickup_due_date');

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

    /*prepare mylerz.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/mylerz/settings.expand';
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
        $this->load->language('shipping/mylerz');

        //Save mylerz config data in settings table
        $this->model_setting_setting->insertUpdateSetting('mylerz', $this->request->post);

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


  public function install() {
		$this->load->model('shipping/mylerz');
		$this->model_shipping_mylerz->install();
	}

  /** HELPER METHODS **/

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/mylerz');

    if (!$this->user->hasPermission('modify', 'shipping/mylerz')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('mylerz') ){
      $this->error['mylerz_not_installed'] = $this->language->get('error_not_installed');
    }

    if ((utf8_strlen($this->request->post['mylerz_username']) < 2) ) {
      $this->error['mylerz_username'] = $this->language->get('error_username');
    }

    if((utf8_strlen($this->request->post['mylerz_password']) < 2) ) {
        $this->error['mylerz_password'] = $this->language->get('error_password');
    }

    $general_rate = $this->request->post['mylerz_price']['mylerz_general_rate'];
    if( !preg_match('/^\d*$/', $general_rate) || $general_rate <= 0 ) {
        $this->error['mylerz_general_rate'] = $this->language->get('error_general_rate');
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
        'href' => $this->url->link('shipping/mylerz', true)
      ]
    ];
    return $breadcrumbs;
  }

}
