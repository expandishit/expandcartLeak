<?php
class ModelModuleFifaCardsFifaCardsClubs extends Model {
	public function getCountry( $country_id, $lang_id = null ) {
		$lang_id = $lang_id ?: (int) $this->config->get('config_language_id');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "' AND status = '1'");
		
		$result = $query->row;

		if ( !empty($result) )
		{
			$query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "countries_locale WHERE country_id = '{$country_id}' AND lang_id = '{$lang_id}'")->row;
			$result['name'] = $query['name'];

			return $result;
		}
		else
		{
			return false;
		}
	}	
	
	public function getClubs( $lang_id = null )
	{
		//$country_data = $this->cache->get('country.status');
		
		//$country_language = $country_data['language'];
		$lang_id = (int) $lang_id ?: (int) $this->config->get('config_language_id');
		
		// Load cached countries in case of no cached countries or cached in different language
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "fifa_cards_clubs c LEFT JOIN " . DB_PREFIX . "fifa_cards_clubs_locale cl ON (c.club_id = cl.club_id AND cl.lang_id = '".$lang_id."') WHERE status = '1' ORDER BY cl.name ASC");

			$clubs_data = $query->rows;

			foreach ($clubs_data as $index => $club)
			{
				// $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "countries_locale WHERE country_id = '{$country['country_id']}' AND lang_id = '{$lang_id}'");
				$clubs_data[$index]['name'] = $club['name'];
				$clubs_data[$index]['logo'] = $club['club_logo'];
			}
			//$country_data['language'] = $lang_id;
			//$this->cache->set('country.status', $country_data);

		return $clubs_data;
	}

	public function getClubsByLanguageID($languageId)
    {
        $query = [];

        $query[] = 'SELECT * FROM ' . DB_PREFIX . 'fifa_cards_clubs_locale as cl';
        $query[] = 'INNER JOIN ' . DB_PREFIX . 'fifa_cards_clubs as c ON c.club_id=cl.club_id';
        $query[] = 'WHERE cl.lang_id = "' . 1 . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

}
?>
