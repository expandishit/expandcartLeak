<?php
class ModelLocalisationArea extends Model
{
    public function addArea($data)
    {
        $status = $data['status'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "geo_area 
            SET 
            status  = '" . (int) $status . "', 
            name    = '" . $this->db->escape($data['names']['1']) . "', 
            zone_id = '" . (int) $data['zone_id'] . "', 
            country_id = '" . (int) $data['country_id'] . "', 
            code    = '". $this->db->escape($data['code']) . "'");

        $last_id = (int) $this->db->getLastId();

        foreach ($data['names'] as $lang_id => $value) {
            $this->insertAreaLocale($last_id, $lang_id, $value);
        }

        $this->cache->delete('area');
    }

    public function editArea($area_id, $data)
    {
        $status = $data['status'];
        $this->db->query("UPDATE " . DB_PREFIX . "geo_area SET status = '" . $status . "', name = '" . $this->db->escape($data['name']['1']) . "', zone_id = '" . (int) $data['zone_id'] . "', country_id = '" . (int) $data['country_id'] . "',  code = '". $this->db->escape($data['code']) . "' WHERE area_id = '" . (int) $area_id . "'");

        $this->cache->delete('geo_area');
    }

    public function deleteArea($area_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "geo_area WHERE area_id = '" . (int) $area_id . "'");

        $this->cache->delete('geo_area');
    }

    public function deleteAreaLocal($area_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "geo_area_locale WHERE area_id = '" . (int) $area_id . "'");

