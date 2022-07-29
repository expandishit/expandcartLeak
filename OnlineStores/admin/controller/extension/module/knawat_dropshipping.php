<?php

// This controller for knawat system library only
// The working one is admin/controller/module/knawat_dropshipping/
class ControllerExtensionModuleKnawatDropshipping extends Controller { 
	
	private $error = array();
	private $route = 'extension/module/knawat_dropshipping';

	public function __construct($registry) {
		parent::__construct($registry);
	}

	public function install(){
		$this->load->model( $this->route );

		$this->model_extension_module_knawat_dropshipping->install();

		/**
		 * Add Events
		 */
		/*
		$this->model_setting_event->addEvent(
			'knawat_dropshipping_add_to_cart',
			'catalog/controller/checkout/cart/add/before',
			'extension/module/knawat_dropshipping/before_add_to_cart'
		);

		$this->model_setting_event->addEvent(
			'knawat_dropshipping_single_product',
			'catalog/controller/product/product/after',
			'extension/module/knawat_dropshipping/after_single_product'
		);

		$this->model_setting_event->addEvent(
            'knawat_dropshipping_order_changed',
            'catalog/model/checkout/order/addOrderHistory/after',
            'extension/module/knawat_dropshipping/order_changed'
        );
		*/
	}

	public function uninstall(){
		$this->load->model('setting/event' );
		$this->load->model('setting/setting');

		$events = $this->model_setting_event->getEvents();
		$data = array(
			'knawat_dropshipping_add_to_cart',
			'knawat_dropshipping_single_product',
			'knawat_dropshipping_order_changed'
		);

		// Delete events
		foreach ($events as $event) {
			if ( in_array($event['code'], $data ) ) {
				$this->model_setting_event->deleteEvent( $event['event_id'] );
			}
		}

		// Delete settings
		$this->model_setting_setting->deleteSetting('module_knawat_dropshipping');
	}

	// Enabled & Disable ingnore for now.
	/* public function enabled() {
		$this->load->model('setting/event');
		$events = $this->model_setting_event->getEvents();

		$data = array(
			'knawat_dropshipping_add_to_cart',
			'knawat_dropshipping_single_product',
			'knawat_dropshipping_order_changed'
		);

		foreach ( $events as $event ) {
			if ( in_array($event['code'], $data) ) {
				$this->model_setting_event->enableEvent( $event['event_id'] );
			}
		}
	}

	public function disabled() {
		$this->load->model('setting/event');
		$events = $this->model_setting_event->getEvents();

		$data = array(
			'knawat_dropshipping_add_to_cart',
			'knawat_dropshipping_single_product',
			'knawat_dropshipping_order_changed'
		);

		foreach ( $events as $event ) {
			if ( in_array($event['code'], $data) ) {
				$this->model_setting_event->disableEvent( $event['event_id'] );
			}
		}
	} */

	public function index() {
		$this->load->model('setting/setting');
		if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			$this->response->redirect($this->url->link('marketplace/home'));		
		}
		if( !session_id() ){
			session_start();
		}

		$this->load->language( $this->route );
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model( $this->route );

