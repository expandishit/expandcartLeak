<?php 
class ModelCatalogAttribute extends Model {

	public function getAttribute($attribute_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->row;
	}

    public function getAttributeByDescription($attribute_name,$lang) {
        $query = $this->db->query("SELECT * FROM ". DB_PREFIX . "attribute_description WHERE name = '" . $attribute_name . "' AND language_id = '" . $lang . "'");

        return $query->row;
    }

}
?>