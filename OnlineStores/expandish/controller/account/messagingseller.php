<?php
class ControllerAccountMessagingseller extends Controller
{
	private $error = array();

	public function __construct($registry) {
		parent::__construct($registry);

		if (!$this->customer->isLogged()) {
			$customer_id = $this->request->get['customer_id'] ? '&customer_id='.$this->request->get['customer_id'] : '';
			$seller_id = $this->request->get['seller_id'] ? '&seller_id='.$this->request->get['seller_id'] : '';
			$product_id = $this->request->get['product_id'] ? '&product_id='.$this->request->get['product_id'] : '';
      		$redirect = urlencode($this->url->link('account/messagingseller', $customer_id.$seller_id.$product_id, 'ssl'));

	  		$this->redirect($this->url->link('account/login', 'redirect='.$redirect, 'SSL'));
    	}

    	//Check if MS & MS Messaging is installed
    	$this->load->model('multiseller/status');
    	$multiseller          = $this->model_multiseller_status->is_installed();

        if($multiseller) {
        	$multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
        	if(!$multisellerMessaging)
            	$this->redirect($this->url->link('account/account', '', 'SSL'));
        }else{
        	$this->redirect($this->url->link('account/account', '', 'SSL'));
        }
	}

	
	public function index()
	{
        $this->data['taps'] = $this->getChild('account/taps/renderTaps', 'account-messagingseller');

		$this->migrateOldConversations();

		$isSeller = $this->data['isSeller'] = $this->MsLoader->MsSeller->isSeller();
    	// check if customer is also seller and admin allow seller to contact seller
		$isSellerContactSellerAllowed = $this->config->get('msconf_allow_seller_to_contact_seller');

		if(!$isSeller) {
			$member_key = $this->data['member_key'] = 'customer';
			$isSeller = false;
		} else {
			if($isSellerContactSellerAllowed) {
				$member_key = $this->data['member_key'] = 'customer';
				$isSeller = false;
			} else {
				$member_key = $this->data['member_key'] = 'seller';
			}
		}
		$this->language->load_json('account/messaging_seller');

		$this->load->model('account/messagingseller');

    	// $this->document->setTitle($this->language->get('heading_title_'.$member_key));
    	$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$this->data['breadcrumbs'][] = array(
        	// 'text'      => $this->language->get('heading_title_'.$member_key),
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/order', $url, 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

		$current_user = $this->customer;
		$current_user_type = $this->MsLoader->MsSeller->isSeller() ? 's' : 'c';

		$chatted_user_id = null;
		$chatted_user_type = null;
		if ($this->request->get['seller_id']) {
			$chatted_user_id = $this->data['chatted_user_id'] = $this->request->get['seller_id'];;
			$chatted_user_type = 's';
		} else if ($this->request->get['customer_id']) {
			$chatted_user_id = $this->data['chatted_user_id'] =  $this->request->get['customer_id'];
			$chatted_user_type = 'c';
		}
		
		$this->data['current_user_id'] = $current_user->getId();
		$this->data['current_user_type'] = $current_user_type;
		$this->data['chatted_user_type'] = $chatted_user_type;
		$this->data['reload']    = $this->url->link('account/messagingseller');

		$conversation = $this->model_account_messagingseller->getMessages(
			$current_user->getId(), 
			$chatted_user_id
		);

		$this->data['subject']  = $conversation['subject'] ?? '';
		$this->data['messages'] = $conversation['messages'];

		// Get Product Info if exists
		$product_id = $this->data['product_id'] = $this->request->get['product_id'] ?? $conversation['product_id'] ?? 0;

		if($product_id) {
			$this->load->model('catalog/product');
			$this->data['product'] = $this->model_catalog_product->getProduct($product_id);
		} else {
			$this->data['product'] = null;
		}

		$conversations = $this->model_account_messagingseller->getConversations($current_user->getId());
		$chatted_users_ids = array_unique(array_merge(
			array_column($conversations, 'user1_id'),
			array_column($conversations, 'user2_id')
		));

		if (($key = array_search($current_user->getId(), $chatted_users_ids)) !== false) {
			unset($chatted_users_ids[$key]);
			$chatted_users_ids = array_values($chatted_users_ids);
		}

		$this->data['members'] = array();

		foreach ($conversations as $conversation) {

			$avatar = $conversation['avatar'] ?? $conversation['_avatar'] ?? ''; 
			if ($avatar && file_exists(DIR_IMAGE . $avatar)) {
				$avatar = $this->MsLoader->MsFile->resizeImage($avatar, $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
			} else {
				$avatar = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
			}

			$seller_id = $member_id = $conversation['seller_id'] ?? $conversation['_seller_id'] ?? '';
			if ($seller_id) {
				$member_id = $seller_id;
				$href = $this->url->link('account/messagingseller', 'seller_id=' . $seller_id, 'SSL');
			} else {
				$customer_id = $member_id = $conversation['customer_id'] ?? $conversation['_customer_id'];
				$href = $this->url->link('account/messagingseller', 'customer_id=' . $customer_id, 'SSL');
			}

			$this->data['members'][] = [
				'member_id'=> $member_id,
				'email'    => $conversation['email'] ?? $conversation['_email'],
				'telephone'=> $conversation['telephone'] ?? $conversation['_telephone'],
				'fax'      => $conversation['fax'] ?? $conversation['_fax'],
				'name' => $conversation['name'] ?? $conversation['_name'],
				'nickname' => $conversation['nickname'] ?? $conversation['_nickname'],
				'company'  => $conversation['company'] ?? $conversation['_company'],
				'website'  => $conversation['website'] ?? $conversation['_website'],
				'avatar'   => $avatar,
				'href'     => $href,
				'total_unread'  => $conversation['total_unread'],
				'member_key' => $seller_id ? 'seller' : 'customer'
			];

			if($chatted_user_id && $chatted_user_id == $member_id) {
				$this->data['current_member'] = $conversation['nickname'] ?? $conversation['_nickname'];
				$this->data['current_name'] = $conversation['name'] ?? $conversation['_name'];
				$this->data['current_email'] = $conversation['email'] ?? $conversation['_email'];
				$this->data['current_phone'] = $conversation['telephone'] ?? $conversation['_telephone'];
			}
		} 
		

		$result = $this->model_account_messagingseller->getCustomerbyId($chatted_user_id);

		if ($chatted_user_id && array_search($chatted_user_id, array_column($this->data['members'], 'member_id')) === false) {

			$avatar = $result['avatar'] ?? $result['_avatar'] ?? ''; 
			if ($avatar) {
				$avatar = $this->MsLoader->MsFile->resizeImage($avatar, $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
			} else {
				$avatar = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_dashboard_image_width'), $this->config->get('msconf_seller_avatar_dashboard_image_height'));
			}

			$member_id = $result['seller_id'] ?? $result['_seller_id'] ?? '';
			if ($member_id) {
				$href = $this->url->link('account/messagingseller', 'seller_id=' . $member_id, 'SSL');
			} else {
				$member_id = $result['customer_id'] ?? $result['_customer_id'];
				$href = $this->url->link('account/messagingseller', 'customer_id=' . $member_id, 'SSL');
			}

			$this->data['members'][] = [
				'email'    => $result['email'],
				'telephone'=> $result['telephone'],
				'fax'      => $result['fax'] ?? $result['_fax'],
				'name' => $result['name'] ?? $result['_name'],
				'nickname' => $result['nickname'],
				'company'  => $result['company'],
				'website'  => $result['website'],
				'avatar'   => $avatar,
				'href'     => $href,
				'total_unread'  => null
			];

			$this->data['current_member'] = $result['nickname'];
			$this->data['current_name'] = $result['name'];
			$this->data['current_email'] = $result['email'];
			$this->data['current_phone'] = $result['telephone'];
		}
  
		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/messaging_sellers.expand')) {
            $this->template = $this->config->get('config_template') . '/template/account/messaging_sellers.expand';
        } else {
             $this->template = 'default/template/account/messaging_sellers.expand';
        }

		$this->children = array(
			'common/footer',
			'common/header'
		);
        
        if (defined('LOGIN_MODE') && LOGIN_MODE === 'identity' && $this->identity->isStoreOnWhiteList()) {
            $this->template = 'default/template/account/messaging_sellers.expand';
        }
        
		$this->response->setOutput($this->render_ecwig());
	}

	public function addmessage(){
		$this->load->model('module/mobile/notifications');
		$status = 0;

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if(
				($this->request->post['user1_id'] && $this->request->post['user2_id'] && $this->request->post['user1_type']) 
		          && $this->request->post['msg'] 
		          && $this->customer->isLogged() 
		      )
			{
				$this->load->model('account/messagingseller');

				$addMsg = $this->model_account_messagingseller->addMessage($this->request->post);

				if (isset($this->request->post['user2_type']) && $this->request->post['user2_type'] == 's') {
				
					$this->notifySeller([
						'customer_name' => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
						'customer_email' => $this->customer->getEmail(),
						'message' => $this->request->post['msg'],
						'seller_email' => $this->MsLoader->MsSeller->getSellerEmail($this->request->post['user2_id']),
						'seller_name' => $this->MsLoader->MsSeller->getSellerName($this->request->post['user2_id'])
					]);
				}

				/**
				 * Push Notification to the customer if the sender is a seller
				 * and the customer has a firebase_token(mobile app installed)
				 */
				if (isset($this->request->post['user2_type']) && $this->request->post['user2_type'] == 'c') {
					$this->load->model('module/mobile/notifications');
					$this->load->model('account/customer');
					
					$customer = $this->model_account_customer->getCustomer($this->request->post['user2_id']);
					if($customer['firebase_token'] != null){
						$userTokens = [$customer['firebase_token']];
						$data['notification'] = [
							'title' => $this->MsLoader->MsSeller->getSellerName($this->request->post['user1_id'])
						];
						$data['data'] = [
							'type'=> 'message',
							'sellerName' => $this->MsLoader->MsSeller->getSellerName($this->request->post['user1_id']),
							'sellerId' => $this->request->post['user1_id'],
							'customerId' => $customer['customer_id'],
							'customerName' => $customer['firstname'] ." ". $customer['lastname'],
                            'product_id' => $this->request->post['product_id']
						];
						$result = $this->model_module_mobile_notifications->pushNotification($userTokens,$data);
					}
				}
				
				$status = 1;

			}else{
				$status = 0;
				$addMsg = 0;
			}
    	} 

		$this->response->setOutput(json_encode(['status' => $status, 'msg_id' => $addMsg]));
	}

	public function getNewMessages() {
		$status = 0;

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if($this->request->post['user1_id'] && $this->request->post['user2_id'])
			{
				$latest_id = $this->request->post['latest_id'];

				$this->load->model('account/messagingseller');

				$user1_id = $this->request->post['user1_id'];
				$user2_id = $this->request->post['user2_id'];

				$messages = $this->model_account_messagingseller->getMessages($user1_id, $user2_id, $latest_id);
			}
    	} 

    	$this->response->setOutput(json_encode(['messages' => $messages]));
	}

