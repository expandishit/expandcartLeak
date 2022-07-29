
<?php

class ControllerShippingParcel extends Controller{
    /**
     * @var array the validation errors array.
     */
    private $error = [];

    public function index(){
	    $this->load->language('shipping/parcel');

	    //buttons
	    $this->data['cancel']      = $this->url->link('extension/shipping', 'type=shipping', true);

	    /*Get form fields data*/
	    $this->data['parcel'] = $this->config->get('parcel');

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

	    /*prepare parcel.expand view data*/
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'shipping/parcel/settings.expand';
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
	        $this->load->language('shipping/parcel');

	        //Save parcel config data in settings table
	        $this->model_setting_setting->insertUpdateSetting('parcel', $this->request->post);

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
	    // $this->load->model('shipping/parcel');
	    // $this->model_shipping_parcel->install();
	}

	public function addAllStatuses(){
		$settings = $this->config->get('parcel');

	    if( !$this->config->get('parcel_are_statuses_added') ){
	        // $parcel_statuses = $settings['statuses'];

	        //add them to status table
	        $this->load->model('localisation/order_status');
	        $this->load->model('setting/setting');

	        foreach($settings['statuses'] as $key => &$status){
	            $inserted_status_id = $this->model_localisation_order_status->addOrderStatus(
	              [
	                "order_status" => [
	                  1 => ['name' => $status['name']['en'] ],
	                  2 => ['name' => $status['name']['ar'] ]
	                ]
	              ]);
	            $status['expandcartid'] = $inserted_status_id;
	        }
	        // $settings['are_statuses_added'] = 1;

	        //update setting
	        $this->model_setting_setting->insertUpdateSetting('parcel', ['parcel' => $settings]);
	        $this->model_setting_setting->insertUpdateSetting('parcel', ['parcel_are_statuses_added' => 1]);
	    }
	    $this->response->redirect($this->url->link('extension/shipping/activate?code=parcel', '', 'SSL'));
	}

  /** HELPER METHODS **/

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/parcel');

    if (!$this->user->hasPermission('modify', 'shipping/parcel')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('parcel') ){
      $this->error['parcel_not_installed'] = $this->language->get('error_not_installed');
    }

    if ( utf8_strlen($this->request->post['parcel']['client_id']) < 20 ) {
      $this->error['parcel_client_id'] = $this->language->get('error_parcel_client_id');
    }
    
    if ( utf8_strlen($this->request->post['parcel']['client_secret']) < 20 ) {
      $this->error['parcel_client_secret'] = $this->language->get('error_parcel_client_secret');
    }

    $general_rate = $this->request->post['parcel']['price']['parcel_general_rate'];
    if( !preg_match('/^\d*$/', $general_rate) || $general_rate <= 0 ) {
        $this->error['parcel_general_rate'] = $this->language->get('error_general_rate');
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
        'href' => $this->url->link('shipping/parcel', true)
      ]
    ];
    return $breadcrumbs;
  }

}
