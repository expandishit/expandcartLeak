<?php

class ModelModuleMartOption extends Model
{

    public function add_option($data){
        $this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = 'select', sort_order = '0'");

        $option_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '2', name = '" . $this->db->escape($data['name']) . "'");

        return $option_id;
    }

    public function add_option_value($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$data['option_id'] . "', image = 'no_image.jpg', sort_order = '0'");

        $option_value_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '2', option_id = '" . (int)$data['option_id'] . "', name = '" . $this->db->escape($data['attribute_name']) . "'");

        return $option_value_id;
    }

    public function add_product_option($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$data['product_id'] . "', option_id = '" . (int)$data['option_id'] . "', required = '0'");

        $product_option_id = $this->db->getLastId();

        return $product_option_id;
    }

    public function add_product_option_value($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$data['product_option_id'] . "', product_id = '" . (int)$data['product_id'] . "', option_id = '" . (int)$data['option_id'] . "', option_value_id = '" . (int)$data['option_value_id'] . "', quantity = '".(int)$data['quantity']."', subtract = '0', price = '0', price_prefix = '+', points = '0', points_prefix = '+', weight = '0', weight_prefix = '+'");

        $product_option_value_id = $this->db->getLastId();

        return $product_option_value_id;
    }

    public function checkOption($name) {
        $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "option_description WHERE name = '" . $name . "' ");

        return $query->row;
    }
    public function checkOptionValue($name,$option_id) {
        $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "option_value_description WHERE name = '" . $this->db->escape($name) . "' AND option_id = '".$option_id."' ");

        return $query->row;
    }
    public function checkProductOption($product_id,$option_id) {
        $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '".(int)$option_id."' ");

        return $query->row;
    }

    public function trancate_category_tables(){
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_description`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_path`");
        $this->db->query("TRUNCATE `" . DB_PREFIX . "category_to_store`");
    }
}
