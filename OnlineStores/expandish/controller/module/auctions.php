<?php 
class ControllerModuleAuctions extends Controller { 

	/**
	* Disable loading module page if auctions app is not installed
	* or status is disabled.
	*/
 	public function __construct($registry){
        parent::__construct($registry);

        if( !\Extension::isInstalled('auctions') || !$this->config->get('auctions_status') ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}

    }

	public function index(){

		$this->language->load_json('module/auctions');
		$this->load->model('module/auctions/auction');

		//Page Title
	  	$this->document->setTitle($this->language->get('title_auctions'));
	
		//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();

		//Get Auctions list
		$auctions = $this->model_module_auctions_auction->getAllCurrent();
		
		$this->data['auctions'] = $auctions;
		$this->data['auctions_timezone'] = $this->config->get('auctions_timezone') ?: $this->config->get('config_timezone');        
		
		$this->template = $this->checkTemplate('module/auctions/list.expand');
		$this->children = [ 'common/footer' , 'common/header' ];
		$this->response->setOutput($this->render_ecwig());
	}

	private function _createBreadcrumbs($page = null, ...$details){

   		$breadcrumbs = [
   			[
	       		'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
	       		'separator' => false
   			],
			[
	       		'text'      => $this->language->get('text_auctions'),
				'href'      => $this->url->link('module/auctions'),
	       		'separator' => false
   			]
   		];

   		if($page == "view"){
   			$breadcrumbs[] = [
	       		'text'      => $details[0],
				'href'      => $this->url->link('module/auctions/view&auction_id='.$details[1]),
	       		'separator' => false
   			];
   		}

   		return $breadcrumbs;
	}

	public function view(){
		//Check if auciton running, only open the page if auction is running
		$this->load->model('module/auctions/auction');
		$is_running = $this->model_module_auctions_auction->isRunning($this->request->get['auction_id']);
		if( !$is_running ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));			
		}

		//If Auction is still running, get page data then render it
		$this->language->load_json('module/auctions');

		//Page Title
	    $this->document->setTitle($this->language->get('title_auctions'));

		//Get Auction data
		$auction = $this->model_module_auctions_auction->getOne($this->request->get['auction_id']);
		$this->data['auction'] = $auction;
        $this->data['default_currency'] = $this->config->get('config_currency');
	    
	    $app_config_timezone = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');        
        $this->data['auctions_timezone'] = $app_config_timezone;		
		// $this->data['now_datetime'] = new DateTime('NOW', new DateTimeZone($app_config_timezone));

        $this->data['place_bid_action'] = $this->url->link('module/auctions/placebid&auction_id=' . $this->request->get['auction_id']);
        $this->data['sse_updates_action'] = $this->url->link('module/auctions/getBidsUpdates&auction_id=' . $this->request->get['auction_id']);

		//Breadcrumbs
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs('view', $auction['product_name'], $this->request->get['auction_id']);

		$this->template = $this->checkTemplate('module/auctions/view.expand');
		
		$this->children = [ 'common/footer' , 'common/header' ];
		$this->response->setOutput($this->render_ecwig());
	}

	private function _customerNotEligibleForBidding($auction_id){
		//if not logged, redirect
		if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('module/auctions/view&auction_id=' . $this->request->get['auction_id']);
	  		$json_response['redirect']       = $this->url->link('account/login', '', 'SSL'); 
	  		return $json_response;
    	} 


    	//if auction has min-deposit value > zero and the customer does not paid the min-deposit for this auction already...
		$this->load->model('module/auctions/auction');		     	
		$is_subscribed = $this->model_module_auctions_auction->isCustomerSubscribedInAuction($auction_id, $this->customer->getId());			
		if( !$is_subscribed ){
	  		$json_response['is_subscribed']  = '0'; 
	  		return $json_response;
		}

		return FALSE;
	}

	public function placebid(){
		$auction_id  = $this->request->get['auction_id'];
		
		if( $json_response = $this->_customerNotEligibleForBidding($auction_id) ){
        	$this->response->setOutput(json_encode($json_response));
	  		return;
		}

		$amount = $this->request->post['bid_amount'];
		$bidder_id   = $this->customer->getId();

		if( $this->_validateBid($amount, $auction_id) ){
			$this->load->model('module/auctions/auction');		 
			//add new bid record in auction_bid table & update current bid column in auction table..
			$this->model_module_auctions_auction->placeNewBid($auction_id, $amount, $bidder_id);			
			$json_response['success'] = '1'; 
		}
		else{
			$json_response['success'] = '0'; 
		}

        $this->response->setOutput(json_encode($json_response));
	}


	public function getBidsUpdates(){
		// make session read-only
        session_start();
        session_write_close();

 		// disable default disconnect checks
        ignore_user_abort(true);

        // set headers for stream
		header("Cache-Control: no-cache");
		header("Content-Type: text/event-stream");
		header("Access-Control-Allow-Origin: *");

				// // Is this a new stream or an existing one?
		  //       $lastEventId = floatval(isset($_SERVER["HTTP_LAST_EVENT_ID"]) ?: 0);
		       
		  //       if ($lastEventId == 0) {
		  //           $lastEventId = floatval(isset($_GET["lastEventId"]) ?: 0);
		  //       }

        echo ":" . str_repeat(" ", 2048) . "\n"; // 2 kB padding for IE
        echo "retry: 2000\n";

        // start stream
		$auction_id = $this->request->get['auction_id'];
		$this->load->model('module/auctions/auction');		 

		while (true) {
		  // Every second, send a "newBid" event.
		  echo "event: new_bid\n";

		  //get last bid for this auction
		  $bid = $this->model_module_auctions_auction->getBidsUpdates($auction_id);
		  $bid['next_minimum_allowed_bid'] = number_format($bid['next_minimum_allowed_bid'] ,  $this->currency->getDecimalPlace()); 
		  //set bid data...
		  // echo 'data: {"bid": "' . $bid . '"}';
		  echo 'data:' . json_encode($bid) ;
		  echo "\n\n";
		  
		  
		  ob_end_flush();
		  flush();

		  // Break the loop if the client aborted the connection (closed the page)
		  if ( connection_aborted() ) exit();
		  sleep(1);
		  // usleep(100000); //microseconds
		}
	}



	public function payDeposit(){
		
		$this->language->load_json('module/auctions');

		$auction_id  = $this->request->post['auction_id'];		
		$bidder_id   = $this->customer->getId();
		
		$this->load->model('module/auctions/auction');
		$auction = $this->model_module_auctions_auction->getOne($auction_id);
	
		if($this->customer->getBalance() < $auction['min_deposit']){
			$json_response['success'] = '0';
			$json_response['message'] =  $this->language->get('error_balance_not_sufficient');
		}
		else{
			$this->model_module_auctions_auction->payMinimumDeposit($auction_id, $bidder_id, $auction['min_deposit']);

			$json_response['success'] = '1';
			$json_response['message'] =  $this->language->get('text_min_deposit_paid_successfully');
		}
		
		$this->response->setOutput(json_encode($json_response));
	}



	/**
	* validate bid amount (not-empty, >= next_minimum_allowed_bid)
	*
	*/
	private function _validateBid($amount, $auction_id){
		$this->load->model('module/auctions/auction');		 
		$next_minimum_allowed_bid = $this->model_module_auctions_auction->getNextMinAllowedBid($auction_id);
		// var_dump(empty($amount) || $amount < $next_minimum_allowed_bid);die();

		return ( empty($amount) || $amount < $next_minimum_allowed_bid) ? FALSE : TRUE;
	}
}
