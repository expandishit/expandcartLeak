<?php
class ModelLocalisationLanguage extends Model {
	public function getLanguage($language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE language_id = '" . (int)$language_id . "'");
		
		return $query->row;	
	}

	public function getLanguages() {
		$language_data = $this->cache->get('language');
		
		if (!$language_data) {		
			$language_data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");
		
    		foreach ($query->rows as $result) {
      			$language_data[$result['code']] = array(
        			'language_id' => $result['language_id'],
        			'name'        => $result['name'],
        			'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'filename'    => $result['filename'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
      			);
    		}	
			
			$this->cache->set('language', $language_data);
		}
		
		return $language_data;	
	}

	/**
	 * This method should getting the language data raw in language table
	 * By using the locale code ex: ar for arabic or en for english
	 */
	public function getLanguageByLocale($locale){
		return $this->db->query("
			SELECT * 
			FROM `" . DB_PREFIX . "language` 
			WHERE locale = '" . $this->db->escape($locale) . "'")->row;
	}
}
?>
