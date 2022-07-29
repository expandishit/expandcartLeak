<?php
class ModelLocalisationArea extends Model {

	public function getAreas() { 
        
        $lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');

        $query = $this->db->query("SELECT *  FROM " . DB_PREFIX . "geo_area 
        join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
        WHERE geo_area.status = '1' 
        AND geo_area_locale.lang_id = '{$lang_id}'
        ORDER BY geo_area_locale.area_id");
        $area_data = $query->rows;
        return $area_data;
		
	}	
}
?>