		// Validate and Submit Posts 
		if ( ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {
			$this->model_setting_setting->editSetting('module_knawat_dropshipping', $this->request->post );
			require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
			$knawatapi = new KnawatMPAPI( $this->registry );
			$this->session->data['user_token']=$knawatapi->getAccessToken();

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link( $this->route, 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		else{
			require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
			$knawatapi = new KnawatMPAPI( $this->registry );
			$this->session->data['user_token']=$knawatapi->getAccessToken();
		}

		// Load Laguage strings
		$data = array(
		    'heading_title'                 => $this->language->get('heading_title'),
			'entry_consumer_key' 			=> $this->language->get('entry_consumer_key'),
			'consumer_key_placeholder' 		=> $this->language->get('consumer_key_placeholder'),
			'entry_consumer_secret' 		=> $this->language->get('entry_consumer_secret'),
			'consumer_secret_placeholder' 	=> $this->language->get('consumer_secret_placeholder'),
            'entry_status'                  => $this->language->get('entry_status'),
            'entry_store'                   => $this->language->get('entry_store'),
            'text_enabled'                  => $this->language->get('text_enabled'),
            'text_disabled'                 => $this->language->get('text_disabled'),
            'text_edit'                     => $this->language->get('text_edit'),
            'button_save'                   => $this->language->get('button_save'),
			'button_cancel'                 => $this->language->get('button_cancel'),
			'success_ajaximport'			=> $this->language->get('success_ajaximport'),
			'text_run_import'				=> $this->language->get('text_run_import'),
			'text_import_stats'				=> $this->language->get('text_import_stats'),
			'text_imported'					=> $this->language->get('text_imported'),
			'text_products'					=> $this->language->get('text_products'),
			'text_updated'					=> $this->language->get('text_updated'),
			'text_failed'					=> $this->language->get('text_failed'),
			);

		// Check and set warning.
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// Check for order sync Warning.
		$sync_failed_orders = $this->model_extension_module_knawat_dropshipping->get_sync_failed_orders();

		if( !empty( $sync_failed_orders ) ){
			$data['ordersync_warning'] = 1;
		}

		// Status Error
		if (isset($this->error['error_status'])) {
            $data['error_status'] = $this->error['error_status'];
        } else {
            $data['error_status'] = '';
		}

		// Set Success.
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		// Token Error.
		if (isset($this->session->data['token_error'])) {
			$data['token_error'] = $this->session->data['token_error'];

			unset($this->session->data['token_error']);
		} else {
			$data['token_error'] = '';
		}

		$data['token_valid'] = false;
		if( $this->is_valid_token() ){
			$data['token_valid'] = true;
		}

		// Setup Breadcrumbs Data.
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link( $this->route)
		);

		$data['action'] = $this->url->link( $this->route);

		$data['cancel'] = $this->url->link('marketplace/home');

		// Set module status
		if (isset($this->request->post['module_knawat_dropshipping_status'])) {
			$data['module_knawat_dropshipping_status'] = $this->request->post['module_knawat_dropshipping_status'];
		} else {
			$data['module_knawat_dropshipping_status'] = $this->config->get('module_knawat_dropshipping_status');
		}

		// Set Consumer Key
		if (isset($this->request->post['module_knawat_dropshipping_consumer_key'])) {
			$data['module_knawat_dropshipping_consumer_key'] = $this->request->post['module_knawat_dropshipping_consumer_key'];
		} else {
			$data['module_knawat_dropshipping_consumer_key'] = $this->config->get('module_knawat_dropshipping_consumer_key');
		}

		// Set Consumer Secret
		if (isset($this->request->post['module_knawat_dropshipping_consumer_secret'])) {
			$data['module_knawat_dropshipping_consumer_secret'] = $this->request->post['module_knawat_dropshipping_consumer_secret'];
		} else {
			$data['module_knawat_dropshipping_consumer_secret'] = $this->config->get('module_knawat_dropshipping_consumer_secret');
		}


		// Setup Stores.
		$this->load->model('setting/store');
		$data['stores'] = array();
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		if (isset($this->request->post['module_knawat_dropshipping_store'])) {
			$data['module_knawat_dropshipping_store'] = $this->request->post['module_knawat_dropshipping_store'];
		} else {
			$kstores = $this->config->get('module_knawat_dropshipping_store');
			if( !empty( $kstores ) ){
				$data['module_knawat_dropshipping_store'] = $kstores;
			}else{
				$data['module_knawat_dropshipping_store'] = array(0);
			}
		}

		$data['knawat_ajax_url'] = $this->url->link( $this->route .'/ajax_import', 'user_token=' . $this->session->data['user_token'], true);

		$_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
		$data['csrf_token'] = $_SESSION['csrf_token'];
		$data['knawat_ordersync_url'] = HTTP_CATALOG . 'index.php?route='. $this->route .'/sync_failed_order';

		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ) {
			$data['knawat_ajax_loader']  = HTTPS_SERVER .'view/image/knawat_ajax_loader.gif';
		}else{
			$data['knawat_ajax_loader']  = HTTP_SERVER .'view/image/knawat_ajax_loader.gif';
		}
/*
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
*/
        $this->template = 'extension/module/knawat_dropshipping.expand';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->data=$data;
        $this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission( 'modify', $this->route ) ) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

		if(!isset( $this->request->post['module_knawat_dropshipping_status'])){
			$this->error['error_status'] = $this->language->get('error_status');
			return false;
		}
		return true;
	}

	public function ajax_import(){

		$this->load->model('setting/setting');
		if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			$this->response->redirect($this->url->link('marketplace/home'));	
		}
		require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatimporter.php');

		$item = array();
		if( isset( $this->request->post['process_data'] ) && !empty( $this->request->post['process_data'] ) ){
			$process_data = $this->request->post['process_data'];
			$item = array();
			if( isset( $process_data['limit'] ) ){
				$item['limit'] = (int)$process_data['limit'];
			}
			if( isset( $process_data['page'] ) ){
				$item['page'] = (int)$process_data['page'];
			}
			if( isset( $process_data['force_update'] ) ){
				$item['force_update'] = (boolean)$process_data['force_update'];
			}
			if( isset( $process_data['prevent_timeouts'] ) ){
				$item['prevent_timeouts'] = (boolean)$process_data['prevent_timeouts'];
			}
			if( isset( $process_data['is_complete'] ) ){
				$item['is_complete'] = (boolean)$process_data['is_complete'];
			}
			if( isset( $process_data['imported'] ) ){
				$item['imported'] = (int)$process_data['imported'];
			}
			if( isset( $process_data['failed'] ) ){
				$item['failed'] = (int)$process_data['failed'];
			}
			if( isset( $process_data['updated'] ) ){
				$item['updated'] = (int)$process_data['updated'];
			}
			if( isset( $process_data['batch_done'] ) ){
				$item['batch_done'] = (boolean)$process_data['batch_done'];
			}
		}

		$knawatimporter = new KnawatImporter( $this->registry, $item );
		$import_data = $knawatimporter->import();
		
		$params = $knawatimporter->get_import_params();

		$item = $params;
		if( true == $params['batch_done'] ){
			$item['page']  = $params['page'] + 1;
		}else{
			$item['page']  = $params['page'];
		}

		$item['imported'] += count( $import_data['imported'] );
		$item['failed']   += count( $import_data['failed'] );
		$item['updated']  += count( $import_data['updated'] );
		echo $import_data = json_encode( $item );
		exit();
	}

	/**
	 * Check if site has valid Access token
	 */
	public function is_valid_token(){
		
		$this->load->model('setting/setting');
		if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			$this->response->redirect($this->url->link('marketplace/home'));	
		}
		$is_valid = $this->config->get('module_knawat_dropshipping_valid_token');
		if( $is_valid == '1' ){
			return true;
		}
		return false;
	}
}
?>