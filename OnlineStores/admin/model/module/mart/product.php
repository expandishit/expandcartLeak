<?php
use ExpandCart\Foundation\String\Slugify;
class ModelModuleMartProduct extends Model
{

    public function add_product($data){
        $insertQuery = $insertFields = [];
        $insertQuery[] = "INSERT INTO " . DB_PREFIX . "product SET";
        $insertFields[] = "product_id = '" . (int)$data['product_id'] . "'";
        $insertFields[] = "model = '" . $this->db->escape($data['model']) . "'";
        $insertFields[] = "sku = '" . $this->db->escape($data['sku']) . "'";
        $insertFields[] = "upc = '" . $this->db->escape($data['upc']) . "'";
        $insertFields[] = "ean = '" . $this->db->escape($data['ean']) . "'";
        $insertFields[] = "jan = '" . $this->db->escape($data['jan']) . "'";
        $insertFields[] = "isbn = '" . $this->db->escape($data['isbn']) . "'";
        $insertFields[] = "mpn = '" . $this->db->escape($data['mpn']) . "'";
        $insertFields[] = "location = '" . $this->db->escape($data['location']) . "'";
        $insertFields[] = "quantity = '" . (int)$data['quantity'] . "'";
        $insertFields[] = "minimum = '" . (int)$data['minimum'] . "'";
        $insertFields[] = "preparation_days = '" . (int)$data['preparation_days'] . "'";
        $insertFields[] = "maximum = '" . (int)$data['maximum'] . "'";
        $insertFields[] = "subtract = '" . (int)$data['subtract'] . "'";
        $insertFields[] = "notes = '" . $this->db->escape($data['notes']) . "'";
        $insertFields[] = "barcode = '" . $this->db->escape($data['barcode']) . "'";
        $insertFields[] = "stock_status_id = '" . (int)$data['stock_status_id'] . "'";
        $insertFields[] = "date_available = '" . $this->db->escape($data['date_available']) . "'";
        $insertFields[] = "manufacturer_id = '" . (int)$data['manufacturer_id'] . "'";
        $insertFields[] = "shipping = '" . (int)$data['shipping'] . "'";
        $insertFields[] = "image = '" . $data['image'] . "'";

        if (isset($data['storable']) && (int)$data['storable'] === 1) {
            $insertFields[] = "storable = 1";
        }

        $insertFields[] = "price = '" . (float)$data['price'] . "'";
        if (isset($data['minimum_deposit_price'])){
            $insertFields[] = "minimum_deposit_price = '" . (float)$data['minimum_deposit_price'] . "'";
        }
        $insertFields[] = "cost_price = '" . (float)$data['cost_price'] . "'";
        $insertFields[] = "points = '" . (int)$data['points'] . "'";
        $insertFields[] = "weight = '" . (float)$data['weight'] . "'";
        $insertFields[] = "weight_class_id = '" . (int)$data['weight_class_id'] . "'";
        $insertFields[] = "length = '" . (float)$data['length'] . "'";
        $insertFields[] = "width = '" . (float)$data['width'] . "'";
        $insertFields[] = "height = '" . (float)$data['height'] . "'";
        $insertFields[] = "length_class_id = '" . (int)$data['length_class_id'] . "'";
        $insertFields[] = "status = '" . (int)$data['status'] . "'";
        $insertFields[] = "tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "'";
        $insertFields[] = "sort_order = '" . (int)$data['sort_order'] . "'";
        $insertFields[] = "date_added = NOW()";
        $insertQuery[] = implode(', ', $insertFields);

        $this->db->query(implode(' ', $insertQuery));

        $product_id = $data['product_id'];

        $this->db->query("
			    INSERT INTO " . DB_PREFIX . "product_description SET
			    product_id = '" . (int)$product_id . "',
			    language_id = '2',
			    name = '" . $this->db->escape($data['name']) . "',
			    slug = '" . $this->db->escape((new Slugify)->slug($data['name'])) . "',
			    meta_keyword = '',
			    meta_description = '',
			    description = '" . $this->db->escape($data['description']) . "',
			    tag = ''
			");

        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '0'");

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image, ENT_QUOTES, 'UTF-8')) . "', sort_order = '0'");
            }
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['category_id'] . "'");


    }

    public  function delete_product($product_id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
    }

    public function checkProductExists($product_id) {
        $query = $this->db->query("SELECT count(product_id) as total FROM  " . DB_PREFIX . "product WHERE product_id = '" . $product_id . "' ");

        return ($query->row['total'] > 0) ? TRUE : FALSE;
    }
}
