<?php 
class ControllerAccountCustomInvoice extends Controller { 
	private $error = array();

	private $voucher_data  = array();
	private $voucher_total = 0;
	private $custmer_data  = array();
	
	public function __construct($registry) {
		parent::__construct($registry);

		if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

    	//Check if MS & MS Messaging is installed
    	$this->load->model('multiseller/status');
    	$multiseller = $this->model_multiseller_status->is_installed();

        if($multiseller) {
        	$multisellerCtmInv = $this->model_multiseller_status->is_custom_invice_installed();
        	if(!$multisellerCtmInv)
            	$this->redirect($this->url->link('account/account', '', 'SSL'));
        }else{
        	$this->redirect($this->url->link('account/account', '', 'SSL'));
        }
	}

	public function index() {
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-invoice');
        
		$this->language->load_json('account/voucher', true);
		
		if($this->config->get('config_voucher_max') == 'disabled') {
            $this->redirect($this->url->link('common/home'));
            return;
        }

		$this->document->setTitle($this->language->get('heading_title_custom_invoice'));
		
		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = array();
		}		

    	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
    		$requestData = $this->request->post;
    		$items       = $requestData['items'];

    		$isData = false;

    		if (!empty($items)) {

    			//Get Selected Customer Data
    			$this->load->model('account/customer');
				$custmer_data = $this->model_account_customer->getCustomer($requestData['customer']);
				$this->custmer_data = $custmer_data;
				////////////////////////////
				
				foreach ($items as $voucher) 
				{
					if($voucher['name'] != '' && $voucher['amount'] != '' && $voucher['quantity'] != '')
					{
						$isData = true;
						$voucher_data[] = array(
							'description'      => $voucher['name'],
							'code'             => substr(md5(mt_rand()), 0, 10),
							'to_name'          => $custmer_data['firstname']. ' ' .$custmer_data['lastname'],
							'to_email'         => $custmer_data['email'],
							'from_name'        => $requestData['from_name'],
							'from_email'       => $requestData['from_email'],
							'voucher_theme_id' => 0,
							'message'          => '',						
							'amount'           => $voucher['amount'],
							'quantity'         => $voucher['quantity']
						);

						$total += $voucher['amount'];
					}
				}	
			}

