<?php
class ModelSaleExternalOrder extends Model {
	
	public function getOrders() {
		$sql = "SELECT externalorder.*, customer.firstname, customer.lastname
				FROM externalorder
				-- JOIN order_status ON externalorder.statusvalue = order_status.order_status_id
				JOIN customer ON externalorder.customerid = customer.customer_id
				-- WHERE order_status.language_id = " . (int)$this->config->get('config_language_id') . "
				ORDER BY externalorder.createdon DESC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function updateOrderStatus($orderId, $statusValue) {
		$this->db->query("UPDATE externalorder SET statusvalue='" . (int)$statusValue . "' WHERE id = " . (int)$orderId);
	}
}
?>