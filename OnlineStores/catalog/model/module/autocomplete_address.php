<?php
class ModelModuleAutocompleteAddress extends Model {
	public function getCountry($country_name) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE name = '" . $this->db->escape($country_name) . "' AND status = '1'");
		
		return $query->row;
	}
    
	public function getZone($country_id, $zone_name) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE name = '" . $this->db->escape($zone_name) . "' AND country_id = '" . (int)$country_id . "' AND status = '1'");
		
		return $query->row;
	}
}
?>