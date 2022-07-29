<?php 
class ControllerSaleSmsharesms extends Controller {
	private $error = array();
	 
	public function index() {

		$this->load->language('sale/smshare_sms');
 
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/customer_group');

		//Seems to be available in a "default" language object. See controller/common/header.php hash: 0ce36
		$this->data['direction'] = $this->language->get('direction');
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_newsletter'] = $this->language->get('text_newsletter');
		$this->data['text_customer_all'] = $this->language->get('text_customer_all');	
		$this->data['text_customer'] = $this->language->get('text_customer');	
		$this->data['text_customer_group'] = $this->language->get('text_customer_group');
		$this->data['text_affiliate_all'] = $this->language->get('text_affiliate_all');	
		$this->data['text_affiliate'] = $this->language->get('text_affiliate');	
		$this->data['text_product'] = $this->language->get('text_product');	

		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_to'] = $this->language->get('entry_to');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_customer'] = $this->language->get('entry_customer');
		$this->data['entry_affiliate'] = $this->language->get('entry_affiliate');
		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_subject'] = $this->language->get('entry_subject');
		$this->data['entry_message'] = $this->language->get('entry_message');

		$this->data['text_ava_var'] = $this->language->get('text_ava_var');
		$this->data['text_firstname'] = $this->language->get('text_firstname');
		$this->data['text_lastname'] = $this->language->get('text_lastname');
		$this->data['text_phonenumber'] = $this->language->get('text_phonenumber');
		$this->data['text_arrow'] = $this->language->get('text_arrow');
		
