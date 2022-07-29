<?php

class ControllerShippingAbsher extends Controller{
  /**
   * @var array the validation errors array.
   */
  private $error = [];

  public function index(){

    $this->load->language('shipping/absher');

    //save button - Ajax post request
    $this->data['action'] = $this->url->link('shipping/absher/save', '' , 'SSL');
    $this->data['cancel'] = $this->url->link('extension/shipping', 'type=shipping', true);


    /*Get form fields data*/
    $this->data['absher_email']          = $this->config->get('absher_email');
    $this->data['absher_password']       = $this->config->get('absher_password');

    $this->data['absher_status']         = $this->config->get('absher_status');
    $this->data['absher_debugging_mode'] = $this->config->get('absher_debugging_mode');
    $this->data['absher_geo_zone_id']    = $this->config->get('absher_geo_zone_id');
    $this->data['absher_tax_class_id']   = $this->config->get('absher_tax_class_id');
    $this->data['absher_after_creation_status'] = $this->config->get('absher_after_creation_status');
    $this->data['absher_gateway_display_name']  = $this->config->get('absher_gateway_display_name');
    $this->data['absher_price']                 = $this->config->get('absher_price');
    $this->data['absher_account_currency']      = $this->config->get('absher_account_currency');

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

    /*prepare absher.expand view data*/
    $this->document->setTitle($this->language->get('heading_title'));
    $this->template = 'shipping/absher/settings.expand';
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
        $this->load->language('shipping/absher');

        //Save absher config data in settings table
          $this->model_setting_setting->insertUpdateSetting('absher', $this->request->post);

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



  /** HELPER METHODS **/

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/absher');

    if (!$this->user->hasPermission('modify', 'shipping/absher')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('absher') ){
      $this->error['absher_not_installed'] = $this->language->get('error_not_installed');
    }

    if((utf8_strlen($this->request->post['absher_password']) < 3) ) {
        $this->error['absher_password'] = $this->language->get('error_absher_password');
    }

    //$email_pattern_validation = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
    if((utf8_strlen($this->request->post['absher_email']) < 3) || !filter_var($this->request->post['absher_email'], FILTER_VALIDATE_EMAIL) ) {
        $this->error['absher_email'] = $this->language->get('error_absher_email');
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
        'href' => $this->url->link('shipping/absher', true)
      ]
    ];
    return $breadcrumbs;
  }

}
