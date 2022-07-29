<?php
/**
 * Created by PhpStorm.
 * User: Applehouse
 * Date: 10/8/14
 * Time: 8:14 PM
 */

class ModelPromotionsRewardPointsTransactions extends Model
{
	CONST BEHAVIOR_SIGN_UP          = '1::Signing Up';
	CONST BEHAVIOR_POSTING_REVIEW   = '2::Posting Product Review';
	CONST BEHAVIOR_REFERRAL_VISITOR = '3::Referral Visitor (Friend click on referral link)';
	CONST BEHAVIOR_REFERRAL_SIGN_UP = '4::Referral Sign-Up';
	CONST BEHAVIOR_NEWSLETTER       = '5::Signing Up Newsletter';
	CONST BEHAVIOR_FACEBOOK_LIKE    = '6::Facebook Like';
	CONST BEHAVIOR_FACEBOOK_SHARE   = '7::Facebook Share';
	CONST BEHAVIOR_BIRTHDAY         = '8::Customer Birthday';

	CONST TRANSACTION_USE_POINTS_ON_ORDER = 11;
	CONST TRANSACTION_REWARD_ON_ORDER     = 12;
	public function beforeUpdateOrder($order_id, $data)
	{
		$redeemed_order_query   = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE order_id = $order_id AND transaction_type = ".self::TRANSACTION_USE_POINTS_ON_ORDER);
		$rewarded_order_query   = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE order_id = $order_id AND transaction_type = ".self::TRANSACTION_REWARD_ON_ORDER);

        $this->load->model('sale/customer');
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($order_id);
        $customer_info = $this->model_sale_customer->getCustomerByEmail($order_info['email']);
         //some stores haved configed single value and some have value as array 
        if($data['order_status_id']==$this->config->get('update_based_order_status') || 
           in_array($data['order_status_id'], $this->config->get('update_based_order_status')))
		{
            $customer_reward_data = $this->db->query("SELECT * FROM ".DB_PREFIX."customer_reward WHERE order_id = $order_id");

            if(!$customer_reward_data->num_rows){
                $order_total_data = $this->db->query("SELECT * FROM ".DB_PREFIX."order_total WHERE order_id = $order_id");
//                print_r($order_total_data);
                $reward_point = 0;
                foreach($order_total_data->rows as $odt){
                    if($odt['code'] == 'earn_point'){
                        $reward_point = $odt['value'];
                    }
                }
               
                if($reward_point > 0){
//                    echo "\nreward is " . $reward_point . "\n";exit;
                    $this->insertRewardPoints($data, $reward_point, $order_id);

                }
            }
			if(isset($customer_info['customer_id']) && $customer_info['customer_id'])
//				$this->db->query("UPDATE ".DB_PREFIX."customer_reward SET status = 1, customer_id = {$customer_info['customer_id']} WHERE order_id = $order_id");
				$this->db->query("UPDATE ".DB_PREFIX."customer_reward SET status = 1 WHERE order_id = $order_id AND points > 0");
		}

        if($data['order_status_id']==$this->config->get('update_deduction_based_order_status') || 
           in_array($data['order_status_id'], $this->config->get('update_deduction_based_order_status'))) {

                $redeem_point = 0;
                $order_total_data = $this->db->query("SELECT * FROM ".DB_PREFIX."order_total WHERE order_id = $order_id");
                foreach($order_total_data->rows as $odt){
                    if($odt['code'] == 'redeem_point') {
                        $redeem_point = $odt['value'];
                    }
                }

                if($redeem_point > 0){
                    $log_message = "Order ID: <b>$order_id</b>, Redeemed -$redeem_point ".$this->config->get('text_points_'.$this->language->get('code'));
                    $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET
							  order_id = $order_id,
							  customer_id = '" . (int)$data['customer_id'] . "',
							  description = '" . $this->db->escape($log_message) ."',
							  points = '" . (float)-$redeem_point . "',
							  transaction_type = '" . (int)self::TRANSACTION_USE_POINTS_ON_ORDER . "',
							  status = 1,
							  date_added = NOW()");
                }

                if(isset($customer_info['customer_id']) && $customer_info['customer_id'])
                    $this->db->query("UPDATE ".DB_PREFIX."customer_reward SET status = 1 WHERE order_id = $order_id AND points < 0");
            }
	}

