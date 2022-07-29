<?php
class ModelModuleProductClassificationBrand extends Model {
    // define table name
    private $table = "pc_brand";

    public function addBrand(array $data) {
        if(is_array($data))
        {
            $queryString = $fields = [];
            $queryString[] = "INSERT INTO " . DB_PREFIX.$this->table . " SET";

            $fields[] = "status = '" . (int)$data['status'] . "'";

            $queryString[] = implode(',', $fields);

            $this->db->query(implode(' ', $queryString));

            $brand_id = $this->db->getLastId();

            foreach ($data['product_classification'] as $language_id => $value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "pc_brand_description SET pc_brand_id = '" . (int)$brand_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
            }
            return true;
        }
        return false;
    }


    public function editBrand($brand_id, array $data) {
        $queryString = $fields = [];
        $queryString[] = "UPDATE " . DB_PREFIX.$this->table . " SET";


        $fields[] = "status = '" . $data['status'] . "'";

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE pc_brand_id="' . $brand_id . '"';

        $this->db->query(implode(' ', $queryString));

        $this->db->query("DELETE FROM " . DB_PREFIX . "pc_brand_description WHERE pc_brand_id = '" . (int)$brand_id . "'");

        foreach ($data['product_classification'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "pc_brand_description SET pc_brand_id = '" . (int)$brand_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
        }
        return True;
    }

    public function deleteBrand($brand_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX.$this->table . " WHERE 	pc_brand_id = '" . (int)$brand_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "pc_brand_description WHERE pc_brand_id = '" . (int)$brand_id . "'");

        return true;
    }

    public function getBrand($brand_id) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX.$this->table  . " b LEFT JOIN " . DB_PREFIX . "pc_brand_description bd ON (b.pc_brand_id = bd.pc_brand_id) WHERE b.pc_brand_id = '" . (int)$brand_id . "' AND bd.language_id = '{$lang_id}'");

        return $query->row;
    }

    public function getBrandDescriptions($brand_id) {
        $brand_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_brand_description WHERE pc_brand_id = '" . (int)$brand_id . "'");

        foreach ($query->rows as $result) {
            $brand_data[$result['language_id']] = array(
                'name'        => $result['name'],
            );
        }

        return $brand_data;
    }


    public function getBrands($data = array()) {
        $sql = "SELECT b.pc_brand_id,b.status,bd.name  FROM " . DB_PREFIX .$this->table . " b LEFT JOIN " . DB_PREFIX . "pc_brand_description bd ON (b.pc_brand_id = bd.pc_brand_id) WHERE  bd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND bd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'bd.name',
            'b.pc_brand_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY bd.name";
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


    public function getTotalBrands() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX.$this->table. " ");
        return $query->row['total'];
    }

    public function getModelsByBrandId($brand_id) {
        $sql = "SELECT md.pc_model_id,md.name  FROM " . DB_PREFIX . " pc_relations r ";
        $sql  .=" INNER JOIN " . DB_PREFIX . "pc_model_description md ON (r.sub_id = md.pc_model_id)";
        $sql .= " AND  (r.parent_id = ".$brand_id.") ";
        $sql  .=" WHERE r.type='model' AND  md.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        return $query->rows;
    }


}
?>