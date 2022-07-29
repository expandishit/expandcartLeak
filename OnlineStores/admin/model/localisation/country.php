<?php
class ModelLocalisationCountry extends Model {
	public function addCountry($data)
    {

        $status = $data['status'];
        $postcode_required = array_key_exists('postcode_required', $data) ? 1 : 0;

		$this->db->query("INSERT INTO " . DB_PREFIX . "country SET name = '" . $this->db->escape($data['countryLang1']) . "', iso_code_2 = '" . $this->db->escape($data['iso_code_2']) . "', iso_code_3 = '" . $this->db->escape($data['iso_code_3']) . "', address_format = '" . $this->db->escape($data['address_format']) . "', postcode_required = '" . $postcode_required . "', status = '" . $status . "'");


        $last_id = (int) $this->db->getLastId();

        foreach ($data['names'] as $lang_id => $name)
        {
            $this->db->query("INSERT INTO ". DB_PREFIX ."countries_locale SET country_id='{$last_id}', lang_id='{$lang_id}', name='{$name}'");
        }

		$this->cache->delete('country');

        return $last_id;
	}
	
	public function editCountry($country_id, $data) {
	    $name = null;

		// Get first language name
		foreach ( array_keys($data) as $key )
        {
            if ( preg_match('/countryLang.*/', $key) )
            {
                $name = $data[$key];
                break;
            }
        }

        $status = $data['status'];
        $postcode_required = array_key_exists('postcode_required', $data) ? 1 : 0;
        
		$this->db->query("UPDATE " . DB_PREFIX . "country SET name = '" . $this->db->escape($name) . "', iso_code_2 = '" . $this->db->escape($data['iso_code_2']) . "', iso_code_3 = '" . $this->db->escape($data['iso_code_3']) . "', address_format = '" . $this->db->escape($data['address_format']) . "', postcode_required = '" . $postcode_required . "', status = '" . $status . "', phonecode =".(int)$data['phonecode']." WHERE country_id = '" . (int)$country_id . "'");


        if($status != null && in_array($status, ['0','1', 1, 0])) {

            $this->db->query("UPDATE " . DB_PREFIX . "zone  SET status = $status WHERE country_id = $country_id ");
        }

		$this->cache->delete('country');
		$this->cache->delete('zone');
	}


	public function bulkUpdate($countries, $status) {

        $countries = implode(",", (array)$countries);

        $negative_status = $status == 1 ? 0 : 1;
        $this->db->query("UPDATE " . DB_PREFIX . "country SET status=$status WHERE country_id IN ($countries)");
        $this->db->query("UPDATE " . DB_PREFIX . "country SET status=$negative_status WHERE country_id NOT IN ($countries)");
        $this->db->query("UPDATE " . DB_PREFIX . "zone  SET status=$status WHERE country_id IN ($countries)");
        $this->db->query("UPDATE " . DB_PREFIX . "zone  SET status=$negative_status WHERE country_id NOT IN ($countries)");

        $this->cache->delete('country');
        $this->cache->delete('zone');

    }

	public function deleteCountry($country_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "country WHERE country_id = '" . (int)$country_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "zone WHERE country_id = '" . (int)$country_id . "'");