	public function updateStatusReward($data){
		$this->db->query("UPDATE ".DB_PREFIX."customer_reward SET status = {$data['status']} WHERE customer_reward_id = {$data['customer_reward_id']}");
	}

	public function insertRewardPoints($data, $reward_point, $order_id)
    {
        $log_message = "Reward for checkout order #$order_id";

        /**
         * This code has been commented because this was an attempt to create an application
         * but the process stop and this code has not been updated
         */
        // $this->load->model('znetwork_marketing/settings');

        // if (!$this->model_network_marketing_settings->appStatus()) {
        //     $dataSet = [];

        //     $dataSet[0] = [
        //         'customer_id' => $data['customer_id'],
        //         'points' => $reward_point,
        //         'description' => $log_message,
        //     ];
        // } else {
        //     $this->load->model('network_marketing/downlines');

        //     $points = $this->model_network_marketing_downlines->explainEarnPoints($total_reward_points);

        //     $dataSet = [];

        //     $this->language->load_json('module/network_marketing');

        //     foreach ($points as $key => $point) {

        //         if ($point['customer_id'] != $data['customer_id']) {
        //             $log_message = $this->language->get('your_profit_from_selling_order') . "#" . $order_id;
        //         }

        //         $dataSet[$key] = [
        //             'customer_id' => $point['customer_id'],
        //             'points' => $point['points'],
        //             'description' => $log_message,
        //         ];
        //     }
        // }

        // foreach ($dataSet as $entry) {

        //     $queryString = $fields = [];
        //     $queryString[] = "INSERT INTO " . DB_PREFIX . "customer_reward SET";
        //     $fields[] = "order_id = $order_id";
        //     $fields[] = "order_status_id = ".(int)$this->config->get('config_order_status_id')."";
        //     $fields[] = "customer_id = '" . (int)$entry['customer_id'] . "'";
        //     $fields[] = "description = '" . $this->db->escape($entry['description']) ."'";
        //     $fields[] = "points = '" . $entry['points'] . "'";
        //     $fields[] = "transaction_type = '" . (int)self::TRANSACTION_REWARD_ON_ORDER . "'";
        //     $fields[] = "date_added = '{$date_added}'";

        //     $queryString[] = implode(', ', $fields);

        //     $this->db->query(implode(' ', $queryString));

        // }

        $dataSet = [
            'customer_id' => $data['customer_id'],
            'points' => $reward_point,
            'description' => $log_message,
        ];
        
        $queryString = $fields = [];
        $queryString[] = "INSERT INTO " . DB_PREFIX . "customer_reward SET";
        $fields[] = "order_id = $order_id";
        $fields[] = "order_status_id = ".(int)$this->config->get('config_order_status_id')."";
        $fields[] = "customer_id = '" . (int)$dataSet['customer_id'] . "'";
        $fields[] = "description = '" . $this->db->escape($dataSet['description']) ."'";
        $fields[] = "points = '" . $dataSet['points'] . "'";
        $fields[] = "transaction_type = '" . (int)self::TRANSACTION_REWARD_ON_ORDER . "'";
        $fields[] = "date_added = '{$date_added}'";

        $queryString[] = implode(', ', $fields);

        $this->db->query(implode(' ', $queryString));
    }

	public function behaviorToText($id)
	{
		$const = new ReflectionClass ('ModelPromotionsRewardPointsTransactions');

		foreach($const->getConstants() as $text => $cons)
		{
			$explodeConst = explode("::", $cons);
			$behavior_id = (int)$explodeConst[0];

			$_text = explode("BEHAVIOR_", $text);
			$_text = ucwords(str_replace("_", " ", $_text[1]));
			if(!empty($id) && $id == $behavior_id)
			{
				return $_text;
			}
		}
	}
}
