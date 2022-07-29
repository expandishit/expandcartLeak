<?php
class ModelLocalisationZone extends Model {
	public function getZone($zone_id, $lang_id = null) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "' AND status = '1'");
		$query = $query->row;

		if ( ! empty( $query ) )
		{
			$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');

			$locale = $this->db->query("SELECT * FROM " . DB_PREFIX . "zones_locale WHERE zone_id = '{$zone_id}' AND lang_id = '{$lang_id}'");
			$locale = $locale->row;

			$query['name'] = $locale['name'];
			
			return $query;
		}
		
		return false;
	}		

	public function getZoneIdByName($zone){

		$query = $this->db->query("SELECT zone_id FROM " . DB_PREFIX . "zones_locale WHERE LOWER(name) LIKE '" .
			"%". $this->db->escape(utf8_strtolower($zone)) . "%" .  "' LIMIT 1");

		if ($query->num_rows) {
			return $query->row['zone_id'];
		} else {
			return false;
		}
	}

	public function getZonesByCountryId( $country_id, $lang_id = null )
	{
		$zone_data = $this->cache->get('zone.' . (int)$country_id);
		$zone_language =  $this->cache->get('zone_language' . (int)$country_id);

		$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');
		
		if (!$zone_data || $lang_id != $zone_language) {
			$query = $this->db->query("SELECT zones_locale.name,zone.zone_id  FROM " . DB_PREFIX . "zone 
			join zones_locale on zone.zone_id=zones_locale.zone_id
			WHERE zone.country_id = '" . (int)$country_id . "' AND zone.status = '1' 
			AND zones_locale.lang_id = '{$lang_id}'
			ORDER BY zones_locale.name");
	
			$zone_data = $query->rows;
			$this->cache->set('zone.' . (int)$country_id, $zone_data);
			$this->cache->set('zone_language.' . (int)$country_id, $lang_id);
		}
	
		return $zone_data;
	}
}
?>
