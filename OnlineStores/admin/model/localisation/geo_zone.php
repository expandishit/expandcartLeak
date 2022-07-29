<?php
class ModelLocalisationGeoZone extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC") {
        $query = "SELECT * FROM " . DB_PREFIX . "geo_zone";
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(geo_zone.name like '%" . $this->db->escape($search) . "%'
                    OR geo_zone.description like '%" . $this->db->escape($search) . "%'
                	OR geo_zone.geo_zone_id like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        //$data = array_merge($this->db->query($query)->rows, array($totalFiltered));
        $data = array (
            'data' => $this->db->query($query)->rows,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        //$data = $this->db->query($query)->rows;
        return $data;
    }

	public function addGeoZone($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "geo_zone SET name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', date_added = NOW()");

		$geo_zone_id = $this->db->getLastId();
		
		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "zone_to_geo_zone SET country_id = '"  . (int)$value['country_id'] . "', zone_id = '"  . (int)$value['zone_id'] . "', area_id = '"  . (int)$value['area_id'] . "', geo_zone_id = '"  .(int)$geo_zone_id . "', date_added = NOW()");
			}
		}
		
		$this->cache->delete('geo_zone');
	}
	
	public function editGeoZone($geo_zone_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "geo_zone SET name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', date_modified = NOW() WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		$this->load->model('localisation/zone');
		
		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
                            //this block comment because we have change logic to save all zones zero value
//				if ($value['zone_id'] == '0') {
//					$zones = $this->model_localisation_zone->getZonesByCountryId($value['country_id']);
//					foreach ($zones as $zone)
//					{
//						$this->db->query("INSERT INTO " . DB_PREFIX . "zone_to_geo_zone SET country_id = '"  . (int)$value['country_id'] . "', zone_id = '"  . (int)$zone['zone_id'] . "', geo_zone_id = '"  .(int)$geo_zone_id . "', date_added = NOW()");
//					}
//				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "zone_to_geo_zone SET country_id = '"  . (int)$value['country_id'] . "', zone_id = '"  . (int)$value['zone_id'] . "', area_id = '"  . (int)$value['area_id'] . "', geo_zone_id = '"  .(int)$geo_zone_id . "', date_added = NOW()");
//				}
			}
		}
		
		$this->cache->delete('geo_zone');
	}
	
	public function deleteGeoZone($geo_zone_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone");
		
		if ( $query->num_rows <= 1 )
		{
			return false;
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		$this->cache->delete('geo_zone');
		return true;
	}
	
	public function getGeoZone($geo_zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		return $query->row;
	}

	public function getGeoZones($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "geo_zone";
	
			$sort_data = array(
				'name',
				'description'
			);	
			
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
			
			$query = $this->db->query($sql);
	
			return $query->rows;
		} else {
			$geo_zone_data = $this->cache->get('geo_zone');

			if (!$geo_zone_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name ASC");
	
				$geo_zone_data = $query->rows;
			
				$this->cache->set('geo_zone', $geo_zone_data);
			}
			
			return $geo_zone_data;				
		}
	}
	
	public function getTotalGeoZones() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "geo_zone");
		
		return $query->row['total'];
	}	
	
	public function getZoneToGeoZones($geo_zone_id) {
		$query = $this->db->query("SELECT * FROM " .DB_PREFIX ."zone_to_geo_zone WHERE geo_zone_id = '".(int)$geo_zone_id ."'");

		return $query->rows;	
	}
    public function getZoneToGeoZones2($geo_zone_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone,countries_locale as cl,zones_locale as  WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

        return $query->rows;
    }
    public function getTotalZoneToGeoZoneByGeoZoneId($geo_zone_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalZoneToGeoZoneByCountryId($country_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalZoneToGeoZoneByZoneId($zone_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}

    public function getGeozoneCountries($geo_zone_id)
    {
        $lang_id = $this->config->get('config_language_id');

        $query = $this->db->query("SELECT gz.country_id, gz.geo_zone_id, c.name FROM " . DB_PREFIX . "zone_to_geo_zone gz LEFT JOIN countries_locale c ON (c.country_id=gz.country_id) WHERE c.lang_id='".$lang_id."' AND gz.geo_zone_id ='".$geo_zone_id."' ORDER BY c.name ASC");
        return $query->rows;
    }
}
?>