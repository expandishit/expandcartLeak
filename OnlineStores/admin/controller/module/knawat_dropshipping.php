<?php
class ControllerModuleKnawatDropshipping extends Controller { 
	
	private $error = array();
	private $route = 'module/knawat_dropshipping';

	public function __construct($registry) {
		parent::__construct($registry);
	}

	public function install(){
		$this->load->model( $this->route );
		$this->model_module_knawat_dropshipping->install();
	}

	public function uninstall(){
		$this->load->model('setting/setting');
		$this->load->model('catalog/product');
		$this->load->model( $this->route );
		// Delete settings
		$this->model_setting_setting->deleteSetting('module_knawat_dropshipping');
		$this->model_setting_setting->deleteSetting('knawat_dropshipping_last_update');
		
		$knawat_products=$this->model_module_knawat_dropshipping->get_knawat_products();
		foreach ($knawat_products as $product) {
			# code...
			$this->model_catalog_product->deleteProduct($product['resource_id']);
		}
		$this->model_module_knawat_dropshipping->uninstall();
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
		//if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
		//	$this->response->redirect($this->url->link('marketplace/home'));
		//}
		if( !session_id() ){
			session_start();
		}

		$this->load->language( $this->route );
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model( $this->route );
		$this->load->model('localisation/order_status');

		// Validate and Submit Posts 
		if ( ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() ) {
			if (!$this->request->post['module_knawat_dropshipping_product_update_status']) {
				$this->request->post['module_knawat_dropshipping_product_update_status'] = 'off';
			}
			if (!$this->request->post['module_knawat_dropshipping_pin_status']) {
				$this->request->post['module_knawat_dropshipping_pin_status'] = 'off';
			}
			if (!$this->request->post['module_knawat_dropshipping_log']) {
				$this->request->post['module_knawat_dropshipping_log'] = 'off';
			}
			
			$this->model_setting_setting->editSetting('module_knawat_dropshipping', $this->request->post );
			require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
			$knawatapi = new KnawatMPAPI( $this->registry );
			$this->session->data['user_token']=$knawatapi->getAccessToken();

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link( $this->route));
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
			'entry_sending_order_status'	=> $this->language->get('entry_sending_order_status'),
            'text_enabled'                  => $this->language->get('text_enabled'),
            'text_disabled'                 => $this->language->get('text_disabled'),
            'text_edit'                     => $this->language->get('text_edit'),
            'button_save'                   => $this->language->get('button_save'),
			'button_cancel'                 => $this->language->get('button_cancel'),
			'success_ajaximport'			=> $this->language->get('success_ajaximport'),
			'text_force_update'				=> $this->language->get('text_force_update'),
			'text_update_category_on_product_update' => $this->language->get('text_update_category_on_product_update'),
			'text_run_import'				=> $this->language->get('text_run_import'),
			'text_import_stats'				=> $this->language->get('text_import_stats'),
			'text_imported'					=> $this->language->get('text_imported'),
			'text_products'					=> $this->language->get('text_products'),
			'text_updated'					=> $this->language->get('text_updated'),
			'text_failed'					=> $this->language->get('text_failed'),
			'text_failed_hint'					=> $this->language->get('text_failed_hint'),
			'warning_ordersync'				=>$this->language->get('warning_ordersync'),
			'text_ordersync_botton'			=>$this->language->get('text_ordersync_botton'),
			'warning_ajaximport'			=>$this->language->get('warning_ajaximport'),
			'error_ajaximport'				=>$this->language->get('error_ajaximport'),
			'error_ajaximport_404'			=>$this->language->get('error_ajaximport_404'),
            'entry_pin_status'	=> $this->language->get('entry_pin_status'),
            'entry_pin_status_help'	=> $this->language->get('entry_pin_status_help'),
            'entry_category'	=> $this->language->get('entry_category'),
            'entry_category_help'	=> $this->language->get('entry_category_help'),
            'error_pin_status'	=> $this->language->get('error_pin_status'),
            'entry_product_update_status'	=> $this->language->get('entry_product_update_status'),
            'entry_product_update_status_help'	=> $this->language->get('entry_product_update_status_help'),
            'text_syncing'	=> $this->language->get('text_syncing'),
            'success_ajaxsync'	=> $this->language->get('success_ajaxsync'),
			'entry_log'=>          $this->language->get('entry_log')
		);

