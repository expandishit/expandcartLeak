<?php

class ControllerPaymentJumiaPay extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];


    public function __construct($registry)
    {
        parent::__construct($registry);
        //White-list for testing
        if( ! in_array( strtolower(STORECODE), ['qibvyz9370', 'plrjqi1762', 'iofawm1705'] ) ){
          $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
        }
    }

   /**
    * Method open the Mobi Cash Settings Page
    * @return void
    */
    public function index()
    {
    	/*prepare jumiapay.expand view data*/
		$this->load->language('payment/jumiapay');
		$this->document->setTitle($this->language->get('heading_title'));

		//Form Buttons
		//save button - Ajax post request
		$this->data['action'] = $this->url->link('payment/jumiapay/save', '' , 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

		/*Get form fields data*/
		$this->data['jumiapay']          = $this->config->get('jumiapay');

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');
		$this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses(['order' => 'DESC']);

        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('localisation/currency');
        $this->data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->template = 'payment/jumiapay.expand';
		$this->children = ['common/header', 'common/footer' ];

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
            $this->load->language('payment/jumiapay');

            $this->tracking->updateGuideValue('PAYMENT');

            $this->model_setting_setting->insertUpdateSetting('jumiapay', $this->request->post ); //jumiapay_30days (default)

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
        // $this->load->model('payment/jumiapay');
        // $this->model_payment_jumiapay->install();
    }

    public function uninstall(){
        // $this->load->model('payment/jumiapay');
        // $this->model_payment_jumiapay->uninstall();
    }
    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
		$this->load->language('payment/jumiapay');

		if (!$this->user->hasPermission('modify', 'payment/jumiapay')) {
		  $this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['jumiapay']['api_key']) < 4) ) {
		  $this->error['api_key'] = $this->language->get('error_api_key');
		}

		if ((utf8_strlen($this->request->post['jumiapay']['shop_config_id']) < 1) ) {
		  $this->error['shop_config_id'] = $this->language->get('error_shop_config_id');
		}

    if ((utf8_strlen($this->request->post['jumiapay']['shop_config']) < 1) ) {
      $this->error['shop_config'] = $this->language->get('error_shop_config');
    }

    if ((utf8_strlen($this->request->post['jumiapay']['merchant_token']) < 1) ) {
      $this->error['merchant_token'] = $this->language->get('error_merchant_token');
    }

		if($this->error && !isset($this->error['error']) ){
		  $this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
    }

    private function _isAjax() {

      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}
