<?php

use ExpandCart\Foundation\String\Slugify;
class ModelCatalogProduct extends Model
{
    public function addProduct($data)
    {
        $currentProductsCount = $this->getTotalProductsCount();
        if(($currentProductsCount + 1 > PRODUCTSLIMIT  && PRODUCTID == 3)
        || (!isset($data['isKanawatProduct']) && PRODUCTID == 52)
        || (isset($data['isKanawatProduct']) && $currentProductsCount + 1 > KANAWAT_PRODUCTSLIMIT && PRODUCTID == 52)) {
            return false;
        }

        $product_affiliate_link_status = $this->config->get('product_affiliate_link_status');

        $this->load->model('module/rental_products/settings');
        $this->load->model('module/price_per_meter/settings');
        $this->load->model('module/product_classification/settings');
        $this->load->model('module/printing_document');
        $this->load->model('module/sales_booster');

        $this->load->model('module/product_video_links');

        $this->load->model('module/product_preparation_period');
        $this->load->model('module/products_notes');

        // when product quantity equal unlimited  subtract stock must be equal 0
        if ($data['unlimited'] == 1)
            $data['subtract']= 0;

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
        $insertFields[] = "maximum = '" . (int)$data['maximum'] . "'";
        $insertFields[] = "subtract = '" . (int)$data['subtract'] . "'";
        $insertFields[] = "barcode = '" . $this->db->escape($data['barcode']) . "'";
        $insertFields[] = "stock_status_id = '" . (int)$data['stock_status_id'] . "'";
        $insertFields[] = "date_available = '" . $this->db->escape($data['date_available']) . "'";
        $insertFields[] = "manufacturer_id = '" . (int)$data['manufacturer_id'] . "'";
        $insertFields[] = "shipping = '" . (int)$data['shipping'] . "'";
        $insertFields[] = "is_simple = '" . (int)$data['is_simple'] . "'";
        $insertFields[] = "unlimited = '" . (int)$data['unlimited'] . "'";

        if (isset($data['storable']) && (int)$data['storable'] === 1) {
            $insertFields[] = "storable = 1";
        }

        if( $this->model_module_product_video_links->isInstalled() ){
            $insertFields[] = "external_video_url = '" . $this->db->escape($data['external_video_url']) . "'";
        }

        if ($this->model_module_product_preparation_period->isActive()) {
            $insertFields[] = "preparation_days = '" . (int)$data['preparation_days'] . "'";

        }

        if ($this->model_module_products_notes->isActive()) {
            if($this->config->get('products_notes')['internal_notes']){
                $insertFields[] = "notes = '" . $this->db->escape($data['notes']) . "'";
            }
            if($this->config->get('products_notes')['general_use']){
                $insertFields[] = "general_use = '" . $this->db->escape($data['general_use']) . "'";
            }
        }

        if ($this->model_module_rental_products_settings->isActive()) {
            $insertFields[] = "transaction_type = '" . (int)$data['transaction_type'] . "'";
        }
        $insertFields[] = "price = '" . (float)$data['price'] . "'";


       if (isset($data['minimum_deposit_price'])){
           $insertFields[] = "minimum_deposit_price = '" . (float)$data['minimum_deposit_price'] . "'";
       }

        //Printing Document
        if ($this->model_module_printing_document->isActive()) {
            $insertFields[] = "printable = '" . (int)$data['printable'] . "'";
        }

        //Sales Booster
        if ($this->model_module_sales_booster->isActive()) {
            $arraySalesBooster = array(
                'video' => $data['sls_bstr']['video'],
                'status' => $data['sls_bstr']['status']
            );

            foreach ($data['sls_bstr']['free_html'] as $key => $value) {
                $arraySalesBooster['free_html'][$key] = $this->db->escape($value);
            }

            $dataSalesBoosterJson = json_encode($arraySalesBooster, JSON_UNESCAPED_UNICODE);
            $insertFields[] = "sls_bstr = '" . $dataSalesBoosterJson . "'";
        }

        //price per meter
        if ($this->model_module_price_per_meter_settings->isActive()) {
            $arrayPriceMeter = array(
                'main_status' => $data['main_status'],
                'main_unit' => $data['main_unit'],
                'main_meter_price' => $data['main_meter_price'],
                'main_package_size' => $data['main_package_size'],
                'main_price_percentage' => $data['main_price_percentage'],
                'skirtings_status' => $data['skirtings_status'],
                'skirtings_meter_price' => $data['skirtings_meter_price'],
                'skirtings_package_size' => $data['skirtings_package_size'],
                'skirtings_price_percentage' => $data['skirtings_price_percentage'],
                'metalprofile_status' => $data['metalprofile_status'],
                'metalprofile_meter_price' => $data['metalprofile_meter_price'],
                'metalprofile_package_size' => $data['metalprofile_package_size'],
                'metalprofile_price_percentage' => $data['metalprofile_price_percentage'],
            );
            $dataPriceMeterJson = json_encode($arrayPriceMeter);
            $insertFields[] = "price_meter_data = '" . $dataPriceMeterJson . "'";
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
        //$insertFields[] = $data['date_added'] ? "date_added = '". date('Y-m-d H:i:s', strtotime($data['date_added'])) ."'" :"date_added = NOW()"; //DB column default value is current_timestamp
        if ($product_affiliate_link_status) {
            $insertFields[] = "affiliate_link = '" . $this->db->escape($data['affiliate_link']) . "'";
        }

        $pdModuleSettings = $this->config->get('tshirt_module');
        if (isset($pdModuleSettings['pd_status']) && $pdModuleSettings['pd_status'] == 1) {

            if($data['pd_is_customize'] !== null){
                $insertFields[] = "pd_is_customize='" . $this->db->escape($data['pd_is_customize']) . "'";
            }
            if($data['pd_custom_min_qty'] !== null){
                $insertFields[] = "pd_custom_min_qty='" . $this->db->escape($data['pd_custom_min_qty']) . "'";
            }
            if($data['pd_custom_price'] !== null) {
                $insertFields[] = "pd_custom_price='" . $this->db->escape($data['pd_custom_price']) . "'";
            }else{
                $insertFields[] = " pd_custom_price='0' ";
            }
            $insertFields[] = "pd_back_image='" . $this->db->escape($data['pd_back_image']) . "'";

        }


        $insertQuery[] = implode(', ', $insertFields);

        $this->db->query(implode(' ', $insertQuery));

        $product_id = $this->db->getLastId();
        if (\Extension::isinstalled('payThem') && $this->config->get('payThem_app_status') == 1) {
            $this->db->query("INSERT IGNORE INTO product_to_paythem SET product_id={$product_id}, OEM_ID='{$data['OEM_ID']}', OEM_Name='{$data['OEM_Name']}', OEM_PRODUCT_ID='{$data['OEM_PRODUCT_ID']}', OEM_PRODUCT_VVSSKU='{$data['OEM_PRODUCT_VVSSKU']}'");
        }

        /* Stock Forecasting App */
        $this->_addStockForecastingData($data, $product_id);


        // handle cardless app insertion
        $this->load->model('module/cardless');
        if ($this->model_module_cardless->isInstalled()) {

            $cardless_sku = $data['cardless_sku'];
            if (isset($cardless_sku)) {
                $this->model_module_cardless->addCardlessProduct(compact('product_id', 'cardless_sku'));
            }
        }


        $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
        if ($queryAuctionModule->num_rows) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_bid SET product_id = '" . (int)$product_id . "',
                bid_date_start = '" . $this->db->escape($data['start_time']) . "',
				bid_date_end = '" . $this->db->escape($data['end_time']) . "',
				bid_max_price = '" . (float)($data['max_price']) . "',
				bid_min_price = '" . (float)($data['min_offer_step']) . "',
				bid_start_price = '" . (float)($data['start_price']) . "'
				");

            $product_bid_id = $this->db->getLastId();

            if (isset($data['use_max_price_on'])) {
                $this->db->query("UPDATE " . DB_PREFIX . "product_bid SET max_price_status = '" .
                    (int)($data['use_max_price_on']) . "' WHERE product_bid_id = '" . (int)$product_bid_id . "'");
            }

            if (isset($data['status_on'])) {
                $this->db->query("UPDATE " . DB_PREFIX . "product_bid SET auction_status = '" .
                    (int)($data['status_on']) . "' WHERE 			product_bid_id = '" . (int)$product_bid_id . "'");
            }
        }

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (!isset($data['sort_order']) || $data['sort_order'] == "" ){
            $this->db->query(
                "UPDATE " . DB_PREFIX . "product SET sort_order = '" . (int)($product_id) . "' WHERE product_id = '" . (int)$product_id . "'"
            );
        }

        $this->load->model('module/dedicated_domains/domain_prices');

        if ($this->model_module_dedicated_domains_domain_prices->isActive()) {

            $this->load->model('module/dedicated_domains/product_domain');

            if (isset($data['domainData'])) {

                $this->model_module_dedicated_domains_domain_prices->deleteDedicatedPricesById($product_id);

                $this->model_module_dedicated_domains_product_domain->deleteProductDomains($product_id);

                foreach ($data['domainData'] as $key => $domainData) {

                    if ($key > 0) {
                        $this->model_module_dedicated_domains_domain_prices->insertDedicatedPrice(
                            $product_id, $domainData
                        );
                    }

                    if (!isset($domainData['status'])) {
                        $domainData['status'] = '0';
                    }

                    if (
                        isset($domainData['status']) &&
                        $domainData['status'] == 1 &&
                        (int)$data['domainData'][0]['status'] != 1
                    ) {

                        $this->model_module_dedicated_domains_product_domain->insertProductDomain(
                            $product_id, $domainData['domain_id']
                        );

                    }
                }
            }
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

        $multiseller_installed =\Extension::isInstalled('multiseller');
        foreach ($data['product_description'] as $language_id => $value) {

            if(!$value['name']){
                $value['name']=$fullLanguageProduct['name'];
            }

            if(!$value['description']){
                $value['description']=$fullLanguageProduct['description'];
            }

            $sql_seller_notes = '';
            if( $multiseller_installed == TRUE){
                $sql_seller_notes = ",seller_notes = '" . $this->db->escape($value['seller_notes']) . "'";
            }

            $this->db->query("
			    INSERT INTO " . DB_PREFIX . "product_description SET
			    product_id = '" . (int)$product_id . "',
			    language_id = '" . (int)$language_id . "',
			    name = '" . $this->db->escape($value['name']) . "',
			    slug = '" . $this->db->escape((new Slugify)->slug($value['name'])) . "',
			    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "',
			    meta_description = '" . $this->db->escape($value['meta_description']) . "',
			    description = '" . $this->db->escape($value['description']) . "',
                tag = '" . $this->db->escape($value['tag']) . "' " . $sql_seller_notes );
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }
        /*
        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }
        */

        if (isset($data['product_advanced_attribute'])) {
            $this->load->model('module/advanced_product_attributes/attribute');
            $this->model_module_advanced_product_attributes_attribute->addProductAttributes($product_id, $data['product_advanced_attribute']);
        }

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "', sort_order = '" . (int)$product_option['sort_order'] . "'");

                    $product_option_id = $this->db->getLastId();

                    // Product Option Image PRO module <<
                    $this->load->model('module/product_option_image_pro');
                    if (isset($product_option['poip_settings'])) {
                        $this->model_module_product_option_image_pro->setRealProductSettings($product_id, $product_option_id, $product_option['poip_settings']);
                    }
                    // >> Product Option Image PRO module

                    if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0) {
                        foreach ($product_option['product_option_value'] as $product_option_value) {

                            $product_option_value['price_prefix'] = '+';
                            if ((int)$product_option_value['price'] < 0) {
                                $product_option_value['price_prefix'] = '-';
                                $product_option_value['price'] *= -1;
                            }

                            $product_option_value['weight_prefix'] = '+';
                            if ((int)$product_option_value['weight'] < 0) {
                                $product_option_value['weight_prefix'] = '-';
                                $product_option_value['weight'] *= -1;
                            }

                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "', sort_order = '" . (int)$product_option_value['sort_order'] . "'");

                            // Product Option Image PRO module <<
                            $product_option_value_id = $this->db->getLastId();
                            $this->load->model('module/product_option_image_pro');
                            if (isset($product_option_value['images'])) {
                                $this->model_module_product_option_image_pro->saveProductOptionValueImages($product_id, $product_option_id, $product_option_value_id, $product_option_value['images']);
                            }

                            // dependent options and others compatibility (product_option_image_pro.xml - must be loaded after them)
                            // delete and new insert - give as correct result of $product_option_value_id = $this->db->getLastId();
                            $query_pov = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = " . $product_option_value_id . " ");
                            if ($query_pov->num_rows) {

                                $sql_set = "";
                                foreach ($query_pov->row as $pov_key => $pov_value) {
                                    $sql_set .= ", `" . $pov_key . "` = '" . $pov_value . "' ";
                                }
                                $sql_set = substr($sql_set, 1);
                                ecTargetLog( [
                                    'backtrace' => debug_backtrace(),
                                    'uri' => $_SERVER['REQUEST_URI']
                                ]);
                                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = " . $product_option_value_id . " ");
                                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET " . $sql_set);
                            }

                            // >> Product Option Image PRO module
                        }
                    } else {
                        ecTargetLog( [
                            'backtrace' => debug_backtrace(),
                            'uri' => $_SERVER['REQUEST_URI']
                        ]);
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_option_id = '" . $product_option_id . "'");
                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "', sort_order = '" . (int)$product_option['sort_order'] . "'");
                }
            }
        }

        // start add product classification data
        if ($this->model_module_product_classification_settings->isActive()) {
            $product_classification = isset($data['product_classification']) ? $data['product_classification'] : null;
            if(is_array($product_classification)){
                $queryCompose = [];
                $queryCompose[] = "INSERT INTO " . DB_PREFIX . "pc_product_brand_mapping (`product_id`, `pc_brand_id`, `pc_model_id`, `pc_year_id`,`pc_row_key`) VALUES";

                foreach ($product_classification as $key=>$pc_data){
                    foreach ($pc_data['model'] as $modelValue)
                    {
                        foreach ($pc_data['year']  as $pc_year) {
                            $queryCompose[] = "(".(int)$product_id.", ".(int)$pc_data['brand_id'].", ".(int)$modelValue.", ".(int)$pc_year.", ".(int)$key."),";
                        }
                    }
                }
                $queryCompose[count($queryCompose) - 1] = substr(end($queryCompose), 0, -1);
                $this->db->query(implode(' ', $queryCompose));
            }
        }
        // end add product classification data

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {

                $queryString = $fields = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX . "product_discount SET";
                $fields[] = "product_id = '" . (int)$product_id . "'";
                $fields[] = "customer_group_id = '" . (int)$product_discount['customer_group_id'] . "'";
                $fields[] = "quantity = '" . (int)$product_discount['quantity'] . "'";
                $fields[] = "priority = '" . (int)$product_discount['priority'] . "'";
                $fields[] = "price = '" . (float)$product_discount['price'] . "'";
                $fields[] = "date_start = '" . $this->db->escape($product_discount['date_start']) . "'";
                $fields[] = "date_end = '" . $this->db->escape($product_discount['date_end']) . "'";

                if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
                    $fields[] = "dedicated_domains = '" . $this->db->escape($product_discount['dedicated_domains']) . "'";
                }

                $queryString[] = implode(', ', $fields);

                $this->db->query(implode(' ', $queryString));
            }
        }



        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
        if ($productsoptions_sku_status) {
            if (isset($data['product_options_variations']) && $data['subtract'] == 1 || $this->config->get('module_knawat_dropshipping_status') == 'on') {
                $this->db->query("DELETE FROM product_variations WHERE product_id = '" . (int)$product_id . "'");
                if ($data['num_options'] == count($data['product_option'])) {
                    foreach ($data['product_options_variations'] as $variationKey => $option_variation_value) {

                        sort($option_variation_value['options']);

                        $option_value_ids = implode(',', $option_variation_value['options']);
                        $variationsSku = $option_variation_value['sku'];
                        $variationsQuantity = $option_variation_value['quantity'];
                        $variationsPrice = $option_variation_value['price'] ?? '';
                        $variationsBarcode = $option_variation_value['barcode'];

                        $variationsQueryString = [];
                        $variationsQueryString[] = 'INSERT INTO product_variations SET';
                        $variationsQueryString[] = 'product_id="' . $product_id . '",';
                        $variationsQueryString[] = 'option_value_ids="' . $option_value_ids . '",';
                        if (!empty($variationsQuantity) || $variationsQuantity === '0') {

                            if ($variationsQuantity < -1) {
                                $variationsQuantity = -1;
                            }

                            $variationsQueryString[] = 'product_quantity="' . $variationsQuantity . '",';
                        }
                        $variationsQueryString[] = 'product_price="' . $variationsPrice . '",';
                        $variationsQueryString[] = 'product_sku="' . $variationsSku . '",';
                        $variationsQueryString[] = 'product_barcode="' . $variationsBarcode . '",';
                        $variationsQueryString[] = 'num_options="' . $data['num_options'] . '"';


                        $this->db->query(implode(' ', $variationsQueryString));
                    }
                }
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {

                $queryString = $fields = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX . "product_special SET";
                $fields[] = "product_id = '" . (int)$product_id . "'";
                $fields[] = "customer_group_id = '" . (int)$product_special['customer_group_id'] . "'";
                $fields[] = "priority = '" . (int)$product_special['priority'] . "'";
                $fields[] = "`default` = '" . (int)$product_special['quick_discount'] . "'";
                $fields[] = "price = '" . (float)$product_special['price'] . "'";
                $fields[] = "date_start = '" . $this->db->escape($product_special['date_start']) . "'";
                $fields[] = "date_end = '" . $this->db->escape($product_special['date_end']) . "'";

                if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
                    $fields[] = "dedicated_domains = '" . $this->db->escape($product_special['dedicated_domains']) . "'";
                }


                $queryString[] = implode(', ', $fields);

                $this->db->query(implode(' ', $queryString));
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
				if($product_image['image'] != null && $product_image['image'] != '' ){
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
				}
			}
        }


        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){
            if (isset($data['product_download'])) {
                foreach ($data['product_download'] as $download_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
                }
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");

                if( \Extension::isInstalled('product_sort') && $this->config->get('product_sort_app_status') == '1'){

                    $rows_count = $this->db->query("SELECT COUNT(*) AS rows_count FROM " .
                        DB_PREFIX . "product_to_categories_sorting ".
                        " WHERE category_id = ".(int) $category_id ." AND product_id = ".(int)$product_id
                    )->row['rows_count'];

                    if (!$rows_count){
                        $last_sort_number = $this->db->query("SELECT COUNT(*) AS last_sort_number FROM " .
                            DB_PREFIX . "product_to_categories_sorting ".
                            " WHERE category_id = ".(int) $category_id
                        )->row['last_sort_number'];

                        $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_categories_sorting SET product_id = '" .
                            $this->db->escape((int)$product_id) . "', category_id = '" . $this->db->escape((int)$category_id) . "'".
                            ", manual_sort = '". (int) $last_sort_number ."'"
                        );
                    }

                }
            }
        }

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
            }
        }
        
        // product bundles App
        $this->load->model('module/product_bundles/settings');
        if($this->model_module_product_bundles_settings->isActive()){
            // delete records where the main_product_id = product_id from product_bundles
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_bundles WHERE main_product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['product_bundles'])) {
            $discount_per = $data['product_bundles_discount'] / 100;
            // insert product_bundles records
            foreach ($data['product_bundles'] as $bundle_product_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_bundles SET main_product_id = '" . (int)$product_id . "', bundle_product_id = '" . (int)$bundle_product_id . "', discount = '" .$discount_per . "'");
            }
        }

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
            }
        }

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        if ($data['keyword']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        //Prize draw
        if(isset($data['product_prize_draw']) && $data['product_prize_draw']){
            $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_product_to_prize SET prize_draw_id = '" . (int)$data['product_prize_draw'] . "', product_id = '" . (int)$product_id . "'");
        }
        ////////////

        // Gold App
        $this->load->model('module/gold');
        if ($this->model_module_gold->isActive() && $data['gold_caliber']) {
            if($data['gold_manuf_on'] == 1){
                $gold_manuf_on = 'gram';
                $price = ((float)$data['gold_caliber_price'] * (float)$data['gold_caliber_weight']) +  ((float)$data['gold_manuf_price'] * (float)$data['gold_caliber_weight']);
            }else{
                $gold_manuf_on = 'product';
                $price = ((float)$data['gold_caliber_price'] * (float)$data['gold_caliber_weight']) +  (float)$data['gold_manuf_price'];
            }
            $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . (float)$price . "', weight='".(float)$data['gold_caliber_weight']."' WHERE product_id = '" . (int)$product_id . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "gold_to_product SET gold_id='" . (int)$data['gold_caliber'] . "', product_id = '" . (int)$product_id . "', manuf_on='". $gold_manuf_on ."', manuf_price='".(float)$data['gold_manuf_price']."', weight='".(float)$data['gold_caliber_weight']."'");
        }
        ///////////////////////////////////////


        $this->load->model('module/rotate360');
        if( $this->model_module_rotate360->installed()){
            $this->db->query("DELETE FROM " . DB_PREFIX . "rotate360_images WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['rotate360_image'] as $rotate360_image) {
                if($rotate360_image["image"]){
                    $rotate360_image['product_id'] = (int)$product_id;
                    $rotate360_image['image_order'] = $rotate360_image["sort_order"];
                    $rotate360_image['image_path'] =  $rotate360_image["image"];
                    $rotate360_image['image_name'] = $rotate360_image["name"];
                    $result = $this->model_module_rotate360->insertImage($rotate360_image);
                }
            }
        }

        $this->cache->delete('product');
        $this->cache->delete('manufacturer.' . (int)$this->config->get('config_store_id'));

        $this->language->load('setting/setting');
        // 1 is Create a product in audit_trial_event_type table
        // Author: Bassem

        /*$this->audit_trail->log([
            'event_type'=> 1,
            'event_desc'=>'Add new product',
            'resource_id' => $product_id,
            'resource_type' => 'product'
        ]);*/



        /// Ali express module
        if(isset($data['warehouse_id']) && $data['warehouse_id']) {
            $status = 0;
            if($this->config->get('wk_dropship_user_group') != $this->user->getGroupId()) {
                $status = $this->config->get('wk_dropship_direct_to_store');
            } else {
                $status = 1;
            }
            $this->db->query(
                "UPDATE ".DB_PREFIX."product SET status = '".(int)$status."' WHERE product_id = '".(int)$product_id."' "
            );
            // $this->db->query("INSERT INTO ".DB_PREFIX."warehouse_product SET user_id = '".(int)$this->user->getId()."', warehouse_id = '".(int)$data['warehouse_id']."', product_id = '".$product_id."', quantity = '".(int)$data['quantity']."', approved = '".$status."' ");
            $this->load->model('warehouse/assignwarehouse');
            $this->model_warehouse_assignwarehouse->addProducts($data['quantity'],$product_id,$data['warehouse_id']);
        }

        //fire new product trigger for zapier if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $product = $this->getProduct($product_id);
            if ($product) {
                $product['product_description'] = $this->getProductDescriptions($product_id);
                $this->model_module_zapier->newProductTrigger($product);
            }
        }

        /***************** Start ExpandCartTracking #347720  ****************/

        //get product count
        $products_count = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE demo = 0")->row['total'];

        // send mixpanel add product event and update user products count
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->updateUser(['$products count'=>$products_count]);
        $this->model_setting_mixpanel->trackEvent('Add Product');

        // send amplitude add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->updateUser(['products count'=>$products_count]);
        $this->model_setting_amplitude->trackEvent('Add Product');


        /***************** End ExpandCartTracking #347720  ****************/

        return $product_id;
    }

    public function getid($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_bid
	WHERE product_id = '" . (int)$product_id . "'");
        return $query->row['product_bid_id'];
    }

    /**
     *   Get product specific column/s
     *
     *   @param int $product_id
     *   @param array $columns
     *   @return mixed
     */
    public function getProductCols($product_id, $columns) {

        $selections = implode($columns, ',');

        $query = "SELECT $selections FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "' limit 1";
        $query = $this->db->query($query);

        if ($query->num_rows)
            return $query->row;

        return false;
    }

    public function getproductauction($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p
	LEFT JOIN " . DB_PREFIX . "product_bid pd ON (p.product_id = pd.product_id)
	WHERE p.product_id = '" . (int)$product_id . "'");
        return $query->row;
    }

    public function editProduct($product_id, $data,$knawat_import=false)
    {
        $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
        if ($queryAuctionModule->num_rows) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_bid WHERE product_id = '" . (int)$product_id . "'");

            if ($query->num_rows) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_bid WHERE product_id = '" . $this->db->escape($product_id) . "'");
            }

            if (!isset($data['use_max_price_on'])) {
                $data['use_max_price_on'] = 0;
            }

            if (!isset($data['status_on'])) {
                $data['status_on'] = 0;
            }

            $this->db->query("INSERT INTO  " . DB_PREFIX . "product_bid SET
	        bid_date_start = '" . $this->db->escape($data['start_time']) . "',
	        bid_date_end = '" . $this->db->escape($data['end_time']) . "',

	        bid_max_price = '" . $this->db->escape((float)$data['max_price']) . "',
	        bid_min_price = '" . $this->db->escape((float)$data['min_offer_step']) . "',
	        bid_start_price = '" . $this->db->escape((float)$data['start_price']) . "',

		    max_price_status = '" . $this->db->escape((int)$data['use_max_price_on']) . "',
		    auction_status = '" . $this->db->escape((int)$data['status_on']) . "',
	        product_id = '" . $this->db->escape((int)$product_id) . "'");
        }

        $product_affiliate_link_status = $this->config->get('product_affiliate_link_status');

        $this->load->model('module/rental_products/settings');
        $this->load->model('module/price_per_meter/settings');
        $this->load->model('module/printing_document');
        $this->load->model('module/sales_booster');
        $this->load->model('module/product_classification/settings');

        $this->load->model('module/product_video_links');

        $this->load->model('module/product_preparation_period');
        $this->load->model('module/products_notes');
        // if the product is unlimited then the subtract must equal to  0
        if ($data['unlimited'] == 1)
            $data['subtract']= 0;

        $updateQuery = $updateFields = [];
        $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";
        $updateFields[] = "model = '" . $this->db->escape($data['model']) . "'";
        $updateFields[] = "sku = '" . $this->db->escape($data['sku']) . "'";
        $updateFields[] = "upc = '" . $this->db->escape($data['upc']) . "'";
        $updateFields[] = "ean = '" . $this->db->escape($data['ean']) . "'";
        $updateFields[] = "jan = '" . $this->db->escape($data['jan']) . "'";
        $updateFields[] = "isbn = '" . $this->db->escape($data['isbn']) . "'";
        $updateFields[] = "mpn = '" . $this->db->escape($data['mpn']) . "'";
        $updateFields[] = "location = '" . $this->db->escape($data['location']) . "'";
        $updateFields[] = "quantity = '" . $this->db->escape((int)$data['quantity']) . "'";
        $updateFields[] = "minimum = '" . $this->db->escape((int)$data['minimum']) . "'";
        $updateFields[] = "maximum = '" . $this->db->escape((int)$data['maximum']) . "'";
        $updateFields[] = "subtract = '" . $this->db->escape((int)$data['subtract']) . "'";
        $updateFields[] = "barcode = '" . $this->db->escape($data['barcode']) . "'";
        $updateFields[] = "stock_status_id = '" . $this->db->escape((int)$data['stock_status_id']) . "'";
        $updateFields[] = "date_available = '" . $this->db->escape($data['date_available']) . "'";
        $updateFields[] = "manufacturer_id = '" . $this->db->escape((int)$data['manufacturer_id']) . "'";
        $updateFields[] = "shipping = '" . $this->db->escape((int)$data['shipping']) . "'";
        $updateFields[] = "is_simple = '" . $this->db->escape((int)$data['is_simple']) . "'";
        $updateFields[] = "unlimited = '" . $this->db->escape((int)$data['unlimited']) . "'";

        $storable = 0;
        if (isset($data['storable']) && (int)$data['storable'] === 1) {
            $storable = 1;
        }
        $updateFields[] = sprintf("storable = %d", $storable);

        if( $this->model_module_product_video_links->isInstalled() ){
            $updateFields[] = "external_video_url = '" . $this->db->escape($data['external_video_url']) . "'";
        }

        if ($this->model_module_product_preparation_period->isActive()) {
            $updateFields[] = "preparation_days = '" . $this->db->escape((int)$data['preparation_days']) . "'";
        }

        if ($this->model_module_products_notes->isActive()) {
            if($this->config->get('products_notes')['internal_notes']){
                $updateFields[] = "notes = '" . $this->db->escape($data['notes']) . "'";
            }
            if($this->config->get('products_notes')['general_use']){
                $updateFields[] = "general_use = '" . $this->db->escape($data['general_use']) . "'";
            }
        }


        if ($this->model_module_rental_products_settings->isActive()) {
            $updateFields[] = "transaction_type = '" . $this->db->escape((int)$data['transaction_type']) . "'";
        }
        $updateFields[] = "price = '" . (float)$data['price'] . "'";

        if (isset($data['minimum_deposit_price'])){
            $updateFields[] = "minimum_deposit_price = '" . $this->db->escape((float)$data['minimum_deposit_price']) . "'";
        }

        //Printing Document
        if ($this->model_module_printing_document->isActive()) {
            $updateFields[] = "printable = '" . $this->db->escape((int)$data['printable']) . "'";
        }

        //Sales Booster
        if ($this->model_module_sales_booster->isActive()) {
            $arraySalesBooster = array(
                'video' => $data['sls_bstr']['video'],
                'sound' => $this->db->escape($data['sls_bstr']['sound']),
                'status' => $data['sls_bstr']['status']
            );

            foreach ($data['sls_bstr']['free_html'] as $key => $value) {
                $arraySalesBooster['free_html'][$key] = $this->db->escape($value);
            }

            $dataSalesBoosterJson = json_encode($arraySalesBooster, JSON_UNESCAPED_UNICODE);
            $updateFields[] = "sls_bstr = '" . $dataSalesBoosterJson . "'";
        }

        //price per meter
        $arrayPriceMeter = [];
        if ($this->model_module_price_per_meter_settings->isActive()) {
            $arrayPriceMeter = array(
                'main_status' => $data['main_status'],
                'main_unit' => $data['main_unit'],
                'main_meter_price' => $data['main_meter_price'],
                'main_package_size' => $data['main_package_size'],
                'main_price_percentage' => $data['main_price_percentage'],
                'skirtings_status' => $data['skirtings_status'],
                'skirtings_meter_price' => $data['skirtings_meter_price'],
                'skirtings_package_size' => $data['skirtings_package_size'],
                'skirtings_price_percentage' => $data['skirtings_price_percentage'],
                'metalprofile_status' => $data['metalprofile_status'],
                'metalprofile_meter_price' => $data['metalprofile_meter_price'],
                'metalprofile_package_size' => $data['metalprofile_package_size'],
                'metalprofile_price_percentage' => $data['metalprofile_price_percentage'],
            );
        }

        // start add product classification data
        if ($this->model_module_product_classification_settings->isActive()) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "pc_product_brand_mapping WHERE product_id = '" . (int)$product_id . "'");
            $product_classification = isset($data['product_classification']) ? $data['product_classification'] : null;
            // use hashmap to remove duplicated
            $classificationMap = [];
            foreach ($product_classification as $classification){
                foreach($classification['model'] as $model){
                    foreach ($classification['year'] as $year){
                        $classificationMap[$classification['brand_id']][$model][$year] = true;
                    }
                }
            }
            if(is_array($product_classification)){
                $queryCompose = [];
                $queryCompose[] = "INSERT INTO " . DB_PREFIX . "pc_product_brand_mapping (`product_id`, `pc_brand_id`, `pc_model_id`, `pc_year_id`,`pc_row_key`) VALUES";
                $counter = 0;
                foreach ($product_classification as $key=>$pc_data){
                    foreach ($pc_data['model'] as $modelValue)
                    {
                        if(!empty($modelValue))
                        {
                            foreach ($pc_data['year']  as $pc_year) {
                                if ($classificationMap[$pc_data['brand_id']][$modelValue][$pc_year]){
                                    $queryCompose[] = "(".$this->db->escape((int)$product_id).", ".$this->db->escape((int)$pc_data['brand_id']).", ".(int)$modelValue.", ".$this->db->escape((int)$pc_year).", ".$this->db->escape((int)$key)."),";
                                    unset($classificationMap[$pc_data['brand_id']][$modelValue][$pc_year]);
                                }
                            }
                            $counter++;
                        }
                    }
                }
                if($counter > 0){
                    $queryCompose[count($queryCompose) - 1] = substr(end($queryCompose), 0, -1);
                    $this->db->query(implode(' ', $queryCompose));
                }

            }
        }
        // end add product classification data

        // Curtain Seller
        $this->load->model('module/curtain_seller');

        if ($this->model_module_curtain_seller->isEnabled()) {
            $curtain_seller = $data['curtain_seller'];

            if (! isset($arrayPriceMeter)) {
                $arrayPriceMeter = [];
            }

            $arrayPriceMeter['curtain_seller'] = $curtain_seller;
        }

        $dataPriceMeterJson = json_encode($arrayPriceMeter);

        $updateFields[] = "price_meter_data = '" . $dataPriceMeterJson . "'";

        $updateFields[] = "cost_price = '" . $this->db->escape((float)$data['cost_price']) . "'";
        $updateFields[] = "points = '" . $this->db->escape((int)$data['points']) . "'";
        $updateFields[] = "weight = '" . $this->db->escape((float)$data['weight']) . "'";
        $updateFields[] = "weight_class_id = '" . $this->db->escape((int)$data['weight_class_id']) . "'";
        $updateFields[] = "length = '" . $this->db->escape((float)$data['length']) . "'";
        $updateFields[] = "width = '" . $this->db->escape((float)$data['width']) . "'";
        $updateFields[] = "height = '" . $this->db->escape((float)$data['height']) . "'";
        $updateFields[] = "length_class_id = '" . $this->db->escape((int)$data['length_class_id']) . "'";
        $updateFields[] = "status = '" . $this->db->escape((int)$data['status']) . "'";
        $updateFields[] = "tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "'";

        if (!isset($data['sort_order']) || $data['sort_order'] == "" ){
            $data['sort_order'] = $product_id;
        }
        $updateFields[] = "sort_order = '" . $this->db->escape((int)$data['sort_order']) . "'";
        $updateFields[] = "date_modified = NOW()";

        if ($product_affiliate_link_status) {
            $updateFields[] = "affiliate_link = '" . $this->db->escape($data['affiliate_link']) . "'";
        }

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id = '" . $this->db->escape((int)$product_id) . "'";

        $this->db->query(implode(' ', $updateQuery));

        // update product special table
        $this->db->query("UPDATE " . DB_PREFIX . "product_special
                        SET price = " . (float)$data['price'] . " * (1 - (`discount_value` / 100))
                        WHERE `product_id` = " . $this->db->escape((int)$product_id) ." and `discount_type` = 'per'");

        if (\Extension::isinstalled('payThem') && $this->config->get('payThem_app_status') == 1) {
            $this->db->query("UPDATE product_to_paythem SET OEM_ID='{$data['OEM_ID']}', OEM_Name='{$data['OEM_Name']}', OEM_PRODUCT_ID='{$data['OEM_PRODUCT_ID']}', OEM_PRODUCT_VVSSKU='{$data['OEM_PRODUCT_VVSSKU']}' WHERE product_id='{$product_id}'");
        }

        /* Stock Forecasting App */
        $this->_addStockForecastingData($data, $product_id);


        // Activate and deactivate ms_product
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        if ($queryMultiseller->num_rows) {
            $prd_status = (int)$data['status'] ? 1 : 2;
            $this->db->query('UPDATE ' . DB_PREFIX . 'ms_product SET product_status=' . $prd_status . ' WHERE product_id="' . $this->db->escape((int)$product_id) . '"');
        }
        ////////////////////////////////////

        if (!isset($data['image']) || $data['image'] == "" ) {
            $data['image']="image/no_image.jpg";
        }
        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");

        // For knawat Drop shippment api
        if (isset($data['images'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['images'][0], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        $pdModuleSettings = $this->config->get('tshirt_module');
        if (isset($pdModuleSettings['pd_status']) && $pdModuleSettings['pd_status'] == 1) {
            $query = $fields = [];
            $query[] = "UPDATE " . DB_PREFIX . "product SET";
            $fields[] = "pd_custom_price='" . $this->db->escape($data['pd_custom_price']) . "'";
            $fields[] = "pd_is_customize='" . $this->db->escape($data['pd_is_customize']) . "'";
            $fields[] = "pd_custom_min_qty='" . $this->db->escape($data['pd_custom_min_qty']) . "'";
            $fields[] = "pd_back_image='" . $this->db->escape($data['pd_back_image']) . "'";

            $query[] = implode(',', $fields);

            $query[] = "WHERE product_id='" . (int)$product_id . "'";

            $queryString = implode(' ', $query);
            $this->db->query($queryString);
        }

        $this->load->model('module/dedicated_domains/domain_prices');

        if ($this->model_module_dedicated_domains_domain_prices->isActive()) {

            $this->load->model('module/dedicated_domains/product_domain');

            if (isset($data['domainData'])) {

                $this->model_module_dedicated_domains_domain_prices->deleteDedicatedPricesById($product_id);

                $this->model_module_dedicated_domains_product_domain->deleteProductDomains($product_id);

                foreach ($data['domainData'] as $key => $domainData) {

                    if ($key > 0) {
                        $this->model_module_dedicated_domains_domain_prices->insertDedicatedPrice(
                            $product_id, $domainData
                        );
                    }

                    if (!isset($domainData['status'])) {
                        $domainData['status'] = '0';
                    }

                    if (
                        isset($domainData['status']) &&
                        $domainData['status'] == 1 &&
                        (int)$data['domainData'][0]['status'] != 1
                    ) {

                        $this->model_module_dedicated_domains_product_domain->insertProductDomain(
                            $product_id, $domainData['domain_id']
                        );

                    }
                }
            }
        }

        // $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

        foreach ($data['product_description'] as $language_id => $value) {
            $this->updateProductDescription($product_id, $language_id, $value);
            // $this->db->query("
			//     INSERT INTO " . DB_PREFIX . "product_description SET
			//     product_id = '" . (int)$product_id . "',
			//     language_id = '" . (int)$language_id . "',
			//     name = '" . $this->db->escape($value['name']) . "',
			//     slug = '" . $this->db->escape((new Slugify)->slug($value['name'])) . "',
			//     meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "',
			//     meta_description = '" . $this->db->escape($value['meta_description']) . "',
			//     description = '" . $this->db->escape($value['description']) . "',
			//     tag = '" . $this->db->escape($value['tag']) . "'
			// ");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_store']) && $data['product_store']) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_store SET product_id = '" . $this->db->escape((int)$product_id) . "', store_id = '" . $this->db->escape((int)$store_id) . "'");
            }
        }

//        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
//
//        if (!empty($data['product_attribute'])) {
//            foreach ($data['product_attribute'] as $product_attribute) {
//                if ($product_attribute['attribute_id']) {
//                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
//
//                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
//                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
//                    }
//                }
//            }
//        }

        $is_knawat_product = false;
                
        if ($this->config->get('module_knawat_dropshipping_status') == 'on') {
            $is_knawat_product = $this->db->query("SELECT COUNT(resource_id) resources_count FROM " . DB_PREFIX . "knawat_metadata WHERE `resource_id` = " . (int) $product_id . " AND `meta_key` = 'is_knawat'")->row['resources_count'] != 0;
        }

        $option_values_ids = array();
        $option_values_query = '';
        $option_id_query = '';
        foreach ($data['product_option'] as $product_option) {
            if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0) {
                foreach ($product_option['product_option_value'] as $product_option_value) {
                    $option_values_ids[]= $product_option_value['option_value_id'];
                }
            }
        }

        if(!empty($option_values_ids)){
            $option_values_ids = implode(',',$option_values_ids);
            $option_values_query= "AND option_value_id IN (".$option_values_ids.") ";
        }

        if(isset($this->request->post['product_option']) && !empty(array_keys($this->request->post['product_option']))){
            $option_id_query = "AND option_id NOT IN (".implode(',', array_keys($this->request->post['product_option'])).")";
        }

        if($this->config->get('wk_amazon_connector_status') && isset($data['amazon_product_variation_value']) && $data['amazon_product_variation_value']){
            $getMappedOption = $this->db->query("SELECT amo.*, pov.product_option_id FROM ".DB_PREFIX."amazon_product_variation_map amo LEFT JOIN ".DB_PREFIX."product_option_value pov ON(amo.product_option_value_id = pov.product_option_value_id) WHERE amo.product_id = '".$this->db->escape((int)$product_id)."' AND pov.product_id = '".$this->db->escape((int)$product_id)."' ")->rows;
            foreach ($getMappedOption as $key => $map_option) {
//                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $this->db->escape((int)$product_id) . "' AND product_option_id != '".$this->db->escape((int)$map_option['product_option_id'])."' $option_id_query ");
//                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $this->db->escape((int)$product_id) . "' AND product_option_value_id != '".$this->db->escape((int)$map_option['product_option_value_id'])."' AND product_option_id != '".$this->db->escape((int)$map_option['product_option_id'])."' $option_values_query ");
            }
        }
        elseif(!$is_knawat_product || $knawat_import){
            ecTargetLog( [
                'backtrace' => debug_backtrace(),
                'uri' => $_SERVER['REQUEST_URI']
            ]);
//            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $this->db->escape((int)$product_id) . "' $option_id_query ");
//            $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $this->db->escape((int)$product_id) . "' $option_values_query ");
        }
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->model_module_product_option_image_pro->deleteProductOptionValueImages($product_id);
        $this->model_module_product_option_image_pro->deleteRealProductSettings($product_id);
        // >> Product Option Image PRO module
		//moved this line here as saving if poip also save in product_image
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_option'])) {

            usort($data['product_option'], function ($a, $b) {
                return $a['product_option_id'] < $b['product_option_id'];
            });

            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                    $product_option_id = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] ."' ")->row['product_option_id'];
                    if(!$product_option_id){
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "', sort_order = '" . (int)$product_option['sort_order'] . "'");
                        $product_option_id = $this->db->getLastId();
                    }
                    else{
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "', sort_order = '" . (int)$product_option['sort_order'] ."' WHERE product_option_id = '" . (int)$product_option_id . "'");
                    }
                    // Product Option Image PRO module <<
                    $this->load->model('module/product_option_image_pro');
                    if (isset($product_option['poip_settings'])) {
                        $this->model_module_product_option_image_pro->setRealProductSettings($product_id, $product_option_id, $product_option['poip_settings']);
                    }
                    // >> Product Option Image PRO module
                    
                    if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0) {
                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            if ((bool)$this->config->get('auto_price_weigh_calc') && $product_option_value['weight'] > 0 && !$knawat_import) {
                                $product_option_value['price'] = (float)$data['price'] * (float)$product_option_value['weight'];
                            }
                            
                            $product_option_value['price_prefix'] = '+';
                            if ((float)$product_option_value['price'] < 0) {
                                $product_option_value['price_prefix'] = '-';
                                $product_option_value['price'] *= -1;
                            }

                            $product_option_value['weight_prefix'] = '+';
                            if ((float)$product_option_value['weight'] < 0) {
                                $product_option_value['weight_prefix'] = '-';
                                $product_option_value['weight'] *= -1;
                            }

                            $optionValue = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE 
                            product_option_id='". $this->db->escape((int)$product_option_id) ."' 
                            AND  product_id='". $this->db->escape((int)$product_id) ."'
                            AND  option_id='". $this->db->escape((int)$product_option['option_id']) ."'
                            AND  option_value_id='". $this->db->escape((int)$product_option_value['option_value_id']) ."'
                            ");

                            if($optionValue->num_rows){
                                $product_option_value_id = $product_option_value['product_option_value_id'] ?: $optionValue->row['product_option_value_id'];
                                $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET product_option_id = '" . $this->db->escape((int)$product_option_id) . "', product_id = '" . $this->db->escape((int)$product_id) . "', option_id = '" . $this->db->escape((int)$product_option['option_id']) . "', option_value_id = '" . $this->db->escape((int)$product_option_value['option_value_id']) . "', quantity = '" . $this->db->escape((int)$product_option_value['quantity']) . "', subtract = '" . $this->db->escape((int)$product_option_value['subtract']) . "', price = '" . $this->db->escape((float)$product_option_value['price']) . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . $this->db->escape((int)$product_option_value['points']) . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . $this->db->escape((float)$product_option_value['weight']) . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "', sort_order = '" . $this->db->escape((int)$product_option_value['sort_order']) . "' WHERE product_option_value_id = '" . $this->db->escape((int) $product_option_value_id) . "'");
                            }else{
                                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . $this->db->escape((int)$product_option_value['product_option_value_id']) . "', product_option_id = '" . $this->db->escape((int)$product_option_id) . "', product_id = '" . $this->db->escape((int)$product_id) . "', option_id = '" . $this->db->escape((int)$product_option['option_id']) . "', option_value_id = '" . $this->db->escape((int)$product_option_value['option_value_id']) . "', quantity = '" . $this->db->escape((int)$product_option_value['quantity']) . "', subtract = '" . $this->db->escape((int)$product_option_value['subtract']) . "', price = '" . $this->db->escape((float)$product_option_value['price']) . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . $this->db->escape((int)$product_option_value['points']) . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . $this->db->escape((float)$product_option_value['weight']) . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "', sort_order = '" . $this->db->escape((int)$product_option_value['sort_order']) . "'");
                                $product_option_value_id = $this->db->getLastId();
                            }


                            // Product Option Image PRO module <<
                            $this->load->model('module/product_option_image_pro');
                            if (isset($product_option_value['images'])) {
                                $this->model_module_product_option_image_pro->saveProductOptionValueImages($product_id, $product_option_id, $product_option_value_id, $product_option_value['images']);
								
							}
							

                            // dependent options and others compatibility (product_option_image_pro.xml - must be loaded after them)
                            // delete and new insert - give as correct result of $product_option_value_id = $this->db->getLastId();
                            /*$query_pov = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = " . $product_option_value_id . " ");
                            if ($query_pov->num_rows) {

                                $sql_set = "";
                                foreach ($query_pov->row as $pov_key => $pov_value) {
                                    $sql_set .= ", `" . $pov_key . "` = '" . $pov_value . "' ";
                                }
                                $sql_set = substr($sql_set, 1);
                                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_option_value_id = " . $product_option_value_id . " ");
                                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET " . $sql_set);
                            }*/

                            // >> Product Option Image PRO module
                        }
                    } else {
                        ecTargetLog( [
                            'backtrace' => debug_backtrace(),
                            'uri' => $_SERVER['REQUEST_URI']
                        ]);
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_option_id = '" . $this->db->escape($product_option_id) . "'");
                    }
                } else {
                    $product_option_id = $this->db->query("SELECT product_option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $this->db->escape((int)$product_id) . "' AND option_id = '" . $this->db->escape((int)$product_option['option_id']) ."' ")->row['product_option_id'];
                    if(!$product_option_id) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . $this->db->escape((int)$product_option['product_option_id']) . "', product_id = '" . $this->db->escape((int)$product_id) . "', option_id = '" . $this->db->escape((int)$product_option['option_id']) . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . $this->db->escape((int)$product_option['required']) . "', sort_order = '" . $this->db->escape((int)$product_option['sort_order']) . "'");
                    }
                    else{
                        $this->db->query("UPDATE " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "', sort_order = '" . (int)$product_option['sort_order'] ."' WHERE product_option_id = '" . (int)$product_option_id . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {

                $queryString = $fields = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX . "product_discount SET";
                $fields[] = "product_id = '" . $this->db->escape((int)$product_id) . "'";
                $fields[] = "customer_group_id = '" . $this->db->escape((int)$product_discount['customer_group_id']) . "'";
                $fields[] = "quantity = '" . $this->db->escape((int)$product_discount['quantity']) . "'";
                $fields[] = "priority = '" . $this->db->escape((int)$product_discount['priority']) . "'";
                $fields[] = "price = '" . $this->db->escape((float)$product_discount['price']) . "'";
                $fields[] = "discount_type = '" . $this->db->escape($product_discount['discount_type']) . "'";
                $fields[] = "discount_value = '" . $this->db->escape((float)$product_discount['discount_value']) . "'";
                $fields[] = "date_start = '" . $this->db->escape($product_discount['date_start']) . "'";
                $fields[] = "date_end = '" . $this->db->escape($product_discount['date_end']) . "'";

                if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
                    $fields[] = "dedicated_domains = '" . $this->db->escape($product_discount['dedicated_domains']) . "'";
                }

                $queryString[] = implode(', ', $fields);

                $this->db->query(implode(' ', $queryString));
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
        if ($productsoptions_sku_status) {
            if (isset($data['product_options_variations']) && $data['unlimited']==1 || $data['subtract'] == 1 || $is_knawat_product) {
                $this->db->query("DELETE FROM product_variations WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
                foreach ($data['product_options_variations'] as $option_variation_value) {

                    sort($option_variation_value['options']);

                    $option_value_ids = implode(',', $option_variation_value['options']);
                    $variationsSku = $option_variation_value['sku'];
                    $variationsQuantity = $option_variation_value['quantity'];
                    $variationsPrice = $option_variation_value['price'] ?? '';
                    $variationsBarcode = $option_variation_value['barcode'];

                    $variationsQueryString = [];
                    $variationsQueryString[] = 'INSERT INTO product_variations SET';
                    $variationsQueryString[] = 'product_id="' . $this->db->escape($product_id) . '",';
                    $variationsQueryString[] = 'option_value_ids="' . $this->db->escape($option_value_ids) . '",';
                    if (!empty($variationsQuantity) || $variationsQuantity === '0') {

                        if ($variationsQuantity < -1) {
                            $variationsQuantity = -1;
                        }

                        $variationsQueryString[] = 'product_quantity="' . $this->db->escape($variationsQuantity) . '",';
                    }else{
                        $variationsQueryString[] = 'product_quantity="-1",'; //handle empty sku quantity must be -1 not 0 to avoid add to cart quantity check.
                    }
                    $variationsQueryString[] = 'product_price="' . $this->db->escape($variationsPrice) . '",';
                    $variationsQueryString[] = 'product_sku="' . $this->db->escape($variationsSku) . '",';
                    $variationsQueryString[] = 'product_barcode="' . $this->db->escape($variationsBarcode) . '",';
                    $variationsQueryString[] = 'num_options="' . $this->db->escape($data['num_options']) . '"';

                    $this->db->query(implode(' ', $variationsQueryString));
                }
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                
                //reculaculate discount_value according new price and discount_type
                if($product_special['discount_type']=="flat")
                {
                    $product_special['discount_value']=(float)$product_special['price'];
                }
                elseif($product_special['discount_type']=="sub")
                {
                    $product_special['discount_value']=(float)$data['price']-(float)$product_special['price'];
                }
                elseif($product_special['discount_type']=="per")
                {
                    $product_special['discount_value']=(1-((float)$product_special['price']/(float)$data['price']))*100;
                }
                $queryString = $fields = [];
                $queryString[] = "INSERT INTO " . DB_PREFIX . "product_special SET";
                $fields[] = "product_id = '" . $this->db->escape((int)$product_id) . "'";
                $fields[] = "customer_group_id = '" . $this->db->escape((int)$product_special['customer_group_id']) . "'";
                $fields[] = "priority = '" . $this->db->escape((int)$product_special['priority']) . "'";
                $fields[] = "`default` = '" . $this->db->escape((int)$product_special['quick_discount']) . "'";
                $fields[] = "price = '" . $this->db->escape((float)$product_special['price']) . "'";
                $fields[] = "discount_type = '" . $this->db->escape($product_special['discount_type']) . "'";
                $fields[] = "discount_value = '" . $this->db->escape((float)$product_special['discount_value']) . "'";
                $fields[] = "date_start = '" . $this->db->escape($product_special['date_start']) . "'";
                $fields[] = "date_end = '" . $this->db->escape($product_special['date_end']) . "'";

                if ($this->model_module_dedicated_domains_domain_prices->isActive()) {
                    $fields[] = "dedicated_domains = '" . $this->db->escape($product_special['dedicated_domains']) . "'";
                }


                $queryString[] = implode(', ', $fields);

                $this->db->query(implode(' ', $queryString));
            }
        }

        $this->load->model('module/rotate360');
        if( $this->model_module_rotate360->installed()){
                $this->db->query("DELETE FROM " . DB_PREFIX . "rotate360_images WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
                foreach ($data['rotate360_image'] as $rotate360_image) {
                    if($rotate360_image["image"]){
                    $rotate360_image['product_id'] = (int)$product_id;
                    $rotate360_image['image_order'] = $rotate360_image["sort_order"];
                    $rotate360_image['image_path'] =  $rotate360_image["image"];
                    $rotate360_image['image_name'] = $rotate360_image["name"];
                    $result = $this->model_module_rotate360->insertImage($rotate360_image);
                    }
                }
        }

		//we  added this line at top of poip options 
       // $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
			if (isset($data['product_image'])) {
				foreach ($data['product_image'] as $product_image) {
					/*
					we can't validate on name as we can choose from library photo 2 times  with same name 
					so we will validate on image source 
					and adding of other options of images [that will be null here & not saved ]
					will be in poip array &  saving of it will be at the same function of saving poip options images 
					*/
					if($product_image['image'] != null && $product_image['image'] != '' ){
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . $this->db->escape((int)$product_id) . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . $this->db->escape((int)$product_image['sort_order']) . "'");
					}
				}
			}
		

        // For knawat Drop Shippment api
        if (isset($data['images'])) {
            foreach ($data['images'] as $index => $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . $this->db->escape((int)$product_id) . "', image = '" . $this->db->escape(html_entity_decode($product_image, ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . $index . "'");
            }
        }

        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){

            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

            if (isset($data['product_download'])) {
                foreach ($data['product_download'] as $download_id) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . $this->db->escape((int)$product_id) . "', download_id = '" . $this->db->escape((int)$download_id) . "'");
                }
            }
        }

        if(
            isset($data['product_category']) && 
            ( 
               (($this->config->get('module_knawat_dropshipping_status') == 'on') 
                    && $this->config->get('module_knawat_dropshipping_update_category_on_product_update') == 'on'
                ) || 
               (!$this->config->get('module_knawat_dropshipping_status') 
                  || $this->config->get('module_knawat_dropshipping_status') != 'on' )
            )
          ){

            $isProductSortAppInstalled = \Extension::isInstalled('product_sort');
            if($isProductSortAppInstalled){
                $currentProductCaetgories = $this->getProductCategories($product_id);
            }

            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
            if (isset($data['product_category'])) {
                if($isProductSortAppInstalled) {
                    $deletedCategories = array_diff($currentProductCaetgories, $data['product_category']);
                }
                foreach ($data['product_category'] as $category_id) {
                    $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_category SET product_id = '" . $this->db->escape((int)$product_id) . "', category_id = '" . $this->db->escape((int)$category_id) . "'");

                    if($isProductSortAppInstalled && $this->config->get('product_sort_app_status') == '1'){

                        $rows_count = $this->db->query("SELECT COUNT(*) AS rows_count FROM " .
                            DB_PREFIX . "product_to_categories_sorting ".
                            " WHERE category_id = ".(int) $category_id ." AND product_id = ".(int)$product_id
                        )->row['rows_count'];

                        if(!$rows_count){
                            $last_sort_number = $this->db->query("SELECT COUNT(*) AS last_sort_number FROM " .
                                DB_PREFIX . "product_to_categories_sorting ".
                                " WHERE category_id = ".(int) $category_id
                            )->row['last_sort_number'];

                            $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_categories_sorting SET product_id = '" .
                                $this->db->escape((int)$product_id) . "', category_id = '" . $this->db->escape((int)$category_id) . "'".
                                ", manual_sort = '". (int) $last_sort_number ."'"
                            );
                        }
                    }
                }
                    if($isProductSortAppInstalled){
                        foreach ($deletedCategories as $category_id){
                            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_categories_sorting WHERE product_id = '" . $this->db->escape((int)$product_id) . "' AND category_id = '" . $this->db->escape((int)$category_id) . "'");}
                    }
                }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . $this->db->escape((int)$product_id) . "', filter_id = '" . $this->db->escape((int)$filter_id) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape((int)$product_id) . "' AND related_id = '" . $this->db->escape((int)$related_id) . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . $this->db->escape((int)$product_id) . "', related_id = '" . $this->db->escape((int)$related_id) . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape((int)$related_id) . "' AND related_id = '" . $this->db->escape((int)$product_id) . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . $this->db->escape((int)$related_id) . "', related_id = '" . $this->db->escape((int)$product_id) . "'");
            }
        }

        // product bundles App
        $this->load->model('module/product_bundles/settings');
        if($this->model_module_product_bundles_settings->isActive()){
            // delete records where the main_product_id = product_id from product_bundles
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_bundles WHERE main_product_id = '" . $this->db->escape((int)$product_id) . "'");
        }

        if (isset($data['product_bundles'])) {
            $discount_per = $data['product_bundles_discount'] / 100;
            // insert product_bundles records
            foreach ($data['product_bundles'] as $bundle_product_id) {

                $this->db->query("INSERT INTO " . DB_PREFIX . "product_bundles SET main_product_id = '" . $this->db->escape((int)$product_id) . "', bundle_product_id = '" . $this->db->escape((int)$bundle_product_id) . "', discount = '" .$this->db->escape($discount_per) . "'");
            }
        }
        

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . $this->db->escape((int)$product_id) . "', customer_group_id = '" . $this->db->escape((int)$customer_group_id) . "', points = '" . $this->db->escape((int)$value['points']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . $this->db->escape((int)$product_id) . "', store_id = '" . $this->db->escape((int)$store_id) . "', layout_id = '" . $this->db->escape((int)$layout['layout_id']) . "'");
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . $this->db->escape((int)$product_id) . "'");

        if ($data['keyword']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . $this->db->escape((int)$product_id) . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        //Prize draw
        if(isset($data['product_prize_draw'])){
            $this->db->query("DELETE FROM " . DB_PREFIX . "prdw_product_to_prize WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

            if($data['product_prize_draw']){
                $prize_product = $this->db->query("SELECT id FROM " . DB_PREFIX . "prdw_product_to_prize WHERE prize_draw_id = '" . $this->db->escape((int)$data['product_prize_draw']) . "' AND product_id = '" . $this->db->escape((int)$product_id) . "'");

                if ($prize_product->num_rows == 0) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "prdw_product_to_prize SET prize_draw_id = '" . $this->db->escape((int)$data['product_prize_draw']) . "', product_id = '" . $this->db->escape((int)$product_id) . "'");
                }
            }
        }
        ////////////


        // handle cardless app insertion
        $this->load->model('module/cardless');
        if ($this->model_module_cardless->isInstalled()) {

            $cardless_sku = $data['cardless_sku'];
            if (isset($cardless_sku)) {
                $this->model_module_cardless->addCardlessProduct(compact('product_id', 'cardless_sku'));
            }
        }

        // Gold App
        $this->load->model('module/gold');
        if ($this->model_module_gold->isActive() && $data['gold_caliber']) {
            if($data['gold_manuf_on'] == 1){
                $gold_manuf_on = 'gram';
                $price = ((float)$data['gold_caliber_price'] * (float)$data['gold_caliber_weight']) +  ((float)$data['gold_manuf_price'] * (float)$data['gold_caliber_weight']);
            }else{
                $gold_manuf_on = 'product';
                $price = ((float)$data['gold_caliber_price'] * (float)$data['gold_caliber_weight']) +  (float)$data['gold_manuf_price'];
            }
            $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . $this->db->escape((float)$price) . "', weight='".$this->db->escape((float)$data['gold_caliber_weight'])."' WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "gold_to_product WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "gold_to_product SET gold_id='" . $this->db->escape((int)$data['gold_caliber']) . "', product_id = '" . $this->db->escape((int)$product_id) . "', manuf_on='". $gold_manuf_on ."', manuf_price='".$this->db->escape((float)$data['gold_manuf_price'])."', weight='".$this->db->escape((float)$data['gold_caliber_weight'])."'");
        }
        ///////////////////////////////////////

        $this->cache->delete('product');
        $this->cache->delete('manufacturer.' . (int)$this->config->get('config_store_id'));

        $this->language->load('setting/setting');
        // 2 is Update a product in audit_trial_event_type table
        // Author: Bassem
        try{
            $this->audit_trail->log([
                'event_type'=> 2,
                'event_desc'=>'Update existing product',
                'resource_id' => $product_id,
                'resource_type' => 'product'
            ]);
        } catch (Exception $e) {}


         //fire update product trigger for zapier if installed
         $this->load->model('module/zapier');
         if ($this->model_module_zapier->isInstalled()) {
             $product = $this->getProduct($product_id);
             if ($product) {
                 $product['product_description'] = $this->getProductDescriptions($product_id);
                 $this->model_module_zapier->updateProductTrigger($product);
             }
         }

        return $product_id;
    }

    public function copyProduct($product_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($query->num_rows) {
            $data = array();

            $data = $query->row;

            $data['sku'] = '';
            $data['upc'] = '';
            $data['viewed'] = '0';
            $data['keyword'] = '';
            $data['status'] = '0';
            $data['notes'] = '';

            //$data = array_merge($data, array('product_attribute' => $this->getProductAttributes($product_id)));
            $data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));
            $data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
            $data = array_merge($data, array('product_filter' => $this->getProductFilters($product_id)));
            $data = array_merge($data, array('product_image' => $this->getProductImages($product_id)));
            $data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));
            $data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
            $data = array_merge($data, array('product_bundles' => $this->getProductBundles($product_id)));
            $data = array_merge($data, array('product_reward' => $this->getProductRewards($product_id)));
            $data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
            $data = array_merge($data, array('product_category' => $this->getProductCategories($product_id)));
            $data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
            $data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));
            $data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));

            $product2Domains = $this->load->model('module/dedicated_domains/product_domain', ['return' => true]);

            if ($product2Domains->isActive()) {

                if ($domains = $product2Domains->getProductDomains($product_id)) {
                    $data = array_merge(
                        $data, array(
                            'product_domain' => $product2Domains->getProductDomainsIds($domains)
                        )
                    );
                }

            }
            //Check Advanced_Product_attributes App status
            $this->load->model('module/advanced_product_attributes/settings');
            $this->load->model('module/advanced_product_attributes/attribute');
            $advanced_product_attributes_app_installed = $this->model_module_advanced_product_attributes_settings->isInstalled();
            if($advanced_product_attributes_app_installed){
                $data = array_merge($data, array('product_advanced_attribute' => $this->model_module_advanced_product_attributes_attribute->getProductAttributes($product_id)));
            }

            return $this->addProduct($data);
        }
    }

    //Get Product Categories full data
    public function getCategoriesWithDesc($product_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "category WHERE category_id IN (SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '".(int)$product_id ."')";
        $categories = $this->db->query($sql);

        $fullCategories = [];
        $this->load->model('tool/image');

        foreach ($categories->rows as $key => $category) {

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category['category_id'] . "'");
            foreach ($query->rows as $result) {
                $description[$result['language_id']] = array(
                    'name' => $result['name'],
                    'description' => $result['description'],
                    'meta_keyword' => $result['meta_keyword'],
                    'meta_description' => $result['meta_description']
                );
            }

            if($category['image']){
                $image = $this->model_tool_image->resize($category['image'], 550, 550);
                $category['image'] = $image;
            }

            $category['category_description'] = $description;
            $fullCategories[] = $category;
        }

        return $fullCategories;
    }

    public function getOptionsWithDesc($product_id)
    {
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $images = $this->model_module_product_option_image_pro->getProductOptionImages($product_id);
        $product_poip_settings = $this->model_module_product_option_image_pro->getRealProductSettings($product_id);
        // >> Product Option Image PRO module

        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT po.*, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) WHERE po.product_id = '" . (int)$product_id . "'");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT pov.*, ov.image FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "'");

            foreach ($product_option_value_query->rows as $product_option_value) {
                // Product Option Image PRO module <<
                $option_value_images = array();
                if (isset($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']])) {
                    foreach ($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']] as $image) {
                        $option_value_images[] = $image;
                    }
                }
                // >> Product Option Image PRO module

                //Get option value names
                $option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = '" . (int)$product_option_value['option_value_id'] . "'");
                foreach ($option_value_query->rows as $value_result) {
                    $option_value_name[$value_result['language_id']] = array(
                        'name' => $value_result['name']
                    );
                }
                /////////////
                $image = '';
                if($product_option_value['image']){
                    $image = $this->model_tool_image->resize($product_option_value['image'], 200, 200);
                }

                $product_option_value_data[] = array(
                    'option_value_name' => $option_value_name,
                    // Product Option Image PRO module <<
                    'images' => $option_value_images,
                    // >> Product Option Image PRO module

                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'points' => $product_option_value['points'],
                    'points_prefix' => $product_option_value['points_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix'],
                    'image' => $image
                );
            }

            //Get option names
            $option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '" . (int)$product_option['option_id'] . "'");
            foreach ($option_query->rows as $result) {
                $option_name[$result['language_id']] = array(
                    'name' => $result['name']
                );
            }
            /////////////////

            $product_option_data[] = array(
                // Product Option Image PRO module <<
                'option_name' => $option_name,
                'option_type' => $product_option['type'],
                'poip_settings' => (isset($product_poip_settings[$product_option['product_option_id']]) ? $product_poip_settings[$product_option['product_option_id']] : false),
                // >> Product Option Image PRO module
                'product_option_id' => $product_option['product_option_id'],
                'option_id' => $product_option['option_id'],
                'product_option_value' => $product_option_value_data,
                'option_value' => $product_option['option_value'],
                'required' => $product_option['required']
            );
        }

        return $product_option_data;
    }

    public function deleteProduct($product_id)
    {

        // Aliexpress module
        $aliexpress = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension
            WHERE `code` = 'aliexpress_dropshipping'");

        if ($aliexpress->num_rows > 0 && $this->config->get('module_wk_dropship_status')) {
            $result = $this->db->query(
                "SELECT * FROM ".DB_PREFIX."warehouse_aliexpress_product WHERE product_id = '" . $this->db->escape((int)$product_id) . "' "
            )->row;
            if($result) {
                $this->db->query(
                    "DELETE FROM " . DB_PREFIX . "warehouse_aliexpress_product WHERE product_id = '" . $this->db->escape((int)$product_id) . "' "
                );
                $checkSellerProductExists = $this->db->query(
                    "SELECT * FROM ".DB_PREFIX."warehouse_aliexpress_product
                    WHERE aliexpress_seller_id = '" . (int)$result['aliexpress_seller_id'] . "'"
                );
                if (!$checkSellerProductExists->num_rows) {
                    $this->db->query(
                        "DELETE FROM " . DB_PREFIX . "warehouse_aliexpress_seller
                        WHERE id = '" . $this->db->escape((int)$result['aliexpress_seller_id']) . "'"
                    );
                }
            }
        }

        // Knawat module
        $knawat = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension
            WHERE `code` = 'knawat_dropshipping'");

        if ($knawat->num_rows > 0 && $this->config->get('module_knawat_dropshipping_status') == 'on') {

            // check if product is knawat product
            $knawat_product = $this->db->query("SELECT COUNT(resource_id) FROM " . DB_PREFIX . "knawat_metadata WHERE `resource_id` = " . (int) $product_id . " AND `meta_key` = 'is_knawat'");

            if ($knawat_product->num_rows == 1) {

                $product_sku = $this->db->query("SELECT `sku` FROM " . DB_PREFIX . "product WHERE `product_id` = " . (int) $product_id);

                // delete product from knawat api
                if ($product_sku->num_rows == 1 && $product_sku->row['sku']) {

                    $product_sku = $product_sku->row['sku'];
                    require_once( DIR_SYSTEM . 'library/knawat_dropshipping/knawatmpapi.php' );
                    $mp_api = new KnawatMPAPI( $this->registry );
                    $response = $mp_api->delete('catalog/products/' . $product_sku);

                    if ($response->product->status == 'success') {

                        $knawat_module = $this->load->model('module/knawat_dropshipping', ['return' => true]);

                        if($knawat_module->check_knawat_product($product_id)){
                            $knawat_module->delete_knawat_meta($product_id,'is_knawat');
                        }

                    }

                }

            }

        }

        //fire delete product trigger for zapier if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $product = $this->getProduct($product_id);
            if ($product) {
                $product['product_description'] = $this->getProductDescriptions($product_id);
                $this->model_module_zapier->deleteProductTrigger($product);
            }
        }

        /***************** Start ExpandCartTracking #347688  ****************/

        //get product count
        $products_count = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE demo = 0")->row['total'];

        // send mixpanel add product event and update user products count
        $this->load->model('setting/mixpanel');
        $this->model_setting_mixpanel->updateUser(['$products count'=>$products_count]);

        // send amplitude add product event and update user products count
        $this->load->model('setting/amplitude');
        $this->model_setting_amplitude->updateUser(['products count'=>$products_count]);


        /***************** End ExpandCartTracking #347688  ****************/

        //delete product from knawat if sync
        $this->load->model('module/knawat');
        if ($this->model_module_knawat->isInstalled()) {
            $this->model_module_knawat->deleteSyncProduct($product_id);
        }

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if ($queryMultiseller->num_rows) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "ms_product WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "ms_product_attribute WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        }

        $isDropna = $this->db->query('show tables like "product_to_dropna"');

        if ($isDropna->num_rows) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_dropna WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        
        //Elmodaqeq App
        if(\Extension::isInstalled('elmodaqeq')){
            $this->db->query("DELETE FROM " . DB_PREFIX . "elmodaqeq_product WHERE expandcart_product_id = " . (int)$product_id);
        }

        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->model_module_product_option_image_pro->deleteProductOptionValueImages($product_id);
        $this->model_module_product_option_image_pro->deleteRealProductSettings($product_id);
        // >> Product Option Image PRO module
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . $this->db->escape((int)$product_id) . "'");

        // product_bundles , check if app is installed and table is exist
        $this->load->model('module/product_bundles/settings');
        if($this->model_module_product_bundles_settings->isActive()){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_bundles WHERE main_product_id = '" . $this->db->escape((int)$product_id) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . $this->db->escape((int)$product_id) . "'");

        $product2Domains = $this->load->model('module/dedicated_domains/product_domain', ['return' => true]);

        if ($product2Domains->isActive()) {
            $product2Domains->deleteProductDomains($product_id);
        }

        //delete product from ebay if exist
        if (\Extension::isInstalled('ebay_dropshipping')  && $this->config->get('module_wk_ebay_dropship_status')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "commerce_ebay_product WHERE `product_id` = " . (int) $product_id);
            $this->db->query("DELETE FROM " . DB_PREFIX . "commerce_ebay_product_variation WHERE `product_id` = " . (int) $product_id);
            $this->db->query("DELETE FROM " . DB_PREFIX . "commerce_ebay_product_variation_option WHERE `product_id` = " . (int) $product_id);
        }

            //Product notify me App
        $this->load->model('module/product_notify_me');
        if ($this->model_module_product_notify_me->isActive()) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "md_product_notify_me WHERE product_id='" . $this->db->escape((int)$product_id) . "'");
        }
        ////////////////////

        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . $this->db->escape((int)$product_id) . "'");

        $this->cache->delete('product');
        $this->cache->delete('manufacturer.' . (int)$this->config->get('config_store_id'));

        $this->language->load('setting/setting');
        // 3 is Delete a product in audit_trial_event_type table
        // Author: Bassem
        try {
            $this->audit_trail->log([
                'event_type'=> 3,
                'event_desc'=>'Delete product',
                'resource_id' => $product_id,
                'resource_type' => 'product'
            ]);
        } catch (Exception $e) {}
        return $product_id;
    }

    public function deleteKnawatProductById($product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->model_module_product_option_image_pro->deleteProductOptionValueImages($product_id);
        $this->model_module_product_option_image_pro->deleteRealProductSettings($product_id);
        // >> Product Option Image PRO module
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

        // product_bundles , check if app is installed and table is exist
        $this->load->model('module/product_bundles/settings');
        if($this->model_module_product_bundles_settings->isActive()){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_bundles WHERE main_product_id = '" . (int)$product_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");

        $product2Domains = $this->load->model('module/dedicated_domains/product_domain', ['return' => true]);

        if ($product2Domains->isActive()) {
            $product2Domains->deleteProductDomains($product_id);
        }

        //Product notify me App
        $this->load->model('module/product_notify_me');
        if ($this->model_module_product_notify_me->isActive()) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "md_product_notify_me WHERE product_id='" . (int)$product_id . "'");
        }
        ////////////////////

        $this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");

        $this->cache->delete('product');
        $this->language->load('setting/setting');
        // 3 is Delete a product in audit_trial_event_type table
        // Author: Bassem
        try{
            $this->audit_trail->log([
                'event_type'=> 3,
                'event_desc'=>'Delete product',
                'resource_id' => $product_id,
                'resource_type' => 'product'
            ]);
        } catch (Exception $e) {}
        return $product_id;
    }

    public function deleteProductOptions($option_id) {
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE `option_id` = " . (int) $option_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE `option_id` = " . (int) $option_id);
    }

    public function getProduct($product_id)
    {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.archived = 0 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->row;
    }

    public function getProductByLanguageId($product_id,$language_id)
    {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language_id . "'");
        return $query->row;
    }

    public function checkIfProductIdIsExisted($id)
    {
        # code...
        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$id . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$id . "'");

        if($query->num_rows > 0)
            return true;

        return false;
    }
    public function checkIfProductSkuIsExisted($sku)
    {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE sku= '".$this->db->escape($sku)."' ");

        if($query->num_rows > 0)
            return true;

        return false;
    }
    public function getProducts($data = array(),$language_id = null)
    {

        $lang_id = $language_id ?? $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if(defined("POS_FLAG")&&POS_FLAG) {
            $sql = "SELECT *, (SELECT sum(wp.quantity) FROM wkpos_products wp where p.product_id = wp.product_id) as pos_all_quantity FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
        }

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE pd.language_id = '{$lang_id}' AND archived = 0 ";

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price LIKE '%" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity']) && !empty($data['filter_quantity']) ) {
            $sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
        }
        
        if (isset($data['filter_status']) && ( $data['filter_status'] == "0" || $data['filter_status'] == "1")){
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_barcode']) && !is_null($data['filter_barcode']) && !empty($data['filter_barcode']) ) {
            $sql .= " AND p.barcode = '" . $this->db->escape($data['filter_barcode']) . "'";
        }

        if (isset($data['filter_category_id']) && !is_null($data['filter_category_id']) && !empty($data['filter_category_id']) ) {
            $sql .= " AND p2c.category_id = '" . $this->db->escape($data['filter_category_id']) . "'";
        }

        if (isset($data['filter_manufacturer_id']) && !is_null($data['filter_manufacturer_id']) && !empty($data['filter_manufacturer_id']) ) {
            $sql .= " AND p.manufacturer_id = '" . $this->db->escape($data['filter_manufacturer_id']) . "'";
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%' OR p.barcode = '" . $this->db->escape($data['filter_name']) . "' OR p.sku = '" . $this->db->escape($data['filter_name']) . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
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

    public function getProductsFields($fields = array())
    {

        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

        $sql = "SELECT product.product_id,".implode(',', $fields)." FROM " . DB_PREFIX . "product LEFT JOIN " . DB_PREFIX . "product_description  ON (product.product_id = product_description.product_id AND product.archived = 0) ";

        $sql .= " WHERE product_description.language_id = '{$lang_id}'";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductsByCategoryId($category_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND  p.archived = 0 AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

        return $query->rows;
    }

    public function getProductsNamesList() {
        $language_id = $this->config->get('config_language_id');
        return $this->db->query("SELECT `product_id`, `name` FROM " . DB_PREFIX . "product_description WHERE language_id='" . (int)$language_id . "'")->rows;
    }
    
    public function getProductsList($category_ids = []) {

        $language_id = $this->config->get('config_language_id');
        $sql = "";
        $products = [];

        if (\Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
            $sql .= "p.manual_sort,p2cs.manual_sort as category_manual_sort,";
            $sql =  "        
                SELECT p.product_id as p_product_id, p.sort_order ,p.manual_sort as product_manual_sort, pd.name, 
                     p2cs.manual_sort as manual_sort ,p2cs.product_id p2cs_product_id,p2cs.category_id
                FROM product p 
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id) 
                LEFT JOIN product_to_category ptc ON(ptc.product_id = p.product_id) 
                LEFT JOIN " . DB_PREFIX . "product_to_categories_sorting p2cs ON (p2cs.product_id = p.product_id)
                WHERE pd.language_id='" . (int)$language_id ."' AND p.archived = 0 ";

            if(is_array($category_ids) and count($category_ids) > 0){
                $category_ids_string = implode(",",$category_ids);
                $sql .= " AND p2cs.category_id in (". $category_ids_string . ") ";
            }else if ( (int)$category_ids ){
                $sql .= " AND p2cs.category_id = ".(int) $category_ids . " ";
            }

            $sql .=   " GROUP BY p.product_id ORDER BY ".(($category_ids)? "p2cs.manual_sort" : " p.manual_sort")." ASC";


            $products = $this->db->query($sql)->rows;
            if(count($products) == 0){
                $products = $this->get_products_for_sort($category_ids);
            }

        } else {
            $products = $this->db->query("        
                SELECT p.product_id, p.sort_order, pd.name , GROUP_CONCAT(category_id) as categories
                FROM product p 
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id) 
                LEFT JOIN product_to_category ptc ON(ptc.product_id = p.product_id)
                WHERE pd.language_id='" . (int)$language_id ."' AND p.archived = 0 GROUP BY p.product_id ORDER BY sort_order ASC"
            )->rows;
        }
        
        
        $products_after_filter = $products;

//        if(!empty($category_ids)){
//            $products_after_filter = [];
//
              //Filter by categories..
//            array_walk($products, function($v, $k) use ($category_ids, &$products_after_filter){
//                if(!empty( array_intersect(explode(',', $v['categories']) , $category_ids ) )){
//                    $products_after_filter[] = $v;
//                }
//            });
//        }

        return $products_after_filter;
    }

    public function get_products_for_sort($category_ids){


        $language_id = $this->config->get('config_language_id');


        $sql =  "        
                SELECT p.product_id as p_product_id, p.sort_order ,p.manual_sort as product_manual_sort, pd.name, 
                     p2cs.manual_sort as manual_sort ,p2cs.product_id p2cs_product_id,p2cs.category_id
                FROM product p 
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id) 
                LEFT JOIN product_to_category ptc ON(ptc.product_id = p.product_id) 
                LEFT JOIN " . DB_PREFIX . "product_to_categories_sorting p2cs ON (p2cs.product_id = p.product_id)
                WHERE pd.language_id='" . (int)$language_id ."' AND p.archived = 0 ";
		
        if(is_array($category_ids) and count($category_ids) > 0){
            $category_ids_string = implode(",",$category_ids);
            $sql .= " AND ptc.category_id in (". $category_ids_string . ") ";
        }else if ( (int)$category_ids ){
            $sql .= " AND ptc.category_id = ".(int) $category_ids . " ";
        }
	
        $sql .= " AND p.archived = 0 GROUP BY p.product_id ORDER BY ".(($category_ids)? "p2cs.manual_sort" : " p.manual_sort") . " ASC";

        return  $this->db->query($sql)->rows;
    }

    public function getProductDescriptions(int $product_id, int $language_id = null)
    {
        $product_description_data = array();
        
        $sql = "SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'";
        
        if (!is_null($language_id)) {
            $sql.= " AND language_id='" . (int)$language_id . "'";
        }

        $query = $this->db->query($sql);

        $this->load->model('module/custom_product_editor');
        
        $customEditorAppInstalled = $this->model_module_custom_product_editor->isInstalled();
        
        $multiseller_installed = \Extension::isInstalled('multiseller');

        foreach ($query->rows as $result) {
            $description = array(
                'name' => $result['name'],
                'language_id' => $result['language_id'],
                'description' => htmlspecialchars_decode($result['description']),
                'meta_keyword' => $result['meta_keyword'],
                'meta_description' => $result['meta_description'],
                'tag' => $result['tag']
            );

            if($multiseller_installed){
                $description['seller_notes'] = $result['seller_notes'];            
            }
            
            if ($customEditorAppInstalled) {
                $description['custom_html'] = htmlspecialchars_decode($result['custom_html']); 
            }
            
            $product_description_data[$result['language_id']] = $description; 
        }

        return $product_description_data;
    }

    public function getProductCategories($product_id)
    {
        $product_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_category_data[] = $result['category_id'];
        }

        return $product_category_data;
    }

    public function getProductCategoriesNames($product_id, $languageId) {
        $product_category_data = array();

        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . " category_description inner join  product_to_category on category_description.category_id = product_to_category.category_id WHERE product_to_category.product_id = '" . (int)$product_id . "' and category_description.language_id = " . (int) $languageId );

        foreach ($query->rows as $result) {
            $product_category_data[] = $result['name'];
        }

        return $product_category_data;
    }

    public function getProductFilters($product_id)
    {
        $product_filter_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_filter_data[] = $result['filter_id'];
        }

        return $product_filter_data;
    }

    public function getProductAttributes($product_id)
    {
        $product_attribute_data = array();

        $product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

        foreach ($product_attribute_query->rows as $product_attribute) {

            if($this->config->get('wk_amazon_connector_status')){
                $getSpecificationEntry = $this->Amazonconnector->checkSpecificationEntry(array('attribute_id' => $product_attribute['attribute_id']));
                if(isset($getSpecificationEntry) && $getSpecificationEntry){
                    continue;
                }
            }
            $product_attribute_description_data = array();

            $product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

            foreach ($product_attribute_description_query->rows as $product_attribute_description) {
                $product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
            }

            $product_attribute_data[] = array(
                'attribute_id' => $product_attribute['attribute_id'],
                'product_attribute_description' => $product_attribute_description_data
            );
        }

        return $product_attribute_data;
    }

    public function getProductOptions($product_id,$data=[])
    {
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $images = $this->model_module_product_option_image_pro->getProductOptionImages($product_id);
        $product_poip_settings = $this->model_module_product_option_image_pro->getRealProductSettings($product_id);
        // >> Product Option Image PRO module

        $product_option_data = array();

        /**
         * Get option values for specific option
         */
        $limited_option_id_query = "";
        if(isset($data['option_id'])){
            $limited_option_id_query = " AND option.option_id='". $data['option_id'] ."'";
        }
        $product_option_query = $this->db->query("SELECT *, option.type AS option_type FROM " . DB_PREFIX . "product_option LEFT JOIN " . DB_PREFIX . " `option` ON (option.option_id = product_option.option_id) WHERE product_option.product_id = '" . (int)$product_id . "' $limited_option_id_query ORDER BY product_option.sort_order");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            if($this->config->get('wk_amazon_connector_status')){
                $getVariationEntry = $this->Amazonconnector->checkVariationEntry($product_option);
                if(isset($getVariationEntry) && $getVariationEntry){
                    continue;
                }
            }

            $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;

            $limited_options_query = ' LIMIT 0,100 ';
            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 10;
                }

                $limited_options_query = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
            if ($product_option['option_type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
                $query = "SELECT *,pov.option_value_id as option_value_id FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id AND pd.language_id = '{$lang_id}') WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY pov.sort_order". (string)$limited_options_query;
                $product_option_value_query = $this->db->query($query);
            } else {
                $query = "SELECT *,pov.option_value_id as option_value_id FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id AND ovd.language_id = '{$lang_id}') WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY sort_order".(string)$limited_options_query;
                $product_option_value_query = $this->db->query($query);
            }

            foreach ($product_option_value_query->rows as $product_option_value) {
                // Product Option Image PRO module <<
                $option_value_images = array();
                if (isset($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']])) {
                    foreach ($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']] as $image) {
                        $option_value_images[] = $image;
                    }
                }
                // >> Product Option Image PRO module

                $product_option_value_data[] = array(
                    // Product Option Image PRO module <<
                    'images' => $option_value_images,
                    // >> Product Option Image PRO module

                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'points' => $product_option_value['points'],
                    'points_prefix' => $product_option_value['points_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix'],
                    'name'=>$product_option_value['name']
                );
            }

            $product_option_data[] = array(
                // Product Option Image PRO module <<
                'poip_settings' => (isset($product_poip_settings[$product_option['product_option_id']]) ? $product_poip_settings[$product_option['product_option_id']] : false),
                // >> Product Option Image PRO module
                'product_option_id' => $product_option['product_option_id'],
                'type' => $product_option['option_type'],
                'option_id' => $product_option['option_id'],
                'product_option_value' => $product_option_value_data,
                'option_value' => $product_option['option_value'],
                'required' => $product_option['required'],
                'total'=> $this->getProductOptionValuesCount($product_id,$product_option['option_id'])

            );
        }

        return $product_option_data;
    }

    public function getProductOptionValuesCount($product_id,$product_option)
    {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "product_option_value  WHERE product_id = '" . (int)$product_id . "' AND option_id='". (int)$product_option ."'";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }


    public function getProductOptionsWithDescription($product_id)
    {
        // Product Option Image PRO module <<
        $this->load->model('module/product_option_image_pro');
        $this->load->model('catalog/option');
        $images = $this->model_module_product_option_image_pro->getProductOptionImages($product_id);
        $product_poip_settings = $this->model_module_product_option_image_pro->getRealProductSettings($product_id);
        // >> Product Option Image PRO module

        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT *, option.type AS option_type FROM " . DB_PREFIX . "product_option LEFT JOIN " . DB_PREFIX . " `option` ON (option.option_id = product_option.option_id) WHERE product_option.product_id = '" . (int)$product_id . "' ORDER BY product_option.sort_order");
        $ProductOptionSelected = [];

        foreach ($product_option_query->rows as $product_option) {
            $ProductOptionSelected[]=  $product_option['option_id'];
            $product_option_value_data = array();
            if($this->config->get('wk_amazon_connector_status')){
                $getVariationEntry = $this->Amazonconnector->checkVariationEntry($product_option);
                if(isset($getVariationEntry) && $getVariationEntry){
                    continue;
                }
            }

            $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
            if ($product_option['option_type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
                $query = "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ovd.option_value_id = pov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id AND pd.language_id = '{$lang_id}') WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY pov.sort_order";
                $product_option_value_query = $this->db->query($query);

            } else {
                $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov  LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ovd.option_value_id = pov.option_value_id AND ovd.language_id = '{$lang_id}') WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY sort_order");
            }
            // var_dump($product_option_value_query);die;
            foreach ($product_option_value_query->rows as $product_option_value) {
                // Product Option Image PRO module <<
                $option_value_images = array();
                if (isset($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']])) {
                    foreach ($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']] as $image) {
                        $option_value_images[] = $image;
                    }
                }
                // >> Product Option Image PRO module

                $product_option_value_data[] = array(
                    // Product Option Image PRO module <<
                    'images' => $option_value_images,
                    // >> Product Option Image PRO module

                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'points' => $product_option_value['points'],
                    'points_prefix' => $product_option_value['points_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix'],
                    'name' => $product_option_value['name'],
                    'sort_order' => $product_option_value['sort_order'],

                );
            }




            $product_option_data[] = array(
                // Product Option Image PRO module <<
                'poip_settings' => (isset($product_poip_settings[$product_option['product_option_id']]) ? $product_poip_settings[$product_option['product_option_id']] : false),
                // >> Product Option Image PRO module
                'product_option_id' => $product_option['product_option_id'],
                'option_id' => $product_option['option_id'],
                'product_option_value' => $product_option_value_data,
                'option_value' => $product_option['option_value'],
                'required' => $product_option['required']
            );
        }
        // not selected
        $optionnotselectedgroup = implode(',',$ProductOptionSelected);
        if (count($product_option_query->rows) > 0)
        {
            $product_option_data_notselected = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description  WHERE option_description.option_id  NOT IN ({$optionnotselectedgroup}) AND option_description.language_id = '{$lang_id}'");
        }
        else
        {
            $product_option_data_notselected = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description  WHERE option_description.option_id AND option_description.language_id = '{$lang_id}'");
        }
        $product_option_data['not_selected'] = $product_option_data_notselected->rows;
        foreach($product_option_data['not_selected'] as $k => $pons){

            // $option_description_notselected = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description  WHERE option_value_description.option_id = {$pons['option_id']} AND option_value_description.language_id = '{$lang_id}'");
            // $product_option_data['not_selected'][$k]['option_description'] = $option_description_notselected->rows;
 
            $option_description_notselected = $this->db->query("SELECT DISTINCT option_value_id  FROM " . DB_PREFIX . "option_value_description  WHERE option_value_description.option_id = {$pons['option_id']}");
            $option_values = array();
            foreach ($option_description_notselected->rows as $option_value_id)
            {
                $value_description = array(
                    'option_value_id' => $option_value_id['option_value_id'],
                    'option_value_description' => $this->model_catalog_option->getOptionValueDescription($option_value_id['option_value_id']),
                );
                array_push($option_values, $value_description);
            }
            $product_option_data['not_selected'][$k]['option_values'] = $option_values;

        }
        return $product_option_data;
    }


    public function getProductVariationSku($product_id)
    {
        $query = $this->db->query("SELECT * FROM product_variations WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
    }

    public function getProductOptionsCustom($product_id)
    {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT *, option.type AS option_type FROM " . DB_PREFIX . "product_option LEFT JOIN " . DB_PREFIX . " `option` ON (option.option_id = product_option.option_id) WHERE product_option.product_id = '" . (int)$product_id . "'");

        foreach ($product_option_query->rows as $product_option) {
            if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                $product_option_value_data = array();

		        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
                if ($product_option['option_type'] == 'product' && \Extension::isInstalled('product_builder') && $this->config->get('product_builder')['status']) {
                    $query = "SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id AND pd.language_id = '{$lang_id}') WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order";
                    $product_option_value_query = $this->db->query($query);
                } else {
                    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '{$lang_id}' ORDER BY ov.sort_order");
                }
                foreach ($product_option_value_query->rows as $product_option_value) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id' => $product_option_value['option_value_id'],
                        'name' => $product_option_value['name'],
                        'image' => $product_option_value['image'],
                        'quantity' => $product_option_value['quantity'],
                        'subtract' => $product_option_value['subtract'],
                        'price' => $product_option_value['price'],
                        'price_prefix' => $product_option_value['price_prefix'],
                        'weight' => $product_option_value['weight'],
                        'weight_prefix' => $product_option_value['weight_prefix']
                    );
                }

                $product_option_data[] = array(
                    'product_option_id' => $product_option['product_option_id'],
                    'option_id' => $product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_option_value_data,
                    'required' => $product_option['required']
                );
            } else {
                $product_option_data[] = array(
                    'product_option_id' => $product_option['product_option_id'],
                    'option_id' => $product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_option['option_value'],
                    'required' => $product_option['required']
                );
            }
        }

        return $product_option_data;
    }

    public function getOptionValue($ov_id) {

        $lang_id = $this->config->get('config_language_id') ? (int)$this->config->get('config_language_id') : 1;
        return $this->db->query("SELECT ov.*, ovd.*, od.name o_name FROM " . DB_PREFIX . "option_value ov LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) LEFT JOIN `" . DB_PREFIX . "option` o ON (o.option_id = ov.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id AND od.language_id = '{$lang_id}') WHERE ov.option_value_id = '" . (int)$ov_id . "' AND ovd.language_id = '{$lang_id}' ORDER BY ov.sort_order")->row;
    }

    public function getProductImages($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
    }

    public function getProductDiscounts($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, quantity, price");

        return $query->rows;
    }

    public function getProductSpecials($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY `default` desc,priority, price");

        return $query->rows;
    }

    public function getProductSpecialDefault($product_id)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product_special WHERE `default`=1 AND product_id = '" .(int)$product_id
            ."' ORDER BY priority, price  LIMIT 1"
        );

        return $query->row;
    }

    /**
     * Union discounts and specials in one query
     *
     * @param array $filter
     *
     * @return array|bool
     */
    public function getProductDiscountsAndSpecials($filter = [])
    {
        $wheres = [];

        $discountColumns = [
            'product_discount_id as uid',
            'product_id',
            'customer_group_id',
            'quantity',
            'priority',
            'price',
            'date_start',
            'date_end',
            '"discount" as type'
        ];

        $specialColumns = [
            'product_special_id as uid',
            'product_id',
            'customer_group_id',
            '1 as quantity',
            'priority',
            'price',
            'date_start',
            'date_end',
            '"special" as type'
        ];

        if (isset($filter['product_id']) && (int)$filter['product_id'] > 0) {
            $wheres[] = sprintf('AND product_id = %d', $filter['product_id']);
        }

        if (isset($filter['customer_group_id']) && (int)$filter['customer_group_id'] > 0) {
            $wheres[] = sprintf('AND customer_group_id = %d', $filter['customer_group_id']);
        }

        $q = [];
        $q[] = sprintf('SELECT %s FROM product_discount', implode(', ', $discountColumns));
        if (count($wheres) > 0) {
            $q[] = 'WHERE 1=1 ' . implode(' ', $wheres);
        }
        $q[] = 'UNION';
        $q[] = sprintf('SELECT %s FROM product_special', implode(', ', $specialColumns));
        if (count($wheres) > 0) {
            $q[] = 'WHERE 1=1 ' . implode(' ', $wheres);
        }

        $data = $this->db->query(implode(' ', $q));

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }

    public function getProductRewards($product_id)
    {
        $product_reward_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
        }

        return $product_reward_data;
    }

    public function getProductDownloads($product_id)
    {
        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){
            $product_download_data = array();

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

            foreach ($query->rows as $result) {
                $product_download_data[] = $result['download_id'];
            }

            return $product_download_data;
        }
    }

    public function getProductStores($product_id)
    {
        $product_store_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_store_data[] = $result['store_id'];
        }

        return $product_store_data;
    }

    public function getProductLayouts($product_id)
    {
        $product_layout_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $product_layout_data;
    }

    public function getProductRelated($product_id, $with_details = false)
    {
        $product_related_data = array();

        if ($with_details == true)
        {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pr.related_id = pd.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            return $query->rows;
        }
        else{
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
            foreach ($query->rows as $result) {
                $product_related_data[] = $result['related_id'];
            }
            return $product_related_data;
        }
    }

    public function getProductBundles($main_product_id, $with_details = false)
    {
        // prodcut_bundles app
        $this->load->model('module/product_bundles/settings');
        $product_bundles_app_status = $this->model_module_product_bundles_settings->isActive();
        if($product_bundles_app_status){
            $product_bundles_data = array();
            if ($with_details == true)
            {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_bundles pb LEFT JOIN " . DB_PREFIX . "product p ON (pb.bundle_product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pb.bundle_product_id = pd.product_id) WHERE pb.main_product_id = '" . (int)$main_product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
                return $query->rows;
            }
            else{
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_bundles WHERE main_product_id = '" . (int)$main_product_id . "'");
                return $query->rows;
            }
        }

    }

    public function checkBundled($product_ids,$main_product_id)
    {
        $query = $this->db->query("SELECT COUNT(pb.bundle_product_id) as is_bundled FROM " . DB_PREFIX . "product_bundles pb WHERE pb.main_product_id !='" . (int)$main_product_id . "' AND pb.bundle_product_id IN(".implode(',', $product_ids).")");
        return $query->row['is_bundled'];
    
    }
    
    public function getTotalProducts($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.archived = 0 ";

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price LIKE '%" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalProductsCount()
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p WHERE p.archived=0";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
    public function getTotalActiveProductsCount()
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p where p.status=1 AND p.archived=0";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalProductsByTaxClassId($tax_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByStockStatusId($stock_status_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "' AND archived = 0 ");

        return $query->row['total'];
    }

    public function getTotalProductsByWeightClassId($weight_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "' AND archived = 0 ");

        return $query->row['total'];
    }

    public function getTotalProductsByLengthClassId($length_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'  AND archived = 0 ");

        return $query->row['total'];
    }

    public function getTotalProductsByDownloadId($download_id)
    {
        $this->load->model('module/product_attachments');
        if( $this->model_module_product_attachments->isActive()){
            $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

            return $query->row['total'];
        }

    }

    public function getTotalProductsByManufacturerId($manufacturer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'  AND archived = 0");

        return $query->row['total'];
    }

    public function getTotalProductsByAttributeId($attribute_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByOptionId($option_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

        return $query->row['total'];
    }


    public function getProductsByOptionValueId($option_value_id)
    {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_option_value WHERE option_value_id = '" . (int)$option_value_id . "'");

        return $query->rows;
    }

    public function getTotalProductsByLayoutId($layout_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

        return $query->row['total'];
    }

    public function getProductQuantityInOrders($product_id, $pendingStatuses)
    {
        $quantity = $this->db->query("
                SELECT SUM(`op`.`quantity`) AS `order_quantity`
                FROM `".DB_PREFIX."order_product` `op`
                LEFT JOIN `order` `o`
                ON `op`.`order_id` = `o`.`order_id`
                WHERE `op`.`product_id` = ".(int)$product_id."
                AND `o`.`order_status_id` IN(".implode(',', $pendingStatuses).")
            ")->row['order_quantity'] ?? 0;

        return $quantity;
    }

    public function getProductsToFilter($data = [], $filterData = [], $pos = false)
    {
        $queryString = $categoryNameQuery = $fields = [];

        if( isset($filterData['language_code']) && $filterData['language_code'] != '*'){
            $this->load->model('localisation/language');
            $languageId = $this->model_localisation_language->getLanguageByCode($filterData['language_code'])['language_id'];
            $language_code = $filterData['language_code'];
        }
        else {
            $languageId = (int)$this->config->get('config_language_id');
            $language_code = $this->config->get('config_language');
        }

        $groupBYStatement = 'GROUP BY p.product_id';

        $categoryNameQuery[] = 'SELECT name FROM category_description AS cd';
        $categoryNameQuery[] = 'WHERE cd.category_id=p2c.category_id';
        $categoryNameQuery[] = 'AND cd.language_id="' . $languageId . '"';

        $fieldsList = [
                          'p.*',
                          // 'pd.*',
                          // 'pd2.name as localized_name',
                          'p2c.category_id',
                          '(' . implode(' ', $categoryNameQuery) . ') AS category_name'
                      ];

        if(!isset($filterData['language_code']) || $filterData['language_code'] != '*' ){
            $fieldsList[] = 'pd.*';
            $fieldsList[] = 'pd2.name as localized_name';
        }

        //POS Fields
        $posFields = '';
        if($pos){
            $posFields = '(SELECT sum(wp.quantity) FROM wkpos_products wp where p.product_id = wp.product_id) as pos_quantity';
            array_push($fieldsList, $posFields);
        }
        ////////

        $fields = implode(',', $fieldsList);

        //To Get Categories ids, images & names lists
        $cat_fields = ', GROUP_CONCAT( DISTINCT p2c.category_id) AS categories_ids,
        GROUP_CONCAT( DISTINCT c.image) as categories_images,
        GROUP_CONCAT( DISTINCT (SELECT name FROM category_description AS cd WHERE cd.category_id=p2c.category_id AND cd.language_id="' . $languageId .'") ) AS categories_names';
        // GROUP_CONCAT( DISTINCT (SELECT name FROM category_description AS cd WHERE cd.category_id=p2c.category_id AND cd.language_id="' . $languageId .'") ) AS categories_names';

        $queryString[] = 'SELECT ' . $fields . $cat_fields . ' FROM ' . DB_PREFIX . 'product AS p';
        $queryString[] = ' JOIN ' . DB_PREFIX . 'product_description AS pd';
        $queryString[] = 'ON (p.product_id=pd.product_id)';
        $queryString[] = ' LEFT JOIN ' . DB_PREFIX . 'product_description AS pd2';
        $queryString[] = 'ON (p.product_id=pd2.product_id and pd2.language_id="' . $languageId . '")';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'product_to_category AS p2c';
        $queryString[] = 'ON (p.product_id = p2c.product_id)';

        //To Get categories images list
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'category AS c';
        $queryString[] = 'ON (c.category_id = p2c.category_id)';

        //POS JOINs
        if($pos){
            //$queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'wkpos_products wp ON (p.product_id = wp.product_id)';
            $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'wkpos_barcode wb ON (p.product_id = wb.product_id)';
        }
        //////////
            //        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'category_description AS cd';
            //        $queryString[] = 'ON (p2c.category_id = cd.category_id)';

            //        $queryString[] = 'WHERE pd.language_id = "' . (int)$this->config->get('config_language_id') . '"';

        // todo add we will add this later "AND p.demo=0"
        $queryString[] = 'WHERE 1=1';
        // filter out deleted (archived) products 
        $queryString[] = ' AND p.archived = 0 ';

        //        $queryString[] = 'AND cd.language_id = "' . (int)$this->config->get('config_language_id') . '"';

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM (' .
            str_replace($fields, '0 as fakeColumn', (implode(' ', $queryString) . ' ' . $groupBYStatement))
            . ') AS t'
        )->row['totalData'];

        if (isset($filterData['search'])) {
            $queryString[] = 'AND (';
            $queryString[] = 'pd.name LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.model LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.sku LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.barcode LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = ')';
        }

        if(isset($filterData["salesChannel"]) && $filterData["salesChannel"] != "") {
            if($filterData["salesChannel"] == "facebook")
            $queryString[] = " AND ( p.isPublishedOnCatalog = '1' ) ";
        }

        if (isset($filterData['categories']) && count($filterData['categories']) > 0) {

            $categoriesIds = implode(', ', $this->filterArrayOfIds($filterData['categories']));

            $queryString[] = 'AND (p2c.category_id IN (' . $categoriesIds . '))';
            
        } else if (
            isset($filterData['sellers']) && count($filterData['sellers']) > 0 &&
            $productsIdsForSellers = $this->getProductsIdsForSellers($filterData['sellers'])
        ) {
            $queryString[] = 'AND (p.product_id IN (' . $productsIdsForSellers . '))';
        }

        if (isset($filterData['brands']) && count($filterData['brands']) > 0) {

            $brandsIds = implode(', ', $this->filterArrayOfIds($filterData['brands']));

            $queryString[] = 'AND (p.manufacturer_id IN (' . $brandsIds . '))';
        }

        if (isset($filterData['ranges']) && count($filterData['ranges']) > 0) {

            $ranges = $filterData['ranges'];
            
            if (isset($ranges['price'])) {
                $price = $ranges['price'];

                if (isset($price['min']) && isset($price['max']) && $price['max'] != '' && $price['min'] != '') {
                    if ((int)$price['min'] != (int)$price['max']) {
                        $queryString[] = 'AND ((p.price >= ' . $price['min'] . ') AND (p.price <= ' . $price['max'] . '))';
                    }

                    if ((int)$price['min'] == (int)$price['max']) {
                        $queryString[] = 'AND ((p.price = ' . $price['min'] . '))';
                    }

                }
            }

            if (isset($ranges['quantity'])) {
                $quantity = $ranges['quantity'];

                if (isset($quantity['min']) && isset($quantity['max']) && $quantity['max'] != '' && $quantity['min'] != '') {
                    if ( $quantity['min'] != $quantity['max']) {
                        $queryString[] = 'AND (p.quantity BETWEEN ' . $quantity['min'] . ' AND ' . $quantity['max'] . ')';
                    }
    
                    if ($quantity['min'] == $quantity['max']) {
                        $queryString[] = 'AND (p.quantity = ' . $quantity['min'] . ')';
                    }
                }
                
            }

            if (isset($ranges['points'])) {
                $points = $ranges['points'];

                if (((int)$points['min'] > 0 || (int)$points['max'] > 0) && $points['min'] != $points['max']) {
                    $queryString[] = 'AND (p.points BETWEEN ' . $points['min'] . ' AND ' . $points['max'] . ')';
                }

                if (((int)$points['min'] > 0 || (int)$points['max'] > 0) && $points['min'] == $points['max']) {
                    $queryString[] = 'AND (p.points = ' . $points['min'] . ')';
                }
            }

            if (isset($ranges['cost_price'])) {
                $costPrice = $ranges['cost_price'];

                if (
                    (isset($costPrice['min']) || isset($costPrice['max'])) &&
                    $costPrice['min'] != $costPrice['max']
                ) {
                    $queryString[] = 'AND (';
                    $queryString[] = '(p.cost_price >= ' . $costPrice['min'] . ')';
                    $queryString[] = 'AND';
                    $queryString[] = '(p.cost_price <= ' . $costPrice['max'] . ')';
                    $queryString[] = ')';
                }

                if (
                    ( isset($costPrice['min']) || isset($costPrice['max']) ) &&
                    $costPrice['min'] == $costPrice['max'] && !empty($costPrice['min']) && !empty($costPrice['max'])
                ) {
                    $queryString[] = 'AND (p.cost_price = ' . $costPrice['min'] . ')';
                }
            }

            if (isset($ranges['weight'])) {
                $weight = $ranges['weight'];

                if (((int)$weight['min'] > 0 || (int)$weight['max'] > 0) && $weight['min'] != $weight['max']) {
                    $queryString[] = 'AND (p.weight BETWEEN ' . $weight['min'] . ' AND ' . $weight['max'] . ')';
                }

                if (((int)$weight['min'] > 0 || (int)$weight['max'] > 0) && $weight['min'] == $weight['max']) {
                    $queryString[] = 'AND (p.weight = ' . $weight['min'] . ')';
                }
            }

            if (isset($ranges['length'])) {
                $length = $ranges['length'];

                if (((int)$length['min'] > 0 || (int)$length['max'] > 0) && $length['min'] != $length['max']) {
                    $queryString[] = 'AND (p.length BETWEEN ' . $length['min'] . ' AND ' . $length['max'] . ')';
                }

                if (((int)$length['min'] > 0 || (int)$length['max'] > 0) && $length['min'] == $length['max']) {
                    $queryString[] = 'AND (p.length = ' . $length['min'] . ')';
                }
            }

            if (isset($ranges['width'])) {
                $width = $ranges['width'];

                if (((int)$width['min'] > 0 || (int)$width['max'] > 0) && $width['min'] != $width['max']) {
                    $queryString[] = 'AND (p.width BETWEEN ' . $width['min'] . ' AND ' . $width['max'] . ')';
                }

                if (((int)$width['min'] > 0 || (int)$width['max'] > 0) && $width['min'] == $width['max']) {
                    $queryString[] = 'AND (p.width = ' . $width['min'] . ')';
                }
            }

            if (isset($ranges['height'])) {
                $height = $ranges['height'];

                if (((int)$height['min'] > 0 || (int)$height['max'] > 0) && $height['min'] != $height['max']) {
                    $queryString[] = 'AND (p.height BETWEEN ' . $height['min'] . ' AND ' . $height['max'] . ')';
                }

                if (((int)$height['min'] > 0 || (int)$height['max'] > 0) && $height['min'] == $height['max']) {
                    $queryString[] = 'AND (p.height = ' . $height['min'] . ')';
                }
            }

            unset($ranges);
        }

        if (isset($filterData['booleans']) && count($filterData['booleans']) > 0) {
            $booleans = $filterData['booleans'];

            if (isset($booleans['image'])) {
                if ($booleans['image'] == '1') {
                    $queryString[] = 'AND (p.image IS NOT NULL OR  p.image NOT LIKE "%no_image%" )';
                } else if ($booleans['image'] == '0') {
                    $queryString[] = 'AND (p.image IS NULL OR p.image LIKE "%no_image%")';
                }
            }

            if (isset($booleans['subtract'])) {
                if ($booleans['subtract'] == '1') {
                    $queryString[] = 'AND (p.subtract = 1)';
                } else if ($booleans['subtract'] == '0') {
                    $queryString[] = 'AND (p.subtract = 0)';
                }
            }

            if (isset($booleans['status'])) {
                if ($booleans['status'] == '1') {
                    $queryString[] = 'AND (p.status = 1)';
                } else if ($booleans['status'] == '0') {
                    $queryString[] = 'AND (p.status = 0)';
                }
            }

            if (isset($booleans['shipping'])) {
                if ($booleans['shipping'] == '1') {
                    $queryString[] = 'AND (p.shipping = 1)';
                } else if ($booleans['shipping'] == '0') {
                    $queryString[] = 'AND (p.shipping = 0)';
                }
            }

            $this->load->model('module/product_attachments');
            if( $this->model_module_product_attachments->isActive()){
                if (isset($booleans['downloads'])) {
                    if ($booleans['downloads'] == '1') {
                        $downloadsSubQuery = '(SELECT product_id FROM product_to_download as p2d group by p2d.product_id)';
                        $queryString[] = 'AND (p.product_id IN (' . $downloadsSubQuery . '))';
                    } else if ($booleans['downloads'] == '0') {
                        $downloadsSubQuery = '(SELECT product_id FROM product_to_download as p2d group by p2d.product_id)';
                        $queryString[] = 'AND (p.product_id NOT IN (' . $downloadsSubQuery . '))';
                    }
                }
            }

            if (isset($booleans['discounts'])) {
                if ($booleans['discounts'] == '1') {
                    $discountSubQuery = '(SELECT product_id FROM product_discount as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id IN (' . $discountSubQuery . '))';
                } else if ($booleans['discounts'] == '0') {
                    $discountSubQuery = '(SELECT product_id FROM product_discount as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id NOT IN (' . $discountSubQuery . '))';
                }
            }

            if (isset($booleans['specials'])) {
                if ($booleans['specials'] == '1') {
                    $specialSubQuery = '(SELECT product_id FROM product_special as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id IN (' . $specialSubQuery . '))';
                } else if ($booleans['specials'] == '0') {
                    $specialSubQuery = '(SELECT product_id FROM product_special as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id NOT IN (' . $specialSubQuery . '))';
                }
            }

            if (isset($booleans['specialAndDiscounts'])) {
                if ($booleans['specialAndDiscounts'] == '1') {
                    /*
                    $discountSubQuery = '(SELECT product_id FROM product_discount as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id IN (' . $discountSubQuery . '))';
                    */
                    $specialSubQuery = '(SELECT product_id FROM product_special as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id IN (' . $specialSubQuery . '))';
                } else if ($booleans['specialAndDiscounts'] == '0') {
                    /*
                    $discountSubQuery = '(SELECT product_id FROM product_discount as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id NOT IN (' . $discountSubQuery . '))';
                    */
                    $specialSubQuery = '(SELECT product_id FROM product_special as pd group by pd.product_id)';
                    $queryString[] = 'AND (p.product_id NOT IN (' . $specialSubQuery . '))';
                }
            }

            unset($booleans);
        }

        if (isset($filterData['date_available'])) {

            $startDate = null;
            $endDate = null;
            if (isset($filterData['date_available']['start']) && isset($filterData['date_available']['start'])) {
                $startDate = strtotime($filterData['date_available']['start']);
                $endDate = strtotime($filterData['date_available']['end']);
            }

            if (($startDate && $endDate) && $endDate > $startDate) {

                $formattedStartDate = date('Y-m-d', $startDate);
                $formattedEndDate = date('Y-m-d', $endDate);

                $queryString[] = 'AND (p.date_available BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
            } else if (($startDate && $endDate) && $endDate == $startDate) {
                $formattedStartDate = date('Y-m-d', $startDate);

                $queryString[] = 'AND (p.date_available="' . $formattedStartDate . '")';
            }
        }

        if (isset($filterData['taxes']) && count($filterData['taxes']) > 0) {

            $taxesIds = implode(', ', $filterData['taxes']);

            $queryString[] = 'AND (p.tax_class_id IN (' . $this->filterArrayOfIds($taxesIds) . '))';
        }

        if (isset($filterData['weights']) && count($filterData['weights']) > 0) {

            $weightsIds = implode(', ', $filterData['weights']);

            $queryString[] = 'AND (p.weight_class_id IN (' . $this->filterArrayOfIds($weightsIds) . '))';
        }

        if (isset($filterData['lengths']) && count($filterData['lengths']) > 0) {

            $lengthsIds = implode(', ', $filterData['lengths']);

            $queryString[] = 'AND (p.length_class_id IN (' . $this->filterArrayOfIds($lengthsIds) . '))';
        }
       
        // filter out deleted (archived) products 
        $queryString[] = ' AND (p.archived = 0) ';

        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM (' .
            str_replace($fields, '0 as fakeColumn', (implode(' ', $queryString) . ' ' . $groupBYStatement))
            . ') AS t'
        )->row['totalData'];

        $queryString[] = $groupBYStatement;

        $sort_data = array(
            'pd2.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.date_added',
            'p.sku',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = 'ORDER BY ' . $data['sort'];
        } else {
            $queryString[] = 'ORDER BY pd2.name';
        }

        if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
            $queryString[] = 'DESC';
        } else {
            $queryString[] = 'ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = $total;
            }

            //            if ($viewType == 'list')
            $queryString[] = 'LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
        }
        // var_dump(implode(' ', $queryString));exit;
        $products = $this->db->query(implode(' ', $queryString))->rows;
        //get all locales if requested...
        if( isset($filterData['language_code']) && $filterData['language_code'] == '*' ){
            $data = $this->_getProductsWithAllLocales($products);
        }
        else{
            $data = $products;
        }
        return [
            'data' => $data,
            'totalFiltered' => $totalFiltered,
            'total' => $total
        ];
    }

	public function getFBProductsToFilter ($data = [], $filterData = [])
    {

        $queryString = $categoryNameQuery = $fields = [];

        if( isset($filterData['language_code']) && $filterData['language_code'] != '*'){
            $this->load->model('localisation/language');
            $languageId = $this->model_localisation_language->getLanguageByCode($filterData['language_code'])['language_id'];
            $language_code = $filterData['language_code'];
        }
        else {
            $languageId = (int)$this->config->get('config_language_id');
            $language_code = $this->config->get('config_language');
        }

        $groupBYStatement = 'GROUP BY p.product_id';

        $categoryNameQuery[] = 'SELECT name FROM category_description AS cd';
        $categoryNameQuery[] = 'WHERE cd.category_id=p2c.category_id';
        $categoryNameQuery[] = 'AND cd.language_id="' . $languageId . '"';

        $fieldsList = [
                          'p.*',
                          'p2c.category_id',
                          '(' . implode(' ', $categoryNameQuery) . ') AS category_name'
                      ];

        if(!isset($filterData['language_code']) || $filterData['language_code'] != '*' ){
            $fieldsList[] = 'pd.*';
            $fieldsList[] = 'pd2.name as localized_name';
        }

        $fields = implode(',', $fieldsList);

        //To Get Categories ids, images & names lists
        $cat_fields = ', GROUP_CONCAT( DISTINCT p2c.category_id) AS categories_ids,
        GROUP_CONCAT( DISTINCT c.image) as categories_images,
        GROUP_CONCAT( DISTINCT (SELECT name FROM category_description AS cd WHERE cd.category_id=p2c.category_id AND cd.language_id="' . $languageId .'") ) AS categories_names';


		$additional_fields = $additional_joins = "" ;

		if(isset($data["show_facebook_statuses"]) && isset($data["catalog_id"])){
			$facebook_pushed_products = 'facebook_business_pushed_products';
			$additional_fields .= ', fp.push_status'; 
			$additional_fields .= ', fp.rejection_reason'; 
			$additional_fields .= ', fp.batch_id'; 
			$additional_fields .= ', fp.retailer_id'; 

			$additional_joins  .=  'LEFT JOIN `' . DB_PREFIX .$facebook_pushed_products . '`  fp ON fp.expand_product_id = p.product_id';
			$additional_joins  .=  ' AND fp.facebook_catalog_id = '.$data["catalog_id"];
		}

        $queryString[] = 'SELECT ' . $fields . $cat_fields .$additional_fields  . ' FROM ' . DB_PREFIX . 'product AS p';
        $queryString[] = ' JOIN ' . DB_PREFIX . 'product_description AS pd';
        $queryString[] = 'ON (p.product_id=pd.product_id)';
        $queryString[] = ' LEFT JOIN ' . DB_PREFIX . 'product_description AS pd2';
        $queryString[] = 'ON (p.product_id=pd2.product_id and pd2.language_id="' . $languageId . '")';
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'product_to_category AS p2c';
        $queryString[] = 'ON (p.product_id = p2c.product_id)';

        //To Get categories images list
        $queryString[] = 'LEFT JOIN ' . DB_PREFIX . 'category AS c';
        $queryString[] = 'ON (c.category_id = p2c.category_id)';

		if($additional_joins){
			$queryString[] = $additional_joins;
		}

        $queryString[] = 'WHERE 1=1';

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM (' .
            str_replace($fields, '0 as fakeColumn', (implode(' ', $queryString) . ' ' . $groupBYStatement))
            . ') AS t'
        )->row['totalData'];

        if (isset($filterData['search'])) {
            $queryString[] = 'AND (';
            $queryString[] = 'pd.name LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.model LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.sku LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = 'OR p.barcode LIKE "%' . $this->db->escape($filterData['search']) . '%"';
            $queryString[] = ')';
        }

        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM (' .
            str_replace($fields, '0 as fakeColumn', (implode(' ', $queryString) . ' ' . $groupBYStatement))
            . ') AS t'
        )->row['totalData'];

        $queryString[] = $groupBYStatement;

        $sort_data = array(
            'pd2.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
            'p.date_added',
            'p.sku',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = 'ORDER BY ' . $data['sort'];
        } else {
            $queryString[] = 'ORDER BY pd2.name';
        }

        if (isset($data['order']) && (strtoupper($data['order']) == 'DESC')) {
            $queryString[] = 'DESC';
        } else {
            $queryString[] = 'ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = $total;
            }

            $queryString[] = ' LIMIT ' . (int)$data['start'] . ',' . (int)$data['limit'];
        }

        $products = $this->db->query(implode(' ', $queryString))->rows;

        //get all locales if requested...
        if( isset($filterData['language_code']) && $filterData['language_code'] == '*' ){
            $data = $this->_getProductsWithAllLocales($products);
        }
        else{
            $data = $products;
        }

        return [
            'data' => $data,
            'totalFiltered' => $totalFiltered,
            'total' => $total
        ];
    }

    public function getProductReviews($productId)
    {
        $queryString = [];
        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'review';
        $queryString[] = 'WHERE product_id=' . $productId;

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {

            $reviews = $data->rows;

            $rates = array_column($reviews, 'rating');

            $rate = round((array_sum($rates) / $data->num_rows));

            return [
                'count' => $data->num_rows,
                'rate' => $rate
            ];
        }

        return false;
    }

    public function getProductsByIds($ids = [],$language_id = false)
    {
        $products = [];

        foreach ($ids as $key => $id) {
			if($language_id){
				$product = $this->getProductByLanguageId($id,$language_id);
			}else{
				$product = $this->getProduct($id);
			}
            $products[$key]['product_id'] = $product['product_id'];
            $products[$key]['sku'] = $product['sku'] ? $product['sku'] : 'product-sku';
            $products[$key]['price'] = $product['price'] ? $product['price'] : 0.00;
            $products[$key]['quantity'] = $product['quantity'] ? $product['quantity'] : 0;
            $products[$key]['maximum'] = $product['maximum'] ? $product['maximum'] : 0;
            $products[$key]['name'] = $product['name'];
            $products[$key]['model'] = $product['model'];
            $products[$key]['description'] = $product['description'];
            $products[$key]['image'] = $product['image'];
            $products[$key]['availability'] = $product['quantity'] > 0 ? 'in stock' : 'out of stock';
            $products[$key]['condition'] = 'new';
            $products[$key]['manufacturer_id'] = $product['manufacturer_id'];
            $products[$key]['currency'] = $this->config->get('config_currency');
            $products[$key]['isPublishedOnCatalog'] = $product["isPublishedOnCatalog"];
            $products[$key]['tax_class_id'] = $product["tax_class_id"];


            $product_specials = $this->getProductSpecials($id);
            foreach ($product_specials as $product_special) {
                $date_start = $product_special['date_start'];
                $date_end = $product_special['date_end'];
                if (
                    (!$date_start || $date_start == null || $date_start == '0000-00-00' || $date_start < date('Y-m-d')) &&
                    (!$date_end || $date_end == null || $date_end == '0000-00-00' || $date_end > date('Y-m-d'))
                ) {
                    $products[$key]['sale_price'] = $product_special['price'] ?? 0.00;
                    break;
                }
            }
        }

        return $products;
    }

    public function massEditProducts($id, $product)
    {
        $query = $fields = [];

        $query[] = 'UPDATE ' . DB_PREFIX . 'product SET';

        $fields[] = 'price="' . $this->db->escape($product['price']) . '"';
        $fields[] = 'model="' . $this->db->escape($product['model']) . '"';
        $fields[] = 'quantity="' . $this->db->escape($product['quantity']) . '"';
        $fields[] = 'maximum="' . $this->db->escape($product['maximum']) . '"';

        $query[] = implode(',', $fields);
        $query[] = 'WHERE product_id=' . (int)$this->db->escape($id);

        $this->db->query(implode(' ', $query));
    }

    public function updateNameAndPrice($id, $product) {
        $query = $fields = [];

        $query[] = 'UPDATE ' . DB_PREFIX . 'product SET';

        $fields[] = 'price="' . $this->db->escape($product['price']) . '"';

        $query[] = implode(',', $fields);
        $query[] = 'WHERE product_id=' . (int)$id;

        $this->db->query(implode(' ', $query));

        $nameQuery[] = 'UPDATE ' . DB_PREFIX . 'product_description SET';
        $nameField[] = 'name="' . $this->db->escape($product['name']) . '"';

        $nameQuery[] = implode(',', $nameField);
        $nameQuery[] = 'WHERE product_id=' . (int)$id;

        $this->db->query(implode(' ', $nameQuery));

    }

    /**
     * Update product status.
     *
     * @param int $id
     * @param int status
     *
     * @return void
     */
    public function updateProductStatus($id, $status)
    {
        $query = [];

        $query[] = 'UPDATE ' . DB_PREFIX . 'product SET';
        $query[] = 'status=' . $this->db->escape($status);
        $query[] = 'WHERE product_id="' . $this->db->escape($id) . '"';

        $this->db->query(implode(' ', $query));

        // Activate and deactivate ms_product
        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
        if ($queryMultiseller->num_rows) {
            $prd_status = (int)$status ? 1 : 2;
            $this->db->query('UPDATE ' . DB_PREFIX . 'ms_product SET product_status=' . $prd_status . ' WHERE product_id="' . $this->db->escape($id) . '"');
        }
        ////////////////////////////////////
    }

    /**
     * Update product quantity.
     *
     * @param int id
     * @param array updateData -- ['column' => COL_NAME, 'value' => NEW_VALUE, 'action' => PDATE_OR_DECREMENT]
     * @return void
     */
    public function updateProductValue($id, $updateData)
    {
        $column = $updateData['column'];

        $query = [];
        $query[] = 'UPDATE ' . DB_PREFIX . 'product SET';

        if($updateData['action'] == 'decrement')
            $query[] = $column.'=('.$column .' - ' . (int)$this->db->escape($updateData['value']).')';
        else
            $query[] = $column.'=' . $this->db->escape($updateData['value']);

        $query[] = 'WHERE product_id="' . $this->db->escape($id) . '"';

        return $this->db->query(implode(' ', $query));
    }

    /**
     * Update product multiple values .
     *
     * @param int id
     * @param array updateData -- [
     *      ['column'=>col_name,'value'=>new_value]],
     *      ['column'=>col_name,'value'=>new_value]],
     * ]
     * @return void
     */
    public function updateProductMultipleValues($id, $updateData)
    {
        $query = [];
        $query[] = "UPDATE " . DB_PREFIX . "product SET";

        foreach($updateData as $key => $value){
            $query[]    = $key !== 0 ? "," : "";
            $query[]    = $value['column']." = '" . $this->db->escape($value['value'])."'";
        }
        $query[] = "WHERE product_id=" . $this->db->escape($id);

        return $this->db->query(implode(' ', $query));
    }

    /**
     * Filter array of ids, this method is targeting filtering only ids.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function filterArrayOfIds($inputs)
    {
        return array_filter($inputs, function ($input) {
            return $this->filterInteger($input);
        });
    }

    /**
     * Filter int input.
     *
     * @param int $input
     *
     * @return array
     */
    public function filterInteger($input)
    {
        return filter_var($input, FILTER_VALIDATE_INT);
    }

    /**
     * Get total products by stock status ids.
     *
     * @param array $stock_status_ids
     *
     * @return array|bool
     */
    public function getTotalProductsByStockStatusIds($stock_status_ids)
    {
        $query = [];
        $query[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'product WHERE';
        $query[] = 'archived = 0 AND stock_status_id IN (' . implode(',', $stock_status_ids) . ')';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows) {
            return $data->row['total'];
        }

        return false;
    }


    //Store Dropna Ids
    public function addDropnaProduct($data){
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_dropna SET product_id = '" . (int)$data['store_product_id'] . "', dropna_product_id = '" . (int)$data['dropna_product_id'] . "', dropna_user_id = '" . (int)$data['dropna_user_id'] . "'");
    }

    public function getDropnaProduct($id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_dropna WHERE dropna_product_id = '" . (int)$id . "'");

        return $query->row;
    }

    public function getDropnaProductById($id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_dropna WHERE product_id = '" . (int)$id . "'");

        return $query->row;
    }

    public function getDropnaProOptValtByDropnaId($id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pr_op_val_to_dropna WHERE dropna_pr_op_val_id = '" . (int)$id . "'");

        return $query->row;
    }


    public function updateProductCategory( $product_id, $data, $overwrite = false )
    {
        if ( $overwrite ) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        return true;
    }


    public function getQty(int $product_id)
    {
        $result = $this->db->query("SELECT `quantity` FROM ".DB_PREFIX."`product` WHERE `product_id` = '{$product_id}' LIMIT 1");

        return $result->row['quantity'];
    }


    public function addQty(int $product_id, int $qty)
    {
        $old_qty = $this->getQty($product_id);
        $final_qty = $old_qty + $qty;

        return $this->db->query("UPDATE ".DB_PREFIX."product SET `quantity` = '{$final_qty}' WHERE `product_id` = '{$product_id}'");
    }


    public function deductQty(int $product_id, int $qty)
    {
        $old_qty = $this->getQty($product_id);
        $final_qty = $old_qty - $qty;

        return $this->db->query("UPDATE ".DB_PREFIX."product SET `quantity` = '{$final_qty}' WHERE `product_id` = '{$product_id}'");
    }


    public function isProductByAMutliSeller(int $product_id)
    {
        $check_that_multiseller_is_installed = $this->db->query("SHOW TABLES LIKE ".DB_PREFIX."'ms_product';");

        if (! $check_that_multiseller_is_installed->num_rows) {
            return false;
        }

        $result = $this->db->query("SELECT * FROM ".DB_PREFIX."`ms_product` WHERE `product_id` = '{$product_id}'");

        if (! $result->num_rows) {
            return false;
        }

        return $result->row['seller_id'];
    }

    public function countProducts()
    {
        // todo add this condition after finishing demo products feature where demo=0;
        $sql_str = "SELECT count(*) as products_count from ".DB_PREFIX."product where archived = 0;";

        $result = $this->db->query($sql_str);

        return $result->row['products_count'];
    }

        //Check Dropna Client
    public function checkDropnaClient(){
        $this->load->model("api/clients");
        $dropnaClient = $this->model_api_clients->getDropnaClient();
        if(!$dropnaClient){
            return false;
        }
        return $dropnaClient;
    }

    //Prepare Dropna Products Schedule
    public function dropnaScheduleProduct($values, $saveStoreToDropna = false){
        //Check if Dropna Integrated
        $dropnaClient = $this->checkDropnaClient();
        if(!$dropnaClient){
            return false;
        }/////////////////
        $saveList = true;

        if($saveStoreToDropna){
            $dropnaData['apikey']     = DROPNA_APIKEY;
            $dropnaData['store_code'] = $dropnaClient['store_code'];
            $dropnaData['client_id']  = $dropnaClient['client_id'];
            $dropnaData['store_to_dropna']  = 1;

            $soap_do     = curl_init();
            curl_setopt($soap_do, CURLOPT_URL, DROPNA_DOMAIN."api/v1/schedule_store");
            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap_do, CURLOPT_POST, true);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($dropnaData));
            // curl_setopt($soap_do, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $response = curl_exec($soap_do);
            $responseArr = json_decode($response, true);

            $err = curl_error();
            curl_close();
            if($responseArr['status'] != 'success'){
                $saveList = false;
            }
        }

        if($saveList){
            $sql = "INSERT INTO " . DB_PREFIX . "product_to_dropna_schedule (pid,status,reference) VALUES ".$values;
            $this->db->query($sql);

            return true;
        }

        return false;
    }

    //Run Dropna Schedule Products, Called within: Product API & this:dropnaExportProducts method
    public function dropnaRunScheduleProduct($limit){
        //Check if Dropna Integrated
        $dropnaClient = $this->checkDropnaClient();
        if(!$dropnaClient){
            return false;
        }/////////////////

        ////////////////////////Get Dollar Rate
        $be_supplier_settings = $this->config->get('be_supplier');
        $dollar_rate = 1;

        if($be_supplier_settings['auto_dollar_rate']){
            //// Get Dollar rate from API
            $dollar_rate = $this->currency->gatUSDRate($this->config->get('config_currency'));
            ////////////////////////////
        }else if($be_supplier_settings['dollar_rate']){
            $dollar_rate = $be_supplier_settings['dollar_rate'];
        }
        ///////////////////

        $pLimit = $limit ? 'limit 10' : '';
        $pids = $this->db->query("SELECT pid FROM " . DB_PREFIX . "product_to_dropna_schedule WHERE status = 'wait' ".$pLimit);

        $products   = [];
        $dropnaData = [];

        if ($pids->num_rows) {
            foreach ($pids->rows as $product) {
                $products[] = $this->getDropnaExportProduct($product['pid'], $dollar_rate);
            }

            $dropnaData['products']   = $products;
            $dropnaData['apikey']     = DROPNA_APIKEY;
            $dropnaData['store_code'] = $dropnaClient['store_code'];
            $dropnaData['client_id']  = $dropnaClient['client_id'];
            $dropnaData['store_to_dropna']  = 1;

            return json_encode($dropnaData);
        }

        return false;
    }

    //Get Products Export to Dropna
    public function getDropnaExportProduct($product_id, $dollar_rate)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($query->num_rows) {
            $data = array();

            $data = $query->row;
            $productData = [];

            $productData['store_product_id'] = $product_id;
            $productData['model'] = $data['model'];
            $productData['sku'] = $data['sku'] ? $data['sku'] : 'Not Available';
            $productData['location'] = $data['location'] ? $data['location'] : 'Not Available';
            $productData['price'] = $data['price'] * $dollar_rate;
            $productData['cost_price'] = $data['cost_price'];
            $productData['weight'] = $data['weight'];
            $productData['length'] = $data['length'];
            $productData['width'] = $data['width'];
            $productData['height'] = $data['height'];
            $productData['minimum'] = $data['quantity'];
            $productData['status'] = $data['status'];
            $productData['min_cpa'] = 0.00;
            $productData['max_cpa'] = 0.00;

            $this->load->model('tool/image');
            if($data['image']){
                $image = $this->model_tool_image->resize($data['image'], 550, 550);
                $productData['image'] = $image;
            }

            $productData = array_merge($productData, array('product_description' => $this->getProductDescriptions($product_id)));
            $images = $this->getProductImages($product_id);
            foreach ($images as $key => $img) {
                $productData['images'][] = $this->model_tool_image->resize($img['image'], 550, 550);
            }
            $productData = array_merge($productData, array('options' => $this->getOptionsWithDesc($product_id)));
            $productData = array_merge($productData, array('category' => $this->getCategoriesWithDesc($product_id)));

            $this->db->query("UPDATE " . DB_PREFIX . "product_to_dropna_schedule SET status='proccessing' WHERE pid='" . $this->db->escape($product_id)."'");

            return $productData;
        }
        return false;
    }

    //Export product to dropna, called within ControllerCatalogProduct@dropnaExport
    public function dropnaExportProducts(){
        $dropnaData = $this->dropnaRunScheduleProduct(false);
        $dropnaData = json_decode($dropnaData, true);

        $soap_do     = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, DROPNA_DOMAIN."api/v1/product");
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST, true);
        curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($dropnaData));
        // curl_setopt($soap_do, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($soap_do);
        $responseArr = json_decode($response, true);

        $err = curl_error();
        curl_close();
    }

    //Mapp Dropna Products and update shcedule status
    public function dropnaProductsMapp($mappData){
        foreach ($mappData as $item) {
            if($item['dropna_product_id']){

                $this->addDropnaProduct($item);

                $this->db->query("UPDATE " . DB_PREFIX . "product_to_dropna_schedule SET status='success' WHERE pid='" . $this->db->escape($item['store_product_id'])."'");
            }else{
                $this->db->query("UPDATE " . DB_PREFIX . "product_to_dropna_schedule SET status='failed' WHERE pid='" . $this->db->escape($item['store_product_id'])."'");
            }
        }
    }

    //Export product to dropna
    /*public function dropnaExportProduct($product_id)
    {
        //Check if Dropna Integrated
        $dropnaClient = $this->checkDropnaClient();
        if(!$dropnaClient){
            return false;
        }/////////////////

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($query->num_rows) {
            $data = array();

            $data = $query->row;
            $dropnaData = [];

            $dropnaData['model'] = $data['model'];
            $dropnaData['sku'] = $data['sku'] ? $data['sku'] : 'Not Available';
            $dropnaData['location'] = $data['location'] ? $data['location'] : 'Not Available';
            $dropnaData['price'] = $data['price'];
            $dropnaData['cost_price'] = $data['cost_price'];
            $dropnaData['weight'] = $data['weight'];
            $dropnaData['length'] = $data['length'];
            $dropnaData['width'] = $data['width'];
            $dropnaData['height'] = $data['height'];
            $dropnaData['minimum'] = $data['quantity'];
            $dropnaData['status'] = $data['status'];
            $dropnaData['min_cpa'] = 0.00;
            $dropnaData['max_cpa'] = 0.00;

            $this->load->model('tool/image');
            if($data['image']){
                $image = $this->model_tool_image->resize($data['image'], 550, 550);
                $dropnaData['image'] = $image;
            }

            //$data = array_merge($data, array('product_attribute' => $this->getProductAttributes($product_id)));
            $dropnaData = array_merge($dropnaData, array('product_description' => $this->getProductDescriptions($product_id)));
            //$data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
            //$data = array_merge($data, array('product_filter' => $this->getProductFilters($product_id)));
            $images = $this->getProductImages($product_id);
            foreach ($images as $key => $img) {
                $dropnaData['images'][] = $this->model_tool_image->resize($img['image'], 550, 550);
            }
            $dropnaData = array_merge($dropnaData, array('options' => $this->getOptionsWithDesc($product_id)));
            //$data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
            //$data = array_merge($data, array('product_reward' => $this->getProductRewards($product_id)));
            //$data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
            $dropnaData = array_merge($dropnaData, array('category' => $this->getCategoriesWithDesc($product_id)));
            //$data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
            //$data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));
            //$data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));

            $dropnaData['apikey']     = DROPNA_APIKEY;
            $dropnaData['store_code'] = $dropnaClient['store_code'];
            $dropnaData['client_id']  = $dropnaClient['client_id'];
            $dropnaData['store_to_dropna']  = 1;

            $soap_do     = curl_init();
            curl_setopt($soap_do, CURLOPT_URL, DROPNA_DOMAIN."api/v1/product");
            curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_TIMEOUT, 10);
            curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($soap_do, CURLOPT_POST, true);
            curl_setopt($soap_do, CURLOPT_POSTFIELDS, http_build_query($dropnaData));
            // curl_setopt($soap_do, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $response = curl_exec($soap_do);
            $responseArr = json_decode($response, true);

            $err = curl_error();
            curl_close();

            if($responseArr['status'] == 'success' && $responseArr['dropna_product_id']){

                $dropnaResult['product_id'] = $data['product_id'];
                $dropnaResult['dropna_product_id'] = $responseArr['dropna_product_id'];
                $dropnaResult['dropna_user_id'] = $responseArr['dropna_user_id'];

                $this->addDropnaProduct($dropnaResult);

                $this->db->query("UPDATE " . DB_PREFIX . "product_to_dropna_schedule SET status='success' WHERE pid='" . $this->db->escape($product_id)."'");
            }else{
                $this->db->query("UPDATE " . DB_PREFIX . "product_to_dropna_schedule SET status='failed' WHERE pid='" . $this->db->escape($product_id)."'");
            }
        }
    }*/

    /**
     * Get related orders products.
     *
     * @param arrsy $productIds
     * @param string $return
     *
     * @return array|int|bool
     */
    public function getRelatedOrderProductsByIds($productIds, $return = 'rows')
    {
        $query = 'SELECT * FROM `order_product` WHERE product_id IN (%s)';

        $data = $this->db->query(sprintf($query, implode(',', $productIds)));

        if (isset($data->num_rows) && $data->num_rows > 0) {

            if (in_array($return, ['rows', 'num_rows', 'rows'])) {
                return $data->$return;
            }

            return false;
        }

        return false;
    }

    /**
     * get all product description
     *
     */
    public function getProductsDescription()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description");

        return $query->rows;
    }

    /**
     * Update slug
     *
     */
    public function slugUpdate($product_id, $lang_id, $name)
    {
        $this->db->query(
                "UPDATE ".DB_PREFIX."product_description SET slug = '".$this->db->escape((new Slugify)->slug($name))."' WHERE product_id = '".(int)$product_id."' AND language_id='".(int)$lang_id."'");
    }

    /**
     * Get product name
     */
    public function getProductName($product_id, $language_id) {
        $query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "product_description WHERE product_id = " . $product_id . " AND language_id = " . $language_id);

        return $query->row;
    }

    /**
     * Get product status
    */
    public function getProductStatus($product_id) {
        $query = $this->db->query("SELECT `product_status` FROM " . DB_PREFIX . "ms_product WHERE product_id = " . $product_id);

        return $query->row;
    }
    /**
     * Get product  quantity
    */
    public function getProductsQuantity() {
        $query = $this->db->query("SELECT   MIN(quantity) AS min_quantity, MAX(quantity) AS max_quantity  FROM " . DB_PREFIX . " product  ORDER BY quantity ASC LIMIT 1");
        return $query->row;
    }

    /**
     * Get product by a variable column.
     * this is usually will be used internally
     * but will be kept as public for any potential use
     *
     * @param string $column
     * @param string $value
     *
     * @return mixed
     */
    public function getProductBy($column, $value)
    {
        $results = $this->db->query(sprintf('SELECT * FROM `product` WHERE %s = "%s"', $column, $value));

        if ($results->num_rows > 0) {
            return $results->rows;
        }

        return false;
    }

    /**
     * Factory method to get product by barcode column.
     *
     * @param string $barcode
     *
     * @return mixed
     */
    public function getProductByBarcode($barcode)
    {
        return $this->getProductBy('barcode', $barcode);
    }

    /**
     * Factory method to get product by sku column.
     *
     * @param string $sku
     *
     * @return mixed
     */
    public function getProductBySku($sku)
    {
        return $this->getProductBy('sku', $sku);
    }

    /**
     * Factory method to get product by name column.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getProductByName($name)
    {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_description WHERE name LIKE  '%" . $this->db->escape($name) . "%' LIMIT 1 ");
        return $query->num_rows ? $query->row : false;
    }

    /**
     * get product classification by product id.
     *
     * @param int $product_id
     *
     * @return mixed
     */
    public function getProductClassificationData($product_id)
    {
        $query = "SELECT  `pc_brand_id`,`pc_model_id`,`pc_row_key`,`product_id` FROM " . DB_PREFIX . "pc_product_brand_mapping" ;
        $query .= " WHERE product_id = $product_id GROUP BY `pc_row_key`";
        $results = $this->db->query($query);
        return $results->rows;
    }

    public function getProductClassificationYearsByRowData($brand_id,$row_key,$product_id)
    {
        $query = "SELECT pc_year_id FROM " . DB_PREFIX . "pc_product_brand_mapping" ;
        $query .= " WHERE  pc_brand_id = $brand_id AND pc_row_key = $row_key AND product_id = $product_id GROUP BY `pc_year_id`   ";
        $results = $this->db->query($query);
        return $results->rows;
    }

    public function getProductClassificationModelsByRowData($brand_id,$row_key,$product_id)
    {
        $query = "SELECT pc_model_id FROM " . DB_PREFIX . "pc_product_brand_mapping" ;
        $query .= " WHERE  pc_brand_id = $brand_id AND pc_row_key = $row_key AND product_id = $product_id GROUP BY `pc_model_id`  ";
        $results = $this->db->query($query);
        return $results->rows;
    }

  
    /**
    * Get product video links Module information.
    *
    * @return Array of order_status_id_evu & external_video_url values
    */
    public function getVideoURLInfo($product_id){

        $query = $this->db->query("SELECT external_video_url FROM `". DB_PREFIX ."product` WHERE product_id='" . $product_id . "'");

        $order_status_id_evu = $this->config->get('product_video_links_order_status_id_evu');

        return [
            'external_video_url'  => $query->row['external_video_url'],
            'order_status_id_evu' => $order_status_id_evu
        ];
    }
  
    public function getProductSpecificFields($id, $fields = [])
    {
        $query = "SELECT " . implode(',', $fields) . " FROM " . DB_PREFIX . "product ";
        $query .= "WHERE product_id=" . $id;
        $results = $this->db->query($query);
        return $results->row;
    }
       
    public function getProductsCountByIds($ids, $field = 'product_id')
    {
        $query = "SELECT COUNT({$field}) AS {$field}_count FROM " . DB_PREFIX . "product ";
        $query .= "WHERE archived = 0 AND product_id IN (" . implode(',', $ids) .")";
        $results = $this->db->query($query);
        return $results->row["{$field}_count"];
    }

    public function updateProductBarcode($product_id=null,$new_barcode=null){
        if($product_id && $new_barcode){
            $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";
            $updateQuery[] = "barcode = '" . $this->db->escape($new_barcode) . "'";
            $updateQuery[] = "WHERE product_id = '" . $this->db->escape($product_id) . "'";
            $updateQuery[] = "LIMIT 1";
            
            $this->db->query(implode(' ',$updateQuery));
        }
    }
    
    /**
     * update product desc.
     *
     * @param integer $product_id
     * @param integer $language_id
     * @param array $data
     * @return void
     */
    public function updateProductDescription(int $product_id, int $language_id, array $data)
    {

        //check id data with such language is exist
        $description_exist = $this->db->query("SELECT product_id FROM " .DB_PREFIX."product_description WHERE product_id=" . (int) $product_id . " AND language_id=" . (int) $language_id  ." LIMIT 1");

        if(!$description_exist->num_rows){
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "',language_id = '" . (int)$language_id . "'");
        }
        $updateQuery = $updateFields = $parameters = [];
        $descriptionData = $data['description']; // escape data before add to insert statment
        $updateQuery[] = "UPDATE " . DB_PREFIX . "product_description SET";
        $updateFields[] = " name = ?";
		$parameters[] =  $data['name'];
        $updateFields[] = "description = ?";
		$parameters[] = $descriptionData;
        
        if (isset($data['custom_html'])) {
            $this->load->model('module/custom_product_editor');
            if($this->model_module_custom_product_editor->isInstalled()) {
                $updateFields[] = "custom_html = ?";
				$parameters[] = $data['custom_html'];
            }
        }
        if(isset($data['product_description']) && isset($data['product_description'][$language_id])){
            $updateFields[] = " name = ?";
			$parameters[] = $data['product_description'][$language_id]['name'];
            $updateFields[] = "description = ?";
			$parameters[] = $data['product_description'][$language_id]['description'];
        }
        
        $updateFields[] = "meta_description =?";
		$parameters[] = $data['meta_description'];
        $updateFields[] = "meta_keyword = ?";
		$parameters[] = $data['meta_keyword'];
        $updateFields[] = "tag = ?";
		$parameters[] = $data['tag'];
        $updateFields[] = "slug = ?";
		$parameters[] = (new Slugify)->slug($data['name']);
        
        if(\Extension::isInstalled('multiseller') == TRUE){
            $updateFields[] = "seller_notes = ?";
			$parameters[] = $data['seller_notes'];
        }

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id=" . (int) $product_id . " AND language_id=" . (int) $language_id . "";
        
        $sql = implode(' ', $updateQuery);

        $this->db->execute($sql, $parameters);
    }
    
     /**
      * Update Custom HTML settings
      *
      * @param integer $id the product id
      * @param integer $settings [custom_html_status, display_main_page_layout]
      * @return bool
      */
    public function updateProductCustomHtmlPageSettings(int $id, array $settings)
    {
        $query = [];
        $query[] = 'UPDATE ' . DB_PREFIX . 'product SET';
        $query[] = 'custom_html_status=' . $this->db->escape($settings['custom_html_status']);
        $query[] = ',display_main_page_layout=' . $this->db->escape($settings['display_main_page_layout']);
        $query[] = 'WHERE product_id="' . $this->db->escape($id) . '"';

        return $this->db->query(implode(' ', $query));
    }
    
    private function getProductsIdsForSellers(array $sellers)
    {
        $sellersIds = implode(', ', $this->filterArrayOfIds($sellers));
        
        $result = $this->db->query(
            'SELECT GROUP_CONCAT(DISTINCT product_id SEPARATOR ", ") AS ids FROM ' . DB_PREFIX . 
            'ms_product WHERE seller_id IN (' . $sellersIds . ')'
        );
        
        return $result->row['ids'];
    }

    public function isKnawatProduct($product_id) {
        if ($this->config->get('module_knawat_dropshipping_status') != 'on' || !(bool)$product_id) {
            return false;
        }
        return $this->db->query("SELECT COUNT(resource_id) resources_count FROM " . DB_PREFIX . "knawat_metadata WHERE `resource_id` = " . (int) $product_id . " AND `meta_key` = 'is_knawat'")->row['resources_count'] != 0;
    }

    public function removeDemoProducts() {

        $products= null;
        $sql_str = "SELECT product_id from ".DB_PREFIX."product where demo=1;";

        $demo_products =  $this->db->query($sql_str)->rows;
        foreach ($demo_products as $demo_product){
            $products[]= $demo_product['product_id'];
        }

        $products_ids_string = implode(',',$products);

        if ($products){

            $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE `demo` = 1");

            $this->db->query(
                "DELETE FROM " . DB_PREFIX . "product_description WHERE `product_id` in (". $products_ids_string.")"
            );
        }

    }

    public function updateProductShipping($id,$data)
    {

        $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";

        if(isset($data['preparation_days'])){
            $updateFields[] = "preparation_days = '" . $this->db->escape($data['preparation_days']) . "'";

        }
        if(isset($data['weight'])){
            $updateFields[] = "weight = '" . $this->db->escape($data['weight']) . "'";

        }
        if(isset($data['weight_class_id'])){
            $updateFields[] = "weight_class_id = '" . $this->db->escape($data['weight_class_id']) . "'";

        }
        if(isset($data['length'])){
            $updateFields[] = "length = '" . $this->db->escape($data['length']) . "'";

        }
        if(isset($data['width'])){
            $updateFields[] = "width = '" . $this->db->escape($data['width']) . "'";

        }
        if(isset($data['height'])){
            $updateFields[] = "height = '" . $this->db->escape($data['height']) . "'";

        }
        if(isset($data['length_class_id'])){
            $updateFields[] = "length_class_id = '" . $this->db->escape($data['length_class_id']) . "'";

        }

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id=" . (int) $id ."";

        $sql = implode(' ', $updateQuery);

        return  $this->db->query($sql);

    }


    public function updateProductAttributes($id,$updateData)
    {
            foreach($updateData as $language => $data){

                $lang = $this->db->query("SELECT `language`.language_id FROM `language` WHERE `language`.CODE = '$language'");
                $lang_id = $lang->row['language_id'];

                $updateQuery[] = "UPDATE " . DB_PREFIX . "product_attribute SET";

                if(isset($data['attribute_id']) && isset($data['text'])){
                    $updateFields[] = "attribute_id = '" . $this->db->escape($data['attribute_id']) . "'";
                    $updateFields[] = "text = '" . $this->db->escape($data['text']) . "'";
                }

                $updateQuery[] = implode(', ', $updateFields);

                $updateQuery[] = " WHERE product_id=" . (int) $id . " AND language_id=" . (int) $lang_id . "";

                $sql = implode(' ', $updateQuery);
                $updateQuery = [];
                $updateFields = [];
                $res =  $this->db->query($sql);
        }
        return $res;

    }

    public function updateProductInfo($id,$data)
    {

        $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";

        if(isset($data['image'])){
            $updateFields[] = "image = '" . $this->db->escape($data['image']) . "'";
        }
        if(isset($data['price'])){
            $updateFields[] = "price = '" . $this->db->escape($data['price']) . "'";

        }
        if(isset($data['cost_price'])){
            $updateFields[] = "cost_price = '" . $this->db->escape($data['cost_price']) . "'";

        }
        if(isset($data['date_available'])){
            $updateFields[] = "date_available = '" . $this->db->escape($data['date_available']) . "'";

        }
        if(isset($data['status'])){
            $updateFields[] = "status = '" . $this->db->escape($data['status']) . "'";

        }
        if(isset($data['tax_class_id'])){
            $updateFields[] = "tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "'";

        }

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id=" . (int) $id ."";

        $sql = implode(' ', $updateQuery);


        return   $this->db->query($sql);

    }

    public function UpdateProductNameAndDesc($id,$updateData){

        foreach($updateData['product_description'] as $language => $data){

            $lang = $this->db->query("SELECT `language`.language_id FROM `language` WHERE `language`.CODE = '$language'");
            $lang_id = $lang->row['language_id'];

            $updateQuery[] = "UPDATE " . DB_PREFIX . "product_description SET";

            if(isset($data['name'])){
                $updateFields[] = "name = '" . $this->db->escape($data['name']) . "'";
            }
            if(isset($data['description'])){
                $updateFields[] = "description = '" . $this->db->escape($data['description']) . "'";
            }

            $updateQuery[] = implode(', ', $updateFields);

            $updateQuery[] = " WHERE product_id=" . (int) $id . " AND language_id=" . (int) $lang_id . "";

            $sql = implode(' ', $updateQuery);
            $updateQuery = [];
            $updateFields = [];
            $res=  $this->db->query($sql);

        }
        return $res;
    }


    public function UpdateProductLinking($id,$data){

        $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";

        if(isset($data['manufacturer_id'])){
            $updateFields[] = "manufacturer_id = '" . $this->db->escape($data['manufacturer_id']) . "'";
        }
        if(isset($data['model']) ){
            $updateFields[] = "model = '" . $this->db->escape($data['model']) . "'";

        }

        if (isset($data['product_related'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = " . (int)$id);

            foreach ($data['product_related'] as $related_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$id . "', related_id = '" . (int)$related_id['product_id'] . "'");
            }
        }

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id=" . (int) $id ."";

        $sql = implode(' ', $updateQuery);

        return  $this->db->query($sql);

    }

    public function updateProductDiscount($data){

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {

                $queryString = $fields = [];
                $queryString[] = "UPDATE " . DB_PREFIX . "product_discount SET";
                $fields[] = "product_id = '" . (int)$product_discount['product_id'] . "'";
                $fields[] = "customer_group_id = '" . (int)$product_discount['customer_group_id'] . "'";
                $fields[] = "quantity = '" . (int)$product_discount['quantity'] . "'";
                $fields[] = "priority = '" . (int)$product_discount['priority'] . "'";
                $fields[] = "price = '" . (float)$product_discount['price'] . "'";
                $fields[] = "date_start = '" . $this->db->escape($product_discount['date_start']) . "'";
                $fields[] = "date_end = '" . $this->db->escape($product_discount['date_end']) . "'";

                $queryString[] = implode(', ', $fields);

                $queryString[] = " WHERE product_discount_id = ".(int)$product_discount['product_discount_id'];

                $this->db->query(implode(' ', $queryString));
            }
        }
        return true;
    }

    public function addProductDiscount($id, $data)
    {
        $insertQuery = $insertFields = [];
        $insertQuery[] = "INSERT INTO " . DB_PREFIX . "product_discount SET";
        $insertFields[] = "product_id = '" . (int)$id . "'";
        $insertFields[] = "quantity = '" . $this->db->escape($data['quantity']) . "'";
        $insertFields[] = "customer_group_id = '" . $this->db->escape($data['customer_group_id']) . "'";
        $insertFields[] = "priority = '" . $this->db->escape($data['priority']) . "'";
        $insertFields[] = "price = '" . $this->db->escape($data['price']) . "'";
        $insertFields[] = "date_start = '" . $this->db->escape($data['date_start']) . "'";
        $insertFields[] = "date_end = '" . $this->db->escape($data['date_end']) . "'";

        $insertQuery[] = implode(', ', $insertFields);

        return $this->db->query(implode(' ', $insertQuery));
    }

    public function deleteProductDiscount($id)
    {
        $delete_discount =  $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_discount_id = " . (int)$id);
        if ($delete_discount)
        {
            return true;
        }
    }

    public function updateProductrewardpoints($id,$data){

        $updateQuery[] = "UPDATE " . DB_PREFIX . "product_reward SET";

        $updateFields[] = "points = '" . $this->db->escape($data['points']) . "'";
        $updateFields[] = "customer_group_id = '" . $this->db->escape($data['customergroup']) . "'";
        $updateFields[] = "product_reward_id = '" . $this->db->escape($data['rewardpoint']) . "'";


        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id = ".(int)$id;

        $sql = implode(' ', $updateQuery);



        return  $this->db->query($sql);
    }

    public function updateProductInventory($id,$data){

        $updateQuery[] = "UPDATE " . DB_PREFIX . "product SET";

        $updateFields[] = "quantity = '" . $this->db->escape($data['quantity']) . "'";
        $updateFields[] = "minimum = '" . $this->db->escape($data['minimum']) . "'";
        $updateFields[] = "sku = '" . $this->db->escape($data['sku']) . "'";
        $updateFields[] = "barcode = '" . $this->db->escape($data['barcode']) . "'";

        $updateQuery[] = implode(', ', $updateFields);

        $updateQuery[] = " WHERE product_id = ".(int)$id;

        $sql = implode(' ', $updateQuery);


        return  $this->db->query($sql);
    }

    public function UpdateSeo($id,$updateData){

        foreach($updateData as $language => $value){
            $lang = $this->db->query("SELECT `language`.language_id FROM `language` WHERE `language`.CODE = '$language'");
            $lang_id = $lang->row['language_id'];

            $updateQuery[] = "UPDATE " . DB_PREFIX . "product_description SET";

            if(isset($value['meta_description']) && isset($value['tag'])){
                $updateFields[] = "meta_description = '" . $this->db->escape($value['meta_description']) . "'";
                $updateFields[] = "tag = '" . $this->db->escape($value['tag']) . "'";

            }

            $updateQuery[] = implode(', ', $updateFields);

            $updateQuery[] = " WHERE product_id=" . (int) $id . " AND language_id=" . (int) $lang_id . "";

            $sql = implode(' ', $updateQuery);
            $updateQuery = [];
            $updateFields = [];
            $res=   $this->db->query($sql);

        }
        return $res;

    }


    public function UpdateProductImages($product_id,$data){

       $delete_status =  $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = " . (int)$product_id);

        if (isset($data['product_image']) && $delete_status) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['product_image'][0]['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['product_image'] as $key => $product_image) {
				if($product_image['image'] != null && $product_image['image'] != '' && $key != 0){
                    $res =  $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
				}
			}
        }
        return $res;
    }


    public function AddProductImages($product_id,$uploaded_images){
        if (isset($uploaded_images)) {
            if(is_array($uploaded_images)){
                $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($uploaded_images[0], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
                foreach ($uploaded_images as $key => $image) {
                    if($image != null && $image != '' && $key != 0){
                        $res =  $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $image . "'");
                    }
                }
            }else{
                $res =  $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $uploaded_images . "'");
            }
        }
        return $res;
    }

	//get non-indexed products only 
    public function getLablebProducts($product_id=false,$non_indexed=true){	
		
		//check for table & create if its not created yet 
		//this for old users we will leave it for shor time & then we can remove it anytime 
		$module_name 	= 'lableb';
		$setting_data 	= $this->config->get($module_name);

		if(!isset($setting_data['indexed_product_table_created'])){
			$this->load->model('module/lableb');
			$this->load->model('setting/setting');
			
			$this->model_module_lableb->createIndexedProductTable();
			$setting_data['indexed_product_table_created']=1;
			$this->model_setting_setting->editSetting($module_name, [$module_name => $setting_data]);
		}
		//#this for old users - should be removed after a while 
		
	
        $sql = "SELECT p.product_id, p.date_available, pd.name, p.quantity, p.price,p.status,
            p.image, ss.name as `stock_status`, l.code as `language_code`,  pd.language_id, pd.description, 
            (SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = p.product_id ORDER BY sort_order ASC limit 1 ) as image_swap ,
			GROUP_CONCAT(cd.name ORDER BY cd.category_id DESC) as categories_names
			FROM " . DB_PREFIX . "product p 
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)  
			LEFT JOIN " . DB_PREFIX . "stock_status ss ON  (p.stock_status_id = ss.stock_status_id)
            LEFT JOIN " . DB_PREFIX . "language l ON  (pd.language_id = l.language_id)   
            LEFT JOIN " . DB_PREFIX . "product_to_category pc ON  (pc.product_id = p.product_id )   
            LEFT JOIN " . DB_PREFIX . "category_description cd
				ON  (cd.category_id = pc.category_id and pd.language_id = cd.language_id )   
				WHERE 1 ";
				
		if($product_id){
			$sql .= "AND p.product_id = ".$product_id;
		}
			
		if($non_indexed){
			$sql .= "AND CONCAT(p.product_id,'-',l.code) NOT IN (select lableb_id from `" . DB_PREFIX . "lableb_indexed_products` )  ";
		}
		
            $sql .= " Group by p.product_id , pd.language_id ";

        $query = $this->db->query($sql);
        return $query->rows;        
    }
    
    public function getProductLangauges($product_id){
        $sql = "SELECT l.code FROM ". DB_PREFIX . "language l 
                LEFT JOIN product_description pd ON (l.language_id = pd.language_id) 
                WHERE pd.product_id = ".$product_id ;
        $query = $this->db->query($sql);
        return $query->rows; 
    }

    public function addProductMainImages($product_id , $image){
        if(isset($image)){
            $res =  $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $image . "' WHERE product_id =".$product_id);
        }
        return $res;
    }

    public function getProductMainImage($product_id){
        if(isset($product_id)){
            $res =  $this->db->query("SELECT image FROM " . DB_PREFIX . "product  WHERE product_id =".$product_id);
        }
        return $res->rows;
    }

    public function _getProductsWithAllLocales($products){
        $products_ids = array_column($products, 'product_id');
        $data = $this->db->query("
            SELECT pd.*, l.code as language_code
            FROM `" . DB_PREFIX . "product_description` pd
            JOIN `" . DB_PREFIX . "language` l on l.language_id = pd.language_id
            WHERE product_id IN (" . implode(',', $products_ids) . ")")->rows;
        $data = $this->_formatTranslationsArray($data);

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        foreach ($products as $key => $product) {
            foreach ($languages as $code => $language) {
                $products[$key]['name'][$code] = $data[$product['product_id']][$code]['name'];
                $products[$key]['description'][$code] = $data[$product['product_id']][$code]['description'];
                $products[$key]['slug'][$code] = $data[$product['product_id']][$code]['slug'];
            }
        }
        return $products;
    }

    public function _formatTranslationsArray($products){
        $result = [];
        
        foreach ($products as $product) {
            $result[$product['product_id']][$product['language_code']]['name'] = $product['name'];
            $result[$product['product_id']][$product['language_code']]['description'] = $product['description'];
            $result[$product['product_id']][$product['language_code']]['slug'] = $product['slug'];
        }
        return $result;
    }

    /**
     * Get product cover
     */
    public function getProductCover($product_id) {
        $query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product WHERE product_id = " . $product_id . " LIMIT 1");
        return $query->row['image'] ?? 'image/no_image.jpg';
    }

    public function getPayThemProductByOEMID($OEM_PRODUCT_ID) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_paythem WHERE OEM_PRODUCT_ID = '" . $OEM_PRODUCT_ID . "'");

        return $query->row;
    }

    private function _addStockForecastingData($data, $product_id){

        if( \Extension::isinstalled('stock_forecasting') && 
            $this->config->get('stock_forecasting_status') == 1){

            $this->db->query("DELETE FROM `" . DB_PREFIX . "product_stock_forecasting_quantities` WHERE product_id = " . (int)$product_id);

            if(isset($data['stock_forecasting'])){
                foreach ($data['stock_forecasting'] as $record) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_stock_forecasting_quantities` 
                    SET product_id = " . (int)$product_id . ",
                    day = '" . $this->db->escape($record['day']) .  "',
                    available_quantity = " . (int) $record['quantity'] . "
                    ");
                }
            }
        }

    }
    public function deleteOptionValue($optionValueID, $productId = 0){
        ecTargetLog( [
            'backtrace' => debug_backtrace(),
            'uri' => $_SERVER['REQUEST_URI']
        ]);

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE option_value_id = '" . $this->db->escape((int) $optionValueID). "' AND product_id= '" . $this->db->escape((int) $productId) . "'");
         return true;
    }

    public function revokeOption($productId, $optionId){
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . $this->db->escape((int)$productId) . "' AND option_id = '" . $this->db->escape((int)$optionId) . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . $this->db->escape((int)$productId) . "' AND option_id = '" . $this->db->escape((int)$optionId) . "'");
        return true;
    }

    public function getLastProductInLimitId($products_limit){
        $query = $this->db->query("select product_id FROM " . DB_PREFIX . "product where archived = 0 limit 1 offset ".($products_limit - 1) );
        return $query->row['product_id'];
    }

    public function disableTrialProducts($products_limit){
        $lastProductInLimitId =  (int) $this->getLastProductInLimitId($products_limit);
        if ($lastProductInLimitId){
            $sql = "UPDATE " . DB_PREFIX . "product SET status = '" . 0 . "'". " WHERE product_id > $lastProductInLimitId";
            $this->db->query($sql);
        }
    }

    public function archive($product_ids) 
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET archived = '" . 1 . "',". "status = '" . 0 . "'". "WHERE product_id IN(".implode(',', $product_ids).")");
        return $query->row;
    }
    public function insertDeleteProcess($product_ids)
    {
        $product_ids = json_encode($product_ids);
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_delete_process SET products = '" . $product_ids ."'");
        return $query->row;
    }

    public function getIncomlpetedDeleteProcesses()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_delete_process
                    WHERE is_done = '" . 0 ."' ORDER BY CHAR_LENGTH(products) DESC");
        return $query->rows; 
    }

    public function updateDeleteProcessStatus($process_id) 
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product_delete_process SET date_modified = NOW(), is_done = '" . 1 . "'". "WHERE id = " . (int)$process_id);
        return $query->row;
    }     
    
}
