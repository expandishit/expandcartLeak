<?php

class ControllerSellerAccountAuctions extends ControllerSellerAccount {

	public function index() {

        $this->document->setTitle($this->language->get('ms_account_dashboard_aucation'));
        $this->data['ms_account_dashboard_aucation'] = $this->language->get('ms_account_dashboard_aucation');
        $this->data['column_bidder_name'] = $this->language->get('column_product_name');
        $this->data['column_starting_price'] = $this->language->get('column_starting_price');
        $this->data['column_increment'] = $this->language->get('column_increment');
        $this->data['column_start_datetime'] = $this->language->get('column_start_datetime');
        $this->data['column_close_datetime'] = $this->language->get('column_close_datetime');
        $this->data['column_auction_status'] = $this->language->get('column_auction_status');
        $this->data['column_biding_status'] = $this->language->get('column_biding_status');
        $this->data['column_min_deposit'] = $this->language->get('column_min_deposit');
        $this->data['ms_account_aucation_update'] = $this->language->get('ms_account_aucation_update');
        $this->data['tab_auction_bids'] = $this->language->get('tab_auction_bids');
        $this->breadcrumbs();
        
      
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/auctions/account-auctions');
        
		$this->response->setOutput($this->render());
	}
   
    public function breadcrumbs()
    {
        $this->load->model('seller/seller');
        $seller_title = $this->model_seller_seller->getSellerTitle();
        $this->data['breadcrumbs'] = $this->MsLoader->MsHelper->setBreadcrumbs(array(
			array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', 'SSL'),
			),
			array(
				'text' => sprintf( $this->language->get('ms_account_dashboard_breadcrumbs') , $seller_title ),
				'href' => $this->url->link('seller/account-dashboard', '', 'SSL'),
			),
			array(
				'text' => $this->language->get('ms_account_dashboard_aucation'),
				'href' => $this->url->link('seller/account-auctions', '', 'SSL'),
			)
		));
        $this->data['link_back'] = $this->url->link('seller/account-auctions', '', 'SSL');

    }
    public function getTableData() {
	
		$seller_id = $this->customer->getId();
       $this->load->model('module/auctions/seller');
        $actions=$this->model_module_auctions_seller->getAll($seller_id);
		
		$total = isset($actions[0]) ? $actions[0]['total_rows'] : 0;

		$columns = array();
		foreach ($actions as $action) {	
			// actions
            $actions = "<a href='" . $this->url->link('seller/account-auctions/edit', 'auction_id=' . $action['auction_id'] , 'SSL') ."' class='ms-button ms-button-edit' title='" . $this->language->get('ms_edit') . "'></a>";
        
			$columns[] = array_merge(
				$action,
				array(
					'product_name' => $action['product_name'],
					'starting_bid_price' => $action['starting_bid_price'],
					'increment' => $action['increment'],
					'start_datetime' => $action['start_datetime'],
					'close_datetime' => $action['close_datetime'],
					'auction_status' => $action['auction_status'],
					'bidding_status' => $action['bidding_status'],
					'min_deposit' => $action['min_deposit'],	
					'actions' => $actions
				)
			);
		}
		$this->response->setOutput(json_encode(array(
			'iTotalRecords' => $total,
			'iTotalDisplayRecords' => $total,
			'aaData' => $columns,
		)));
	}

    public function add() 
    {
        $this->document->setTitle($this->language->get('ms_account_aucation_add'));
        $this->data['ms_account_dashboard_aucation'] = $this->language->get('ms_account_aucation_add');
        $this->data['submit_link'] = $this->url->link('seller/account-auctions/store', '', 'SSL');
        $this->getForm();         
	}
  
    public function edit() 
    {
        $this->document->setTitle($this->language->get('ms_account_aucation_update'));
        $this->data['ms_account_dashboard_aucation'] = $this->language->get('ms_account_aucation_update');
       $this->load->model('module/auctions/seller');
		$this->data['auction'] = $this->model_module_auctions_seller->get($this->request->get['auction_id']);
        $this->data['bids'] = $this->data['auction']['bids'];
        $this->getForm();    
        
	}
    public function store(){
        $json = array();
         $json= $this->validateForm();
         if (empty($json))
         {
            $this->load->model('module/auctions/seller');
             $data=$this->request->post;
             $auction_id = $this->request->post['auction_id'];
             if($auction_id)
              $this->model_module_auctions_seller->update($data,$this->customer->getId());
             else $this->model_module_auctions_seller->add($data,$this->customer->getId()); 
             $json['success']='1';
             $data['ms_success']=$this->language->get('ms_success');
         }
   
         $this->response->setOutput(json_encode($json));
                
     }
    private function getForm() {
		$this->data['textenabled'] = $this->language->get('text_enabl');
		$this->data['textdisabled'] = $this->language->get('text_disable');

		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		$this->breadcrumbs();

       $this->load->model('module/auctions/seller');
        $seller_id = $this->customer->getId();
        $this->data['products']  = $this->model_module_auctions_seller->getAuctionProducts($seller_id);
        $this->data['current_store_currency'] = ($this->config->get('config_admin_language') === 'ar' ? $currency['symbol_right'] : $currency['symbol_left']) ?: $this->config->get('config_currency');
      
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/auctions/auction_form');
        
		$this->response->setOutput($this->render());
  	}
      private function validateForm(){
        $data = $this->request->post;
		if( empty($data['product_id']) ){
			$json['errors']['product'] = $this->language->get('error_product');
		}

		if( empty($data['starting_bid_price']) 
			|| !preg_match("/^\d{1,11}\.?\d{0,4}$/", $data['starting_bid_price']) 
			|| $data['starting_bid_price'] < 1){
			$json['errors']['starting_bid_price'] = $this->language->get('error_starting_bid_price');			
		}

		if( !isset($data['min_deposit']) 
			|| !preg_match("/^\d{0,11}\.?\d{0,4}$/", $data['min_deposit']) 
			|| $data['min_deposit'] < 0){
			$json['errors']['min_deposit'] = $this->language->get('error_min_deposit');			
		}

		if( !empty($data['increment']) &&
			!preg_match("/^\d{0,9}\.?\d{0,4}$/", $data['increment'])||
			$data['increment'] < 0 ){
			$json['errors']['increment'] = $this->language->get('error_increment');			
		}
			
		$timezone = $data['client_timezone']?:$this->config->get('config_timezone');
		$start_datetime = DateTime::createFromFormat('Y-m-d h:iA', (new DateTime($data['start_datetime'], new DateTimeZone($timezone)))->format('Y-m-d h:iA'));
		$now_datetime   = DateTime::createFromFormat('Y-m-d h:iA', (new DateTime('NOW', new DateTimeZone($timezone)))->format('Y-m-d h:iA'));
		
		if( $start_datetime === FALSE){
			$json['errors']['start_datetime'] = $this->language->get('error_start_datetime');									
		}
		/*else if($start_datetime < $now_datetime ){
			$json['errors']['start_datetime'] = $this->language->get('error_start_datetime');									
		}*/
		
		$close_datetime = DateTime::createFromFormat('Y-m-d h:iA', $data['close_datetime']);
		if( $close_datetime === FALSE || $close_datetime < $start_datetime){
			$json['errors']['close_datetime'] = $this->language->get('error_close_datetime');						
		}
		if( 
		 !preg_match("/^\d+$/", $data['purchase_valid_days']) ||
		 $data['purchase_valid_days'] < 0){
			$json['errors']['purchase_valid_days'] = $this->language->get('error_purchase_valid_days');									
		}

        return $json;
	}
   
    public function deleteBid(){
	
     $this->load->model('module/auctions/seller');
      $is_deleted = $this->model_module_auctions_seller->deleteBid($this->request->post['id']);
      if($is_deleted) return true; else return false;
    }

    public function auction_orders() {

        $this->document->setTitle($this->language->get('ms_account_aucation_orders'));
        $this->data['ms_account_dashboard_aucation'] = $this->language->get('ms_account_aucation_orders');
        $this->data['column_auction_id'] = $this->language->get('column_auction_id');
        $this->data['ms_account_orders_id'] = $this->language->get('ms_account_orders_id');
        $this->data['ms_account_orders_customer'] = $this->language->get('ms_account_orders_customer');
        $this->data['column_paid_at'] = $this->language->get('column_paid_at');
        $this->breadcrumbs();  
        list($this->template, $this->children) = $this->MsLoader->MsHelper->loadTemplate('multiseller/auctions/auction_orders');
        $this->load->model('module/auctions/seller');
        $seller_id = $this->customer->getId();
        $this->data['auction_orders']  = $this->model_module_auctions_seller->getAuctionsOrders($seller_id);
		$this->response->setOutput($this->render());
	}

}
