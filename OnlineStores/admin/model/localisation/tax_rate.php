<?php

class ModelLocalisationTaxRate extends Model
{
    public function addTaxRate($data)
    {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $queryString = [];
        $queryString[] = "INSERT INTO " . DB_PREFIX . "tax_rate SET";
        $queryString[] = "name = '" . $this->db->escape($data['tax_rate_name'][$language_id]) . "',";
        $queryString[] = "rate = '" . (float)$data['rate'] . "',";
        $queryString[] = "`type` = '" . $this->db->escape($data['type']) . "',";
        $queryString[] = "geo_zone_id = '" . (int)$data['geo_zone_id'] . "',";
        $queryString[] = "date_added = NOW(),";
        $queryString[] = "date_modified = NOW()";

        $this->db->query(implode(' ', $queryString));

        $tax_rate_id = $this->db->getLastId();

        foreach ($data['tax_rate_name'] as $language_id => $value) {
            $this->db->query("
                    INSERT INTO " . DB_PREFIX . "tax_rate_description SET
                    tax_rate_id = '" . (int)$tax_rate_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value) . "'
                ");
        }

        if (isset($data['tax_rate_customer_group'])) {
            foreach ($data['tax_rate_customer_group'] as $customer_group_id) {

                $queryString = [];
                $queryString[] = 'INSERT INTO ' . DB_PREFIX . 'tax_rate_to_customer_group SET';
                $queryString[] = 'tax_rate_id = "' . (int)$tax_rate_id . '",';
                $queryString[] = 'customer_group_id = "' . (int)$customer_group_id . '"';

                $this->db->query(implode(' ', $queryString));
            }
        }
    }

    public function editTaxRate($tax_rate_id, $data)
    {

        $language_id = $this->config->get('config_language_id') ?: 1;

        $queryString = [];
        $queryString[] = "UPDATE " . DB_PREFIX . "tax_rate SET";
        $queryString[] = "name = '" . $this->db->escape($data['tax_rate_name'][$language_id]) . "',";
        $queryString[] = "rate = '" . (float)$data['rate'] . "',";
        $queryString[] = "`type` = '" . $this->db->escape($data['type']) . "',";
        $queryString[] = "geo_zone_id = '" . (int)$data['geo_zone_id'] . "',";
        $queryString[] = "date_modified = NOW()";
        $queryString[] = "WHERE tax_rate_id = '" . (int)$tax_rate_id . "'";
        $this->db->query(implode(' ', $queryString));

        $this->db->query("DELETE FROM " . DB_PREFIX . "tax_rate_description WHERE tax_rate_id = '" . (int)$tax_rate_id . "'");

        foreach ($data['tax_rate_name'] as $language_id => $value) {
            $this->db->query("
                    INSERT INTO " . DB_PREFIX . "tax_rate_description SET
                    tax_rate_id = '" . (int)$tax_rate_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value) . "'
                ");
        }


        if (isset($data['tax_rate_customer_group'])) {
            // Delete all customer groups attacted to that tax rate
            $queryString="DELETE from ". DB_PREFIX ."tax_rate_to_customer_group ";
            $queryString.= ' WHERE tax_rate_id = "' . (int)$tax_rate_id . '";';    
            $this->db->query($queryString);
            foreach ($data['tax_rate_customer_group'] as $customer_group_id) {
                $queryString = 'INSERT INTO ' . DB_PREFIX . 'tax_rate_to_customer_group SET';
                $queryString.= ' tax_rate_id = "' . (int)$tax_rate_id . '",';
                $queryString.= ' customer_group_id = "' . (int)$customer_group_id . '"';
                $this->db->query($queryString);
            }
        }
        else{
             // Delete all customer groups attacted to that tax rate
             $queryString="DELETE from ". DB_PREFIX ."tax_rate_to_customer_group ";
             $queryString.= ' WHERE tax_rate_id = "' . (int)$tax_rate_id . '";';    
             $this->db->query($queryString);
        }
    }

    public function deleteTaxRate($tax_rate_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "tax_rate WHERE tax_rate_id = '" . (int)$tax_rate_id . "'");
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "tax_rate_to_customer_group WHERE tax_rate_id = '" . (int)$tax_rate_id . "'"
        );
        $this->db->query("DELETE FROM " . DB_PREFIX . "tax_rate_description WHERE tax_rate_id = '" . (int)$tax_rate_id . "'");
    }

