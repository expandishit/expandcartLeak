<?php
class ModelCatalogArea extends Model {
	public function getAreasByZone($zone_id, $lang_id) {
		$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');
		$zone_id = (int)$zone_id;
		$lang_id = (int)$lang_id;
		$query = $this->db->query("SELECT a.*, al.name locale_name FROM geo_area a LEFT JOIN geo_area_locale al on a.area_id=al.area_id WHERE a.zone_id='{$zone_id}' AND al.lang_id='{$lang_id}'");
		return $query->rows;
	}
}
?>