	public function removeConvr() {
		$status = 0;

		if($this->request->post['user1_id'] && $this->request->post['user2_id'] && $this->customer->isLogged() && $this->MsLoader->MsSeller->isSeller()) {
			$this->load->model('account/messagingseller');

			$this->model_account_messagingseller->removeConvr(
				$this->request->post['user1_id'], 
				$this->request->post['user2_id'] 
			);

			$status = 1;
		}
		$this->response->setOutput(json_encode(['status' => $status]));
	}

	/**
	 * Notify seller
	 */
	private function notifySeller($data)
	{

		$this->language->load_json('multiseller/multiseller');

		$mail_type = MsMail::SMT_SELLER_CONTACT;

		$mails[] = array(
			'type' => $mail_type,
			'data' => array(
				'recipients' => $data['seller_email'],
				'customer_name' => $data['customer_name'],
				'customer_email' => $data['customer_email'],
				'customer_message' => $data['message'],
				'product_id' => 0,
				'addressee' => $data['seller_name']
			)
		);
		$this->MsLoader->MsMail->sendMails($mails);

	}



	public function migrateOldConversations()
	{

		$this->load->model('account/messagingseller');


		// check if app is already upgraded
		$upgraded = $this->model_account_messagingseller->isAppUpgraded();
		if ($upgraded) {
			return;
		}

		// select old chats
		$all_conversations = $this->model_account_messagingseller->getOldConversations();

		$all_convs = "";
		$all_conv_msgs = "";
		foreach ($all_conversations as $key => $conversation) {

			$all_convs  .= "('{$conversation['seller_id']}', '{$conversation['customer_id']}', 's', 'c', '{$conversation['subject']}', ";
			$all_convs .= "'{$conversation['product_id']}', '{$conversation['date_added']}'), ";

			$conversation_msgs = $this->model_account_messagingseller->getConversationMsgs($conversation['id']);


			foreach ($conversation_msgs as $msg) {
			
				$owner_id = $msg['owner'] == 's' ? $conversation['seller_id'] : $conversation['customer_id'];

				$all_conv_msgs .= "('{$conversation['id']}', '{$owner_id}', '{$msg['owner']}', '{$msg['message']}', '1', '{$msg['date_added']}'";
				$all_conv_msgs .= "), ";
			}

		}

		if ($all_convs != "") {
			$all_convs = rtrim($all_convs, ', ') . ';';
		}

		if ($all_convs != "") {
			$all_conv_msgs = rtrim($all_conv_msgs, ', ') . ';';
		}

		$this->model_account_messagingseller->migrateOldConversations($all_convs, $all_conv_msgs);
	}

}
?>
