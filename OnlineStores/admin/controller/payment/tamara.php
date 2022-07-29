<?php

class ControllerPaymentTamara extends Controller{

    /**
     * @var array the validation errors array.
     */
    private $error = [];

   /**
    * Method open the Tamara Settings Page
    * @return void
    */
    public function index(){
      /*prepare tamara.expand view data*/
      $this->load->language('payment/tamara');
      $this->document->setTitle($this->language->get('heading_title'));

      //Breadcrumbs
      $this->data['breadcrumbs'] = $this->_createBreadcrumbs();
      //Form Buttons
      //save button - Ajax post request
      $this->data['action'] = $this->url->link('payment/tamara/save', '' , 'SSL');
      $this->data['cancel'] = $this->url->link('extension/payment', 'type=payment', true);

      /*Get form fields data*/
      $this->data['tamara']          = $this->config->get('tamara');
      $this->data['tamara_statuses'] = $this->config->get('tamara_statuses');
      $this->data['tamara_are_statuses_added']  = $this->config->get('tamara_are_statuses_added');
      // echo '<pre>'; print_r($this->data['tamara_statuses'] );die();
      $this->data['tamara_callback'] = HTTP_CATALOG . 'index.php?route=payment/tamara/notification';

      $this->load->model('localisation/geo_zone');
      $this->data['geo_zones']         = $this->model_localisation_geo_zone->getGeoZones();

      $this->load->model('localisation/order_status');
      $this->data['order_statuses']     = $this->model_localisation_order_status->getOrderStatuses();

      $this->template = 'payment/tamara.expand';
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
          $this->load->language('payment/tamara');

                      $this->tracking->updateGuideValue('PAYMENT');
          
          $this->model_setting_setting->insertUpdateSetting('tamara', $this->request->post ); //tamara_30days (default)
          $this->model_setting_setting->insertUpdateSetting('tamara_installment', ['tamara_installment'      => $this->request->post['tamara'] ] );

          $result_json['success_msg'] = $this->language->get('text_success');
          $result_json['success']  = '1';
        }

        $this->response->setOutput(json_encode($result_json));
      }
      else{
        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
      }
    }

    public function addAllStatuses(){

      if( !$this->config->get('tamara_are_statuses_added') ){
          $tamara_statuses = $this->config->get('tamara_statuses');

          //add them to status table
          $this->load->model('localisation/order_status');
          $this->load->model('setting/setting');

          foreach($tamara_statuses as $key => $status){
              if( ($expandcart_id = $this->model_localisation_order_status->findOrderStatusByName($status['name_en'])) <= 0 ){
                $expandcart_id = $this->model_localisation_order_status->addOrderStatus(
                [
                  "order_status" => [
                    1 => ['name' => $status['name_en'] ],
                    2 => ['name' => $status['name_ar'] ]
                  ]
                ]);
              }

              $tamara_statuses[$key]['expandcartid'] = $expandcart_id;
          }

          //update setting
          $this->model_setting_setting->insertUpdateSetting('tamara', ['tamara_are_statuses_added' => '1']);
          $this->model_setting_setting->insertUpdateSetting( 'tamara', ['tamara_statuses' => $tamara_statuses]);
      }
      $this->response->redirect($this->url->link('extension/payment/activate?code=tamara', '', 'SSL'));
    }

    public function install(){
        $this->load->model('payment/tamara');
        $this->model_payment_tamara->install();     
    }

    public function uninstall(){      
        $this->load->model('payment/tamara');
        $this->model_payment_tamara->uninstall();
    }
    /**
    * Validate form fields.
    *
    * @return bool TRUE|FALSE
    */
    private function _validateForm(){
      $this->load->language('payment/tamara');

      if (!$this->user->hasPermission('modify', 'payment/tamara')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }

      if ((utf8_strlen($this->request->post['tamara']['api_token']) < 10) ) {
          $this->error['tamara_api_token'] = $this->language->get('error_token_api');
      }

      if ($this->request->post['tamara']['presentation_type'] === 'image'){
        if(empty($this->request->post['tamara']['display_image']) )
          $this->error['tamara_display_image'] = $this->language->get('error_display_image');
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