        $this->cache->delete('country');
        $this->cache->delete('zone');
	}
	
	public function getCountry($country_id) {
        $language_id = $this->config->get('config_language_id');
		$query = $this->db->query("SELECT cl.*, c.iso_code_2, c.iso_code_3, c.phonecode, c.status FROM " . DB_PREFIX . "country c JOIN countries_locale cl on c.country_id = cl.country_id
         WHERE 
            c.country_id = " . (int)$country_id . "
            AND lang_id = " . (int)$language_id );

		return $query->row;
    }

    public function getCountryByName($name) {

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "countries_locale WHERE 
            name = '{$name}'");

        return $query->row;
    }

    public function getCountryData($country_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = '{$country_id}'");

        return $query->row;
    }
    
    public function getCountryWithAllDetails($country_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "countries_locale as c_l
            join " . DB_PREFIX . "country as c on c.country_id=c_l.country_id WHERE 
            c.country_id = '{$country_id}'");

		return $query->row;
	}



	public function getCountries($data = array()) {
		if ($data) {
            $lang_id = $this->config->get('config_language_id');
            $sql = "SELECT  c.iso_code_2, c.phonecode, c.iso_code_3, cl.id, cl.name, cl.country_id, c.status FROM " .DB_PREFIX . "countries_locale as cl left join " . DB_PREFIX . "country as c on c.country_id = cl.country_id where  cl.lang_id = '" . $lang_id . "'";
			
			$sort_data = array(
				'name',
				'iso_code_2',
				'iso_code_3'
			);


             if (isset($data['country_id'])&&!empty($data['country_id']))
             {
                 $sql .= "and c.country_id = '$data[country_id]'";
             }
             if (isset($data['filter_name'])&&!empty($data['filter_name']))
            {
                $sql .= "and cl.name like '%$data[filter_name]%'";
            }
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY name";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}					

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}		

                $query= $this->db->query($sql);
            $country_data=$query->rows;


			return $country_data;
		} else {
            $country_data = $this->cache->get('admin_country');
            $country_language =  $this->cache->get('admin_country_language');
            $lang_id = $this->config->get('config_language_id');

			if (!$country_data || $lang_id != $country_language) {

                $sql = "SELECT  c.iso_code_2, c.iso_code_3, cl.id, cl.name, cl.country_id, c.status FROM " . DB_PREFIX . "countries_locale cl left join " . DB_PREFIX . "country c on c.country_id = cl.country_id WHERE 
                cl.lang_id = '" . $lang_id . "' and c.status ='1' ORDER BY name";


                //$sql = "SELECT * FROM " . DB_PREFIX . "countries_locale WHERE lang_id = '{$lang_id}' ORDER BY name";
				$query = $this->db->query($sql);
	
				$country_data = $query->rows;
                $this->cache->set('admin_country', $country_data);
                $this->cache->set('admin_country_language', $lang_id);
			}

			return $country_data;			
		}	
	}
	
	public function getTotalCountries() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "country");
		
		return $query->row['total'];
	}
        //ahmed
    public function getAllCountryLocale($country_id) {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "countries_locale WHERE country_id = '" . (int) $country_id . "' ORDER BY lang_id");
        return $query->rows;
    }
    
    public function getAllCountriesLocale($lang_id = 1) {
        $query = $this->db->query("SELECT  country_id, name FROM " . DB_PREFIX . "countries_locale WHERE lang_id = '" . (int) $lang_id . "' ORDER BY country_id");
        return $query->rows;
    }

    public function getCountryLocale($country_id, $lang_id) {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "countries_locale WHERE country_id = '" . (int) $country_id . "' AND lang_id = '" . $lang_id . "'");
        return $query->row;
    }


    public function getCountriesLocale($country_ids, $lang_id) {
        $query = $this->db->query
        (
            "SELECT name FROM " . DB_PREFIX . "countries_locale WHERE country_id IN ('" .implode("', '",$country_ids) . "') AND lang_id = '" . $lang_id . "'"
        );

        return $query->rows;
    }

    public function insertCountryLocale($country_id, $lang_id, $name) {
        $query = "INSERT INTO " . DB_PREFIX . "countries_locale (`country_id`, `lang_id`, `name`) VALUES ({$country_id}, {$lang_id}, '{$name}')";
        $this->db->query($query);
    }

    public function updateCountryLocale($country_id, $lang_id, $name) {
        $query = "UPDATE " . DB_PREFIX . "countries_locale SET name='" . $this->db->escape($name) . "' where country_id='{$country_id}' AND lang_id='{$lang_id}'";
        $this->db->query($query);
    }

    public function handler($start = 0, $length = 10, $lang_id = 1, $search = null, $orderColumn="name", $orderType="ASC")
    {
        $query = "SELECT  c.country_id, c.iso_code_2, c.iso_code_3, cl.name, c.status FROM " . DB_PREFIX . "country c inner join " . DB_PREFIX . "countries_locale cl on c.country_id = cl.country_id WHERE cl.lang_id = '" . $lang_id . "' ";
        
        $total = $totalFiltered = $this->db->query($query)->num_rows;

        $lang_id = $this->config->get('config_language_id');

        if (!empty($search)) {
            $query .= "AND (";
            $query .= "c.iso_code_2 LIKE '%" . $this->db->escape($search) . "%' ";
            $query .= "OR c.iso_code_3 LIKE '%" . $this->db->escape($search) . "%' ";
            $query .= "OR cl.name LIKE '%" . $this->db->escape($search) . "%')";
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        
        $rows = $this->db->query($query)->rows;

        $data = array(
            'data' => $rows,
            'total' => $total, 
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

    /**
     * Get country Info by country id and language id.
     *
     * @param int $countryId
     * @param int $languageId
     *
     * @return array|bool
     */
    public function getCountryByLanguageId($countryId, $languageId)
    {
        $query = [];

        $query[] = 'SELECT *,cl.name as locale_name FROM ' . DB_PREFIX . 'countries_locale as cl';
        $query[] = 'INNER JOIN ' . DB_PREFIX . 'country as c ON c.country_id=cl.country_id';
        $query[] = 'WHERE c.country_id = "' . $countryId . '"';
        $query[] = 'AND cl.lang_id = "' . $languageId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row;
        }

        return false;
    }

    public function getCountryIdByCountryCode($countryCode)
    {
        $query = [];

        $query[] = 'SELECT country_id FROM ' . DB_PREFIX . 'country as c ';
        $query[] = 'WHERE c.iso_code_2 in ("' . implode('", "', $countryCode) . '")';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function reset()
    {
        try {
            $table_names = ['countries_locale','country','zones_locale','zone'];
            return $this->resetTablesByName($table_names);
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * RESET TABLES TO DEFAULT BY NAME
     * @param array $table_names
     * @return bool
     */
    function resetTablesByName($table_names = array())
    {
        $query ="";
        try{
            foreach ($table_names as $table_name) {
                $file_path = $_SERVER['DOCUMENT_ROOT'] . "/database/".$table_name.".sql";
                if (!file_exists($file_path)) {
                    return false;
                }

                $query = "DROP TABLE IF EXISTS ".$table_name;
                $this->db->query($query);

                $file_content = file($file_path);

                $query = '';
                foreach ($file_content as $line) {
                    $endWith = substr(trim($line), -1, 1);
                    if (empty($line)) {
                        continue;
                    }

                    $query = $query . $line;
                    if ($endWith == ';') {
                        $this->db->query($query);
                        $query = '';
                    }
                }
            }
            return true;
        }catch (Exception $ex){
            return false;
        }
    }

    public function isCountryExist($name){
        $country_id =  $this->db->query("SELECT country_id FROM `" . DB_PREFIX . "country` WHERE `name` = '" . $this->db->escape($name) . "' ")->row['country_id'];
        return $country_id?: FALSE;
    }

    public function getCountriesInGeoZone($geo_zones_ids){
        $lang_id = $this->config->get('config_language_id');
        $sql = "SELECT c.country_id, c.iso_code_3, cl.name from country c
            join countries_locale  cl on cl.country_id = c.country_id            
            WHERE lang_id = " . (int) $lang_id;

        if(!empty($geo_zones_ids)){
            $sql .= " AND c.country_id IN (SELECT country_id FROM zone_to_geo_zone WHERE geo_zone_id IN(" . implode(',', $geo_zones_ids) . "))";
        }

        return $this->db->query($sql)->rows;
    }
}

?>
