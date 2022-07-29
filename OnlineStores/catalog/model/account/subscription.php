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

    /** Private Methods **/
    private function _getSubscription($subscription_id){
        $subscription = $this->db->query("
				SELECT * FROM `" . DB_PREFIX . "buyer_subscription` bs
				JOIN `" . DB_PREFIX . "buyer_subscription_translations` bst
					ON bs.subscription_id = bst.subscription_id
				WHERE bs.subscription_id = " . (int)$subscription_id . " AND bst.language_id = " . (int)$this->config->get('config_language_id'))->row;

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
    
    
	public function getSubscriptions($data = array()) {
		
		
		$sql = "SELECT sb.*,(pd.name) AS pname,(p.image) AS image ,(pd.description) AS description FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)		
		LEFT JOIN " . DB_PREFIX . "subscriptionsbids 
		sb ON (p.product_id = sb.product_id)";
		
		$sql .= " WHERE sb.customer_id = '" . (int)$this->customer->getId() . "'";
		   
		$sort_data = array(
			'sb.email',
			'sb.date_added',
			'sb.name'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY sb.date_added";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
	
		return $query->rows;
	}	
		
	public function getTotalsubscriptions() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "subscriptionsbids` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		return $query->row['total'];
	}
	
	
	public function getTsubscriptions() {
      	$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscriptionsbids` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;	
		}
	}

    public function deletesubscription($product_id,$customer_id){
		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "subscriptionsbids WHERE product_id=".(int)$product_id." AND customer_id=".(int)$customer_id."");
		return 1;
	}
	
			
	
}
?>