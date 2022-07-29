<?php

class ModelLocalisationTaxClass extends Model
{
    public function addTaxClass($data)
    {
        $queryString = [];
        $queryString[] = 'INSERT INTO ' . DB_PREFIX . 'tax_class SET';
        $queryString[] = 'title = "' . $this->db->escape($data['title']) . '",';
        $queryString[] = 'description = "' . $this->db->escape($data['description']) . '",';
        $queryString[] = 'date_added = NOW()';

        $this->db->query(implode(' ', $queryString));

        $tax_class_id = $this->db->getLastId();

        if (isset($data['tax_rule'])) {
            foreach ($data['tax_rule'] as $tax_rule) {

                $queryString = [];
                $queryString[] = 'INSERT INTO ' . DB_PREFIX . 'tax_rule SET';
                $queryString[] = 'tax_class_id = "' . (int)$tax_class_id . '",';
                $queryString[] = 'tax_rate_id = "' . (int)$tax_rule['tax_rate_id'] . '",';
                $queryString[] = 'based = "' . $this->db->escape($tax_rule['based']) . '",';
                $queryString[] = 'priority = "' . (int)$tax_rule['priority'] . '"';

                $this->db->query(implode(' ', $queryString));
            }
        }

        $this->cache->delete('tax_class');
    }

    public function editTaxClass($tax_class_id, $data)
    {
        $queryString = [];

        $queryString[] = "UPDATE " . DB_PREFIX . "tax_class SET";
        $queryString[] = "title = '" . $this->db->escape($data['title']) . "',";
        $queryString[] = "description = '" . $this->db->escape($data['description']) . "',";
        $queryString[] = "date_modified = NOW()";
        $queryString[] = "WHERE tax_class_id = '" . (int)$tax_class_id . "'";

        $this->db->query(implode(' ', $queryString));

        if ( isset($data['tax_rule']) )
        {
            $this->db->query("DELETE FROM " . DB_PREFIX . "tax_rule WHERE tax_class_id = '" . (int) $tax_class_id ."'");

            foreach ($data['tax_rule'] as $tax_rule)
            {
                $queryString = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX . "tax_rule SET";
                $queryString[] = "tax_rate_id = '" . (int)$tax_rule['tax_rate_id'] . "',";
                $queryString[] = "based = '" . $this->db->escape($tax_rule['based']) . "',";
                $queryString[] = "priority = '" . (int)$tax_rule['priority'] . "',";
                $queryString[] = "tax_class_id = '" . (int)$tax_class_id . "'";

                $this->db->query(implode(' ', $queryString));
            }
        }

        $this->cache->delete('tax_class');
    }

    public function deleteTaxClass($tax_class_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "tax_class WHERE tax_class_id = '" . (int)$tax_class_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "tax_rule WHERE tax_class_id = '" . (int)$tax_class_id . "'");

        $this->cache->delete('tax_class');
    }

    public function getTaxClass($tax_class_id)
    {
        $queryString = "SELECT * FROM " . DB_PREFIX . "tax_class WHERE tax_class_id = '" . (int)$tax_class_id . "'";
        $query = $this->db->query($queryString);

        return $query->row;
    }

    public function getTaxClasses($data = null)
    {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "tax_class";

            $sql .= " ORDER BY title";

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
            $tax_class_data = $this->cache->get('tax_class');

            if (!$tax_class_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_class");

                $tax_class_data = $query->rows;

                $this->cache->set('tax_class', $tax_class_data);
            }

            return $tax_class_data;
        }
    }

    public function getTotalTaxClasses()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "tax_class");

        return $query->row['total'];
    }

    public function getTaxRules($tax_class_id)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "tax_rule WHERE tax_class_id = '" . (int)$tax_class_id . "'"
        );

        return $query->rows;
    }

    public function getTotalTaxRulesByTaxRateId($tax_rate_id)
    {
        $queryString = [];
        $queryString[] = "SELECT COUNT(DISTINCT tax_class_id) AS total FROM " . DB_PREFIX . "tax_rule";
        $queryString[] = "WHERE tax_rate_id = '" . (int)$tax_rate_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        return $query->row['total'];
    }



    /*
    * This method is used to update all/some products tax_class_id
    *
    */
    public function updateProductsTaxClassId($tax_class_id, $entity_type, $ids=[]){

        if($entity_type === 'product'){
            if(!$ids){ //empty or null
                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET tax_class_id = ". (int)$tax_class_id);
            }
            else{
                $this->db->query("UPDATE `" . DB_PREFIX . "product` SET tax_class_id = ". (int)$tax_class_id . " WHERE product_id IN (" . implode(',',$ids) . ");");
            }
        }
        elseif($entity_type === 'categoty'){
            $this->db->query("UPDATE `" . DB_PREFIX . "product` SET tax_class_id = ". (int)$tax_class_id . " WHERE product_id IN ( SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id IN(" . implode(',',$ids) . ") );");
        }
        

    }
}
