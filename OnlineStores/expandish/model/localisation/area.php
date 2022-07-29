<?php
class ModelLocalisationArea extends Model {
	public function getArea($area_id, $lang_id = null) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_area WHERE area_id = '" . (int)$area_id . "' AND status = '1'");
		$query = $query->row;

		if ( ! empty( $query ) )
		{
			$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');

			$locale = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_area_locale WHERE area_id = '{$area_id}' AND lang_id = '{$lang_id}'");
			$locale = $locale->row;

			$query['name'] = $locale['name'];
			
			return $query;
		}
		
		return false;
	}		

	public function getAreaIdByName($area){

		$query = $this->db->query("SELECT area_id FROM " . DB_PREFIX . "geo_area_locale WHERE LOWER(name) LIKE '" .
			"%". $this->db->escape(utf8_strtolower($area)) . "%" .  "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row['area_id'];
		} else {
			return false;
		}
	}

	public function getAreaByZoneId($zone_id, $lang_id = null )
	{
		$area_data = $this->cache->get('area.' . (int)$zone_id);
		$area_language =  $this->cache->get('area_language' . (int)$zone_id);

		$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');
		
		if (!$area_data || $lang_id != $area_language) {
			$query = $this->db->query("SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area 
			join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.zone_id = '" . (int)$zone_id . "' AND geo_area.status = '1' 
			AND geo_area_locale.lang_id = '{$lang_id}'
			ORDER BY geo_area_locale.name");
	
			$area_data = $query->rows;
			$this->cache->set('area.' . (int)$zone_id, $area_data);
			$this->cache->set('area_language.' . (int)$zone_id, $lang_id);
		}
	
		return $area_data;
	}
}
?>
