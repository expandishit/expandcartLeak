<?php


class ModelModuleWhatsappCloud extends Model {
		
	
	//==========================# events #============================//
	
	/**
	 * this event is fired when admin change the order status 
	 * 
	 * @parm array $order_info
	 * @parm array $data
	 *
	 * @return void 
	 */
    public function orderStatusUpdateNotification(array $order_info,array $data) : void {
		//prepare data
		//The order total
        $this->load->model('sale/order');
		$order_id = $order_info['order_id'];
        $order_total = WhatappCloudHelper::getOrderTotal($this->model_sale_order->getOrderTotals($order_id));

        if(!empty($order_info['order_status_id'])){
			
			$this->load->model('localisation/order_status');
			$lang_id = (int)$order_info['language_id'];
			$status  = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id'],$lang_id);
			$order_info['status_name'] = $status["name"]??"";
        }
		
		$comment = strip_tags(html_entity_decode($data['comment']??"", ENT_QUOTES, 'UTF-8'));

		$order_info['comment']		= $comment;
		$order_info['total']		= $order_total;
		$order_info['phonenumber']	= $order_info['telephone'];
		$order_info['order_date']	= $order_info['date_added'];
		
		$this->_customerOrderStatusUpdateNotification($order_info, $data);
        $this->_adminOrderStatusUpdateNotification($order_info, $data);
    }

	/**
	 * check the admin notification for order status change 
	 * 
	 * @parm array $order_info
	 * @parm array $data
	 *
	 * @return  
	 */
    private function _customerOrderStatusUpdateNotification(array $order_info,array $data){

        if(!$data['notify_by_whatsapp'??false]) {
          WhatappCloudHelper::clientLog(" Do not notify customer on order update because notify is false. Not checked or not submitted. Aborting!");
            return;
        }
		
		$this->load->model('module/whatsapp_cloud/template_message');

        $observer_template = $this->model_module_whatsapp_cloud_template_message
							->getTemplateFromObserver($data['order_status_id'], 'whatsapp_cloud_customer_order_observers');
		
		if(empty($observer_template)){
				WhatappCloudHelper::clientLog(' no template exists at observer with this status :'. $data['order_status_id']);//for test 
			return ;
		}

        $template_to    		= $order_info['telephone'];
		$lang_code 				= $this->language->get('code');		
		$template_variables		= $order_info;
		$receiver_country_code  = $order_info['shipping_iso_code_2'] ?? "";
		
		
		
		return $this->model_module_whatsapp_cloud_template_message
										->sendObserverTemplate([
											'template_to'			=> $template_to,
											'lang_code'			 	=> $lang_code,
											'template_variables'	=> $template_variables, 
											'receiver_country_code' => $receiver_country_code,
											'observer_template' 	=> $observer_template,
											]);
    }

	/**
	 * check the customer notification for order status change 
	 * 
	 * @parm array $order_info
	 * @parm array $data
	 *
	 * @return  
	 */
    private function _adminOrderStatusUpdateNotification(array $order_info,array $data){

		$this->load->model('module/whatsapp_cloud/template_message');
		
        $observer_template = $this->model_module_whatsapp_cloud_template_message
								->getTemplateFromObserver($data['order_status_id'], 'whatsapp_cloud_admin_order_observers');
		
		if(empty($observer_template)){
           WhatappCloudHelper::clientLog("[whatsapp] Do not notify store owner on order status update because no observer is found. Aborting!");
           return;
        }

		$template_to  		= $this->config->get('config_telephone');
		$template_to 		= explode(",", str_replace(' ', '', $template_to));
		$lang_code 	  		= $this->language->get('code');
		$template_variables	= $order_info;

		return $this->model_module_whatsapp_cloud_template_message->sendObserverTemplate([
											'template_to'			=> $template_to,
											'lang_code'			 	=> $lang_code,
											'template_variables'	=> $template_variables, 
											'observer_template' 	=> $observer_template,
											]);
    }

	//firing at system\library\MsWhatsappCloud.php
    public function sellerStatusNotification($message)
    {
		$this->load->model('module/whatsapp_cloud/template_message');
		
		$msg_type = $message['type'];
		 
        $observer_template = $this->model_module_whatsapp_cloud_template_message
								->getTemplateFromObserver($msg_type, 'whatsapp_cloud_seller_order_observers');
		
		if(empty($observer_template)){
           WhatappCloudHelper::clientLog("[whatsapp] Do not notify seller on order status update because no observer is found. Aborting!");
           return;
        }
		
		$template_to 		= $message['data']['seller_mobile'];
		$lang_code 	  		= $this->language->get('code');
		$template_variables	= $message['data'];

		return $this->model_module_whatsapp_cloud_template_message->sendObserverTemplate([
											'template_to'			=> $template_to,
											'lang_code'			 	=> $lang_code,
											'template_variables'	=> $template_variables, 
											'observer_template' 	=> $observer_template,
											]);
    }


	/**
     * validate received webhook signature [internal signature <> ectools]
     * 
     * @parm string $payload
     * @parm string $leader_signature
     * 
     * @return bool 
     * 
     */
	public function validateSignature($payload, $header_signature = ''): bool
    {
       
		if(!defined('ECTOOLS_ENC_KEY')){
			define ('ECTOOLS_ENC_KEY', '8ah3ww72bk4b9agddm2art1gy5h75zhaz4im9gd3');
		}
		
		// Signature matching
		$expected_signature = hash_hmac('sha1', $payload , ECTOOLS_ENC_KEY );

		$signature = '';
		if(
			strlen($header_signature) == 45 &&
			substr($header_signature, 0, 5) == 'sha1='
		  ) {
		  $signature = substr($header_signature, 5);
		}
		
		if (hash_equals($signature, $expected_signature)) {
		 return true;
		}

		return false;
    }

	/**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return \Extension::isInstalled('whatsapp_cloud');
    }
	//==========================# installation #============================//
	/**
	 * Create the needed schema for the APP | fired from Marketplace 
	 * 
	 * @return  void 
	 */
	public function install() : void {
		
		$this->load->model('module/whatsapp_cloud/chat');
		$this->model_module_whatsapp_cloud_chat->createChatsTable();
		
		$this->load->model('module/whatsapp_cloud/media');
		$this->model_module_whatsapp_cloud_media->createMediasTable();
		
		$this->load->model('module/whatsapp_cloud/message');
		$this->model_module_whatsapp_cloud_message->createMessagesTable();
		
		$this->load->model('module/whatsapp_cloud/template_message');
		$this->model_module_whatsapp_cloud_template_message->createMessageTemplatesTable();
	}
	
	/**
	 * Dump the App schema | should be in order to avoid foreign key restrictions
	 * 
	 * @return  void 
	 */	
	public function uninstall() :void {
		
		$this->load->model('module/whatsapp_cloud/template_message');
		$this->model_module_whatsapp_cloud_template_message->dropMessageTemplatesTable();
		
		$this->load->model('module/whatsapp_cloud/message');
		$this->model_module_whatsapp_cloud_message->dropMessagesTable();
		
		$this->load->model('module/whatsapp_cloud/media');
		$this->model_module_whatsapp_cloud_media->dropMediasTable();
		
		$this->load->model('module/whatsapp_cloud/chat');
		$this->model_module_whatsapp_cloud_chat->dropChatsTable();
		
		$this->load->model('module/whatsapp_cloud/waba');
		$this->model_module_whatsapp_cloud_waba->integrationRequestUninstall();
	}  

}
?>
