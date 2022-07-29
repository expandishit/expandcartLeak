<?php
class ModelModuleProductClassificationModel extends Model {
    // define table name
    private $table = "pc_model";


    public function getModel($model_id) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX.$this->table  . " b LEFT JOIN " . DB_PREFIX . "pc_model_description bd ON (b.pc_model_id = bd.pc_model_id) WHERE b.pc_model_id = '" . (int)$model_id . "' AND bd.language_id = '{$lang_id}'");

        return $query->row;
    }

    public function getModelDescriptions($model_id) {
        $model_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pc_model_description WHERE pc_model_id = '" . (int)$model_id . "'");

        foreach ($query->rows as $result) {
            $model_data[$result['language_id']] = array(
                'name'        => $result['name'],
            );
        }

        return $model_data;
    }
    public function getModelBrand($model_id) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

        $query = $this->db->query("SELECT bd.name,bd.pc_brand_id FROM " . DB_PREFIX . "pc_relations r  LEFT JOIN " . DB_PREFIX . "pc_brand_description bd ON (bd.pc_brand_id = r.parent_id ) WHERE r.sub_id = '" . (int)$model_id . "' AND r.type = 'model' AND bd.language_id = '{$lang_id}' ");

        return $query->row;

    }
    public function getModelYearsIds($model_id) {
        $model_years = array();

        $query = $this->db->query("SELECT y.pc_year_id FROM " . DB_PREFIX . "pc_relations r  LEFT JOIN " . DB_PREFIX . "pc_year y ON (y.pc_year_id = r.parent_id ) WHERE r.sub_id = '" . (int)$model_id . "' AND r.type = 'year'");

        foreach ($query->rows as $result) {
            $model_years[] = $result['pc_year_id'];
        }

        return $model_years;
    }

    public function getModels($data = array()) {
        $sql = "SELECT b.pc_model_id,b.status,bd.name  FROM " . DB_PREFIX .$this->table . " b LEFT JOIN " . DB_PREFIX . "pc_model_description bd ON (b.pc_model_id = bd.pc_model_id) WHERE  bd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND bd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'bd.name',
            'b.pc_model_id'
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

    public function getTotalModels() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX.$this->table. " ");
        return $query->row['total'];
    }
    public function getYearsByModelId($model_id) {
        $sql = "SELECT y.pc_year_id,y.name  FROM " . DB_PREFIX . " pc_relations r ";
        $sql  .=" INNER JOIN " . DB_PREFIX . "pc_year y ON (r.parent_id = y.pc_year_id)";
        $sql .= " AND  (r.sub_id = ".(int)$model_id." ) ";
        $sql  .=" WHERE r.type='year' GROUP BY y.pc_year_id";
        $query = $this->db->query($sql);

        return $query->rows;
    }


}
?>