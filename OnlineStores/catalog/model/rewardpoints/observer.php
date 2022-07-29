<?php
/**
 * Created by PhpStorm.
 * User: ANH To
 * Date: 10/10/14
 * Time: 3:58 PM
 */

class ModelRewardPointsObserver extends Model
{
	CONST BEHAVIOR_SIGN_UP          = 1;
	CONST BEHAVIOR_POSTING_REVIEW   = 2;
	CONST BEHAVIOR_REFERRAL_VISITOR = 3;
	CONST BEHAVIOR_REFERRAL_SIGN_UP = 4;
	CONST BEHAVIOR_NEWSLETTER       = 5;
	CONST BEHAVIOR_FACEBOOK_LIKE    = 6;
	CONST BEHAVIOR_FACEBOOK_SHARE   = 7;
	CONST BEHAVIOR_BIRTHDAY         = 8;

	CONST TRANSACTION_USE_POINTS_ON_ORDER = 11;
	CONST TRANSACTION_REWARD_ON_ORDER     = 12;
	/** DISPATCH_EVENT:OBSERVER_BEFORE_METHODS */
	public function afterAddCustomer($customer_id, $customer_data = array())
	{
		/** Earn reward points after sign-up */
		$behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = ".self::BEHAVIOR_SIGN_UP. " AND status = 1");
		if(isset($behavior_query->row['reward_point']))
		{
			$this->language->load('rewardpoints/index');
			$reward_point   = $behavior_query->row['reward_point'];

			$date_added_time = time();
			$date_added = date("Y-m-d h:i:s", $date_added_time);
			$expired_date = "0000-00-00 00:00:00";

			$log_message    = $this->language->get('behavior_sign_up');
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
						  order_id = 0, order_status_id = 0,
						  customer_id = '" . (int)$customer_id . "',
						  description = '" . $this->db->escape($log_message) ."',
						  transaction_type = '" . (int)self::BEHAVIOR_SIGN_UP . "',
						  points = '" . (float)$reward_point . "',
						  status = '{$customer_data['status']}',
						  date_added = '{$date_added}'");
			/** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_SIGN_UP */
		}
		$this->afterSubscribeNewsletter($customer_id, $customer_data);
	}

	public function afterSubscribeNewsletter($customer_id, $data)
	{
		$customer_reward_query = $this->db->query("SELECT customer_reward_id FROM ".DB_PREFIX."customer_reward WHERE customer_id = $customer_id AND transaction_type = ".self::BEHAVIOR_NEWSLETTER);
		if(isset($customer_reward_query->row['customer_reward_id']))
		{
			$sql = "UPDATE ".DB_PREFIX."customer_reward SET
				    status = ".($data['newsletter'] ? 1 : 0)."
				    WHERE customer_id = $customer_id AND transaction_type = ".self::BEHAVIOR_NEWSLETTER;
			$this->db->query($sql);
			/** DISPATCH_EVENT:OBSERVER_AFTER_CHECK_EXIST_REWARD_NEWSLETTER */
			return false;
		}
		$this->language->load('rewardpoints/index');
		/** Earn reward points after subscribe newsletter  */
		$behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = ".self::BEHAVIOR_NEWSLETTER. " AND status = 1");

		if(isset($behavior_query->row['reward_point']))
		{
			if(isset($data['newsletter']) && $data['newsletter']){
				$data['status'] = 1;
			}else{
				$data['status'] = 0;
			}
			$this->language->load('rewardpoints/index');
			$reward_point   = $behavior_query->row['reward_point'];

			$date_added_time = time();
			$date_added = date("Y-m-d h:i:s", $date_added_time);
			$expired_date = "0000-00-00 00:00:00";

			$log_message    = $this->language->get('behavior_newsletter');
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
					  order_id = 0, order_status_id = 0,
					  customer_id = '" . (int)$customer_id . "',
					  transaction_type = '" . (int)self::BEHAVIOR_NEWSLETTER . "',
					  description = '" . $this->db->escape($log_message) ."',
					  points = '" . (float)$reward_point . "',
					  status = '".(isset($data['status']) ? $data['status'] : 1)."',
					  date_added = '{$date_added}'");
			/** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_NEWSLETTER */
		}
	}

    public function afterPlaceOrder($order_id, $data = array())
    {
        $this->language->load('rewardpoints/index');
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);

        $redeemed_order_query   = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE order_id = $order_id AND transaction_type = ".self::TRANSACTION_USE_POINTS_ON_ORDER);
        $rewarded_order_query   = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE order_id = $order_id AND transaction_type = ".self::TRANSACTION_REWARD_ON_ORDER);

	    $date_added_time = time();
	    $date_added = date("Y-m-d h:i:s", $date_added_time);
	    $expired_date = "0000-00-00 00:00:00";

        /** Update points used to checkout of customer */
        if(!isset($redeemed_order_query->row['customer_reward_id'])){
	        $points_to_checkout = 0;
	        if(isset($this->session->data['points_to_checkout']) && (int)$this->session->data['points_to_checkout'] > 0)
	        {
				$points_to_checkout = $this->session->data['points_to_checkout'];
	        }else if(isset($data['points_to_checkout'])){
		        $points_to_checkout = $data['points_to_checkout'];
	        }
	        $points = (float)$points_to_checkout;
	        if($points > 0){
		        $log_message = sprintf($this->language->get('text_redeem_order'), $order_id, $points, $this->config->get('text_points_'.$this->language->get('code')));
		        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
							  order_id = $order_id,
							  customer_id = '" . (int)$this->customer->getId() . "',
							  description = '" . $this->db->escape($log_message) ."',
							  points = '" . (float)-$points . "',
							  transaction_type = '" . (int)self::TRANSACTION_USE_POINTS_ON_ORDER . "',
							  status = 1,
							  date_added = '{$date_added}'");
	        }
        }

        /** Make reward points to customer in status Pending (Catalog Earning Rules, Shopping Earning Cart Rules)*/
        if(!isset($rewarded_order_query->row['customer_reward_id']))
        {
	        $message_awarded = "";
	        $total_reward_points = 0 ;
            if(isset($this->session->data['html_awarded']) && !empty($this->session->data['html_awarded']))
            {
                $message_awarded = $this->session->data['html_awarded'];
            }

	        if(isset($this->session->data['total_reward_points']) && !empty($this->session->data['total_reward_points']))
	        {
		        $total_reward_points = $this->session->data['total_reward_points'];
	        }else if(isset($data['total_reward_points'])){
		        $total_reward_points = $data['total_reward_points'];
	        }
	        $status_reward = 0;
	        $log_message = $this->language->get('text_reward_for_checkout')." #$order_id"."<br />".
		        "<ul class='order-rules-rewarded'>
						   ".$message_awarded."
						   </ul>";
			
			//some stores haved configed single value and some have value as array 
			if($order_info['order_status_id']==$this->config->get('update_based_order_status') || 
			in_array($order_info['order_status_id'], $this->config->get('update_based_order_status')))
		    {
		        $status_reward = 1;
	        }
	        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
						  order_id = $order_id,
						  order_status_id = ".(int)$this->config->get('config_order_status_id').",
						  customer_id = '" . (int)$this->customer->getId() . "',
						  description = '" . $this->db->escape($log_message) ."',
						  points = '" . $total_reward_points . "',
						  transaction_type = '" . (int)self::TRANSACTION_REWARD_ON_ORDER . "',
						  status = $status_reward,
						  date_added = '{$date_added}'");
	        /** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_POINTS_RULES */
        }
    }
}