			//Create Custome Invoice Action
			if($isData){
				$this->voucher_data  = $voucher_data;
				$this->voucher_total = $total;

				if($this->create_custome_invoice_order($this->request->post))  	  	
	  				$this->redirect($this->url->link('account/custom_invoice/success'));
			}
			////////////////////////////////
    	} 		

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	); 
		
      	// $this->data['breadcrumbs'][] = array(       	
        // 	'text'      => $this->language->get('text_account'),
		// 	'href'      => $this->url->link('account/account', '', 'SSL'),
        // 	'separator' => $this->language->get('text_separator')
      	// );
		
      	$this->data['breadcrumbs'][] = array(       	
        	'text'      => $this->language->get('text_custom_invoice'),
			'href'      => $this->url->link('account/custom_invoice', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['entry_amount'] = sprintf($this->language->get('entry_amount'), $this->currency->format($this->config->get('config_voucher_min')), $this->currency->format($this->config->get('config_voucher_max')));

		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
					
		if (isset($this->request->post['from_name'])) {
			$this->data['from_name'] = $this->request->post['from_name'];
		} elseif ($this->customer->isLogged()) {
			$this->data['from_name'] = $this->customer->getFirstName() . ' '  . $this->customer->getLastName();
		} else {
			$this->data['from_name'] = '';
		}
		
		if (isset($this->request->post['from_email'])) {
			$this->data['from_email'] = $this->request->post['from_email'];
		} elseif ($this->customer->isLogged()) {
			$this->data['from_email'] = $this->customer->getEmail();		
		} else {
			$this->data['from_email'] = '';
		}
		
		//Get customers messaged this seller
		$this->load->model('account/messagingseller');
		$customersIds = array($this->customer->getId());
		$this->data['customers'] = $this->model_account_messagingseller->getCustomersbyIds($customersIds);
		//////////////////////////////////////

 		$this->load->model('checkout/voucher_theme');
			
		$this->data['voucher_themes'] = $this->model_checkout_voucher_theme->getVoucherThemes();

    	if (isset($this->request->post['voucher_theme_id'])) {
      		$this->data['voucher_theme_id'] = $this->request->post['voucher_theme_id'];
		} else {
      		$this->data['voucher_theme_id'] = '';
    	}	
		
		if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/custom_invoice.expand')) {
            $this->template = $this->config->get('config_template') . '/template/account/custom_invoice.expand';
        }
        else {
            $this->template = 'default/template/account/custom_invoice.expand';
        }
        
		// $this->children = array(
            // 	'common/footer',
            // 	'common/header'
            // );
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->template = 'default/template/account/custom_invoice.expand';
        }
        
		$this->response->setOutput($this->render_ecwig());
  	}
	

	/**
	 *	Create Order
	 */
	private function create_custome_invoice_order($params){
		$data = array();
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');
		
		if ($data['store_id']) {
			$data['store_url'] = $this->config->get('config_url');		
		} else {
			$data['store_url'] = HTTP_SERVER;	
		}
		
		$data['affiliate_id'] = 0;
		$data['commission'] = 0;
		$data['language_id'] = $this->config->get('config_language_id');
		$data['currency_id'] = $this->currency->getId();
		$data['currency_code'] = $this->currency->getCode();
		$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		$data['forwarded_ip'] = '';
		$data['user_agent'] = '';
		$data['accept_language'] = '';
		$data['total'] = 0;
		
		$this->load->model('quickcheckout/order');

		if(preg_match("/1.5.1/i", VERSION)){
			$order_id = $this->model_quickcheckout_order->addOrder151($data);
		}else{
			$order_id = $this->model_quickcheckout_order->addOrder($data);
		}	

		if($order_id)
			return $this->update_custome_invoice_order($order_id, $params);
		
		return false;
	}

	public function update_custome_invoice_order($order_id, $params){
		$data = array();
			
		$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');
		
		if ($data['store_id']) {
			$data['store_url'] = $this->config->get('config_url');		
		} else {
			$data['store_url'] = HTTP_SERVER;	
		}

		$data['customer_id'] = $this->custmer_data['customer_id'];
		$data['customer_group_id'] = $this->custmer_data['customer_group_id'];
		$data['firstname'] = $this->custmer_data['firstname'];
		$data['lastname'] = $this->custmer_data['lastname'];
		$data['email'] = $this->custmer_data['email'];
		$data['telephone'] = $this->custmer_data['telephone'];
		$data['fax'] = $this->custmer_data['fax'];

		// Gift Voucher
		$voucher_data = array();

		$data['vouchers'] = $this->voucher_data;
		$data['total']    = $this->voucher_total;
		
		$data['language_id'] = $this->config->get('config_language_id');
		$data['currency_id'] = $this->currency->getId();
		$data['currency_code'] = $this->currency->getCode();
		$data['currency_value'] = $this->currency->getValue($this->currency->getCode());

		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		if(preg_match("/1.5.2/i", VERSION)){
			$this->model_quickcheckout_order->updateOrder152($order_id, $data);
		}elseif(preg_match("/1.5.1/i", VERSION)){
			$this->model_quickcheckout_order->updateOrder151($order_id, $data);
		}else{
			$this->model_quickcheckout_order->updateOrder($order_id, $data);
		}

		$order_status_id = $this->model_multiseller_status->is_custom_invice_installed(true);
		$this->model_quickcheckout_order->updateOrderStatus($order_id, $order_status_id);
		

		//Notify Customer
        $this->language->load_json('account/voucher');
		$seller_info = $this->MsLoader->MsSeller->getSellerBasic($this->customer->getId());
        $_vars = [
        			 'orders_url'       => $this->url->link('account/order', '', 'SSL'),
                     'order_id'         => $order_id,
                     /*'seller_email'   => $seller_info['email'], 
                     'seller_firstname' => $seller_info['firstname'],
                     'seller_lastname'  => $seller_info['lastname'],*/
                     'seller_nickname'  => $seller_info['nickname']
                 ];

        $mails[] = array(
                        'type' => MsMail::SMT_SELLER_CUSTOM_INVOICE,
                        'data' => array('class'      => 'ControllerAccountCustomInvoice', 
                                        'function'   => 'update_custome_invoice_order',
                                        'vars'       => $_vars,
                                        'recipients' => $this->custmer_data['email'],
                                        'addressee'  => $this->custmer_data['firstname']
                                    )
                    );

        $this->MsLoader->MsMail->sendMails($mails);
        //////

		return true;
	}

  	public function success() {
		$this->language->load_json('account/voucher');

		$this->document->setTitle($this->language->get('heading_title_custom_invoice')); 

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title_custom_invoice'),
			'href'      => $this->url->link('account/custom_invoice'),
        	'separator' => $this->language->get('text_separator')
      	);


    	$this->data['continue'] = $this->url->link('account/account');

    	$this->data['heading_title'] = $this->language->get('heading_title_custom_invoice');
    	$this->data['text_message']  = $this->language->get('custom_invoice_success');

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/common/success.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/common/success.expand';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
				
 		$this->response->setOutput($this->render_ecwig());
	}
}
?>
