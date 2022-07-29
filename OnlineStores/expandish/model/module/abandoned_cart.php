<?php

class ModelModuleAbandonedCart extends Model {

	public function orderHasCronJob($order_id){
		return $this->db->query("SELECT cronjob_id 
			FROM `" . DB_PREFIX . "abandoned_order_cronjob`
			WHERE order_id = " . (int)$order_id)->row['cronjob_id'];
	}

	public function orderHasBeenEmailed($order_id){
		return $this->db->query("SELECT emailed
			FROM `" . DB_PREFIX . "emailed_abandoned_orders`
			WHERE order_id = " . (int)$order_id)->row['emailed'];
	}

	public function addOrderCronJob( $order_id ){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "abandoned_order_cronjob`
				SET order_id = " . (int) $order_id);
	}

	public function removeOrderCronJob( $order_id ){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "abandoned_order_cronjob`
				WHERE order_id = " . (int) $order_id);
	}

	public function getCrobJobFileName($order_id){
		return $this->db->query("SELECT cronjob_file_name 
			FROM `" . DB_PREFIX . "abandoned_order_cronjob`
			WHERE order_id = " . (int)$order_id);
	}

	public function setOrderAsEmailed($order_id, $isSent = true){
        $isInserted = $this->db->query("SELECT id FROM `" . DB_PREFIX . "emailed_abandoned_orders` WHERE order_id='" . (int)$order_id . "'");
        if ($isSent){
            if ($isInserted->num_rows > 0){
                $this->db->query("UPDATE `" . DB_PREFIX . "emailed_abandoned_orders` SET `emailed`=1 WHERE order_id='" . (int)$order_id . "'");
            }else{
                $this->db->query("INSERT INTO `" . DB_PREFIX . "emailed_abandoned_orders` SET emailed = 1, order_id='" . (int)$order_id . "'");
            }
        }else{
            if ($isInserted->num_rows > 0){
                $this->db->query("UPDATE `" . DB_PREFIX . "emailed_abandoned_orders` SET `emailed`=0 WHERE order_id='" . (int)$order_id . "'");
            }else{
                $this->db->query("INSERT INTO `" . DB_PREFIX . "emailed_abandoned_orders` SET emailed = 0, order_id='" . (int)$order_id . "'");
            }
        }
	}
}
