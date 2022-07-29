<?php

class ControllerPaymentBookeey extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the Bookeey Settings Page
    * @return void
    */
    public function index(){
      /*prepare bookeey.expand view data*/
      $this->load->language('payment/bookeey');
      $this->document->setTitle($this->language->get('heading_title'));
      $this->template = 'payment/bookeey.expand';
      $this->children = array( 'common/header', 'common/footer');

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/bookeey/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['bookeey_status']        = $this->config->get('bookeey_status');
      $this->data['bookeey_live_mode']     = $this->config->get('bookeey_live_mode');
      $this->data['bookeey_payment_modes'] = $this->config->get('bookeey_payment_modes');
      $this->data['bookeey_merchant_id']   = $this->config->get('bookeey_merchant_id');
      $this->data['bookeey_secret_key']    = $this->config->get('bookeey_secret_key');
      $this->data['bookeey_complete_status_id'] = $this->config->get('bookeey_complete_status_id');

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

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
          $this->load->language('payment/bookeey');
          //Insert a record for bookeey gatway in the extension table in DB if not any
          $this->model_setting_setting->checkIfExtensionIsExists('payment', 'bookeey', TRUE);

            $this->tracking->updateGuideValue('PAYMENT');

          $this->model_setting_setting->insertUpdateSetting('bookeey', $this->request->post );

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
      $this->load->model('payment/bookeey');
      $this->model_payment_bookeey->install();
    }

    public function uninstall(){
      // $this->load->model('payment/bookeey');
      // $this->model_payment_bookeey->uninstall();
    }


    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
      $this->load->language('payment/bookeey');

      if (!$this->user->hasPermission('modify', 'payment/bookeey')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['bookeey_merchant_id']) < 3) ) {
          $this->error['bookeey_merchant_id'] = $this->language->get('error_merchant_id');
      }

      if ((utf8_strlen($this->request->post['bookeey_secret_key']) < 3) ) {
          $this->error['bookeey_secret_key'] = $this->language->get('error_secret_key');
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
