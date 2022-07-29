<?php

class ModelModuleBuyerSubscriptionPlanSubscription extends Model {

	public function add($subscription){
		//validation
		if( empty($subscription) ) return -1;

		//insert into DB - Main table buyer_subscription
		$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription` SET
			status =" . $subscription['status'] . ", 
			price  =" . $subscription['price']  . ",
			validity_period =" . $subscription['validity_period'] . ",
			validity_period_unit ='" . $subscription['validity_period_unit'] . "';");

		$subscription_id = $this->db->getLastId();

		//insert into translations table
		foreach ($subscription['translations'] as $language_id => $translation) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription_translations` SET 
				subscription_id = " . (int)$subscription_id . ", 
				language_id     = " . (int)$language_id .     ", 
				title           = '" . $this->db->escape($translation['title']) . "',
				description     = '" . $this->db->escape($translation['description']) . "'");
		}

		//insert coupons into buyer_subscription_coupon table
		foreach ($subscription['coupons'] as $coupon_id) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription_coupon` SET 
				subscription_id = " . $subscription_id . ",
				coupon_id       = " . $coupon_id);
		}

		return $subscription_id;
	}

	public function update($subscription){

		//validation
		if( empty($subscription) ) return -1;

		//update DB records
		try{
			//Update main table buyer_subscription
			$subscription_id = $subscription['subscription_id'];

			$this->db->query("UPDATE `" . DB_PREFIX . "buyer_subscription` SET
				status =" . $subscription['status'] . ", 
				price  =" . $subscription['price']  . ",
				validity_period =" . $subscription['validity_period'] . ",
				validity_period_unit ='" . $subscription['validity_period_unit'] . "'
				WHERE subscription_id = ". (int)$subscription_id);


			//update into translations table
			$this->db->query("DELETE FROM buyer_subscription_translations WHERE subscription_id=".(int)$subscription_id);

			foreach ($subscription['translations'] as $language_id => $translation) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription_translations` SET 
					subscription_id = " . (int)$subscription_id . ", 
					language_id     = " . (int)$language_id .     ", 
					title           = '" . $this->db->escape($translation['title']) . "',
					description     = '" . $this->db->escape($translation['description']) . "'");
			}

			//update coupons into buyer_subscription_coupon table
			$this->db->query("DELETE FROM buyer_subscription_coupon WHERE subscription_id=".(int)$subscription_id);

			foreach ($subscription['coupons'] as $coupon_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "buyer_subscription_coupon` SET 
					subscription_id = " . $subscription_id . ",
					coupon_id       = " . $coupon_id);
			}

			return TRUE;

		}catch (Exception $e) {
			// var_dump($e);die();

		    $log = new Log('subscription-model-errors');
		    $log->write('Caught exception: ' . $e->getMessage() .  "\n");

			return FALSE;		
		}

	}

	public function delete($ids){
		return $this->db->query( "DELETE FROM `".DB_PREFIX."buyer_subscription` WHERE subscription_id IN (" . implode(',', $ids) . ")" );
	}
	
	/**
	* Get one Subscription data or all subscriptions grid data 
	* it basically check if there is a subscription_id variable to call the appropriat private sub-method.
	* @return subscription array or subscriptions array of arrays.
	*/
	public function get($subscription_id = null){
	
		return !empty($subscription_id) ? $this->_getSubscription($subscription_id) : $this->_getAllSubscriptions();
	}

	public function getOrderPaymentLog($order_id){
            return $this->db->query("
				SELECT bspl.*, bst.title  FROM `" . DB_PREFIX . "buyer_subscription_payments_log` bspl 
				LEFT JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst
				ON bst.subscription_id = bspl.subscription_id
				WHERE bspl.order_id = " . (int)$order_id . " AND bst.language_id = ".(int)$this->config->get('config_language_id'))->row;
    }


	public function getCustomerPaymentsLog($customer_id){
		return $this->db->query("SELECT bspl.*, bst.title FROM `".DB_PREFIX."buyer_subscription_payments_log` bspl
			JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst
				ON bst.subscription_id = bspl.subscription_id
			WHERE bspl.buyer_id=".(int)$customer_id . " AND bst.language_id =".(int)$this->config->get('config_language_id') . " 
			ORDER BY bspl.payment_datetime desc")->rows;
	}

	/** Private Methods **/
	private function _getSubscription($subscription_id){
		$subscription = $this->db->query("
				SELECT * FROM `" . DB_PREFIX . "buyer_subscription` 
				WHERE subscription_id = " . (int)$subscription_id)->row;

		$subscription['translations'] = $this->_getSubscriptionTranslations($subscription_id) ;

		$subscription['coupons'] = array_column($this->db->query("
			SELECT coupon_id FROM `" . DB_PREFIX . "buyer_subscription_coupon` 
			WHERE subscription_id = " . (int)$subscription_id)->rows, 'coupon_id');

		return $subscription;
	}

	private function _getAllSubscriptions(){
		return $this->db->query("
			SELECT * FROM `" . DB_PREFIX . "buyer_subscription` bs 
			JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst 
				ON bs.subscription_id = bst.subscription_id 			
			WHERE bst.language_id = " . (int)$this->config->get('config_language_id'))->rows;
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
