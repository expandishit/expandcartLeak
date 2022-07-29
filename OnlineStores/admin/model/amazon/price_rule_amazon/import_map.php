<?php

class ModelAmazonPriceRuleAmazonImportMap extends Model {

  public function getPriceRules($type) {

     $sql_query = '';

     $sql_query = "SELECT * FROM " . DB_PREFIX . "amazon_price_rules WHERE price_status = 1 AND rule_type='".$type."'";

     $query = $this->db->query($sql_query);

     return $query->rows;
  }

  public function getPriceRule($rule_product_id, $type) {
    $price_map_rules = $this->db->query("SELECT * FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$rule_product_id." AND rule_type='".$type."'");
    return $price_map_rules->row;
  }

  public function getMapProduct($rule_product_id,$type) {
    $price_map_rules = $this->db->query("SELECT rule_product_id FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$rule_product_id." AND rule_type='".$type."'");

    return $price_map_rules->num_rows;
  }

  public function updateRuleMapProduct($data) {

    if($this->_upadateProductPrice($data)) {
        $this->db->query("INSERT INTO ".DB_PREFIX."amazon_price_rules_map_product SET rule_product_id = '" . (int)$data['product_id'] . "', change_price = '" . (float)$data['price_change'] . "',change_type = '" . (int)$data['change_type'] . "', source = '" . $this->db->escape($data['source']) . "',rule_id = '" . (int)$data['rule_id'] . "', rule_type='".$data['rule_type']."'");
    }
  }
  public function updateQuantityRuleMapProduct($data) {

    if($this->_upadateProductQauntity($data)) {
        $this->db->query("INSERT INTO ".DB_PREFIX."amazon_price_rules_map_product SET rule_product_id = '" . (int)$data['product_id'] . "', change_price = '" . (float)$data['price_change'] . "',change_type = '" . (int)$data['change_type'] . "', source = '" . $this->db->escape($data['source']) . "',rule_id = '" . (int)$data['rule_id'] . "', rule_type='".$data['rule_type']."'");
    }
  }
  public function _upadateProductQauntity($data) {
    $status = $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '".(int)$data['price']."' WHERE product_id = ".(int)$data['product_id']."");
    return $status;

  }

  public function _upadateProductPrice($data) {
    $status = $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '".(float)$data['price']."' WHERE product_id = ".(int)$data['product_id']."");
    return $status;

  }

  public function getPrice($product_id) {
    $price = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
    return $price->row['price'];

  }
  public function getQuantity($product_id) {
    $price = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
    return $price->row['quantity'];

  }

  public function deleteEntry($product_id) {
    $status = $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$product_id."");
    return $status;
  }

}