        $this->cache->delete('geo_area');
    }

    public function getArea($area_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "geo_area WHERE area_id = '" . (int) $area_id . "'");

        return $query->row;
    }
    
    public function getAreasMuilteLangBasedOnZone($zone_id)
    {
        // we set t1.lang = 2 to get data in arabic and english
        
        $squery = "SELECT t1.area_id,t1.name as 'name_ar',t2.name as 'name_en'
                    FROM " . DB_PREFIX . " `geo_area_locale` t1 
                    INNER JOIN `geo_area_locale` t2 ON
                    t1.area_id = t2.area_id
                    INNER JOIN `geo_area` t3
                    ON t1.area_id = t3.area_id
                    WHERE t1.lang_id = 2 AND t3.zone_id = $zone_id
                    GROUP BY t1.name";
                  return $this->db->query($squery)->rows;
    }

    /**
     * Get geo_area name in particular language
     * Author: Bassem
     */
    public function getAreaInLanguage($area_id, $lang_id = 1)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE language_id =" . $lang_id);

        if ($query->num_rows == 0) {
            $lang_id = (int) $this->config->get('config_language_id');
        }

        // if($lang_id == 1){
        //     $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE language_id =".$lang_id);

        //     if($query->num_rows == 0){
        //     $query = $this->db->query("SELECT * FROM ".DB_PREFIX." language where code='".$this->config->get('config_language')."'");

        //     $lang_id = (int) $query->rows[0]['language_id'];
        //     }
        // }

        $query = $this->db->query("SELECT z_l.name FROM " . DB_PREFIX . "geo_area as z
									join geo_area_locale as z_l
									on z.area_id=z_l.area_id
									WHERE z.area_id = '" . (int) $area_id . "' AND z_l.lang_id = '{$lang_id}' AND z.status = '1'");

        if ($query->num_rows > 0) {
            return $query->row;
        }
        return false;
    }

    public function getAreas($data = array())
    {
        $sql = "SELECT *, z.name, c.name AS country FROM " . DB_PREFIX . "geo_area z LEFT JOIN " . DB_PREFIX . "country c ON (z.country_id = c.country_id)";

        $sort_data = array(
            'c.name',
            'z.name',
            'z.code',
        );

        $data = array(
            'filter_name' => trim($this->request->get['filter_name']),
            'country_id' => trim($this->request->get['coun_id']),
            'start' => 0,
            'limit' => 20


        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY c.name";
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


            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];


        }
        //we add fillter by country id
        if (isset($data['country_id'])&&!empty($data['country_id']))
        {
            $sql .= "z.country_id ='".$data['country_id']."'";
        }
        //we add fillter by name
        if (isset($data['filter_name'])&&!empty($data['filter_name']))
        {
            $sql .= "and cl.name like '%$data[filter_name]%'";
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getAreasLocalized()
    {
        return $this->db->query("SELECT `area_id`, `name` FROM `" . DB_PREFIX . "geo_area_locale` WHERE `lang_id` = " . (int) $this->config->get('config_language_id'))->rows;
    }

    public function getAreasByCountryId($country_id)
    {

        $geo_area_data = $this->cache->get('admin_geo_area.' . (int) $country_id);
        $geo_area_language = $this->cache->get('admin_geo_area_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        if (!$geo_area_data || $lang_id != $geo_area_language) {
            $query = $this->db->query("SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area
			join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.country_id = '" . (int) $country_id . "' AND geo_area.status = '1'
			AND geo_area_locale.lang_id = '{$lang_id}'
			ORDER BY geo_area_locale.name");

            $geo_area_data = $query->rows;
            $this->cache->set('admin_geo_area.' . (int) $country_id, $geo_area_data);
            $this->cache->set('admin_geo_area_language.' . (int) $country_id, $lang_id);
        }

        return $geo_area_data;
    }


//to ge one geo_area to be select as value for select geo_area_to_geo_geo_area
    public function getAreaByCountryId($country_id,$area_id)
    {

        $geo_area_data = $this->cache->get('admin_geo_area.' . (int) $country_id);
        $geo_area_language = $this->cache->get('admin_geo_area_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        if (!$geo_area_data || $lang_id != $geo_area_language) {


            $query = $this->db->query("SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area
			join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.country_id = '" . (int) $country_id . "' AND geo_area.status = '1' and geo_area.area_id='".$area_id."'
			AND geo_area_locale.lang_id = '{$lang_id}'
			ORDER BY geo_area_locale.name");

            $geo_area_data = $query->rows;
            $this->cache->set('admin_geo_area.' . (int) $country_id, $geo_area_data);
            $this->cache->set('admin_geo_area_language.' . (int) $country_id, $lang_id);
        }

        return $geo_area_data;
    }

    public function getAreaByCountry($data)
    {


        $geo_area_data = $this->cache->get('admin_geo_area.' . (int) $country_id);
        $geo_area_language = $this->cache->get('admin_geo_area_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        $sql="SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area
        join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.status = '1' and geo_area_locale.lang_id = '{$lang_id}'
			";


        //we add fillter by country id
        if (isset($data['country_id'])&&!empty($data['country_id']))
        {
            $sql .= " and geo_area.country_id = '" .$data['country_id']. "' ";
        }
        //we add fillter by  'area_id'
        if (isset($data['area_id'])&&!empty($data['area_id']))
        {
            $sql .= " and geo_area.area_id= '" .$data['area_id']. "' ";
        }
        //we add fillter by name
        if (isset($data['filter_name'])&&!empty($data['filter_name']))
        {
            $sql .= "and geo_area_locale.name like '%$data[filter_name]%'";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql_order=" ORDER BY geo_area_locale.name";
            $sql=$sql.$sql_order;

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];


        }

        if (!$geo_area_data || $lang_id != $geo_area_language) {


            $query = $this->db->query($sql);

            $geo_area_data = $query->rows;
            $this->cache->set('admin_geo_area.' . (int) $country_id, $geo_area_data);
            $this->cache->set('admin_geo_area_language.' . (int) $country_id, $lang_id);
        }



        return $geo_area_data;
    }
    /**
     * Get geo_area info using country id and language id.
     *
     * @param int $countryId
     * @param int $languageId
     *
     * @return array
     */
    public function getAreasByZoneIdAndLanguageId($zone_id, $lang_id = null)
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
 
    public function getAreaByZone($data)
    {

        $area_data = $this->cache->get('area.' . (int)$zone_id);
		$area_language =  $this->cache->get('area_language' . (int)$zone_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        $sql= "SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area 
			join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.status = '1' 
			AND geo_area_locale.lang_id = '{$lang_id}'";


        //we add fillter by zone id
        if (isset($data['zone_id'])&&!empty($data['zone_id']))
        {
            $sql .= " and geo_area.zone_id  = '" .$data['zone_id']. "' ";
        }
        //we add fillter by  'area_id'
        if (isset($data['area_id'])&&!empty($data['area_id']))
        {
            $sql .= " and geo_area.area_id= '" .$data['area_id']. "' ";
        }
        //we add fillter by name
        if (isset($data['filter_name'])&&!empty($data['filter_name']))
        {
            $sql .= "and geo_area_locale.name like '%$data[filter_name]%'";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql_order=" ORDER BY geo_area_locale.name";
            $sql=$sql.$sql_order;

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];

        }

        if (!$area_data || $lang_id != $area_language) {


            $query = $this->db->query($sql);

            $area_data = $query->rows;
			$this->cache->set('area.' . (int)$zone_id, $area_data);
			$this->cache->set('area_language.' . (int)$zone_id, $lang_id);
        }



        return $area_data;
    }
   
    
    public function getTotalAreas()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "geo_area");

        return $query->row['total'];
    }

    public function getTotalAreasByCountryId($country_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "geo_area WHERE country_id = '" . (int) $country_id . "'");

        return $query->row['total'];
    }
    
    public function getTotalAreasByZoneId($zone_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "geo_area WHERE zone_id = '" . (int) $zone_id . "'");

        return $query->row['total'];
    }

    //ahmed
    public function getAllAreaLocale($area_id)
    {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "geo_area_locale WHERE area_id = '" . (int) $area_id . "' ORDER BY lang_id");
        return $query->rows;
    }

    public function getAreaLocale($area_id, $lang_id)
    {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "geo_area_locale WHERE area_id = '" . (int) $area_id . "' AND lang_id = '" . $lang_id . "'");
        return $query->row;
    }

    public function updateAreaLocale($area_id, $lang_id, $name)
    {
        $query = "UPDATE " . DB_PREFIX . "geo_area_locale SET name='" . $this->db->escape($name) . "' where area_id='{$area_id}' AND lang_id='{$lang_id}'";
        $this->db->query($query);
    }

    public function getAreaCountry($area_id)
    {
        $query = $this->db->query("SELECT  country_id FROM " . DB_PREFIX . "geo_area WHERE area_id = '" . (int) $area_id . "'");
        return $query->row['country_id'];
    }

    public function insertAreaLocale($area_id, $lang_id, $name)
    {
        $area_id = (int)$area_id;
        $lang_id = (int)$lang_id;
        $name = $this->db->escape($name);
        $query = "INSERT INTO " . DB_PREFIX . "geo_area_locale SET area_id = {$area_id}, lang_id='{$lang_id}', name='{$name}'";
        $this->db->query($query);
    }

    public function handler($start, $length, $lang_id = 1, $orderColumn, $orderType, $search = null)
    {
                $query = "select * from ";
                $query .= "(select set1.zone_id, set1.area_id,set1.area_name, zl.name as 'zone_name', set1.status, set1.code from "
            . "(select a.zone_id, a.area_id , a.status, al.name as 'area_name', al.lang_id , a.code from "
            . DB_PREFIX . "geo_area a inner join " . DB_PREFIX . "geo_area_locale al on a.area_id=al.area_id where al.lang_id='" . $lang_id . "')set1 "
            . "left JOIN " . DB_PREFIX . "zones_locale zl on set1.zone_id=zl.zone_id where zl.lang_id='" . $lang_id . "')set2 ";
        $query .= "where 1=1 ";

	$total_query =  "SELECT COUNT(*) AS count FROM (".$query.' GROUP BY area_id) AS mt';
        $total = $totalFiltered = $this->db->query($total_query)->row['count'];//"SELECT COUNT(area_id) AS total FROM `" . DB_PREFIX . "geo_area`;")->row['total'];

        if (!empty($search)) {
            $query .= "and set2.zone_name like '%" . $this->db->escape($search) . "%' ";
            $query .= "or set2.area_name like '%" . $this->db->escape($search) . "%' ";
            // $totalFiltered = $this->db->query("SELECT COUNT(area_id) AS total FROM " . $query_1)->row['total'];
        }
        // $totalFiltered = $this->db->query($query)->num_rows;
        // $query .= $query_1;

		$query .= " GROUP BY area_id";
        $query .= " ORDER by set2.{$orderColumn} {$orderType}";
        $query .= " LIMIT " . $start . " ," . $length;

        $data = array('data' => $this->db->query($query)->rows, 'total' => $total, 'totalFiltered' => $totalFiltered);

        return $data;
    }

    public function getAreasByZonesId($zones, $lang_id)
    {
        return $this->db->query("
            SELECT ga.area_id , gal.`name` , ga.code
            FROM `" . DB_PREFIX . "geo_area` ga
            JOIN `" . DB_PREFIX . "geo_area_locale` gal
                ON ga.area_id = gal.area_id
            WHERE ga.zone_id in(" . implode(',', $zones) .") and gal.lang_id = " . (int)$lang_id)->rows;
    }

    public function isAreaExist($name){
        $area =  $this->db->query("SELECT * FROM `" . DB_PREFIX . "geo_area` WHERE `name` = '" . $this->db->escape($name) . "' ")->row;
        return $area ?: FALSE;
    }
    
    public function getAreaByZoneId($zone_id, string $query = null)
    {
        $cache_key = 'area.' . (int)$zone_id . ($query ? ('.' . $query) : '');

        $area_data = $this->cache->get($cache_key);

        $area_language =  $this->cache->get('area_language' . (int)$zone_id);

        $lang_id = (int) $this->config->get('config_language_id');

        if (!$area_data || $lang_id != $area_language) {
            $query = $this->db->query("SELECT geo_area_locale.name,geo_area.area_id  FROM " . DB_PREFIX . "geo_area 
			join geo_area_locale on geo_area.area_id=geo_area_locale.area_id
			WHERE geo_area.zone_id = '" . (int)$zone_id . "' AND geo_area.status = '1'
            " . ($query ? " AND  geo_area_locale.name LIKE '%{$query}%' " : "") . "
			AND geo_area_locale.lang_id = '{$lang_id}'
			ORDER BY geo_area_locale.name");

            $area_data = $query->rows;
            $this->cache->set($cache_key, $area_data);
            $this->cache->set('area_language.' . (int)$zone_id, $lang_id);
        }

        return $area_data;
    }
}
