<?php
class ModelModuleProductClassificationBrand extends Model {
    // define table name
    private $table = "pc_brand";


    public function getBrands($data = array()) {
        $sql = "SELECT b.pc_brand_id,b.status,bd.name  FROM " . DB_PREFIX .$this->table . " b LEFT JOIN " . DB_PREFIX . "pc_brand_description bd ON (b.pc_brand_id = bd.pc_brand_id) WHERE b.status = 1 AND  bd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        return $query->rows;
    }



    public function getModelsByBrandId($brand_id) {
        $sql = "SELECT md.pc_model_id,md.name,m.status  FROM " . DB_PREFIX . " pc_relations r ";
        $sql  .=" INNER JOIN " . DB_PREFIX . "pc_model_description md ON (r.sub_id = md.pc_model_id)";
        $sql  .=" INNER JOIN " . DB_PREFIX . "pc_model m ON (m.pc_model_id = md.pc_model_id)";
        $sql .= " AND  (r.parent_id = ".$brand_id.") ";
        $sql  .=" WHERE m.status = 1 AND r.type='model' AND  md.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);


        return $query->rows;
    }


}
?>