<?php
class ModelTrackingInfo extends Model {	

	public function getOrder($order_id, $phone_number){
		if(!$order_id || !$phone_number) return [];

		//Get Order info
		$sql = "
			SELECT 
			order_id, 
			CONCAT(firstname, lastname) as `name`, 
			telephone, 
			email, 
			shipping_tracking_url,
			order_status_id
			
			FROM `order` 

			WHERE order_id = " . (int)$order_id ." AND telephone = '" . $this->db->escape($phone_number) . "'";

		$order_info = $this->db->query($sql)->row;

		if( !$order_info ) return [];

		//Get Order status history
		$sql = "
			SELECT
			order_status.`name`, 
			oh.order_status_id, 
			oh.date_added, 
			oh.comment

			FROM order_history oh 
			INNER JOIN order_status ON order_status.order_status_id = oh.order_status_id
			
			WHERE oh.order_id = ".(int)$order_id ."
			AND order_status.`language_id` = {$this->config->get('config_language_id')};";

		$order_status_history = $this->db->query($sql)->rows;
		
		return [
			'order_info'       => $order_info,
			'order_history'    => $order_status_history
		];
	}
}
