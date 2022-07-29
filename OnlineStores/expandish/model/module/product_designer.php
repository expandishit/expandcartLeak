<?php

class ModelModuleProductDesigner extends Model
{
    public function getProductImages($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->row;
    }

    public function getAllCategoryName() {
        $query = $this->db->query("SELECT caid,category_name,category_image FROM " . DB_PREFIX . "clipart_category WHERE status <> 0 order by category_name ASC");
        return $query->rows;
    }

    public function getClipArtImageByCategoryId($catId) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "clipart_image WHERE category_id = '" .(int)$catId . "'");
        return $query->rows;
    }
}
