<?php
class ModelUserUser extends Model {
	
	public function getUser($user_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$user_id . "'");
		
		return $query->row;
	}
	
}