<?php

class ControllerPaymentSadadBahrain extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the SadadBahrain Settings Page
    * @return void
    */
    public function index(){
      /*prepare sadad_bahrain.expand view data*/
      $this->load->language('payment/sadad_bahrain');
      $this->document->setTitle($this->language->get('heading_title'));

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/sadad_bahrain/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['sadad_bahrain']  = $this->config->get('sadad_bahrain');

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

      $this->load->model('localisation/language');
      $this->data['languages'] = $this->model_localisation_language->getLanguages();

      $this->template = 'payment/sadad_bahrain/settings.expand';
      $this->children = array( 'common/header', 'common/footer' );

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
          $this->load->language('payment/sadad_bahrain');

          $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'PAYMENT', '1');
          $this->model_setting_setting->insertUpdateSetting('sadad_bahrain' , $this->request->post );

          $result_json['success_msg'] = $this->language->get('text_success');
          $result_json['success']  = '1';
          if ($shouldReload) {
            $result_json['redirect'] = "1";
            $result_json['to'] = "".$this->url->link('extension/payment/activate', 'code=sadad_bahrain', true);
          }

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
      $this->load->language('payment/sadad_bahrain');

      if (!$this->user->hasPermission('modify', 'payment/sadad_bahrain')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['sadad_bahrain']['api_key']) < 36) ) {
          $this->error['sadad_bahrain_api_key'] = $this->language->get('error_api_key');
      }
      if (!preg_match('/^[0-9]{3,}$/',$this->request->post['sadad_bahrain']['vendor_id']) ) {
          $this->error['sadad_bahrain_vendor_id'] = $this->language->get('error_vendor_id');
      }
      if (!preg_match('/^[0-9]{3,}$/',$this->request->post['sadad_bahrain']['terminal_id']) ) {
          $this->error['sadad_bahrain_terminal_id'] = $this->language->get('error_terminal_id');
      }          
      if (!preg_match('/^[0-9]{3,}$/',$this->request->post['sadad_bahrain']['branch_id']) ) {
          $this->error['sadad_bahrain_branch_id'] = $this->language->get('error_branch_id');
      }
      if ((utf8_strlen($this->request->post['sadad_bahrain']['customer_name']) < 3) ) {
          $this->error['sadad_bahrain_customer_name'] = $this->language->get('error_customer_name');
      }
      if (!filter_var($this->request->post['sadad_bahrain']['email'], FILTER_VALIDATE_EMAIL) ) {
          $this->error['sadad_bahrain_email'] = $this->language->get('error_email');
      }
      if (!preg_match('/^[0-9]{11}$/',$this->request->post['sadad_bahrain']['msisdn']) ) {
          $this->error['sadad_bahrain_msisdn'] = $this->language->get('error_msisdn');
      }                       

      if($this->error && !isset($this->error['error']) ){
        $this->error['warning'] = $this->language->get('error_warning');
      }

      return !$this->error;
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
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        ],
        [
          'text' => $this->language->get('text_payment'),
          'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        ],
        [
          'text' => $this->language->get('heading_title'),
          'href' => $this->url->link('payment/gate2play', true)
        ]
      ];
      return $breadcrumbs;
    }





    private function _isAjax() {

      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

}
