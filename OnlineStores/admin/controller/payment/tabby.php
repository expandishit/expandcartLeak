

<?php

class ControllerPaymentTabby extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the Tabby Settings Page
    * @return void
    */
    public function index(){
      /*prepare tabby.expand view data*/
      $this->load->language('payment/tabby');
      $this->document->setTitle($this->language->get('heading_title'));

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/tabby/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['tabby']  = $this->config->get('tabby_pay_later'); // or $this->config->get('tabby_installments');

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

      $this->template = 'payment/tabby.expand';
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
          $this->load->model('payment/tabby');
          $this->load->model('setting/setting');
          $this->load->language('payment/tabby');

          $this->model_setting_setting->editGuideValue('GETTING_STARTED', 'PAYMENT', '1');
          $shouldReload = $this->config->get('tabby_pay_later') == null;
          $this->model_setting_setting->insertUpdateSetting('tabby_pay_later'	, ['tabby_pay_later'     => $this->request->post['tabby'] ] ); //tabby_pay_later (default)
          $this->model_setting_setting->insertUpdateSetting('tabby_installments', ['tabby_installments'  => $this->request->post['tabby'] ] );

          $secret_key = $this->request->post['tabby']['secret_key'];
          $country_code = $this->request->post['tabby']['account_country_code'];

          $webhook = $this->model_payment_tabby->createWebhook($secret_key,$country_code);
          $webhook = $webhook->getContent();

          if (isset($webhook['error'])) {
            $result_json['success'] = '0';
            $result_json['errors'] = ['warning' => $webhook['error']];
            $this->response->setOutput(json_encode($result_json));
            return;
          }


          $result_json['success_msg'] = $this->language->get('text_success');
          $result_json['success']  = '1';
          if ($shouldReload) {
            $result_json['redirect'] = "1";
            $result_json['to'] = "".$this->url->link('extension/payment/activate', 'code=tabby', true);
          }
          
        }

        $this->response->setOutput(json_encode($result_json));
      }
      else{
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }
    }


    public function install(){
        $this->load->model('payment/tabby');
        $this->model_payment_tabby->install();     
    }

    public function uninstall(){      
        $this->load->model('payment/tabby');
        $this->model_payment_tabby->uninstall();
    }
    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
      $this->load->language('payment/tabby');

      if (!$this->user->hasPermission('modify', 'payment/tabby')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if (empty($this->request->post['tabby']['public_key'])) {
          $this->error['tabby_public_key'] = $this->language->get('error_public_key');
      }

      if (empty($this->request->post['tabby']['secret_key'])) {
          $this->error['tabby_secret_key'] = $this->language->get('error_secret_key');
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




