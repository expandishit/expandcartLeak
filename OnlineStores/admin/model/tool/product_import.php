<?php

class ModelToolProductimport extends Model
{

    private $import_files_tb = DB_PREFIX . 'import_files';
    
    public function addoldproduct($data, $language_id, $language_data='', $store_id, $product_id, $seller_id=null)
    {
            if ($this->getTotalProductsCount() + 1 > PRODUCTSLIMIT) return false;

            /**
             * if product have not quantity and limit is 1
             * set quantity infinity (subtract = 1 and quantity = 1 and unlimited = 1 )
             */
            if ($data['quantity'] == '0' && $data['unlimited'] == '1' ){
                $unlimited = 1;
                $quantity =1;
                $subtract =0;
            }else{
                $quantity =(int)$data['quantity'] ;
                $subtract =(int)$data['subtract'] ;
                $unlimited = 0;

            }

        $sql = "INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) .
            "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) .
            "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) .
            "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . $quantity ."', maximum = '" . (int)$data['maximum'] .
            "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . $subtract . "', stock_status_id = '" . (int)$data['stock_status_id'] .
            "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] .
            "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] .
            "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] .
            "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = 
            '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] .
            "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] .
            "', date_added = NOW(),points = '" . (int)$data['points'] . "', viewed = '" . (int)$data['viewed']  .
            "', barcode = '" . $data['barcode']  . "', cost_price = '" . (float)$data['cost_price'] . "', unlimited = '". $unlimited . "'"
        ;

        $this->db->query(
            $sql
        );

        $product_id = $this->db->getLastId();

            if ((isset($data['image'])) &&  ($data['image'] != '')) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
            }else{
                if (($data['image'] == '')  && isset($data['images'])){

                    foreach ($data['images'] as $k => $img) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(trim($img)) . "' WHERE product_id = '" . (int)$product_id . "'");
                        break;
                    }
                }
            }


            $this->load->model('setting/setting');
            $expand_seo = $this->model_setting_setting->getSetting('expand_seo');
            if ($language_data == 'all'){
                foreach($this->getLanguages() as $language) {
                    $querySql = " REPLACE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', tag = '" . $this->db->escape($data['tag']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "'";
                    if ($expand_seo) {
                        $pattern = '[^\p{Arabic}\w\-]+';
                        $separator = '-';
                        $slugify = mb_strtolower(
                            preg_replace('#' . $pattern. '#iu', $separator, trim(
                                    html_entity_decode($data['name']))
                            ),'UTF-8'
                        );
                        if (isset($data['name'])) {
                            $querySql .= ", slug = '" . $this->db->escape($slugify) . "'";
                        }
                    }

                    $this->db->query($querySql);
                }

            }else{
                $querySql = " REPLACE INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', tag = '" . $this->db->escape($data['tag']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "'";
                if ($expand_seo) {
                    $pattern = '[^\p{Arabic}\w\-]+';
                    $separator = '-';
                    $slugify = mb_strtolower(
                        preg_replace('#' . $pattern. '#iu', $separator, trim(
                                html_entity_decode($data['name']))
                        ),'UTF-8'
                    );
                    if (isset($data['name'])) {
                        $querySql .= ", slug = '" . $this->db->escape($slugify) . "'";
                    }
                }

                $this->db->query($querySql);
            }

            $this->db->query("REPLACE INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");

            if (isset($data['attributes'])) {
                foreach ($data['attributes'] as $product_attribute) {
                    if ($product_attribute['attribute_id']) {
                        $this->db->query("REPLACE INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute['text']) . "'");
                    }
                }
            }

            foreach($data['options'] as $product_option){
                if($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image'){
                    if (isset($product_option['optionvalue'])){
                        $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'");
                        if(!empty($query->row['product_option_id'])){
                            $product_option_id = $query->row['product_option_id'];
                            $this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "' WHERE product_option_id = '".(int)$product_option_id."'");
                        }else{
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
                            $product_option_id = $this->db->getLastId();
                        }


                        if(isset($product_option['optionvalue'])){
                            foreach ($product_option['optionvalue'] as $optionvalue) {
                                if($optionvalue){
                                    if ($optionvalue['price'] >= 0 ){
                                        $prefix = '+';
                                    }else{
                                        $prefix = '-';
                                        $optionvalue['price'] = str_replace('-','',$optionvalue['price']);
                                    }
                                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$optionvalue['option_value_id'] . "', quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '" . $prefix ."' , weight = '" . (float)$optionvalue['weight'] . "',points = '" . (int)$optionvalue['points'] . "'");
                                }
                            }
                        }

                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }

            if (isset($data['discounts'])) {
                foreach ($data['discounts'] as $product_discount) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['startdate']) . "', date_end = '" . $this->db->escape($product_discount['enddate']) . "'");
                }
            }

            if (isset($data['specails'])) {
                foreach ($data['specails'] as $product_special) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['startdate']) . "', date_end = '" . $this->db->escape($product_special['enddate']) . "'");
                }
            }
            if (isset($data['product_variation_sku']) && $data['product_variation_sku'] !='' ) {
                foreach ($data['product_variation_sku'] as $variation_sku) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_variations SET product_id = '" . (int)$product_id . "', option_value_ids = '" .  $this->db->escape($variation_sku['option_value_ids']) . "', num_options = '" . (int)$variation_sku['num_options'] . "', product_sku = '" . $this->db->escape($variation_sku['product_sku']) . "', product_quantity = '" . (int)$variation_sku['product_quantity'] . "', product_barcode = '" . $this->db->escape($variation_sku['product_barcode']) . "', product_price = '" . (float)$variation_sku['product_price'] . "'");
                }
            }


            if (isset($data['images'])) {
                foreach ($data['images'] as $k => $img) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(trim($img)) . "', sort_order = '" . (int)$k . "'");
                }
            }

            if (isset($data['downloads']) &&
                \Extension::isInstalled('product_attachments') &&
                $this->config->get('product_attachments')['status'] == 1
            ) {
                foreach ($data['downloads'] as $download_id) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
                }
            }

            if (isset($data['categories'])) {
                foreach ($data['categories'] as $category_id) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
                }
            }

            if (isset($data['filters'])) {
                foreach ($data['filters'] as $filter_id) {
                    $this->db->query("REPLACE INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
                }
            }

            if (isset($data['relaled_products'])) {
                foreach ($data['relaled_products'] as $related_id) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
                }
            }

            if (isset($data['keyword'])) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
            }
            
            if (isset($data['optionsrequired'])) {   
                foreach ($data['optionsrequired'] as $roption) {
                    $query = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$roption['option_id'] . "'");
                    if (!empty($query->row['product_option_id'])) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option SET required = '" . (int)$roption['required'] . "' WHERE product_option_id = '" . (int)$query->row['product_option_id'] . "'");
                    }
                }
            }
        
        // Check if product came from the merchant, of so, they must be linked to the merchant
        if (!is_null($seller_id)) {
            $this->linkProductToSeller($seller_id, $product_id);
        }

        return $product_id;

    }

    public function addproduct($data, $language_id, $store_id, $seller_id=null)
    {

        if ($this->getTotalProductsCount() + 1 > PRODUCTSLIMIT) return false;

        /**
         * if product have not quantity and limit is 1
         * set quantity infinity (subtract = 1 and quantity = 1 and unlimited = 1 )
         */
        if ($data['quantity'] == '0' && $data['unlimited'] == '1' ){
            $unlimited = 1;
            $quantity =1;
            $subtract =0;
        }else{
            $quantity =(int)$data['quantity'] ;
            $subtract =(int)$data['subtract'] ;
            $unlimited = 0;

        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . $quantity . "', maximum = '" . (int)$data['maximum'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . $subtract . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(),points = '" . (int)$data['points'] . "', viewed = '" . (int)$data['viewed'] . "', barcode = '" . $data['barcode']  . "', cost_price = '" . (float)$data['cost_price'] . "' , unlimited = '". $unlimited . "'");



        $product_id = $this->db->getLastId();


        if ((isset($data['image'])) &&  ($data['image'] != '')) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }else{
            if (($data['image'] == '')  && isset($data['images'])){

                foreach ($data['images'] as $k => $img) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(trim($img)) . "' WHERE product_id = '" . (int)$product_id . "'");
                    break;
                }
            }
        }
        $querySql = " INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', tag = '" . $this->db->escape($data['tag']) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "'";

        $this->load->model('setting/setting');
        $expand_seo = $this->model_setting_setting->getSetting('expand_seo');
        if ($expand_seo) {
            $pattern = '[^\p{Arabic}\w\-]+';
            $separator = '-';
            $slugify = mb_strtolower(
                preg_replace('#' . $pattern. '#iu', $separator, trim(
                        html_entity_decode($data['name']))
                ),'UTF-8'
            );
                $querySql .= ", slug = '" . $this->db->escape($slugify) . "'";
        }

		$this->db->query($querySql);
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
		
		if (isset($data['attributes'])) {
			foreach ($data['attributes'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute['text']) . "'");
				}
			}
		}
		foreach($data['options'] as $product_option){
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				if(isset($product_option['optionvalue'])){
					$query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'");
					if(!empty($query->row['product_option_id'])){
						$product_option_id = $query->row['product_option_id'];
					}else{
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
						$product_option_id = $this->db->getLastId();
					}
					
					if(!isset($product_option['optionvalue'][0])){
						$optionvalue = $product_option['optionvalue'];
						if($optionvalue){
                            if ($optionvalue['price'] >= 0 ){
                                $prefix = '+';
                            }else{
                                $prefix = '-';
                                $optionvalue['price'] = str_replace('-','',$optionvalue['price']);
                            }
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$optionvalue['option_value_id'] . "', quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '" .  $prefix . "', weight = '" . (float)$optionvalue['weight'] . "',points = '" . (int)$optionvalue['points'] . "'");
                        }
					}
					else{
						foreach ($product_option['optionvalue'] as $optionvalue) {
							if($optionvalue){
                                if ($optionvalue['price'] >= 0 ){
                                    $prefix = '+';
                                }else{
                                    $prefix = '-';
                                    $optionvalue['price'] = str_replace('-','',$optionvalue['price']);
                                }
                                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$optionvalue['option_value_id'] . "', quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '" . $prefix . "' , weight = '" . (float)$optionvalue['weight'] . "',points = '" . (int)$optionvalue['points'] . "'");
                            }
						}
					}
					
				}
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
			}
		}

            if(isset($data['discounts'])){
                foreach ($data['discounts'] as $product_discount) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['startdate']) . "', date_end = '" . $this->db->escape($product_discount['enddate']) . "'");
                }
            }

            if(isset($data['specails'])){
                foreach ($data['specails'] as $product_special){
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['startdate']) . "', date_end = '" . $this->db->escape($product_special['enddate']) . "'");
                }
            }
            if (isset($data['product_variation_sku']) && $data['product_variation_sku'] !='' ) {
                foreach ($data['product_variation_sku'] as $variation_sku) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_variations SET product_id = '" . (int)$product_id . "', option_value_ids = '" .  $this->db->escape($variation_sku['option_value_ids']) . "', num_options = '" . (int)$variation_sku['num_options'] . "', product_sku = '" . $this->db->escape($variation_sku['product_sku']) . "', product_quantity = '" . (int)$variation_sku['product_quantity'] . "', product_barcode = '" . $this->db->escape($variation_sku['product_barcode']) . "', product_price = '" . (float)$variation_sku['product_price'] . "'");
                }
            }
			
			
			if (isset($data['images'])) {
				foreach ($data['images'] as $k => $img) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(trim($img)) . "', sort_order = '" . (int)$k . "'");
				}
			}
			
			if (isset($data['downloads']) &&
                \Extension::isInstalled('product_attachments') &&
                $this->config->get('product_attachments')['status'] == 1
            ) {
				foreach ($data['downloads'] as $download_id) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
				}
			}
			
			if (isset($data['categories'])) {
				foreach($data['categories'] as $category_id){
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
				}
			}
			
			if (isset($data['filters'])) {
				foreach ($data['filters'] as $filter_id) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
				}
			}
			
			if(isset($data['relaled_products'])){
				foreach ($data['relaled_products'] as $related_id){
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
				}
			}
			
			if (isset($data['keyword'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
            }
            
            if (isset($data['optionsrequired'])) {
                foreach($data['optionsrequired'] as $roption){
                    $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$roption['option_id'] . "'");
                    if(!empty($query->row['product_option_id'])){
                        $this->db->query("UPDATE ".DB_PREFIX."product_option SET required = '".(int)$roption['required']."' WHERE product_option_id = '".(int)$query->row['product_option_id']."'");
                    }
                }
            }
			
            
        // Check if product came from the merchant, of so, they must be linked to the merchant
        if (!is_null($seller_id)) {
            $this->linkProductToSeller($seller_id, $product_id);
        }
        return $product_id;
	}
	
	public function Editproduct($data,$product_id,$language_id,$store_id, $seller_id = null){

		$import_product_query = "UPDATE " . DB_PREFIX . " product SET  date_modified = NOW() ";
		if ($data['model'] != ''){
		    $import_product_query .=  " , model = '" . $this->db->escape($data['model']) . "'";
        }

		if ($data['sku'] != ''){
            $import_product_query .= " , sku = '" . $this->db->escape($data['sku']) . "'";
        }

		if ($data['upc'] != ''){
            $import_product_query .= " , upc = '" . $this->db->escape($data['upc']) . "'";
        }

		if ($data['ean'] != ''){
            $import_product_query .= " , ean = '" . $this->db->escape($data['ean']) . "'";
        }

		if ($data['jan'] != ''){
            $import_product_query .= " , jan = '" . $this->db->escape($data['jan']) . "'";
        }

		if ($data['isbn'] != ''){
            $import_product_query .= " , isbn = '" . $this->db->escape($data['isbn']) . "'";
        }

		if ($data['mpn'] != ''){
            $import_product_query .= " , mpn = '" . $this->db->escape($data['mpn']) . "'";
        }

		if ($data['location'] != ''){
            $import_product_query .= " , location = '" . $this->db->escape($data['location']) . "'";
        }

		if ($data['quantity'] != ''){
            $import_product_query .= " , quantity = '" . (int)$data['quantity'] . "'";
        }else{
            if ($data['minimum'] == ''){
                $import_product_query .= " , quantity = '" . 0 . "'";
            }
        }
		if ($data['maximum'] != ''){
            $import_product_query .= " , maximum = '" . (int)$data['maximum'] . "'";
        }

		if ($data['minimum'] != ''){
            $import_product_query .= " , minimum = '" . (int)$data['minimum']  . "'";
        }else{
            if ($data['minimum'] == ''){
                $import_product_query .= " , minimum = '" . 0 . "'";
            }
        }

		if ($data['subtract'] != ''){
            $import_product_query .= " , subtract = '" . (int)$data['subtract']  . "'";
        }

		if ($data['stock_status_id'] != ''){
            $import_product_query .= " , stock_status_id = '" . (int)$data['stock_status_id']  . "'";
        }

		if ($data['date_available'] != ''){
            $import_product_query .= " , date_available = '" . $this->db->escape($data['date_available'])  . "'";
        }

		if ($data['manufacturer_id'] != ''){
            $import_product_query .= " , manufacturer_id = '" .  (int)$data['manufacturer_id'] . "'";
        }

		if ($data['shipping'] != ''){
            $import_product_query .= " , shipping = '" .  (int)$data['shipping']. "'";
        }

		if ($data['price'] != ''){
            $import_product_query .= " , price = '" .   (float)$data['price'] . "'";
        }else{
            $import_product_query .= " , price = '" .  0 . "'";
        }

		if ($data['weight'] != ''){
            $import_product_query .= " , weight = '" .   (float)$data['weight'] . "'";
        }

		if ($data['weight_class_id'] != ''){
            $import_product_query .= " , weight_class_id = '" .   (int)$data['weight_class_id'] . "'";
        }

		if ($data['length'] != ''){
            $import_product_query .= " , length = '" .   (float)$data['length'] . "'";
        }

		if ($data['width'] != ''){
            $import_product_query .= " , width = '" .   (float)$data['width'] . "'";
        }

		if ($data['height'] != ''){
            $import_product_query .= " , height = '" .   (float)$data['height'] . "'";
        }

		if ($data['length_class_id'] != ''){
            $import_product_query .= " , length_class_id = '" .  (int)$data['length_class_id']  . "'";
        }

		if ($data['status'] != ''){
            $import_product_query .= " , status = '" .  (int)$data['status']  . "'";
        }else{
            $import_product_query .= " , status = '" .  0 . "'";

        }

		if ($data['tax_class_id'] != ''){
            $import_product_query .= " , tax_class_id = '" .  (int)$data['tax_class_id']  . "'";
        }

		if ($data['sort_order'] != ''){
            $import_product_query .= " , sort_order = '" .  (int)$data['sort_order']  . "'";
        }

		if ($data['viewed'] != ''){
            $import_product_query .= " , viewed = '" .  (int)$data['viewed']  . "'";
        }

		if ($data['points'] != ''){
            $import_product_query .= " , points = '" .  (int)$data['points']  . "'";
        }

		if ($data['barcode'] != ''){
            $import_product_query .= " , barcode = '" .  $data['barcode']  . "'";
        }

		if ($data['cost_price'] != ''){
            $import_product_query .= " , cost_price = '" .   (float)$data['cost_price'] . "'";
        }

		$import_product_query .= "  WHERE product_id = '" . (int)$product_id . "'";
		
        $this->db->query($import_product_query);
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		//Product Description
		$ifpdesc = $this->db->query("SELECT * FROM ".DB_PREFIX."product_description WHERE product_id = '".(int)$product_id."' AND language_id ='".(int)$language_id."'");
		if($ifpdesc->row){
            $import_product_description_query = $import_product_description_fields = [];

            $import_product_description_query[] = "UPDATE " . DB_PREFIX . "product_description SET ";

            if ($data['name'] != ''){
                $import_product_description_fields[] = "  name = '" . $this->db->escape($data['name']) . "'";
            }

            $this->load->model('setting/setting');
            $expand_seo = $this->model_setting_setting->getSetting('expand_seo');
            if ($expand_seo) {
                $pattern = '[^\p{Arabic}\w\-]+';
                $separator = '-';
                $slugify = mb_strtolower(
                    preg_replace('#' . $pattern. '#iu', $separator, trim(
                        html_entity_decode($data['name']))
                    ),'UTF-8'
                );
                if (isset($data['name'])) {
                    $import_product_description_fields[] = "  slug = '" . $slugify . "'";
                }
            }
            if ($data['description'] != ''){
                $import_product_description_fields[] = "  description = '" . $this->db->escape($data['description']) . "'";
            }
            if ($data['tag'] != ''){
                $import_product_description_fields[] = "  tag = '" . $this->db->escape($data['tag']) ."'";
            }
            if ($data['meta_description'] != ''){
                $import_product_description_fields[] = "  meta_description = '" . $this->db->escape($data['meta_description']) . "'";
            }
            if ($data['meta_keyword'] != ''){
                $import_product_description_fields[] = "  meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "'";
            }

            $import_product_description_query[] = implode(',', $import_product_description_fields);

            $import_product_description_query[]     = " WHERE product_id = '" . (int)$product_id . "'";
            $import_product_description_query[]     = " AND language_id ='".(int)$language_id."'";

            $this->db->query(implode('  ', $import_product_description_query));

		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($data['name']) . "', description = '" . $this->db->escape($data['description']) . "', tag = '" . $this->db->escape($data['tag']) . "',  meta_description = '" . $this->db->escape($data['meta_description']) . "', meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "'");
		}
		
		//Store
		$ifpstore = $this->db->query("SELECT * FROM ".DB_PREFIX."product_to_store WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$store_id . "'");
		if(!$ifpstore->row){
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
		}
		
		//Attribute
		if (isset($data['attributes'])){
			foreach ($data['attributes'] as $product_attribute){
				if ($product_attribute['attribute_id']){
					$ifattribute = $this->db->query("SELECT * FROM ".DB_PREFIX."product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");
					if($ifattribute->row){
					  $this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET text = '" .  $this->db->escape($product_attribute['text']) . "' WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");
					}else{
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute['text']) . "'");
					}
				}
			}
		}
		if (isset($data['options'] )){

		 foreach($data['options'] as $product_option){
			if($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image'){
				if (isset($product_option['optionvalue'])){
					$query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'");
					if(!empty($query->row['product_option_id'])){
						$product_option_id = $query->row['product_option_id'];
						$this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "' WHERE product_option_id = '".(int)$product_option_id."'");
					}else{
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
						$product_option_id = $this->db->getLastId();
					}
					

					if(!isset($product_option['optionvalue'][0])){
						$optionvalue = $product_option['optionvalue'];

						if($optionvalue){
							$ifoptionvalue = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option_value WHERE  product_option_id = '" . (int)$product_option_id . "' AND option_value_id = '" . (int)$optionvalue['option_value_id'] ."'");
							if($ifoptionvalue->row){

								if ((bool)$this->config->get('auto_price_weigh_calc')) {
									$optionvalue['price'] = (float)$data['price'] * (float)$ifoptionvalue['weight'];
								}
								

                                if ($optionvalue['price'] >= 0 ){
                                    $prefix = '+';
                                }else{
                                    $prefix = '-';
                                    $optionvalue['price'] = str_replace('-','',$optionvalue['price']);
                                }

								$optionvalue['weight_prefix'] = '+';
								if ((float)$optionvalue['weight'] < 0) {
									$optionvalue['weight_prefix'] = '-';
									$optionvalue['weight'] *= -1;
								}

                                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '" . $prefix . "' , weight = '" . (float)$optionvalue['weight'] . "', weight_prefix = '" . $optionvalue['weight_prefix'] . "', points = '" . (int)$optionvalue['points'] . "' WHERE  product_option_value_id = '".(int)$ifoptionvalue->row['product_option_value_id']."'");
                            }else{
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$optionvalue['option_value_id'] . "', quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '+', weight = '" . (float)$optionvalue['weight'] . "',points = '" . (int)$optionvalue['points'] . "'");
							}
						}
					}
					else{
						foreach ($product_option['optionvalue'] as $optionvalue) {
							# code...
							if($optionvalue){
								$ifoptionvalue = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option_value WHERE  product_option_id = '" . (int)$product_option_id . "' AND option_value_id = '" . (int)$optionvalue['option_value_id'] ."'");
								if($ifoptionvalue->row){
								
									if ((bool)$this->config->get('auto_price_weigh_calc')) {
										$optionvalue['price'] = (float)$data['price'] * (float)$ifoptionvalue['weight'];
									}

                                    if ($optionvalue['price'] >= 0 ){
                                        $prefix = '+';
                                    }else{
                                        $prefix = '-';
                                        $optionvalue['price'] = str_replace('-','',$optionvalue['price']);
                                    }

									$optionvalue['weight_prefix'] = '+';
									if ((float)$optionvalue['weight'] < 0) {
										$optionvalue['weight_prefix'] = '-';
										$optionvalue['weight'] *= -1;
									}

                                    $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '" . $prefix . "' , weight = '" . (float)$optionvalue['weight'] . "', weight_prefix = '" . $optionvalue['weight_prefix'] . "', points = '" . (int)$optionvalue['points'] . "' WHERE  product_option_value_id = '".(int)$ifoptionvalue->row['product_option_value_id']."'");
                                }else{
									$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$optionvalue['option_value_id'] . "', quantity = '" . (int)$optionvalue['qty'] . "', subtract = '" . (int)$optionvalue['subtract'] . "', price = '" . (float)$optionvalue['price'] . "', price_prefix = '+', weight = '" . (float)$optionvalue['weight'] . "',points = '" . (int)$optionvalue['points'] . "'");
								}
							}
						}
						
					}
					
				}
			} else {
				$query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "'");
				if(!empty($query->row['product_option_id'])){
					$this->db->query("UPDATE " . DB_PREFIX . "product_option SET option_value = '" . $this->db->escape($product_option['value']) . "' WHERE product_option_id = '".(int)$query->row['product_option_id']."'");
				}else{
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		 }
		}

        if(!empty($data['discounts'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['discounts'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['startdate']) . "', date_end = '" . $this->db->escape($product_discount['enddate']) . "'");
            }
        }

        if(!empty($data['specails'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['specails'] as $product_special){
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['startdate']) . "', date_end = '" . $this->db->escape($product_special['enddate']) . "'");
            }
        }

            if (isset($data['product_variation_sku']) && $data['product_variation_sku'] !='' ) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_variations WHERE product_id = '" . (int)$product_id . "'");
                foreach ($data['product_variation_sku'] as $variation_sku) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_variations SET product_id = '" . (int)$product_id . "', option_value_ids = '" .  $this->db->escape($variation_sku['option_value_ids']) . "', num_options = '" . (int)$variation_sku['num_options'] . "', product_sku = '" . $this->db->escape($variation_sku['product_sku']) . "', product_quantity = '" . (int)$variation_sku['product_quantity'] . "', product_barcode = '" . $this->db->escape($variation_sku['product_barcode']) . "', product_price = '" . (float)$variation_sku['product_price'] . "'");
                }
            }
			
			
			if (!empty($data['images'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
				foreach ($data['images'] as $k => $img) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(trim($img)) . "', sort_order = '" . (int)$k . "'");
				}
			}
			
			if (!empty($data['downloads']) &&
                \Extension::isInstalled('product_attachments') &&
                $this->config->get('product_attachments')['status'] == 1
            ) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
				foreach ($data['downloads'] as $download_id) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
				}
			}
			
			if (!empty($data['categories'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
				foreach($data['categories'] as $category_id){
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
				}
			}
			
			if (!empty($data['filters'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
				foreach ($data['filters'] as $filter_id) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
				}
			}
			
			if(!empty($data['relaled_products'])){
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
			    $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
				foreach ($data['relaled_products'] as $related_id){
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
				}
			}
			
			if (!empty($data['keyword'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
			}
            
            if (isset($data['optionsrequired'])) {
                foreach($data['optionsrequired'] as $roption){
                    $query = $this->db->query("SELECT product_option_id FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$roption['option_id'] . "'");
                    if(!empty($query->row['product_option_id'])){
                        $this->db->query("UPDATE ".DB_PREFIX."product_option SET required = '".(int)$roption['required']."' WHERE product_option_id = '".(int)$query->row['product_option_id']."'");
                    }
                }
            }
        
        // Check if product came from the merchant, of so, they must be linked to the merchant
        if (!is_null($seller_id)) {
            $this->linkProductToSeller($seller_id, $product_id);
        }
	}
		
	public function CheckTaxClass($title){
		$query = $this->db->query("SELECT tax_class_id FROM ".DB_PREFIX."tax_class WHERE LCASE(title) = '".utf8_strtolower($this->db->escape($title))."'");
		if($query->row){
			return $query->row['tax_class_id'];
		}else{
			return false;
		}
	}
	
	public function CheckStockStatus($title){
		$query = $this->db->query("SELECT stock_status_id FROM ".DB_PREFIX."stock_status WHERE LCASE(name) = '".utf8_strtolower($this->db->escape($title))."'");
		if($query->row){
			return $query->row['stock_status_id'];
		}else{
			$languages = $this->getLanguages(array());
			foreach($languages as $language){
				if(isset($stock_status_id)){
					$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET stock_status_id = '" . (int)$stock_status_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($title) . "'");
				}else{
					$this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($title) . "'");
					$stock_status_id = $this->db->getLastId();
				}
			}
			return $stock_status_id;
		}
	}
	
	//Manufacture
	public function CheckManufacturer($title,$store_id){
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."manufacturer WHERE LCASE(name) = '".$this->db->escape(utf8_strtolower($title))."'");
		if($query->row){
			$qmanufacturer = $this->db->query("SELECT * FROM ".DB_PREFIX."manufacturer_to_store WHERE manufacturer_id = '".(int)$query->row['manufacturer_id']."' AND store_id = '".$store_id."'")->row;
			if($qmanufacturer){
				return $query->row['manufacturer_id'];
			}else{
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$query->row['manufacturer_id'] . "', store_id = '".(int)$store_id."'");
			}
		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($title) . "'");
			$manufacturer_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '".(int)$store_id."'");
		}
	}
	
	//Categories
	public function CheckCategories($category,$store_id){
		$categoriesdata=array();
		$categories = explode(',',$category);
		foreach($categories as $category){
			$parent_id=0;
			$multiplecategories = explode('>',$category);
			foreach($multiplecategories as $ct){
				$parent_id = $this->getcategorybyname(trim($ct),$parent_id,$store_id);
			}
			$categoriesdata[] = $parent_id;
		}
		return $categoriesdata;
	}
	
	//Categories Start
	public function getcategorybyname($title,$parent_id,$store_id){
		$cquery = $this->db->query("SELECT c.category_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE LCASE(cd.name) =  '".$this->db->escape(utf8_strtolower($title))."' AND c.parent_id = '".(int)$parent_id."'");
		if($cquery->row){
			return $cquery->row['category_id'];
		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "category SET parent_id = '" . (int)$parent_id . "', status = 1, date_added = NOW()");
			
			$category_id = $this->db->getLastId();
			
			foreach($this->getLanguages() as $language){
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($title) . "'");
			}
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			$this->addCategorypath($category_id,$parent_id);
			
			return $category_id;
		}
	}
	
	public function addCategorypath($category_id,$parent_id){
		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY `level` ASC");

		foreach($query->rows as $result){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");
		
	}
	//Categories END
	
	//Filter Start
	public function checkFilter($filters){
		 $filterids=array();
		 $results = explode(',',$filters);
		 foreach($results as $result){
			$filterdata=array();
			$filterdata = explode('::',$result);
			$filtergroup = (isset($filterdata[0]) ? $filterdata[0] :'');
			$filtervalue = (isset($filterdata[1]) ? $filterdata[1] :'');
			if($filtervalue && $filtergroup){
				 $rfiltergroup = $result = $this->db->query("SELECT fg.filter_group_id FROM `" . DB_PREFIX . "filter_group` fg LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE LCASE(fgd.name) = '".$this->db->escape(utf8_strtolower($filtergroup))."' LIMIT 0,1");
				 if(isset($rfiltergroup->row['filter_group_id'])){
					 $fquery = $this->db->query("SELECT f.filter_id FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_group_id = '".(int)$rfiltergroup->row['filter_group_id']."' AND LCASE(fd.name) = '".$this->db->escape(utf8_strtolower($filtervalue))."' LIMIT 0,1");
					 if(empty($fquery->row['filter_id'])){
						 $filterids[] = $this->addonlyfilter($rfiltergroup->row['filter_group_id'],$filtervalue);
					 }else{
						 $filterids[] = $fquery->row['filter_id'];
					 }
				 }else{
					$filterids[] = $this->addFilter($filtergroup,$filtervalue);
				}
			}
		  }
	  return $filterids;
	}
	
	public function addFilter($filtergroup,$filtervalue){
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` SET sort_order = 0");

		$filter_group_id = $this->db->getLastId();

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_group_description SET filter_group_id = '" . (int)$filter_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($filtergroup) . "'");
		}
		
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "filter SET filter_group_id = '" . (int)$filter_group_id . "'");

		
		$filter_id = $this->db->getLastId();
		
		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_id = '" . (int)$filter_id . "', language_id = '" . (int)$language['language_id'] . "', filter_group_id = '" . (int)$filter_group_id . "', name = '" . $this->db->escape($filtervalue) . "'");
		}
		
		return $filter_id;
	}
	
	public function addonlyfilter($filter_group_id,$filter){
		$this->db->query("INSERT INTO " . DB_PREFIX . "filter SET filter_group_id = '" . (int)$filter_group_id . "'");
		
		$filter_id = $this->db->getLastId();
		
		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_id = '" . (int)$filter_id . "', language_id = '" . (int)$language['language_id'] . "', filter_group_id = '" . (int)$filter_group_id . "', name = '" . $this->db->escape($filter) . "'");
		}
		return $filter_id;
	}
	//Filter END
	
	//Attribute Start
	public function checkAttribute($attributedatas){
		$attributes=array();
		$results = explode(',',$attributedatas);
		foreach($results as $result){
			$attr = explode('::',$result);
			$attrgroup = trim($attr[0]);
			$attribute = trim($attr[1]);
			$text 	   = trim($attr[2]);
			if($attrgroup && $attribute && $text){
				$rattrgroup = $this->db->query("SELECT ag.attribute_group_id FROM " . DB_PREFIX . "attribute_group ag LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE LCASE(agd.name) = '".$this->db->escape(utf8_strtolower($attrgroup))."' LIMIT 0,1");
				if(!empty($rattrgroup->row['attribute_group_id'])){
					$aresult = $this->db->query("SELECT a.attribute_id FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_group_id = '".(int)$rattrgroup->row['attribute_group_id']."' AND LCASE(ad.name) = '".$this->db->escape(utf8_strtolower($attribute))."' LIMIT 0,1");
					if(!empty($aresult->row['attribute_id'])){
						$attributes[]=array(
						  'attribute_id' => $aresult->row['attribute_id'],
						  'text'		 => $text,
						);
					}else{
						$attributes[]=array(
						  'attribute_id' => $this->addonlyAttribute($attribute,$rattrgroup->row['attribute_group_id']),
						  'text'		 => $text,
						);
					}
				}else{
					$attribute_group_id = $this->addAttributegroup($attrgroup);
					$attributes[]=array(
						  'attribute_id' => $this->addonlyAttribute($attribute,$attribute_group_id),
						  'text'		 => $text,
					);
				}
			}
		}
		return $attributes;
	}
	
	public function addAttributegroup($attrgroup){
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group SET sort_order = 0");

		$attribute_group_id = $this->db->getLastId();

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description SET attribute_group_id = '" . (int)$attribute_group_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($attrgroup) . "'");
		}
		return $attribute_group_id;
	}
	
	public function addonlyAttribute($attribute,$attribute_group_id){
		$this->db->query("INSERT INTO " . DB_PREFIX . "attribute SET attribute_group_id = '" . (int)$attribute_group_id . "'");

		$attribute_id = $this->db->getLastId();

		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description SET attribute_id = '" . (int)$attribute_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($attribute) . "'");
		}
		
		return $attribute_id;
	}
	//Attribute END
	
	//Options Start
	public function checkoptions($optionsdata){
		$options=array();
		$results = explode(';',$optionsdata);
		foreach($results as $result){
			$opt = explode('::',$result);
			$option 	 = (isset($opt[0]) ? trim($opt[0]) : '');
			$type 		 = (isset($opt[1]) ? trim($opt[1]) : '');
			$optionvalue = (isset($opt[2]) ? trim($opt[2]) : '');
			if($option && $type){
				$ovaluedata=array();
				if($optionvalue){
				  $ovaluedata = explode('~',$optionvalue);	
				}
				$optionv = '';
				if(isset($ovaluedata[0])){
					$optionv = trim($ovaluedata[0]);
				}
				if(($type == 'select' || $type == 'radio' || $type == 'checkbox' || $type == 'image')){
					$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($type))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");
					if(!empty($oquery->row['option_id'])){
						$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($optionv))."' LIMIT 0,1");
						if(!empty($ovquery->row['option_value_id'])){
							   $optionvalues=array(
								   'option_value_id' => $ovquery->row['option_value_id'],
								   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
								   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
								   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
								   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
								   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
								);
								$options[]=array(
								  'option_id' 	=> $oquery->row['option_id'],
								  'value'		=> '',
								  'required'	=> 1,
								  'optionvalue' => $optionvalues,
								  'type' => $type,
								);
						}else{
							if($optionv){
								$option_value_id = $this->addoptionvalue($oquery->row['option_id'],$optionv);
								   $optionvalues=array(
									   'option_value_id' => $option_value_id,
									   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
									   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
									   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
									   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
									   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
									);
									$options[]=array(
									  'option_id' 	=> $oquery->row['option_id'],
									  'value'		=> '',
									  'required'	=> 1,
									  'optionvalue' => $optionvalues,
									  'type' => $type,
									);
							}else{
								$options[]=array(
								  'option_id' 	=> $oquery->row['option_id'],
								  'value'		=> '',
								  'required'	=> 1,
								  'type' => $type,
								  'optionvalue' => array(),
								);
							}
						}
					}else{
						$option_id = $this->addoptions($option,$type);
						if($optionv){
						$option_value_id = $this->addoptionvalue($option_id,$optionv);
							$optionvalues=array(
							   'option_value_id' => $option_value_id,
							   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
							   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
							   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
							   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
							   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
							);
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> '',
							  'required'	=> 1,
							  'optionvalue' => $optionvalues,
							  'type' => $type,
							);
						}else{
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> '',
							  'required'	=> 1,
							  'optionvalue' => array(),
							  'type' => $type,
							);
						}
					}
				}else{
					if(($type == 'text' || $type == 'textarea' || $type == 'file' || $type == 'date' || $type == 'time' || $type == 'datetime')){
						$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($type))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");
						if(!empty($oquery->row['option_id'])){
							$options[]=array(
							  'option_id' 	=> $oquery->row['option_id'],
							  'value'		=> $optionv,
							  'required'	=> 1,
							  'optionvalue' => array(),
							  'type' => $type,
							);
						}else{
							$option_id = $this->addoptions($option,$type);
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> $optionv,
						      'required'	=> 1,
						      'optionvalue' => array(),
						      'type' => $type,
							);
						}
					}
				}
			}
		}
		return $options;
	}

	//Options Start
	public function checkSimpleOptionsFormat($optionsdata,$language_id){
		$options=array();
		$results = explode(';',$optionsdata);
		foreach($results as $result){
			$opt = explode(':',$result);
			$option 	 = (isset($opt[0]) ? trim($opt[0]) : '');
			$optionvalue = (isset($opt[1]) ? trim($opt[1]) : '');
			if($option){
				$ovaluedata=array();
				if($optionvalue){
				  $ovalues_data = explode(',',$optionvalue);	
				}

				$optionvalues = [];
				$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = 'select' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");

				if(!empty($oquery->row['option_id'])){

					foreach ($ovalues_data as $o_value) {
                        $o_value =explode('~',$o_value);
                        $o_value_name =trim($o_value[0]);
						$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($o_value_name))."'
						 LIMIT  0,1");

						if(!empty($ovquery->row['option_value_id'])){
								$optionvalues[]=array(
									'option_value_id' => $ovquery->row['option_value_id'],
									'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
									'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
									'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
									'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
									'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
								);
						}else{
							if($o_value_name){
								$option_value_id = $this->addoptionvalue($oquery->row['option_id'],$o_value_name,$language_id);
									$optionvalues[]=array(
										'option_value_id' => $option_value_id,
									    'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
									    'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
									    'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
									    'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
									    'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
									);
							}else{
								$options[]=array(
									'option_id' 	=> $oquery->row['option_id'],
									'value'		=> '',
									'required'	=> 1,
									'type' => 'select',
									'optionvalue' => array(),
								);
							}
						}
					}
					$options[]=array(
						'option_id' 	=> $oquery->row['option_id'],
						'value'		=> '',
						'required'	=> 1,
						'optionvalue' => $optionvalues,
						'type' => 'select',
					);
					
				}
				else{
					$option_id = $this->addoptions($option,'select');
					foreach ($ovalues_data as $o_value) {
                        $o_value =explode('~',$o_value);
                        $o_value_name =trim($o_value[0]);
						if($o_value_name){
							$option_value_id = $this->addoptionvalue($option_id,$o_value_name,$language_id);
							$optionvalues[]=array(
								'option_value_id' => $option_value_id,
                                'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
                                'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
                                'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
                                'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
                                'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
							);
							
						}else{
							$options[]=array(
								'option_id' 	=> $option_id,
								'value'		=> '',
								'required'	=> 1,
								'optionvalue' => array(),
								'type' => 'select',
							);
						}
					}
					$options[]=array(
						'option_id' 	=> $option_id,
						'value'		=> '',
						'required'	=> 1,
						'optionvalue' => $optionvalues,
						'type' => 'select',
					);
				}	
				/*
				if(($type == 'select' || $type == 'radio' || $type == 'checkbox' || $type == 'image')){
					$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($type))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");
					if(!empty($oquery->row['option_id'])){
						$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($optionv))."' LIMIT 0,1");
						if(!empty($ovquery->row['option_value_id'])){
							   $optionvalues=array(
								   'option_value_id' => $ovquery->row['option_value_id'],
								   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
								   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
								   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
								   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
								   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
								);
								$options[]=array(
								  'option_id' 	=> $oquery->row['option_id'],
								  'value'		=> '',
								  'required'	=> 1,
								  'optionvalue' => $optionvalues,
								  'type' => $type,
								);
						}else{
							if($optionv){
								$option_value_id = $this->addoptionvalue($oquery->row['option_id'],$optionv);
								   $optionvalues=array(
									   'option_value_id' => $option_value_id,
									   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
									   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
									   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
									   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
									   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
									);
									$options[]=array(
									  'option_id' 	=> $oquery->row['option_id'],
									  'value'		=> '',
									  'required'	=> 1,
									  'optionvalue' => $optionvalues,
									  'type' => $type,
									);
							}else{
								$options[]=array(
								  'option_id' 	=> $oquery->row['option_id'],
								  'value'		=> '',
								  'required'	=> 1,
								  'type' => $type,
								  'optionvalue' => array(),
								);
							}
						}
					}else{
						$option_id = $this->addoptions($option,$type);
						if($optionv){
						$option_value_id = $this->addoptionvalue($option_id,$optionv);
							$optionvalues=array(
							   'option_value_id' => $option_value_id,
							   'qty'			 => (isset($ovaluedata[1]) ? $ovaluedata[1] : 0),
							   'subtract'		 => (isset($ovaluedata[2]) ? $ovaluedata[2] : 0),
							   'price'			 => (isset($ovaluedata[3]) ? $ovaluedata[3] : 0),
							   'points'			 => (isset($ovaluedata[4]) ? $ovaluedata[4] : 0),
							   'weight'			 => (isset($ovaluedata[5]) ? $ovaluedata[5] : 0),
							);
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> '',
							  'required'	=> 1,
							  'optionvalue' => $optionvalues,
							  'type' => $type,
							);
						}else{
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> '',
							  'required'	=> 1,
							  'optionvalue' => array(),
							  'type' => $type,
							);
						}
					}
				}else{
					if(($type == 'text' || $type == 'textarea' || $type == 'file' || $type == 'date' || $type == 'time' || $type == 'datetime')){
						$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($type))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");
						if(!empty($oquery->row['option_id'])){
							$options[]=array(
							  'option_id' 	=> $oquery->row['option_id'],
							  'value'		=> $optionv,
							  'required'	=> 1,
							  'optionvalue' => array(),
							  'type' => $type,
							);
						}else{
							$option_id = $this->addoptions($option,$type);
							$options[]=array(
							  'option_id' 	=> $option_id,
							  'value'		=> $optionv,
						      'required'	=> 1,
						      'optionvalue' => array(),
						      'type' => $type,
							);
						}
					}
				}
				*/
			}
		}

		return $options;
	}	

	//Options Start
	public function checkAdvancedOptionsFormat($option,$optionvalue,$language_id){
		$options="";
		if($option){
			$o_value=$optionvalue;
            $o_value =explode('~',$o_value);
            $o_value_name =trim($o_value[0]);
			$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = 'select' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");

			if(!empty($oquery->row['option_id'])){
				$ovquery = $this->db->query("SELECT ov.option_value_id FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '".(int)$oquery->row['option_id']."' AND LCASE(ovd.name) = '".$this->db->escape(utf8_strtolower($o_value_name))."'
											 LIMIT 0,1");

				if(!empty($ovquery->row['option_value_id'])){
					$optionvalues[]=array(
						'option_value_id' => $ovquery->row['option_value_id'],
                        'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
                        'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
                        'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
                        'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
                        'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
					);
					$options=array(
						'option_id' 	=> $oquery->row['option_id'],
						'value'		=> '',
						'required'	=> 1,
						'optionvalue' => $optionvalues,
						'type' => 'select',
					);	
				}else{
					if($o_value_name){
						$option_value_id = $this->addoptionvalue($oquery->row['option_id'],$o_value_name,$language_id);
							$optionvalues[]=array(
								'option_value_id' => $option_value_id,
                                'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
                                'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
                                'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
                                'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
                                'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
							);
						$options=array(
							'option_id' 	=> $oquery->row['option_id'],
							'value'		=> '',
							'required'	=> 1,
							'optionvalue' => $optionvalues,
							'type' => 'select',
						);	
					}else{
						$options=array(
							'option_id' 	=> $oquery->row['option_id'],
							'value'		=> '',
							'required'	=> 1,
							'type' => 'select',
							'optionvalue' => array(),
						);
					}
				}
			}
			else{
				$option_id = $this->addoptions($option,'select');
				if($o_value_name){
					$option_value_id = $this->addoptionvalue($option_id,$o_value_name,$language_id);
					$optionvalues=array(
						'option_value_id' => $option_value_id,
                        'qty'			 => (isset($o_value[1]) ? $o_value[1] : 0),
                        'subtract'		 => (isset($o_value[2]) ? $o_value[2] : 0),
                        'price'			 => (isset($o_value[3]) ? $o_value[3] : 0),
                        'points'	     => (isset($o_value[4]) ? $o_value[4] : 0),
                        'weight'	     => (isset($o_value[5]) ? $o_value[5] : 0),
					);
					$options=array(
						'option_id' 	=> $option_id,
						'value'		=> '',
						'required'	=> 1,
						'optionvalue' => $optionvalues,
						'type' => 'select',
					);
				}else{
					$options=array(
						'option_id' 	=> $option_id,
						'value'		=> '',
						'required'	=> 1,
						'optionvalue' => array(),
						'type' => 'select',
					);
				}
			}	
		}
		return $options;
	}	
	
	public function addoptions($option,$type){
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "option` SET type = '" . $this->db->escape($type) . "'");
		
		$option_id = $this->db->getLastId();
		
		foreach($this->getLanguages() as $language){
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = '" . (int)$option_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($option) . "'");
		}
		
		return $option_id;
	}
	
	public function addoptionvalue($option_id,$optionv,$language_id = 'all'){
		$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "'");

		$option_value_id = $this->db->getLastId();

		if($language_id == "all"){
			foreach($this->getLanguages() as $language){
				$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language['language_id'] . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($optionv) . "'");
			}
		}
		else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '" . (int)$language_id . "', option_id = '" . (int)$option_id . "', name = '" . $this->db->escape($optionv) . "'");
		}
		return $option_value_id;
	}
	//Options END
	
	
	//Discounts
	public function checkdiscount($discountdata){
		$results = explode(',',$discountdata);
		$discounts=array();
		foreach($results as $result){
			if($result){
				$rdiscount = explode('::',$result);
				$discounts[]=array(
				  'customer_group_id' => (isset($rdiscount[0]) ? $rdiscount[0] : ''),
				  'quantity' 		  => (isset($rdiscount[1]) ? $rdiscount[1] : ''),
				  'priority' 		  => (isset($rdiscount[2]) ? $rdiscount[2] : ''),
				  'price' 		 	  => (isset($rdiscount[3]) ? $rdiscount[3] : ''),
				  'startdate' 		  => (isset($rdiscount[4]) ? $rdiscount[4] : ''),
				  'enddate' 		  => (isset($rdiscount[5]) ? $rdiscount[5] : ''),
				);
		    }
		}
		return $discounts;
	}
	
	//Specails
	public function checkspecial($specialdata){
		$results = explode(',',$specialdata);
		$special=array();
		foreach($results as $result){
			if($result){
				$rdiscount = explode('::',$result);
				$special[]=array(
				  'customer_group_id' => (isset($rdiscount[0]) ? $rdiscount[0] : ''),
				  'priority' 		  => (isset($rdiscount[1]) ? $rdiscount[1] : ''),
				  'price' 		 	  => (isset($rdiscount[2]) ? $rdiscount[2] : ''),
				  'startdate' 		  => (isset($rdiscount[3]) ? $rdiscount[3] : ''),
				  'enddate' 		  => (isset($rdiscount[4]) ? $rdiscount[4] : ''),
				);
		    }
		}
		return $special;
	}

    //Product Variation Sku
    public function checkProductVariationSku($data){
        $results = explode(';',$data);
        $product_variation_sku=array();
        foreach($results as $result){
            if($result){
                $variation_sku_value = explode('::',$result);
                $product_variation_sku[]=array(
                    'option_value_ids'      => (isset($variation_sku_value[0]) ? $variation_sku_value[0] : ''),
                    'num_options' 		    => (isset($variation_sku_value[1]) ? $variation_sku_value[1] : ''),
                    'product_sku' 		 	=> (isset($variation_sku_value[2]) ? $variation_sku_value[2] : ''),
                    'product_quantity' 		=> (isset($variation_sku_value[3]) ? $variation_sku_value[3] : ''),
                    'product_barcode' 		=> (isset($variation_sku_value[4]) ? $variation_sku_value[4] : ''),
                    'product_price' 		=> (isset($variation_sku_value[5]) ? $variation_sku_value[5] : ''),
                );
            }
        }
        return $product_variation_sku;
    }
	
	///Images
	public function getLanguages($data = array()){
		$language_data = $this->cache->get('language');
		if(!$language_data){
			$language_data = array();

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");

			foreach($query->rows as $result){
				$language_data[$result['code']] = array(
					'language_id' => $result['language_id'],
					'name'        => $result['name'],
					'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
				);
			}

			$this->cache->set('language', $language_data);
		}
		
		return $language_data;
	}
	
	public function getproductIDbymodel($model){
		$query = $this->db->query("SELECT product_id FROM ".DB_PREFIX."product WHERE LCASE(model) = '".$this->db->escape(strtolower($model))."'");
		
		return (isset($query->row['product_id']) ? $query->row['product_id'] : '');
	}
	
	public function checkoptionsrequred($optionsrequireddata){
	   $optionrequireds = array();
	   $results = explode(';',html_entity_decode($optionsrequireddata));
		$i=0;
	   foreach($results as $result){
		$opt = explode('=',$result);
		if(!empty($opt[1])){
		  $required = $opt[1];
		}else{
		  $required = 0;
		}
		 
		if(!empty($opt[0])){
			$path = explode(':',$opt[0]);
			$option = (isset($path[0]) ? trim($path[0]) : '');
			$type   = (isset($path[1]) ? trim($path[1]) : '');
			if($option && $type){
				$oquery = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.type = '".$this->db->escape(utf8_strtolower($type))."' AND LCASE(od.name) = '".$this->db->escape(utf8_strtolower($option))."' LIMIT 0,1");
			
				if(!empty($oquery->row['option_id'])){
					$i++;
					$optionrequireds[]=array(
						'option_id'	=> $oquery->row['option_id'],
						'required'	=> $required,
					);
				}
			}
		 }
	   }
		return $optionrequireds;
	}

    public function fetchingimage(string $image_url, string $image_file = null)
    {
        $fetchedImagesFile = TEMP_DIR_PATH . 'fetchedImages';

        if (!is_dir(TEMP_DIR_PATH)) {
            mkdir(TEMP_DIR_PATH, 0777, true);
        }

        if (file_exists($fetchedImagesFile)) {
            $fetchedImagesArray = json_decode(file_get_contents($fetchedImagesFile), true);
        }

        if (!isset($fetchedImagesArray) || !is_array($fetchedImagesArray)) {
            $fetchedImagesArray = [];
        }

        // check if file already saved before
        if (isset($fetchedImagesArray[$image_url])) {
            return $fetchedImagesArray[$image_url];
        }

        $path = 'image/data/products/';

        if (\Filesystem::isDirectory($path) && !\Filesystem::isDirExists($path)) {
            \Filesystem::createDir($path, 0777, true);
        }

        $image_url = str_replace(' ', '%20', $image_url);

        // download file and save it
        if (strpos($image_url, 'http') === 0) {
            $image_url_split = explode('/', $image_url);
            $image_name = end($image_url_split); //get image name from url
            $image_name = str_replace([' ','%20'],'',$image_name); // rename file name if exist space or %20
            // $fp = fopen($path . $image_name, 'w+'); //set image file writable
            $ch = curl_init($image_url); //connect to curl
            // curl_setopt($ch, CURLOPT_FILE, $fp); //open file and set images data
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //get image data from server
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1); //set limits to CURLOPT_FOLLOWLOCATION to be one time only
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //set limits to CURLOPT_FOLLOWLOCATION to be one time only

            // curl_exec($ch); //execute curl
            $response = curl_exec($ch);
            curl_close($ch); //close curl
            // fclose($fp); //close file
            try {
                \Filesystem::setPath($path . $image_name)->write($response);
            } catch (\Exception $e) {}

            if (!isset($fetchedImagesArray['urls'])) $fetchedImagesArray['urls'] = [];

            $fetchedImagesArray['urls'][] = $image_url;

            $productImage = 'data/products/' . urldecode($image_name);

            $fetchedImagesArray[$image_url] = $productImage;

            $fetchedImagesArray = json_encode($fetchedImagesArray);
            file_put_contents($fetchedImagesFile, $fetchedImagesArray);

            return $productImage;
        }

        // else not a valid image url
        return $image_url;
    }
    
	/**Product Review Start**/
	public function getproductreview($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "review WHERE review_id > 0";
		if(isset($data['filter_status'])){
			$sql .= " AND status = '".(int)$data['filter_status']."'";
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

    public function addproductreview($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "review SET product_id = '" . (int)$data['product_id'] . "',customer_id = '" . (int)$data['customer_id'] . "',author = '" . $this->db->escape($data['author']) . "',text = '" . $this->db->escape($data['text']) . "',rating = '" . (int)$data['rating'] . "',status = '" . (int)$data['status'] . "',date_added = '" . $this->db->escape($data['date_added']) . "',date_modified = '" . $this->db->escape($data['date_modified']) . "'");
    }

    public function addexsitproductreview($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "review SET review_id = '" . (int)$data['review_id'] . "',product_id = '" . (int)$data['product_id'] . "',customer_id = '" . (int)$data['customer_id'] . "',author = '" . $this->db->escape($data['author']) . "',text = '" . $this->db->escape($data['text']) . "',rating = '" . (int)$data['rating'] . "',status = '" . (int)$data['status'] . "',date_added = '" . $this->db->escape($data['date_added']) . "',date_modified = '" . $this->db->escape($data['date_modified']) . "'");
    }

    public function editproductreview($data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "review SET product_id = '" . (int)$data['product_id'] . "',customer_id = '" . (int)$data['customer_id'] . "',author = '" . $this->db->escape($data['author']) . "',text = '" . $this->db->escape($data['text']) . "',rating = '" . (int)$data['rating'] . "',status = '" . (int)$data['status'] . "',date_added = '" . $this->db->escape($data['date_added']) . "',date_modified = '" . $this->db->escape($data['date_modified']) . "' WHERE review_id = '" . (int)$data['review_id'] . "'");
    }

    public function getReview($review_id)
    {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT pd.name FROM " . DB_PREFIX . "product_description pd WHERE pd.product_id = r.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS product FROM " . DB_PREFIX . "review r WHERE r.review_id = '" . (int)$review_id . "'");

        return $query->row;
    }

    public function getTotalProductsCount()
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p WHERE p.archived=0";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
	/**Product Review End**/

	/**
	 * ////////// New import function////////////////
	*/	
	public function add_import_file($data)
	{
		# code...
		$sql = [];
		$sql[] = 'INSERT INTO '.$this->import_files_tb;
		$sql[] = '(`filename`, `import_type`, `import_date`, `file_mapping`, `import_status`, `message`, `error`, `number_of_records`)';
		$sql[] = "VALUES ('".$this->db->escape($data['filename'])."','".$data['import_type']."','"
				 .$data['import_date']."','".$data['file_mapping']."',".$data['import_status'].",null,null,null)";
		
		$this->db->query(implode(' ',$sql));
		return $this->db->getLastId();
		
	}
	public function update_import_file_status($file_id,$message,$errors)
	{
		$import_status = 1;
		$errors = '';
		if(is_array($errors)){
			$errors = json_encode($errors);
			$import_status = 2;
		}
		# code...
		$sql = [];
		$sql[] = 'UPDATE '.$this->import_files_tb;
		$sql[] = 'set import_status = '.$import_status;
		$sql[] = ', message = "'.$message.'"';
		$sql[] = ', error = "'.$errors.'"';
 		$sql[] = 'WHERE id='.$file_id;

        $this->db->query(implode(' ', $sql));
    }

    public function checkImportProcessStatus()
    {
        date_default_timezone_set($this->config->get('config_timezone') ? $this->config->get('config_timezone') : 'UTC');
        # code...
        $sql = [];
        $sql[] = 'SELECT * from ' . $this->import_files_tb;
        $sql[] = 'WHERE 1=1';
        $sql[] = 'ORDER BY id desc LIMIT 1';

        $data = $this->db->query(implode(' ', $sql));

        if($data->row){
            $dateSaved = strtotime($data->row['import_date']);
            $dateNow = strtotime(date('Y-m-d G:i:s'));
            $interval  = abs($dateNow - $dateSaved);
            $minutes   = round($interval / 60);

            if(DEV_MODE){ // For Testing Purpose
                $log = new Log("importingTime.log");
                if (date_default_timezone_get()) {
                    $log->write('date.timezone: ' . date_default_timezone_get());
                }
                $log->write('BEFORE CONVERTING DATA -> Saved:'.$data->row['import_date'].' Date Now:'.date('Y-m-d H:i:s'));
                $log->write('AFTER CONVERTING DATA -> Saved:'.$dateSaved.' Date Now:'.$dateNow.' Diff. in minutes is: '.$minutes);
            }

            if($minutes < 60){
                return $data->row;
            }

        }

        return array();

    }
    ////////////////////////////////////////////////
    
    private function linkProductToSeller($seller_id, $product_id)
    {
        $seller = $this->MsLoader->MsSeller->getSeller($seller_id);
        if ($seller) {
            $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX. "ms_product WHERE seller_id = '". (int)$seller_id ."'" . " AND product_id = '" . (int)$product_id . "'");
            
        
            if (!$query->num_rows) {
                $plan = $this->MsLoader->MsSeller->getPlanBySellerId($seller_id);
                $total_seller_products = $this->MsLoader->MsProduct->getTotalProducts([
                    'seller_id' => $seller_id
                ]);
        
                $maximum_products = $plan['maximum_products'];
                if (!$maximum_products || (int)$total_seller_products < $maximum_products) {                    
        
                    // If it is allowed for inactive seller to list new products
                    if ($this->config->get('msconf_allow_inactive_seller_products') && $this->MsLoader->MsSeller->getStatus() == MsSeller::STATUS_INACTIVE) {
                        $product_status = MsProduct::STATUS_INACTIVE;
                        $product_approved = 0;
                    } else {
                        // Set product status
                        switch ($this->config->get('msconf_product_validation')) {
                            case MsProduct::MS_PRODUCT_VALIDATION_APPROVAL:
                                $product_status = MsProduct::STATUS_INACTIVE;
                                $product_approved = 0;
                                break;
                            case MsProduct::MS_PRODUCT_VALIDATION_NONE:
                            default:
                                $product_status = MsProduct::STATUS_ACTIVE;
                                $product_approved = 1;
                                break;
                        }
                    }
        
                    $sql = "INSERT INTO " . DB_PREFIX . "ms_product
                        SET product_id = '" . (int) $product_id . "', seller_id = '" . (int) $seller_id . "', product_status = '" . (int) $product_status . "',
                            product_approved = '" . (int) $product_approved . "'";
        
                    $this->db->query($sql);
                }   
            }
        }
    }


    private function __getDiscountsAndSpecials($data){
	    $productDiscountsAndSpecial = [];
        $productDiscountsAndSpecial ['discount'] = $productDiscountsAndSpecial['product_special'] = [];
        foreach ($data as $specialDiscount) {
            if ($specialDiscount['quantity'] > 1) {
                $productDiscountsAndSpecial ['discount'][] = $specialDiscount;
            } else {
                $productDiscountsAndSpecial['product_special'] [] = $specialDiscount;
            }
        }
        return $productDiscountsAndSpecial;
    }

    /**
     * @param $data
     * @param $product_id
     */
    public function __addSpecialProducts($data , $product_id): void
    {
        foreach ($data as $product_special) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['startdate']) . "', date_end = '" . $this->db->escape($product_special['enddate']) . "'");
        }
    }
}
