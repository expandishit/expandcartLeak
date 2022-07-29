<?php
class ModelCatalogCountry extends Model {

	public function getAllCountries($lang_id = null)
	{
		$country_data = $this->cache->get('country.status');

		$country_language = $country_data['language'];
		$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');

		// Load cached countries in case of no cached countries or cached in different language
		if (!$country_data || $lang_id != $country_language) {
			$query = $this->db->query("SELECT c.country_id, c.iso_code_2, c.iso_code_3, c.phonecode, c.address_format, c.phonecode, c.postcode_required, 
			c.name as main_name ,cl.name FROM " . DB_PREFIX . "country c LEFT JOIN " . DB_PREFIX . "countries_locale cl ON (c.country_id = cl.country_id AND cl.lang_id = '".$lang_id."') WHERE status = '1' ORDER BY cl.name ASC");

			$country_data = $query->rows;

			foreach ($country_data as $index => $country)
			{
				// $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "countries_locale WHERE country_id = '{$country['country_id']}' AND lang_id = '{$lang_id}'");
				$country_data[$index]['name'] = $country['name'] ? $country['name'] : $country['main_name'];
			}
			//$country_data['language'] = $lang_id;
			$this->cache->set('country.status', $country_data);
		}

		return $country_data;
	}
}
?>
