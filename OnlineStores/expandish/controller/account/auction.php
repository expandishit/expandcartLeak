<?php 
class ControllerAccountAuction extends Controller { 
	
	/**
	* Disable loading module page if auctions app is not installed
	* or status is disabled.
	*/
 	public function __construct($registry){
        parent::__construct($registry);

        if( !\Extension::isInstalled('auctions') || !$this->config->get('auctions_status') ){
	        $this->response->redirect($this->url->link('error/not_found', '', 'SSL'));
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/auction', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
    }

    public function index(){
		$this->language->load_json('account/auction');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
        
		$this->load->model('module/auctions/auction');
		$this->data['auctions'] = $this->model_module_auctions_auction->getCustomerSubscribedAuctions($this->customer->getId());



		$this->template = $this->checkTemplate('account/auctions/myauctions.expand');
		$this->children = [ 'common/footer' , 'common/header' ];
		$this->response->setOutput($this->render_ecwig());
    }



    public function winning(){
		$this->language->load_json('account/auction');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = $this->_createBreadcrumbs();
        
		$this->load->model('module/auctions/auction');
		$this->data['auctions'] = $this->model_module_auctions_auction->getCustomerWinningAuctions($this->customer->getId());

		$this->template = $this->checkTemplate('account/auctions/winning_auctions.expand');
		$this->children = [ 'common/footer' , 'common/header' ];
		$this->response->setOutput($this->render_ecwig());
    }


    public function buyWinningAuctionProduct(){
    	$auction_id = $this->request->get['auction_id'];

    	$this->load->model('module/auctions/auction');
		$auction = $this->model_module_auctions_auction->getOne($this->request->get['auction_id'], false, false);
		
		$price = $this->_calculateAuctionToBePaidAmount($this->request->get['price'], $auction['min_deposit']);

    	$this->session->data['success'] = $this->language->get('text_success');
    	$this->session->data['auction_product']['price']      = $price;
    	$this->session->data['auction_product']['product_id'] = $auction['product_id'];
    	$this->session->data['auction_product']['auction_id'] = $auction_id;


    	//To prevent adding product twice..
		$this->cart->remove($auction['product_id']);
		$this->cart->add($auction['product_id'], 1);

		$this->redirect($this->url->link('checkout/cart'));
    }

    private function _calculateAuctionToBePaidAmount($price, $min_deposit){
    	$diff = $price - $min_deposit; //var_dump($diff); die();
    	
    	//Positive Amount or zero
    	if($diff >= 0){
    		return $diff;
    	}else{ //if min deposit he paid was greater than the winning price..
    		return 0;
    	}
    }

    private function _createBreadcrumbs($page = null, ...$details){

   		$breadcrumbs = [
   			[
	       		'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
	       		'separator' => false
   			],
   			[
    	
	        	'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
	        	'separator' => $this->language->get('text_separator')
   			],
			[
	       		'text'      => $this->language->get('text_auctions'),
				'href'      => $this->url->link('account/auction'),
	       		'separator' => false
   			]
   		];

   		if($page == "winning"){
   			$breadcrumbs[] = [
	       		'text'      => $details[0],
				'href'      => $this->url->link('module/auctions/view&auction_id='.$details[1]),
	       		'separator' => false
   			];
   		}

   		return $breadcrumbs;
	}

}