    public function getTaxRate($tax_rate_id)
    {
        $language_id = $this->config->get('config_language_id') ?: 1;

        $queryString = $fields = [];

        $fields[] = 'tr.tax_rate_id';
        $fields[] = 'tr_desc.name AS name';
        $fields[] = 'tr.rate';
        $fields[] = 'tr.type';
        $fields[] = 'tr.geo_zone_id';
        $fields[] = 'gz.name AS geo_zone';
        $fields[] = 'tr.date_added';
        $fields[] = 'tr.date_modified';

        $queryString[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . DB_PREFIX . 'tax_rate tr';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'geo_zone gz';
        $queryString[] = 'ON (tr.geo_zone_id = gz.geo_zone_id)';
        $queryString[] = 'LEFT JOIN '. DB_PREFIX . 'tax_rate_description tr_desc';
        $queryString[] = 'ON tr_desc.language_id = '.$language_id;
        $queryString[] = 'WHERE tr.tax_rate_id = "' . (int)$tax_rate_id . '"';

        $query = $this->db->query(implode(' ', $queryString));
        return $query->row;
    }

    public function getTaxRates($data = array())
    {
        $queryString = $fields = [];

        $fields[] = 'tr.tax_rate_id';
        $fields[] = 'trd.name AS name';
        $fields[] = 'tr.rate';
        $fields[] = 'tr.type';
        $fields[] = 'tr.geo_zone_id';
        $fields[] = 'gz.name AS geo_zone';
        $fields[] = 'tr.date_added';
        $fields[] = 'tr.date_modified';

        $queryString[] = 'SELECT ' . implode(', ', $fields) . ' FROM ' . DB_PREFIX . 'tax_rate tr';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'geo_zone gz';
        $queryString[] = 'ON (tr.geo_zone_id = gz.geo_zone_id)';

        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'tax_rate_description trd';
        $queryString[] = 'ON (tr.tax_rate_id = trd.tax_rate_id)';
        $queryString[] = 'WHERE trd.language_id = ' . (int)$this->config->get('config_language_id');
                
        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function getTaxRateIdByName($name){
        return $this->db->query("SELECT tr.tax_rate_id 
            FROM tax_rate tr
            JOIN tax_rate_description trd ON tr.tax_rate_id = trd.tax_rate_id
            WHERE trd.name = '" . $this->db->escape($name) . "';")->row['tax_rate_id'] ?? 0;
    }


    public function getTaxRateCustomerGroups($tax_rate_id)
    {
        $tax_customer_group_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'tax_rate_to_customer_group';
        $queryString[] = 'WHERE tax_rate_id = "' . (int)$tax_rate_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            $tax_customer_group_data[] = $result['customer_group_id'];
        }

        return $tax_customer_group_data;
    }

    public function getTotalTaxRates()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "tax_rate");

        return $query->row['total'];
    }

    public function getTotalTaxRatesByGeoZoneId($geo_zone_id)
    {
        $queryString = [];
        $queryString[] = 'ELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'tax_rate';
        $queryString[] = 'WHERE geo_zone_id = "' . (int)$geo_zone_id . '"';
        $query = $this->db->query(implode(' ', $queryString));

        return $query->row['total'];
    }


    public function getTaxRateDescriptions($tax_rate_id)
    {
        $tax_rate_description = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_rate_description WHERE tax_rate_id = '" . (int)$tax_rate_id . "'");

        foreach ($query->rows as $result) {
            $tax_rate_description[$result['language_id']] = $result['name'];
        }

        return $tax_rate_description;
    }
}