		$this->data['button_send'] = $this->language->get('button_send');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		
		$this->data['token'] = null;
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/smshare_sms', '', 'SSL'),
      		'separator' => ' :: '
   		);
				
				
		$this->data['action'] = $this->url->link('sale/smshare_sms', '', 'SSL');
    	$this->data['cancel'] = $this->url->link('sale/smshare_sms', '', 'SSL');

		if (isset($this->request->post['store_id'])) {
			$this->data['store_id'] = $this->request->post['store_id'];
		} else {
			$this->data['store_id'] = '';
		}
		
		$this->load->model('setting/store');
		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('sale/customer_group');
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups(0);
		
		$this->template = 'sale/smshare_sms.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	

	/**
	 * 
	 */
	public function send(){
		
		$this->load->language('sale/contact');
		$this->load->language('sale/smshare_sms');
		
		$json = array();
		
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			//if (!$this->user->hasPermission('modify', 'sale/smshare_sms')) {
			//	$json['error']['warning'] = $this->language->get('error_permission');
			//}
				
			if (!$this->request->post['message']) {
				$json['error']['message'] = $this->language->get('error_message');
			}
			
			if (!$json) {
				
				$this->load->model('setting/store');
			
				$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
					
				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
				}
				
				$this->load->model('sale/customer');
				
				$this->load->model('sale/customer_group');
				
				$this->load->model('sale/affiliate');
				
				if (isset($this->request->get['page'])) {
					$page = $this->request->get['page'];
				} else {
					$page = 1;
				}
					
				$email_total = 0;
					
				switch ($this->request->post['to']) {
					case 'newsletter':
							
						$customer_data = array(
						'filter_newsletter' => 1,
						'start'             => ($page - 1) * 10,
						'limit'             => 10
						);
							
						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
			
						$results = $this->model_sale_customer->getCustomers($customer_data);
			
						break;
			
					case 'customer_all':
						$customer_data = array(
						'start'  => ($page - 1) * 10,
						'limit'  => 10
						);
			
						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
							
						$results = $this->model_sale_customer->getCustomers($customer_data);
							
						break;
			
					case 'customer_group':
						$customer_data = array(
						'filter_customer_group_id' => $this->request->post['customer_group_id'],
						'start'                    => ($page - 1) * 10,
						'limit'                    => 10
						);
							
						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);
							
						$results = $this->model_sale_customer->getCustomers($customer_data);
							
						break;
			
					case 'customer':
						if (isset($this->request->post['customer'])) {
			
							$results = array();
			
							foreach ($this->request->post['customer'] as $customer_id) {
								$customer_info = $this->model_sale_customer->getCustomer($customer_id);
								$results[] = $customer_info;
							}
			
						}
						break;
			
					case 'affiliate_all':
						$affiliate_data = array(
						'start'  => ($page - 1) * 10,
						'limit'  => 10
						);
							
						$email_total = $this->model_sale_affiliate->getTotalAffiliates($affiliate_data);
							
						$results = $this->model_sale_affiliate->getAffiliates($affiliate_data);
			
						break;
			
					case 'affiliate':
						if (isset($this->request->post['affiliate'])) {
			
							$results = array();
			
							foreach ($this->request->post['affiliate'] as $affiliate_id) {
								$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);
								$results[] = $affiliate_info;
							}
			
						}
						break;
			
					case 'product':
						if (isset($this->request->post['product'])) {
							$this->load->model('sale/order');    //load the order model (it will be accessible by as model_sale_order.)
							$results = $this->model_sale_order->getTelephonesByProductsOrdered($this->request->post['product'], 0,0);
						}
						break;
				}
				
				if ($results) {
					$start = ($page - 1) * 10;
					$end = $start + 10;
						
					if ($end < $email_total) {
						$json['success'] = sprintf($this->language->get('text_sent'), $start, $email_total);
					} else {
						$json['success'] = $this->language->get('text_success');
					}
				
					if ($end < $email_total) {
						$json['next'] = str_replace('&amp;', '&', $this->url->link('sale/smshare_sms/send', 'page=' . ($page + 1), 'SSL'));
					} else {
						$json['next'] = '';
					}
				
					/*
					 * Send
					 */
					$sms_gateway_reply_text = "";
					//We don't support yet sending grouped SMS list to gateway.
					if('profile_api' == 'profile_api') {    //Send using api
						$sms_gateway_reply 		= $this->oldSendSMSList($results);
						$sms_gateway_reply_text = " Gateway reply: " . $sms_gateway_reply;
					}else{
						$this->sendSMSList($results);
					}
						
					$json['success'] = $json['success'];
				}
				
				
			}    //if !json
			
			$this->response->setOutput(json_encode($json));
		}
	}

	
	private function sendSMSList($results){
		$smsList = array();
		
		$smshare_patterns   = $this->config->get('smshare_config_number_filtering');
		$number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');

		foreach ($results as $result) {
			
			if(! SmsharePhonenumberFilter::isNumberAuthorized($result['telephone'], $smshare_patterns)
			   ||
		   	   ! SmsharePhonenumberFilter::passTheNumberSizeFilter($result['telephone'], $number_size_filter)
			){
				continue;
			}
			
			//Variable substitution in the SMS body
			$message = html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8');
			$smshareCommons = new SmshareCommons();
			$message = $smshareCommons->replace_smshare_variables($message, $result, 0);
			
			//Create the smsbean
			$smshare_smsBean = new stdClass();
			$smshare_smsBean->destination = $result['telephone'];
			$smshare_smsBean->message= $message;

			//push sms to the list
			$smsList[] = $smshare_smsBean;
		}

		$smshareCommons = new SmshareCommons();
		$smshareCommons->sendSMSList($smsList, $this->config);
	}

	/**
	 * If all messages are the same, we just join phone numbers and make one request to the gateway.
	 * Else, we make as much requests to the gateway as there are phonenumber.  
	 */
	private function oldSendSMSList($results){
		
		$smshareCommons = new SmshareCommons();
		
		$smshare_patterns   = $this->config->get('smshare_config_number_filtering');
		$number_size_filter = $this->config->get('smshare_cfg_num_filt_by_size');
		
		/*
		 * 
		 */
		$smshare_smsBeans = array();
		foreach ($results as $result) {
			
			if(! SmsharePhonenumberFilter::isNumberAuthorized($result['telephone'], $smshare_patterns)
			   ||
			   ! SmsharePhonenumberFilter::passTheNumberSizeFilter($result['telephone'], $number_size_filter)
			  ){
				continue;
			}
			
			//Variable substitution in the SMS body
			$message = html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8');
			$message = $smshareCommons->replace_smshare_variables($message, $result, 0);
			
			//Create the smsbean
			$smshare_smsBean = new stdClass();
			$smshare_smsBean->destination = $result['telephone'];
			$smshare_smsBean->message     = $message;
			
			$smshare_smsBeans[] = $smshare_smsBean;
		}
		
		/*
		 * 
		 */
		$messages_are_equals = true;
		
		if(count($smshare_smsBeans) == 0) return "✘ There is no SMS to send";
		
		$previous_message = $smshare_smsBeans[0]->message;
		
		foreach ($smshare_smsBeans as $smshare_smsBean){
			$current_message = $smshare_smsBean->message;
			if($current_message != $previous_message){
				$messages_are_equals = false;
				break;
			}else{
				$previous_message = $current_message;
			}
		}
		
		if($messages_are_equals){
			
			/*
			 * Get phones
			 */
			$phones = array();
			foreach ($results as $result) {
				$phones[] = $result['telephone'];
			}
			
			/*
			 * Remove duplicate
			 */
			$phones = array_unique($phones);
			
			if ($phones) {
				
				/*
				 * Send sms
				 */                   
	
				//PHONE NUMBER REWRITING
				$sms_to_arr = array();
				foreach ($phones as $phone) {
					array_push($sms_to_arr, SmsharePhonenumberFilter::rewritePhoneNumber($phone, $this->config));
				}
				
				$destinations = join(",", $sms_to_arr);
				return $smshareCommons->sendSMS($destinations, 
	                                            $previous_message,
	                                            $this->config);
			}
		}else{
			$gateway_replies = array();
			/*
			 * Send N requests.
			 */
			foreach ($smshare_smsBeans as $smshare_smsBean) {
				
				//PHONE NUMBER REWRITING
				$phone_rewritten = SmsharePhonenumberFilter::rewritePhoneNumber($smshare_smsBean->destination, $this->config);
				
				$gateway_reply = $smshareCommons->sendSMS($phone_rewritten,
						                                  $smshare_smsBean->message,
						                                  $this->config);
				$gateway_replies[] = $gateway_reply;
			}
			return join(",", $gateway_replies);
		}
	}

}
?>
