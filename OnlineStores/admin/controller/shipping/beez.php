
<?php

class ControllerShippingBeez extends Controller{
    /**
     * @var array the validation errors array.
     */
    private $error = [];

    public function index(){
	    $this->load->language('shipping/beez');

	    //buttons
	    $this->data['cancel']      = $this->url->link('extension/shipping', 'type=shipping', true);

	    /*Get form fields data*/
	    $this->data['beez'] = $this->config->get('beez');
	    $this->data['are_statuses_added'] = $this->config->get('beez_are_statuses_added');

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

	    /*prepare beez.expand view data*/
	    $this->document->setTitle($this->language->get('heading_title'));
	    $this->template = 'shipping/beez/settings.expand';
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
	        $this->load->language('shipping/beez');

	        //Save beez config data in settings table
	        $this->model_setting_setting->insertUpdateSetting('beez', $this->request->post);

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
	    $this->load->model('shipping/beez');
	    $this->model_shipping_beez->install();
	}

	public function addAllStatuses(){
		$settings = $this->config->get('beez');

	    if( !$this->config->get('beez_are_statuses_added') ){
	        // $beez_statuses = $settings['statuses'];

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
	        $this->model_setting_setting->insertUpdateSetting('beez', ['beez' => $settings]);
	        $this->model_setting_setting->insertUpdateSetting('beez', ['beez_are_statuses_added' => 1]);
	    }
	    $this->response->redirect($this->url->link('extension/shipping/activate?code=beez', '', 'SSL'));
	}

  /** HELPER METHODS **/

  /**
  * Validate form fields.
  *
  * @return bool TRUE|FALSE
  */
  private function _validateForm(){
    $this->load->language('shipping/beez');

    if (!$this->user->hasPermission('modify', 'shipping/beez')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    if( !\Extension::isInstalled('beez') ){
      $this->error['beez_not_installed'] = $this->language->get('error_not_installed');
    }

    if ( utf8_strlen($this->request->post['beez']['api_key']) < 32 ) {
      $this->error['beez_api_key'] = $this->language->get('error_beez_api_key');
    }

    if( utf8_strlen($this->request->post['beez']['account_number']) < 7){
      $this->error['beez_price'] = $this->language->get('error_beez_price');
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
        'href' => $this->url->link('shipping/beez', true)
      ]
    ];
    return $breadcrumbs;
  }

}


