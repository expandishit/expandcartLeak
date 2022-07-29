<?php

class ControllerSaleAuction extends Controller {

	private $error = [];

 	public function __construct($registry){
        parent::__construct($registry);

        if (!$this->user->hasPermission('modify', 'module/auctions')) {
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}

        if( !\Extension::isInstalled('auctions') ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}
    }

	public function index(){
		$this->load->language('module/auctions');
	    $this->document->setTitle($this->language->get('heading_title_auctions'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs('auctions');

        //Get config settings
	    $this->load->model('module/auctions/auction');
        $auctions  = $this->model_module_auctions_auction->getAll();
        foreach ($auctions  as &$auction){
            if ($this->getNowTimeWithConfiguredTimeZone() > $auction['close_datetime'])
                $auction['bidding_status'] = 'Closed';
        }
        $this->data['auctions'] = $auctions;

		//render view template
		$this->template = 'module/auctions/auction/index.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());

	}

	public function create(){
		$this->load->language('module/auctions');
		$this->document->setTitle($this->language->get('heading_title_create'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs('create');

		$this->data['action'] = $this->url->link('sale/auction/store', '', 'SSL');

    	$this->_getForm();
	}

	public function store(){

        if ( ! $this->_validateForm() ){
            $result_json['success'] = '0';
            $result_json['errors']  = $this->error;
            $this->response->setOutput(json_encode($result_json));
            return;
        }

        $this->load->model('module/auctions/auction');
		$auction_id = $this->model_module_auctions_auction->add($this->request->post);

		if($auction_id){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/auction','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = 'Errors: ';
	  		$result_json['success'] = '0';
      		$result_json['errors']  = $this->error;
	  	}
    	$this->response->setOutput(json_encode($result_json));
	}


	//Edit auction
	public function edit(){
		$this->load->language('module/auctions');
		$this->document->setTitle($this->language->get('heading_title_edit'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs('edit');

		$this->data['action'] = $this->url->link('sale/auction/update', 'auction_id=' . $this->request->get['auction_id'], 'SSL');

        $this->load->model('module/auctions/auction');
		$this->data['auction'] = $this->model_module_auctions_auction->get($this->request->get['auction_id']);

    	$this->_getForm();
	}
	public function update(){
        if ( ! $this->_validateForm('update') ){
            $result_json['success'] = '0';
            $result_json['errors'] = $this->error;

            $this->response->setOutput(json_encode($result_json));

            return;
        }

		$data = $this->request->post;
		$data['auction_id'] = $this->request->get['auction_id'];

        $this->load->model('module/auctions/auction');
		$is_updated = $this->model_module_auctions_auction->update($data);

		if($is_updated){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/auction','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = 'Errors: ';
	  		$result_json['success'] = '0';
      		$result_json['errors']  = $this->error;
	  	}

    	$this->response->setOutput(json_encode($result_json));
	}


	public function delete(){
		$this->load->language('module/auctions');		
		$this->load->model('module/auctions/auction');
		$is_deleted = $this->model_module_auctions_auction->delete($this->request->post['selected_ids']);

		if($is_deleted){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			$result_json['redirect'] = '1';
      		$result_json['to'] = (string)$this->url->link('sale/auction','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = $this->language->get('error_warning');
	  		$result_json['success'] = '0';
      		$result_json['error']   = $this->language->get('error_delete');
	  	}

    	$this->response->setOutput(json_encode($result_json));
	}

	public function orders(){
		$this->load->language('module/auctions');
	    $this->load->model('module/auctions/auction');

	    $this->document->setTitle($this->language->get('heading_title_orders'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs('orders');

        //Get Data
        $this->data['auctions_orders']  = $this->model_module_auctions_auction->getAuctionsOrders();

		//render view template
		$this->template = 'module/auctions/auction/orders.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());
	}

	public function deposits(){
		$this->load->language('module/auctions');
	    $this->load->model('module/auctions/auction');

	    $this->document->setTitle($this->language->get('heading_title_orders'));

        //Breadcrumbs
	    $this->data['breadcrumbs'] = $this->_createBreadcrumbs('orders');

        //Get Data
        $this->data['auctions_deposits']  = $this->model_module_auctions_auction->getAuctionsMinDepositLog();

		//render view template
		$this->template = 'module/auctions/auction/deposits.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());
	}
	/**
	* Validate form fields.
	*
	* @return bool TRUE|FALSE
	*/
	private function _validateForm($function = 'create'){
		$this->load->language('module/auctions');

		//Permission Validation
		if (!$this->user->hasPermission('modify', 'module/auctions/auction')) {
		    $this->error['warning'] = $this->language->get('error_permission');
		}

		if( empty($this->request->post['product_id']) ){
			$this->error['product'] = $this->language->get('error_product');
		}

		// starting_bid_price => minimum 1, numbers only, decimal point allowed,
		if( empty($this->request->post['starting_bid_price']) 
			|| !preg_match("/^\d{1,11}\.?\d{0,4}$/", $this->request->post['starting_bid_price']) 
			|| $this->request->post['starting_bid_price'] < 1){
			$this->error['starting_bid_price'] = $this->language->get('error_starting_bid_price');			
		}

		//min_deposit => money (numbers only with decimal points 4) min 0 max ...
		if( !isset($this->request->post['min_deposit']) 
			|| !preg_match("/^\d{0,11}\.?\d{0,4}$/", $this->request->post['min_deposit']) 
			|| $this->request->post['min_deposit'] < 0){
			$this->error['min_deposit'] = $this->language->get('error_min_deposit');			
		}

		//increment => money , min and max
		if( !empty($this->request->post['increment']) &&
			!preg_match("/^\d{0,9}\.?\d{0,4}$/", $this->request->post['increment'])||
			$this->request->post['increment'] < 0 ){
			$this->error['increment'] = $this->language->get('error_increment');			
		}
		
		// start_datetime => not in the past, less than close time, date/time format only no letters or symbols but slash		
		$timezone = $this->request->post['client_timezone']?:$this->config->get('config_timezone');
		$start_datetime = DateTime::createFromFormat('Y-m-d h:iA', $this->request->post['start_datetime'], new  DateTimeZone($timezone));
		$now_datetime   = DateTime::createFromFormat('Y-m-d h:iA', (new DateTime('NOW', new DateTimeZone($timezone)))->format('Y-m-d h:iA'), new  DateTimeZone($timezone));

		if( $start_datetime === FALSE){
			$this->error['start_datetime'] = $this->language->get('error_start_datetime');									
		}
		else if( $function == 'create' && $start_datetime < $now_datetime ){
			$this->error['start_datetime'] = $this->language->get('error_start_datetime');									
		}

		// close_datetime => not in the past, greater than start_datetime, date/time format only no letters or symbols but slash.
		$close_datetime = DateTime::createFromFormat('Y-m-d h:iA', $this->request->post['close_datetime']);
		if( $close_datetime === FALSE || $close_datetime < $start_datetime){
			$this->error['close_datetime'] = $this->language->get('error_close_datetime');						
		}

		//min_quantity => number integer, min & max
		// if( empty($this->request->post['min_quantity']) ||
		//  !preg_match("/^\d+$/", $this->request->post['min_quantity']) ||
		//  $this->request->post['min_quantity'] < 0){
		// 	$this->error['min_quantity'] = $this->language->get('error_min_quantity');									
		// }


		// //max_quantity => number integer, min & max
		// if( empty($this->request->post['max_quantity']) ||
		//  !preg_match("/^\d+$/", $this->request->post['max_quantity']) ||
		//  $this->request->post['max_quantity'] < 0 ||
		//  $this->request->post['max_quantity'] <  $this->request->post['min_quantity']){
		// 	$this->error['max_quantity'] = $this->language->get('error_max_quantity');									
		// }


		//purchase_valid_days => number integar, min & max
		if( /*empty($this->request->post['purchase_valid_days']) ||*/
		 !preg_match("/^\d+$/", $this->request->post['purchase_valid_days']) ||
		 $this->request->post['purchase_valid_days'] < 0){
			$this->error['purchase_valid_days'] = $this->language->get('error_purchase_valid_days');									
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
	private function _createBreadcrumbs($page){

		$breadcrumbs = [
		  [
		    'text' => $this->language->get('text_home'),
		    'href' => $this->url->link('common/dashboard', '', 'SSL')
		  ],
		  [
		    'text' => $this->language->get('text_module'),
		    'href' => $this->url->link('marketplace/home', '', 'SSL')
		  ],
		  // [
		  //   'text' => $this->language->get('heading_title'),
		  //   'href' => $this->url->link('module/auctions', '', 'SSL')
		  // ],
		  [
		    'text' => $this->language->get('heading_title'),
		    'href' => $this->url->link('sale/auction', '', 'SSL')
		  ]
		];

		if($page === 'create'){
			$breadcrumbs[] = [
				'text' => $this->language->get('heading_title_create'),
		    	'href' => $this->url->link('sale/auction/create', '', 'SSL')
			];
 		}
 		if($page === 'edit'){
			$breadcrumbs[] = [
				'text' => $this->language->get('heading_title_edit'),
		    	'href' => $this->url->link('sale/auction/edit', '', 'SSL')
			];
 		}
		return $breadcrumbs;
	}

	private function _getForm() {
		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$this->data['error_name']    = isset($this->error['name']) ? $this->error['name'] : [];

		$this->data['cancel'] = $this->url->link('sale/auction', '', 'SSL');

		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		//If there is no left symbol or right symbol use currency code.
		$this->data['current_store_currency'] = ($this->config->get('config_admin_language') === 'ar' ? $currency['symbol_right'] : $currency['symbol_left']) ?: $this->config->get('config_currency');


	    $this->load->model('module/auctions/auction');
        $this->data['products']  = $this->model_module_auctions_auction->getAuctionIllegibleProducts();


		$this->template = 'module/auctions/auction/form.expand';
        $this->children = [ 'common/header', 'common/footer' ];
        $this->response->setOutput($this->render());
  	}


  	public function deleteBid(){
  		$this->load->language('module/auctions');		
		$this->load->model('module/auctions/auction');
		$is_deleted = $this->model_module_auctions_auction->deleteBid($this->request->post['bid_id']);

		if($is_deleted){
	  		$this->session->data['success'] = $result_json['success_msg'] = $this->language->get('text_success');
			$result_json['success']  = '1';
			// $result_json['redirect'] = '1';
      		// $result_json['to'] = (string)$this->url->link('sale/auction','','SSL')->format();
	  	}else{
	  		$this->error['warning'] = $this->language->get('warning');
	  		$result_json['success'] = '0';
      		$result_json['error']   = $this->language->get('error_delete_bid');
	  	}

    	$this->response->setOutput(json_encode($result_json));
  	}

    public function getNowTimeWithConfiguredTimeZone(){
        $app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');
        return (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d H:i:s');
    }
}
