<?php

class Modelaccountwishlist extends Model {

	public function UpdateWishlist($wishlist,$customer_id) {
		$this->db->query("UPDATE `customer` SET `wishlist`= '" . $wishlist . "' WHERE customer_id = '" . $customer_id . "'");
	}
	public function getWishlist($customer_id) {
		$query = $this->db->query("SELECT wishlist FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . (int)$customer_id . "'");

		return unserialize($query->row['wishlist']);
	}
}
