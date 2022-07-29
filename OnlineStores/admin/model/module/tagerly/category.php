<?php
use ExpandCart\Foundation\String\Slugify;

class ModelModuleTagerlyCategory extends Model
{
    public function add_category($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "category SET category_id = '" . (int)$data['ID'] . "', parent_id = '0', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '0', sort_order = '0', status = '1', date_modified = NOW(), date_added = NOW()");

        $categoryDescData[1] = $data['Name'];
        $categoryDescData[2] = $data['Name'];

        foreach ($categoryDescData as $key=>$catData){
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "category_description SET
                category_id = '" . (int)$data['ID']. "',
                language_id = $key,
                name = '" . $this->db->escape($catData) . "',
                slug = '" . $this->db->escape((new Slugify)->slug($catData)) . "',
                meta_keyword = '',
                meta_description = '',
                description = '" . $this->db->escape($catData) . "'
			");
        }


        $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$data['ID'] . "', store_id = '" . (int)0 . "'");

        $level = 0;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)0 . "' ORDER BY `level` ASC");

        foreach ($query->rows as $result) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$data['ID'] . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

            $level++;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$data['ID'] . "', `path_id` = '" . (int)$data['ID'] . "', `level` = '" . (int)$level . "'");

    }

    public function checkCategoryExists($category_id) {
        $query = $this->db->query("SELECT count(category_id) as total FROM  " . DB_PREFIX . "category WHERE category_id = '" . $category_id . "' ");

        return ($query->row['total'] > 0) ? TRUE : FALSE;
    }

    public function trancate_category_tables(){
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_description`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_path`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_to_store`");
    }
}