		// Check and set warning.
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		// Check for order sync Warning.
		$sync_failed_orders = $this->model_module_knawat_dropshipping->get_sync_failed_orders();

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
			$errors = json_decode($this->session->data['token_error'], true)['channel']['errors'];
			foreach ($errors as $error)
			{
				$data['token_error'] .= '- ' . $error['field'] . ': ' . $error['message'] . '<br>';
			}
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

        if (isset($this->request->post['module_knawat_dropshipping_product_update_status'])) {
            $data['module_knawat_dropshipping_product_update_status'] = $this->request->post['module_knawat_dropshipping_product_update_status'];
        } else {
            $data['module_knawat_dropshipping_product_update_status'] = $this->config->get('module_knawat_dropshipping_product_update_status');
        }

		if (isset($this->request->post['module_knawat_dropshipping_update_category_on_product_update'])) {
            $data['module_knawat_dropshipping_update_category_on_product_update'] = $this->request->post['module_knawat_dropshipping_update_category_on_product_update'];
        } else {
            $data['module_knawat_dropshipping_update_category_on_product_update'] = $this->config->get('module_knawat_dropshipping_update_category_on_product_update');
        }
		if (isset($this->request->post['module_knawat_dropshipping_log'])) {
			$data['module_knawat_dropshipping_log'] = $this->request->post['module_knawat_dropshipping_log'];
		} else {
			$data['module_knawat_dropshipping_log'] = $this->config->get('module_knawat_dropshipping_log');
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

		// Set module status
		if (isset($this->request->post['module_knawat_dropshipping_sending_order_status'])) {
			$data['module_knawat_dropshipping_sending_order_status'] = $this->request->post['module_knawat_dropshipping_sending_order_status'];
		} else {
			$data['module_knawat_dropshipping_sending_order_status'] = $this->config->get('module_knawat_dropshipping_sending_order_status');
		}

        if (isset($this->request->post['module_knawat_dropshipping_pin_category'])) {
            $data['module_knawat_dropshipping_pin_category'] = $this->request->post['module_knawat_dropshipping_pin_category'];
        } else {
            $data['module_knawat_dropshipping_pin_category'] = $this->config->get('module_knawat_dropshipping_pin_category');
        }

        if (isset($this->request->post['module_knawat_dropshipping_pin_status'])) {
            $data['module_knawat_dropshipping_pin_status'] = $this->request->post['module_knawat_dropshipping_pin_status'];
        } else {
            $data['module_knawat_dropshipping_pin_status'] = $this->config->get('module_knawat_dropshipping_pin_status');
        }

        if( $data['module_knawat_dropshipping_pin_category']){
            $this->load->model('catalog/category');
            $category_info = $this->model_catalog_category->getCategory($data['module_knawat_dropshipping_pin_category']);
            if ($category_info) {
                $data['category'] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name']];
            }
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

		// Load order statuses
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['knawat_ajax_url'] = $this->url->link( $this->route .'/ajax_import', 'user_token=' . $this->session->data['user_token'], true);
		$data['knawat_sync_ajax_url'] = $this->url->link( $this->route .'/ajaxSync', 'user_token=' . $this->session->data['user_token'], true);

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
        $this->template = 'module/knawat_dropshipping.expand';
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

	public function ajaxSync(){

		set_time_limit(0);

		$this->load->model('setting/setting');
		$this->load->model('catalog/product');

		if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			return false;	
		}

		$this->load->model($this->route);
		$page = $this->config->get('knawat_dropshipping_sync_offset_page');
		$knawatRemoteProductsSKU = $this->model_module_knawat_dropshipping->getPrevSyncSKUs();
		$productsCount = count($knawatRemoteProductsSKU);
		$knawatRemoteProductsSKU = !empty($knawatRemoteProductsSKU) ? array_column($knawatRemoteProductsSKU, 'sku', 'sku') : [];
		$this->fetchProducts($productsCount, $knawatRemoteProductsSKU, $page);
        $storeKnawatProductsSKU = $this->db->query("SELECT product_id, sku FROM " . DB_PREFIX . "product left join knawat_metadata on product.product_id = knawat_metadata.resource_id where `meta_key` = 'is_knawat'")->rows;

		$productsDeletedCount = (int)$this->config->get('knawat_dropshipping_sync_deleted_count');
		foreach ($storeKnawatProductsSKU as $row) {
			if (!isset($knawatRemoteProductsSKU[$row['sku']])) {
                $this->model_catalog_product->deleteKnawatProductById($row['product_id']);
				$productsDeletedCount++;
				$this->model_module_knawat_dropshipping->saveSyncDeletedCount($productsDeletedCount);
			}
		}
		$this->model_module_knawat_dropshipping->resetSyncValues();
		echo $productsDeletedCount;

		exit();
	}

	private function fetchProducts(&$productsCount, &$knawatRemoteProductsSKU, &$page) {
		
		$this->load->model($this->route);
        $result = $this->callAPI(++$page);
        
        if (count($result->products) === 0) {
			if (count($knawatRemoteProductsSKU) != 0) {
				$knawatRemoteProductsSKU += array_column($knawatRemoteProductsSKU, 'sku', 'sku');
			}
            return;
        }
		$knawatRemoteProductsSKU += array_column($result->products, 'sku', 'sku');
		$productsCount = count($knawatRemoteProductsSKU);
		$this->model_module_knawat_dropshipping->saveSncySKUs(
			array_column($result->products, 'sku'), 
			($page - 1)
		);
        if ($productsCount >= $result->total) {
            return;
        }
		unset($result);   
        sleep(1);
		$this->fetchProducts($productsCount, $knawatRemoteProductsSKU, $page);
	}

	private function callAPI($page)
	{
        require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
        
        // temporary disable sinletone instantiation for some arised issues.
		$mp_api = new KnawatMPAPI($this->registry);
        // $mp_api = KnawatMPAPI::signletonInstantiate($this->registry);

		return $mp_api->get('catalog/products/?limit=10&page='.$page);
	}

	

	public function ajax_import(){

		$this->knawat_log('start import process from Knawat');

        sleep(1);
		$this->load->model('setting/setting');
		if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			return false;	
		}
		require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatimporter.php');
        set_time_limit(0);
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
				$item['force_update'] = $process_data['force_update'] == 1 ? true : false;
			}
			if( isset( $process_data['module_knawat_dropshipping_update_category_on_product_update'] ) ){
				$item['module_knawat_dropshipping_update_category_on_product_update'] = $process_data['module_knawat_dropshipping_update_category_on_product_update'] == 1 ? true : false;
			}
			if( isset( $process_data['prevent_timeouts'] ) ){
				$item['prevent_timeouts'] == 'false'? false:true;
			}
			if( isset( $process_data['is_complete'] ) ){
				$item['is_complete'] == 'false'? false:true;
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
				$item['batch_done'] == 'false'? false:true;
			}
			
		}
		$lastupdate = $this->model_setting_setting->getSetting('knawat_dropshipping_last_update')['module_knawat_dropshipping_lastupdate'];
		if ($item['force_update'])
		{
			$lastupdate = null;
		}
		$item['lastupdate']=$lastupdate!=null?$lastupdate:"";
        $item['knawat_pin_category'] = $this->request->post['categoryFilter']['pinCategory'] == 'true' ? true : false ;
        $item['knawat_pin_category_id'] = $this->request->post['categoryFilter']['categoryID'];

		$knawatimporter = new KnawatImporter( $this->registry, $item );
		$import_data = $knawatimporter->import();
		if($import_data === false){
			echo $import_data = json_encode(['fail'=>true]);
			exit();
		}
		
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

		$this->knawat_log('import page process finised');

		if($item['is_complete'] == true){
			$this->model_setting_setting->insertUpdateSetting('knawat_dropshipping_last_update',array('module_knawat_dropshipping_lastupdate'=>time()));
			$this->knawat_log('import process finised');

		}

		echo $import_data = json_encode( $item );
		exit();
	}

	/**
	 * Check if site has valid Access token
	 */
	public function is_valid_token(){
		
		$this->load->model('setting/setting');
		//if(!$this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
		//	$this->response->redirect($this->url->link('marketplace/home'));
		//}
		$is_valid = $this->config->get('module_knawat_dropshipping_valid_token');
		if( $is_valid == '1' ){
			return true;
		}
		return false;
	}

	/**
	 * check and log request on server
	 */
	public function knawat_log($data)
	{
	
		if(!empty($this->config->get('module_knawat_dropshipping_log')) && $this->config->get('module_knawat_dropshipping_log')=='on')
		{
		$mylog = new Log('knawatImport_' . date('Ymd') . '.log');
        $mylog->write($data);
		}
	}
}
?>