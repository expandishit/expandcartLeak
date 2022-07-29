<?php
class ModelAccountVoucher extends Model {	
	public function getVouchersByOrderId($order_id) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'";

		$query = $this->db->query($sql);
	
		return $query->rows;
	}	
}
?>