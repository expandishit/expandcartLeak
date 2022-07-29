<?php

class ControllerSellerAccount extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);

		$this->document->addStyle('expandish/view/javascript/multimerch/datatables/css/jquery.dataTables.css');
		$this->document->addScript('expandish/view/javascript/multimerch/datatables/js/jquery.dataTables.min.js');
		$this->document->addScript('expandish/view/javascript/multimerch/common.js');

		$this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'),$this->language->load_json('account/account'));
		$parts = explode('/', $this->request->request['route']);

		if (isset($parts[2]) && in_array($parts[2], array('jxUpdateFile','jxUploadImages', 'jxUploadDownloads', 'jxUploadSellerAvatar'))) {
			if (empty($_POST) || empty($_FILES))
				return;
			// Re-create session as Flash doesn't pass session info
			if (isset($_POST['session_id'])) {
				if (!isset($this->session->data['customer_id'])) {
					session_destroy();
					$_COOKIE['PHPSESSID'] = $_POST['session_id'];
					$registry->set('session', new Session());
				}
				if (isset($_SESSION['customer_id'])) {
					$salt = $this->MsLoader->MsSeller->getSalt($_SESSION['customer_id']);
					if (isset($_POST['token']) && isset($_POST['timestamp']) && $_POST['token'] == md5($salt . $_POST['timestamp'])) {
						$this->session->data['customer_id'] = $_SESSION['customer_id'];
						$this->customer = new Customer($this->registry);
						// todo re-initialize seller object
					}
				}
			}
		}
		
	  	if (!$this->customer->isLogged()) {
	  		$this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
	  		$this->redirect($this->url->link('account/login', '', 'SSL')); 
    	} else if (!$this->MsLoader->MsSeller->isSeller()) {
    		if (!array_intersect($parts, array('account-profile', 'jxsavesellerinfo', 'jxUploadSellerAvatar'))) {
    			$this->redirect($this->url->link('seller/account-profile', '', 'SSL'));
    		}
    	} elseif (!$this->MsLoader->MsSeller->getSubscriptionPlan()) {
			if (
            	$this->config->get('msconf_enable_subscriptions_plans_system') == 1 &&
            	!array_intersect($parts, array('account-subscriptions'))
			) {
                $this->redirect($this->url->link('seller/account-subscriptions', '', 'SSL'));
            }
		} else if ($this->MsLoader->MsSeller->getStatus() != MsSeller::STATUS_ACTIVE) {
			$allowed_routes = array('account-profile', 'jxsavesellerinfo', 'jxUploadSellerAvatar','account-subscriptions');
			if ($this->MsLoader->MsSeller->getStatus() == MsSeller::STATUS_INACTIVE && $this->config->get('msconf_allow_inactive_seller_products')) {
				$allowed_routes[] = 'account-product';
				$allowed_routes[] = 'jxSubmitProduct';
				$allowed_routes[] = 'jxUploadDownloads';
				$allowed_routes[] = 'jxUploadImages';
				$allowed_routes[] = 'jxAutocomplete';
				$allowed_routes[] = 'tab-shipping';
			}
    		if (!array_intersect($parts, $allowed_routes)) {
				$this->redirect($this->url->link('seller/account-profile', '', 'SSL'));
    		}
    	}

    	if ($this->config->get('msconf_enable_subscriptions_plans_system') == 1) {

			$subscriptions = $this->load->model('multiseller/subscriptions', ['return' => true]);

			$payment = $subscriptions->getLatestPayment($this->customer->getId());
			$plan = $subscriptions->getPlanById($payment['plan_id']);
			if($plan & $payment){

				$formats = ['1' => 'days', '2' => 'month', '3' => 'year'];

				if (strtotime(date('Y-m-d h:i:s', strtotime($payment['lastPaymentDate'] . ' +' . $plan['period'] . ' '.$formats[$plan['format']]))) < strtotime(date('Y-m-d h:i:s', time()))) {

					if (!array_intersect($parts, array('account-subscriptions', 'expiredPlan'))) {
						$this->redirect($this->url->link('seller/account-subscriptions/expiredPlan', '', 'SSL'));
					}
				}
			}
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
    		unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
	
		$this->MsLoader->MsHelper->addStyle('multiseller');
		
		if (!isset($this->session->data['multiseller']['files']))
			$this->session->data['multiseller']['files'] = array();
	}
}
?>
