<?php
use ExpandCart\Foundation\String\Slugify;

class ModelModuleMartCategory extends Model
{
    public function add_category($data){
        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "category SET category_id = '" . (int)$data['category_id'] . "', parent_id = '" . (int)$data['category_parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '0', sort_order = '0', status = '" . (int)$data['active'] . "', date_modified = NOW(), date_added = NOW()");

        if(isset($data['descriptions'])){
            $name = $data['descriptions']['name'];
        }else{
            $name = "Un Available";
        }

        $this->db->query("
                INSERT IGNORE INTO " . DB_PREFIX . "category_description SET
                category_id = '" . (int)$data['category_id']. "',
                language_id = '2',
                name = '" . $this->db->escape($name) . "',
                slug = '" . $this->db->escape((new Slugify)->slug($name)) . "',
                meta_keyword = '',
                meta_description = '',
                description = '" . $this->db->escape($name) . "'
			");

        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$data['category_id'] . "', store_id = '" . (int)0 . "'");

        $level = 0;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['category_parent_id'] . "' ORDER BY `level` ASC");

        foreach ($query->rows as $result) {
            $this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$data['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

            $level++;
        }

        $this->db->query("INSERT  IGNORE INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$data['category_id'] . "', `path_id` = '" . (int)$data['category_id'] . "', `level` = '" . (int)$level . "'");

    }

    public function trancate_category_tables(){
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_description`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_path`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_to_store`");
    }
}
