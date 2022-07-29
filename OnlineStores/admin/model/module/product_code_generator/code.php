<?php
class ModelModuleProductCodeGeneratorCode extends Model {
    public function addCode($product_id,array $data) {
        if(is_array($data))
        {
            foreach ($data as $key=>$value){
                if(!empty($value) && $value != null)
                {
                    $this->db->execute("INSERT INTO " . DB_PREFIX . "product_code_generator SET product_id =?, code =?",[(int)$product_id,$this->db->escape(trim($value))]);
                }
            }
            return true;
        }
        return false;
    }


    public function editCode($code_id, array $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "product_code_generator SET product_id = '" . (int)$data['product_id'] . "', code = '" . $data['code'] . "' WHERE product_code_generator_id = '" . (int)$code_id . "'");

        return true;
    }

    public function deleteCode($code_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_code_generator WHERE 	product_code_generator_id = '" . (int)$code_id . "'");
        return true;
    }

    public function getCode($code_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE product_code_generator_id = '" . (int)$code_id . "' ");
        return $query->row;
    }


    public function getCodes($data = array()) {
        $sql = "SELECT c.product_code_generator_id,c.code,c.is_used,p.name,c.product_id  FROM " . DB_PREFIX . "product_code_generator c LEFT JOIN " . DB_PREFIX . "product_description p ON (c.product_id = p.product_id) WHERE c.is_used = 0 AND p.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND p.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'p.name',
            'c.code'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY p.name";
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
    }
    public function getArciveCodes($data = array()) {
        $sql = "SELECT c.product_code_generator_id,c.code,c.is_used,p.name,c.product_id  FROM " . DB_PREFIX . "product_code_generator c LEFT JOIN " . DB_PREFIX . "product_description p ON (c.product_id = p.product_id) WHERE c.is_used = 1 AND p.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND p.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'p.name',
            'c.code'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY p.name";
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
    }
    public function getTotalCodes($used = 0) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."product_code_generator WHERE is_used = ".$used);
        return $query->row['total'];
    }

    public function getTotalAvaliableCodesByProductId($product_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_code_generator WHERE product_id = '" . (int)$product_id . " AND is_used = 0'");
        return $query->row['total'];
    }

}
?>