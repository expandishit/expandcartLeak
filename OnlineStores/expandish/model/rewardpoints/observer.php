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
			$this->language->load_json('rewardpoints/index');
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
						  status = '1',
						  date_added = '{$date_added}'");
			/** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_SIGN_UP */
		}
		$this->afterSubscribeNewsletter($customer_id, $customer_data);
	}

    public function afterPostingReview($customer_id)
    {
        $behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = ".self::BEHAVIOR_POSTING_REVIEW. " AND status = 1 ");
        $reward_point = 0;
        $insert= false;

        foreach ($behavior_query->rows as $behavior_data){
            $customer_groups = unserialize($behavior_data['customer_group_ids']);

            $current_customer_group = $this->customer->getCustomerGroupId();

            if(!in_array($current_customer_group, $customer_groups))
            {
                continue;
            }

            if(isset($behavior_data['reward_point']))
            {
                $this->language->load_json('rewardpoints/index');
                $reward_point  = $reward_point + $behavior_data['reward_point'];
                $date_added_time = time();
                $date_added = date("Y-m-d h:i:s", $date_added_time);
                $log_message = $this->language->get('behavior_post_review');
                $insert = true;
            }
        }

        if ($insert){
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
						  order_id = 0, order_status_id = 0,
						  customer_id = '" . (int)$customer_id . "',
						  description = '" . $this->db->escape($log_message) ."',
						  transaction_type = '" . self::BEHAVIOR_POSTING_REVIEW . "',
						  points = '" . (float)$reward_point . "',
						  status = '1',
						  date_added = '{$date_added}'"
            );
        }

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
		$this->language->load_json('rewardpoints/index');
		/** Earn reward points after subscribe newsletter  */
		$behavior_query = $this->db->query("SELECT * FROM ".DB_PREFIX."behavior_rules WHERE actions = ".self::BEHAVIOR_NEWSLETTER. " AND status = 1");

		if(isset($behavior_query->row['reward_point']))
		{
			if(isset($data['newsletter']) && $data['newsletter']){
				$data['status'] = 1;
			}else{
				$data['status'] = 0;
			}
			$this->language->load_json('rewardpoints/index');
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
        $this->language->load_json('rewardpoints/index');
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

	        $this->insertEarnPoints($order_id, $total_reward_points, $log_message, $status_reward, $date_added);
	        /** DISPATCH_EVENT:OBSERVER_AFTER_INSERT_REWARD_POINTS_RULES */
        }
    }

    public function insertEarnPoints($order_id, $total_reward_points, $log_message, $status_reward, $date_added)
    {
        $this->load->model('network_marketing/settings');

        if (!$this->model_network_marketing_settings->appStatus()) {
            $dataSet = [];

            $dataSet[0] = [
                'customer_id' => $this->customer->getId(),
                'points' => $total_reward_points,
                'description' => $log_message,
            ];
        } else {
            $this->load->model('network_marketing/downlines');

            $points = $this->model_network_marketing_downlines->explainEarnPoints($total_reward_points);

            $dataSet = [];

            $this->language->load_json('network_marketing/global');

            foreach ($points as $key => $point) {

                if ($point['customer_id'] != (int)$this->customer->getId()) {
                    $logMessage = $this->language->get('your_profit_from_selling_order') . "#" . $order_id;
                } else {
                    $logMessage = $log_message;
                }

                $dataSet[$key] = [
                    'customer_id' => $point['customer_id'],
                    'points' => $point['points'],
                    'description' => $logMessage,
                ];
            }
        }

        foreach ($dataSet as $entry) {

            $queryString = $fields = [];
            $queryString[] = "INSERT INTO " . DB_PREFIX . "customer_reward SET";
			$fields[] = "order_id = $order_id";
            $fields[] = "order_status_id = ".(int)$this->config->get('config_order_status_id')."";
            $fields[] = "customer_id = '" . (int)$entry['customer_id'] . "'";
            $fields[] = "description = '" . $this->db->escape($entry['description']) ."'";
            $fields[] = "points = '" . $entry['points'] . "'";
            $fields[] = "transaction_type = '" . (int)self::TRANSACTION_REWARD_ON_ORDER . "'";
            $fields[] = "status = $status_reward";
            $fields[] = "date_added = '{$date_added}'";

            $queryString[] = implode(', ', $fields);

            $this->db->query(implode(' ', $queryString));

        }


    }
}
