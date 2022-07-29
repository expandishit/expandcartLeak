<?php
/**
 * Created by PhpStorm.
 * User: ANH To
 * Date: 10/11/14
 * Time: 9:05 AM
 */

class ModelPromotionsRewardPointsObserver extends Model
{
	CONST ENABLED = '1';
	CONST DISABLED = '0';
	CONST DELETED = '3';

	CONST BEHAVIOR_SIGN_UP          = 1;
	CONST BEHAVIOR_POSTING_REVIEW   = 2;
	CONST BEHAVIOR_REFERRAL_VISITOR = 3;
	CONST BEHAVIOR_REFERRAL_SIGN_UP = 4;
	CONST BEHAVIOR_NEWSLETTER       = 5;
	CONST BEHAVIOR_FACEBOOK_LIKE    = 6;
	CONST BEHAVIOR_FACEBOOK_SHARE   = 7;

	public function beforeUpdateReview($review_id, $product_id, $status)
	{
		$this->load->model('catalog/review');
		$review = $this->model_catalog_review->getReview($review_id);

		if(!isset($review['customer_id']) || (int)$review['customer_id'] == 0)
		{
			/** Guest review */

			return false;
		}

		$customer_id = (int)$review['customer_id'];
		$product_id  = (int)$review['product_id'];//
		$customer_reward_query = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE customer_id = $customer_id AND product_id = $product_id AND transaction_type = ".self::BEHAVIOR_POSTING_REVIEW);
		if(isset($customer_reward_query->row['customer_reward_id'])){
			$product_id = $customer_reward_query->row['product_id'];
			$customer_id = $customer_reward_query->row['customer_id'];
		}

		/** Earn reward points after subscribe newsletter  */
		$behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = ".self::BEHAVIOR_POSTING_REVIEW. " AND status = 1");

		if($behavior_query->num_rows)
		{
			$this->load->model('sale/customer');
			$customer_info = $this->model_sale_customer->getCustomer($customer_id);
			$customer_group_id = $customer_info['customer_group_id'];
			$behavior_rule = array();
			foreach($behavior_query->rows as $rule){
				$customer_group_ids = unserialize($rule['customer_group_ids']);
				if(in_array($customer_group_id, $customer_group_ids)){
					$behavior_rule = $rule;
					break;
				}
			}

			$this->language->load('module/reward_points_pro');
			$this->load->model('catalog/product');

			$product = $this->model_catalog_product->getProduct($product_id);

			$reward_point   = $behavior_rule['reward_point'];

			$date_added_time = time();
			$date_added = date("Y-m-d h:i:s", $date_added_time);
			$expired_date = "0000-00-00 00:00:00";

			$log_message    = sprintf($this->language->get('behavior_post_review'), $product['name']);
			if($status != '3'){
				$status         = ($status == self::ENABLED) ? 1 : 0;
				if(isset($customer_reward_query->row['customer_reward_id'])){
					$this->db->query("UPDATE " . DB_PREFIX . "customer_reward SET
						  order_id = 0, order_status_id = 0,
						  product_id = '" . (int)$product_id . "',
						  customer_id = '" . (int)$customer_id . "',
						  transaction_type = '" . (int)self::BEHAVIOR_POSTING_REVIEW . "',
						  description = '" . $this->db->escape($log_message) ."',
						  points = '" . (float)$reward_point . "',
						  status = $status,
						  date_added = '{$date_added}' WHERE customer_reward_id = {$customer_reward_query->row['customer_reward_id']}");
					/** DISPATCH_EVENT:OBSERVER_AFTER_UPDATE_REWARD_REVIEW_PRODUCT */
				}else{
					$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
						  order_id = 0, order_status_id = 0,
						  product_id = '" . (int)$product_id . "',
						  customer_id = '" . (int)$customer_id . "',
						  transaction_type = '" . (int)self::BEHAVIOR_POSTING_REVIEW . "',
						  description = '" . $this->db->escape($log_message) ."',
						  points = '" . (float)$reward_point . "',
						  status = $status,
						  date_added = '{$date_added}'");
					/** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_REVIEW_PRODUCT */
				}
			}else{
				if(isset($customer_reward_query->row['customer_reward_id']))
					$this->db->query("DELETE FROM ".DB_PREFIX."customer_reward WHERE customer_reward_id = {$customer_reward_query->row['customer_reward_id']}");
			}
		}
	}

	public function beforeAddReward($customer_id, $description, $points, $order_id)
	{
		$this->load->model('sale/customer');
		$customer_info = $this->model_sale_customer->getCustomer($customer_id);
		if ($customer_info) {
			$date_added_time = time();
			$date_added = date("Y-m-d h:i:s", $date_added_time);
			$expired_date = "0000-00-00 00:00:00";

			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
			                   customer_id = '" . (int)$customer_id . "',
			                   order_id = '" . (int)$order_id . "',
			                   points = '" . (int)$points . "',
			                   status = 1,
			                   description = '" . $this->db->escape($description) . "',
			                   date_added = '{$date_added}'");
			/** DISPATCH_EVENT:OBSERVER_AFTER_ADD_REWARD_FOR_CUSTOMER */
			$this->language->load('mail/customer');
			
			$currentLang = $this->config->get("config_admin_language");
			$configStoreName = $this->config->get('config_name');
			$localizedStoreName = reset($configStoreName);

			if (isset($configStoreName[$currentLang])) {
				$localizedStoreName = $configStoreName[$currentLang];
			}

			if ($order_id) {
				$this->load->model('sale/order');

				$order_info = $this->model_sale_order->getOrder($order_id);

				if (!empty($order_info['store_name'])) {
					$store_name = $order_info['store_name'];
				} else {
					$store_name = $localizedStoreName;
				}
			} else {
				$store_name = $localizedStoreName;
			}
  
			$message  = sprintf($this->language->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->language->get('text_reward_total'), $this->model_sale_customer->getRewardTotal($customer_id));

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
			$mail->setTo($customer_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_reward_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function afterApproveCustomer($customerId)
	{
		$behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = 1 AND status = 1");

		if(isset($behavior_query->row['reward_point'])) {
			$q = $this->db->query(
				vsprintf("SELECT * FROM ".DB_PREFIX."customer_reward WHERE
					customer_id = %d AND status = 0 AND transaction_type = %d", [
					$customerId,
					1
				])
			);

			if (isset($q->row['customer_id'])) {
				$this->db->query('UPDATE '.DB_PREFIX.'customer_reward SET `status` = 1');
			}
		}
	}
}
