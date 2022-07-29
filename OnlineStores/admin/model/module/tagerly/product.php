<?php
use ExpandCart\Foundation\String\Slugify;

class ModelModuleTagerlyProduct extends Model
{
    public function addProduct($data)
    {
        // echo "<pre>";
        // var_dump($data);die;    
        $insertQuery = $insertFields = [];
        $insertQuery[] = "INSERT INTO " . DB_PREFIX . "product SET";
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
        $insertFields[] = "general_use = '" . $this->db->escape($data['general_use']) . "'";
        $insertFields[] = "maximum = '" . (int)$data['maximum'] . "'";
        $insertFields[] = "subtract = '" . (int)$data['subtract'] . "'";
        $insertFields[] = "notes = '" . $this->db->escape($data['notes']) . "'";
        $insertFields[] = "barcode = '" . $this->db->escape($data['barcode']) . "'";
        $insertFields[] = "stock_status_id = '" . (int)$data['stock_status_id'] . "'";
        $insertFields[] = "date_available = '" . $this->db->escape($data['date_available']) . "'";
        $insertFields[] = "manufacturer_id = '" . (int)$data['manufacturer_id'] . "'";
        $insertFields[] = "shipping = '" . (int)$data['shipping'] . "'";
        $insertFields[] = "price = '" . (float)$data['price'] . "'";
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

        $product_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        //Set the first non-empty language
        $fullLanguageProduct = [];
        foreach ($data['product_description'] as $language_id => $value) {
            //Set the fullLanguageProduct if the current is not empty
            if(!$fullLanguageProduct && ($value['name'] && $value['description'])){
                $fullLanguageProduct = $value;
                break;
            }
        }

        foreach ($data['product_description'] as $language_id => $value) {

            if(!$value['name']){
                $value['name']=$fullLanguageProduct['name'];
            }

            if(!$value['description']){
                $value['description']=$fullLanguageProduct['description'];
            }


            $this->db->query("
			    INSERT INTO " . DB_PREFIX . "product_description SET
			    product_id = '" . (int)$product_id . "',
			    language_id = '" . (int)$language_id . "',
			    name = '" . $this->db->escape($value['name']) . "',
			    slug = '" . $this->db->escape((new Slugify)->slug($value['name'])) . "',
			    meta_keyword = '" . $this->db->escape($value['name']) . "',
			    meta_description = '" . $this->db->escape($value['name']) . "',
			    description = '" . $this->db->escape($value['description']) . "',
                tag = '" . $this->db->escape($value['name']) . "' " );
        }

        // product options product_childs
        if(isset($data['product_childs'])){
            foreach ($data['product_childs'] as $key => $options) {

                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "option SET
                    type = 'SELECT',
                    sort_order = '0'
                    " );
                $option_id = $this->db->getLastId();
    
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_option SET
                    product_id = '" . (int)$product_id . "',
                    option_id = '" . (int)$option_id . "',
                    option_value = '" . $this->db->escape($options['product_title']) . "',
                    required = '1',
                    sort_order = '" . (int)$key . "',
                " );
    
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "product_option_value SET
                    product_id = '" . (int)$product_id . "',
                    product_option_id = '" . $this->db->getLastId() . "',
                    option_id = '" . $key . "',
                    price = '" . $this->db->escape($options['product_price']) . "',
                    sort_order = '" . (int)$key . "',
                " );
    
                }    
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                if($product_image != null && $product_image != '' ){
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image, ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)1 . "'");
                }
            }}


        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        return $product_id;
    }


    public function syncProduct($data){
        $query = "UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$data['quantity'] . "' , cost_price = '".$data['cost_price']."' WHERE product_id = '".$data['product_id']."' ";
        $this->db->query($query);
    }

}