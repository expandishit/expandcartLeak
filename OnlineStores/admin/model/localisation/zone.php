<?php
class ModelLocalisationZone extends Model
{
    public function addZone($data)
    {
        $status = $data['status'];
        $this->db->query("INSERT INTO " . DB_PREFIX . "zone SET status = '" . (int) $status . "', name = '" . $this->db->escape($data['names']['1']) . "', code = '" . $this->db->escape($data['code']) . "', country_id = '" . (int) $data['country_id'] . "'");

        $last_id = (int) $this->db->getLastId();

        foreach ($data['names'] as $lang_id => $value) {
            $this->insertZoneLocale($last_id, $data['country_id'], $lang_id, $value);
        }

        $this->cache->delete('zone');
        return $last_id;
    }

    public function editZone($zone_id, $data)
    {
        $status = $data['status'];
        $this->db->query("UPDATE " . DB_PREFIX . "zone SET status = '" . $status . "', name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', country_id = '" . (int) $data['country_id'] . "' WHERE zone_id = '" . (int) $zone_id . "'");

        $this->cache->delete('zone');
    }

    public function deleteZone($zone_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int) $zone_id . "'");

        $this->cache->delete('zone');
    }

    public function deleteZoneLocals($zone_id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "zones_locale WHERE zone_id = '" . (int) $zone_id . "'");
    }

    public function deleteZoneArea($zone_id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "geo_area  WHERE zone_id = '" . (int) $zone_id . "'");
    }

    public function getZone($zone_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int) $zone_id . "'");

        return $query->row;
    }

    /**
     * Get zone name in particular language
     * Author: Bassem
     */
    public function getZoneInLanguage($zone_id, $lang_id = 1)
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

        $query = $this->db->query("SELECT z_l.name FROM " . DB_PREFIX . "zone as z
									join zones_locale as z_l
									on z.zone_id=z_l.zone_id
									WHERE z.zone_id = '" . (int) $zone_id . "' AND z_l.lang_id = '{$lang_id}' AND z.status = '1'");

        if ($query->num_rows > 0) {
            return $query->row;
        }
        return false;
    }

    public function getZones($data = array())
    {
        $sql = "SELECT *, z.name, c.name AS country FROM " . DB_PREFIX . "zone z LEFT JOIN " . DB_PREFIX . "country c ON (z.country_id = c.country_id)";

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

    public function getZonesLocalized()
    {
        return $this->db->query("SELECT `zone_id`, `name` FROM `" . DB_PREFIX . "zones_locale` WHERE `lang_id` = " . (int) $this->config->get('config_language_id'))->rows;
    }

    public function getZonesByCountryId($country_id, string $query = null)
    {

        $zone_data = $this->cache->get('admin_zone.' . (int) $country_id);
        $zone_language = $this->cache->get('admin_zone_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        if (!$zone_data || $lang_id != $zone_language || $query) {
            $query = $this->db->query("SELECT zones_locale.name, zone.name as en_name,zone.zone_id, zone.code  FROM " . DB_PREFIX . "zone
			join zones_locale on zone.zone_id=zones_locale.zone_id
			WHERE zone.country_id = '" . (int) $country_id . "' AND zone.status = '1'
            ". ($query ? " AND  zones_locale.name LIKE '%{$query}%' " : "") ."
			AND zones_locale.lang_id = '{$lang_id}'
			ORDER BY zones_locale.name");

            $zone_data = $query->rows;
            $this->cache->set('admin_zone.' . (int) $country_id, $zone_data);
            $this->cache->set('admin_zone_language.' . (int) $country_id, $lang_id);
        }

        return $zone_data;
    }


//to ge one zone to be select as value for select zone_to_geo_zone
    public function getZoneByCountryId($country_id,$zone_id)
    {

        $zone_data = $this->cache->get('admin_zone.' . (int) $country_id);
        $zone_language = $this->cache->get('admin_zone_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        if (!$zone_data || $lang_id != $zone_language) {


            $query = $this->db->query("SELECT zones_locale.name,zone.zone_id  FROM " . DB_PREFIX . "zone
			join zones_locale on zone.zone_id=zones_locale.zone_id
			WHERE zone.country_id = '" . (int) $country_id . "' AND zone.status = '1' and zone.zone_id='".$zone_id."'
			AND zones_locale.lang_id = '{$lang_id}'
			ORDER BY zones_locale.name");

            $zone_data = $query->rows;
            $this->cache->set('admin_zone.' . (int) $country_id, $zone_data);
            $this->cache->set('admin_zone_language.' . (int) $country_id, $lang_id);
        }

        return $zone_data;
    }

    public function getZoneByCountry($data)
    {


        $zone_data = $this->cache->get('admin_zone.' . (int) $country_id);
        $zone_language = $this->cache->get('admin_zone_language' . (int) $country_id);
        $lang_id = (int) $this->config->get('config_language_id') ?: 1;

        $sql="SELECT zones_locale.name,zone.zone_id  FROM " . DB_PREFIX . "zone
        join zones_locale on zone.zone_id=zones_locale.zone_id
			WHERE zone.status = '1' and zones_locale.lang_id = '{$lang_id}'
			";


        //we add fillter by country id
        if (isset($data['country_id'])&&!empty($data['country_id']))
        {
            $sql .= " and zone.country_id = '" .$data['country_id']. "' ";
        }
        //we add fillter by  'zone_id'
        if (isset($data['zone_id'])&&!empty($data['zone_id']))
        {
            $sql .= " and zone.zone_id= '" .$data['zone_id']. "' ";
        }
        //we add fillter by name
        if (isset($data['filter_name'])&&!empty($data['filter_name']))
        {
            $sql .= "and zones_locale.name like '%$data[filter_name]%'";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql_order=" ORDER BY zones_locale.name";
            $sql=$sql.$sql_order;

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];


        }

        if (!$zone_data || $lang_id != $zone_language) {


            $query = $this->db->query($sql);

            $zone_data = $query->rows;
            $this->cache->set('admin_zone.' . (int) $country_id, $zone_data);
            $this->cache->set('admin_zone_language.' . (int) $country_id, $lang_id);
        }



        return $zone_data;
    }
    /**
     * Get zone info using country id and language id.
     *
     * @param int $countryId
     * @param int $languageId
     *
     * @return array
     */
    public function getZonesByCountryIdAndLanguageId($countryId, $languageId)
    {
        $zone_data = $this->cache->get('zone.' . (int) $countryId);

        if (!$zone_data) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zones_locale WHERE
                country_id = '" . (int) $countryId . "' AND
                lang_id = '{$languageId}' ORDER BY name");

            $zone_data = $query->rows;

            $this->cache->set('zone.' . (int) $countryId, $zone_data);
        }

        return $zone_data;
    }

    public function getTotalZones()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone");

        return $query->row['total'];
    }

    public function getTotalZonesByCountryId($country_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone WHERE country_id = '" . (int) $country_id . "'");

        return $query->row['total'];
    }

    //ahmed
    public function getAllZoneLocale($zone_id)
    {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "zones_locale WHERE zone_id = '" . (int) $zone_id . "' ORDER BY lang_id");
        return $query->rows;
    }

    public function getZoneLocale($zone_id, $lang_id)
    {
        $query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "zones_locale WHERE zone_id = '" . (int) $zone_id . "' AND lang_id = '" . $lang_id . "'");
        return $query->row;
    }

    public function updateZoneLocale($zone_id, $lang_id, $name)
    {
        $zone_id = (int)$zone_id;
        $query = "UPDATE " . DB_PREFIX . "zones_locale SET name='" . $this->db->escape($name) . "' where zone_id='{$zone_id}' AND lang_id='{$lang_id}'";
        $this->db->query($query);
    }

    public function getZoneCountry($zone_id)
    {
        $query = $this->db->query("SELECT  country_id FROM " . DB_PREFIX . "zones_locale WHERE zone_id = '" . (int) $zone_id . "'");
        return $query->row['country_id'];
    }

    public function insertZoneLocale($zone_id, $country_id, $lang_id, $name)
    {
        // $query = "INSERT INTO " . DB_PREFIX . "zones_locale (`zone_id`,`country_id`, `lang_id`, `name`) VALUES ({$zone_id}, {$country_id}, {$lang_id}, '{$name}')";
        $name = $this->db->escape($name);
        $query = "INSERT INTO " . DB_PREFIX . "zones_locale SET zone_id='{$zone_id}', country_id='{$country_id}', lang_id='{$lang_id}', name='{$name}'";
        $this->db->query($query);
    }

    public function handler($start, $length, $lang_id = 1, $orderColumn, $orderType, $search = null)
    {
        $query = "select * from ";
        $query .= "(select set1.zone_id, set1.country_id, set1.zone_name, set1.code, cl.name as 'country_name', set1.status from "
            . "(select z.zone_id, z.country_id, z.code , z.status, zl.name as 'zone_name', zl.lang_id  from "
            . DB_PREFIX . "zone z inner join " . DB_PREFIX . "zones_locale zl on z.zone_id=zl.zone_id where zl.lang_id='" . $lang_id . "')set1 "
            . "left JOIN " . DB_PREFIX . "countries_locale cl on set1.country_id=cl.country_id where cl.lang_id='" . $lang_id . "')set2 ";
        $query .= "where 1=1 ";
        // $total = $this->db->query($query)->num_rows;
        $total = $totalFiltered = $this->db->query("SELECT COUNT(zone_id) AS total FROM `" . DB_PREFIX . "zone`;")->row['total'];

        if (!empty($search)) {
            $query .= "and set2.zone_name like '%" . $this->db->escape($search) . "%' ";
            $query .= "or set2.country_name like '%" . $this->db->escape($search) . "%' ";
            $query .= "or set2.code like '%" . $this->db->escape($search) . "%' ";
            // $totalFiltered = $this->db->query("SELECT COUNT(zone_id) AS total FROM " . $query_1)->row['total'];
        }
        // $totalFiltered = $this->db->query($query)->num_rows;
        // $query .= $query_1;

        $query .= " ORDER by set2.{$orderColumn} {$orderType}";
        $query .= " LIMIT " . $start . " ," . $length;

        $data = array('data' => $this->db->query($query)->rows, 'total' => $total, 'totalFiltered' => $totalFiltered);

        return $data;
    }

    public function getZonesByCountriesId($countriesZonesArray)
    {

        $countries = $countriesZonesArray['countries'];

        $langId = (empty($countriesZonesArray['langId'])) ? 1 : $countriesZonesArray['langId'];

        $query = "select zone_id,name from " . DB_PREFIX . "zones_locale where country_id in($countries) and lang_id = '$langId'";
        return $this->db->query($query)->rows;
    }

    public function isZoneExist($name){
        $zone_id =  $this->db->query("SELECT zone_id FROM `" . DB_PREFIX . "zone` WHERE `name` = '" . $this->db->escape($name) . "' ")->row['zone_id'];
        return $zone_id ?: FALSE;
    }

}
