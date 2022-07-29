<?php

class ModelModuleStoreReviews extends Model  {	
	

	public function saveStoreReviews($data) {
	    //to avoid save rate with out value

       	    if ($data['rate']!= null&&(in_array($data['rate'], array(1,2, 3,4,5))) )
           {
            $query = "INSERT INTO `" . DB_PREFIX . "store_reviews` (`customer_id`, `ip_address`, `rate`,`name`,`rate_description`)";
            $query .= "VALUES('{$data['customer_id']}', '{$data['ip_address']}', '{$data['rate']}', '{$data['name']}', '{$data['rate_description']}')";
            return $this->db->query($query);
        }
	}

	public function getStoreRate() {
        $query = "SELECT count(`rate`) rate_count, sum(`rate`) rate_sum FROM `" . DB_PREFIX . "store_reviews`";
        return $this->db->query($query)->row;
	}
	public function getStoreRate1() {
        $query = "SELECT count(`rate`) rate_count FROM `" . DB_PREFIX . "store_reviews` WHERE rate=1";
        return $this->db->query($query)->row;
	}
	public function getStoreRate2() {
        $query = "SELECT count(`rate`) rate_count FROM `" . DB_PREFIX . "store_reviews` WHERE rate=2";
        return $this->db->query($query)->row;
	}
	public function getStoreRate3() {
        $query = "SELECT count(`rate`) rate_count FROM `" . DB_PREFIX . "store_reviews` WHERE rate=3";
        return $this->db->query($query)->row;
	}
	public function getStoreRate4() {
        $query = "SELECT count(`rate`) rate_count FROM `" . DB_PREFIX . "store_reviews` WHERE rate=4";
        return $this->db->query($query)->row;
	}
	public function getStoreRate5() {
        $query = "SELECT count(`rate`) rate_count FROM `" . DB_PREFIX . "store_reviews` WHERE rate=5";
        return $this->db->query($query)->row;
	}


	public function getCustomerReview($data) {
		$query = "SELECT *  FROM `" . DB_PREFIX . "store_reviews`";
		$query .= "WHERE (ip_address='{$data['ip_address']}' OR (customer_id='{$data['customer_id']}' AND customer_id!=0))";
        return $this->db->query($query)->row;
	}

	public function getReviews() {

        $query = "SELECT * FROM " . DB_PREFIX . "store_reviews sr LEFT JOIN customer c ON (c.customer_id=sr.customer_id) where rate>0";
        return $this->db->query($query)->rows;
    }
	
}
