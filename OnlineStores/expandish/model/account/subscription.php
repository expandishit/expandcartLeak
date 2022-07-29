<?php
class ModelAccountSubscription extends Model {


	/**
	* Get one Subscription data or all subscriptions grid data 
	* it basically check if there is a subscription_id variable to call the appropriat private sub-method.
	* @return subscription array or subscriptions array of arrays.
	*/
	public function get($subscription_id = null){
	
		return !empty($subscription_id) ? $this->_getSubscription($subscription_id) : $this->_getAllSubscriptions();
	}

	
	public function getSubsciptionExpirationDate($subscription, $customer_id = null){

		if(!$subscription) return;
		if(!$customer_id) $customer_id = $this->customer->getId();

		//Get lastpayment date for this subscription
		$last_payment_date = $this->db->query("SELECT max(payment_datetime) as payment_datetime FROM `".DB_PREFIX."buyer_subscription_payments_log` WHERE buyer_id=".(int)$customer_id." AND subscription_id=".(int)$subscription['id'] . " ORDER BY payment_datetime")->row['payment_datetime'];

		if(!$last_payment_date) return;
		
		//Convert it to Datetime object for comparison later
		$last_payment_date = new DateTime($last_payment_date);

		//Add timezone if configured in settings table
		$config_timezone = $this->config->get('config_timezone');
		
		if(!empty($config_timezone)){
			$last_payment_date->setTimezone($this->config->get('config_timezone'));
		}

		//Add Subscription validity interval 
		$interval_unit = $subscription['validity_period_unit'] == 'year' ? 'Y' : ($subscription['validity_period_unit'] == 'month' ? 'M' : 'D');		
		$last_payment_date->add(new DateInterval('P' . $subscription['validity_period'] . $interval_unit));
		
		return $last_payment_date;
	}


	public function checkIfSubscriptionExpired($subscription, $customer_id = null){
		
		if(!$subscription) return;
		
		$last_payment_date = $this->getSubsciptionExpirationDate($subscription, $customer_id);

		if(!$last_payment_date) return;

		//Get Now datetime value to compare with..
		$now_datetime   = new DateTime('NOW');

		//Add timezone if configured in settings table
		$config_timezone = $this->config->get('config_timezone');
		
		if(!empty($config_timezone)){
			$now_datetime->setTimezone($this->config->get('config_timezone'));			
		}

		//Expired
		$is_expired = $last_payment_date < $now_datetime;
		
		return $is_expired;
	}

	public function getCoupons($subscription_id){
		return array_column($this->db->query("
			SELECT `code` 
			FROM `".DB_PREFIX."buyer_subscription_coupon` bsc
			JOIN `".DB_PREFIX."coupon` c
				ON c.coupon_id = bsc.coupon_id
			WHERE bsc.subscription_id = " .(int)$subscription_id)->rows , 'code');
	}

	public function getCustomerSubscriptionPlan($customer_id){
		return $this->db->query("SELECT buyer_subscription_id AS id, bst.title, bst.description, bs.validity_period, bs.validity_period_unit  
			FROM `" . DB_PREFIX . "customer` 
			
			JOIN `" . DB_PREFIX . "buyer_subscription` bs
				ON bs.subscription_id = customer.buyer_subscription_id

			JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst
				ON bst.subscription_id = customer.buyer_subscription_id
			
			WHERE customer_id = " . (int)$customer_id . " AND bst.language_id = " . (int)$this->config->get('config_language_id'))->row;
	}

	/** Private Methods **/
	private function _getSubscription($subscription_id){
		$subscription = $this->db->query("
				SELECT * FROM `" . DB_PREFIX . "buyer_subscription` bs
				JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst
					ON bs.subscription_id = bst.subscription_id
				WHERE bs.subscription_id = " . (int)$subscription_id . " AND bst.language_id = " . (int)$this->config->get('config_language_id'))->row;

		// $subscription['translations'] = $this->_getSubscriptionTranslations($subscription_id) ;

		// $subscription['coupons'] = array_column($this->db->query("
		// 	SELECT coupon_id FROM `" . DB_PREFIX . "buyer_subscription_coupon` 
		// 	WHERE subscription_id = " . (int)$subscription_id)->rows, 'coupon_id');

		return $subscription;
	}

	private function _getAllSubscriptions(){
		$subscriptions = $this->db->query("
			SELECT * FROM `" . DB_PREFIX . "buyer_subscription` bs 
			JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst 
				ON bs.subscription_id = bst.subscription_id 
			WHERE bs.status = 1 AND bst.language_id = " . (int)$this->config->get('config_language_id'))->rows;

		foreach ($subscriptions as $key =>$subscription) {
			$subscriptions[$key]['coupons'] = $this->db->query("
				SELECT bsc.coupon_id, c.type, c.discount 
				FROM `buyer_subscription_coupon` bsc
				JOIN `coupon` c 
					ON c.coupon_id = bsc.coupon_id
				WHERE bsc.subscription_id =" . (int)$subscription['subscription_id'])->rows;

			foreach ($subscriptions[$key]['coupons'] as $key2 => $coupon) {
				$subscriptions[$key]['coupons'][$key2]['categories'] = $this->db->query("
				SELECT cc.category_id, cd.name
				FROM `coupon_category` cc
				JOIN `category_description` cd 
					ON cd.category_id = cc.category_id
				WHERE cc.coupon_id = ". $coupon['coupon_id'] . " AND cd.language_id =". (int)$this->config->get('config_language_id') )->rows;


			}
		}
		return $subscriptions;
	}

	private function _getSubscriptionTranslations($subscription_id){
		$arr = [];
		$translations = $this->db->query("
				SELECT * FROM `" . DB_PREFIX . "buyer_subscription_translations`
				WHERE subscription_id = " . (int)$subscription_id)->rows;

		foreach($translations as $key => $translation){
			$arr[$translation['language_id']] = $translation;
		}
		return $arr;
	}
}
