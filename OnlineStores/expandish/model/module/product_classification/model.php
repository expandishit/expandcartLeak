<?php
class ModelModuleProductClassificationModel extends Model {
    // define table name
    private $table = "pc_model";

    public function getYearsByModelId($model_id) {
        $sql = "SELECT y.pc_year_id,y.name,y.status  FROM " . DB_PREFIX . " pc_relations r ";
        $sql  .=" INNER JOIN " . DB_PREFIX . "pc_year y ON (r.parent_id = y.pc_year_id)";
        $sql .= " AND  (r.sub_id = ".$model_id.") ";
        $sql  .=" WHERE y.status = 1 AND r.type='year' ";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModels($data = array()) {
        $sql = "SELECT b.pc_model_id,b.status,bd.name  FROM " . DB_PREFIX .$this->table . " b LEFT JOIN " . DB_PREFIX . "pc_model_description bd ON (b.pc_model_id = bd.pc_model_id) WHERE  bd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        return $query->rows;
    }


}
?>