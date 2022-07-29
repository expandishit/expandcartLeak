<?php

use ExpandCart\Foundation\String\Slugify;

class ModelCheckoutWarehouse extends Model
{
    public function getProductInfo($product_id)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."product p WHERE p.product_id = '".(int) $product_id."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getalixOption($data)
    {
        $sql = 'SELECT alix_option_value_id FROM '.DB_PREFIX."warehouse_aliexpress_product_option_value WHERE oc_option_id = '".(int) $data['option_id']."' && oc_option_value_id = '".(int) $data['option_value_id']."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result['alix_option_value_id'];
        } else {
            return false;
        }
    }

    public function getOptionDetails($data)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."product_option_value WHERE product_option_value_id = '".(int) $data['product_option_value_id']."' && product_option_id = '".(int) $data['product_option_id']."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function hasWarehouseProducts($products)
    {
        foreach ($products as $key => $product) {
            $result = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_product WHERE product_id = '".(int) $this->db->escape($product['product_id'])."' ")->row;
            if ($result) {
                return true;
            }
        }

        return false;
    }

    public function hasOtherPrice($product_id, $price)
    {
        if ($this->productHasDiscount($product_id, $price) || $this->productHasDiscount($product_id, $price)) {
            return true;
        } else {
            return false;
        }
    }

    public function productHasDiscount($product_id, $price)
    {
        $discount = $this->db->query('SELECT price FROM '.DB_PREFIX."product_discount pd2 WHERE pd2.product_id = '".(int) $this->db->escape($product_id)."' AND pd2.price < '".$this->db->escape($price)."' AND pd2.customer_group_id = '".(int) $this->config->get('config_customer_group_id')."' AND pd2.quantity = '1' AND ((IFNULL(pd2.date_start, '0000-00-00') = '0000-00-00' OR pd2.date_start < NOW()) AND (IFNULL(pd2.date_end, '0000-00-00') = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1")->row;
        if ($discount) {
            return true;
        } else {
            return false;
        }
    }

    public function productHasSpecial($product_id)
    {
        $special = $this->db->query('SELECT price FROM '.DB_PREFIX."product_special ps WHERE ps.product_id = '".(int) $this->db->escape($product_id)."' AND pd2.price < '".$this->db->escape($price)."' AND ps.customer_group_id = '".(int) $this->config->get('config_customer_group_id')."' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1")->row;
        if ($special) {
            return true;
        } else {
            return false;
        }
    }

    public function getAliExpressProductsByOrderId($order_id)
    {
        $sql = "SELECT c.iso_code_2, o.shipping_address_1,o.shipping_address_2, o.shipping_zone as state,shipping_postcode as zipcode,telephone,CONCAT(o.shipping_firstname,' ', o.shipping_lastname) as contact_name, o.shipping_city FROM `".DB_PREFIX.'order` o LEFT JOIN '.DB_PREFIX."country c ON (c.country_id=o.shipping_country_id) WHERE o.order_id = '".(int) $this->db->escape($order_id)."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            if ($this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_order WHERE order_id = '".(int) $this->db->escape($order_id)."' ")->row) {
                $this->db->query('UPDATE '.DB_PREFIX."warehouse_aliexpress_order SET customer_name = '".$this->db->escape($result['contact_name'])."', status = '0' WHERE order_id = '".(int) $this->db->escape($order_id)."'");
            } else {
                $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_order SET order_id = '".(int) $this->db->escape($order_id)."', customer_name = '".$this->db->escape($result['contact_name'])."', status = '0'");
            }

            return $result;
        } else {
            return false;
        }
    }

    public function getAliExpressOrderList()
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_order WHERE status = '0' ORDER BY order_id DESC")->rows;

        $result = '';
        foreach ($query as $key => $value) {
            $result .= '+'.$value['order_id'].'_'.$value['customer_name'].'_'.$value['status'];
        }

        $result = ltrim($result, '+');

        return $result;
    }

    public function orderPlace($order_id)
    {
        $this->db->query('UPDATE '.DB_PREFIX."warehouse_aliexpress_order SET status = '1' WHERE order_id = '".(int) $this->db->escape($order_id)."'");
    }

    public function addAliexpressProductGeneral($data)
    {
        // Checking for redundancy
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_product WHERE ali_product_id = '".$this->db->escape($data['product_id'])."' ")->row;
        if ($result) {
            return false;
        }

        // Main product addition
        // $sql = "INSERT INTO ".DB_PREFIX."product SET model = '" . $this->db->escape($data['product_name']) . "', price = '" . $this->db->escape(preg_replace("/[^0-9.]/", "", trim($data['price'],'.') )) . "', quantity = '" . (int)$this->config->get('wk_dropship_aliexpress_quantity') . "', status = '" . $this->config->get('wk_dropship_direct_to_store') . "', date_added = NOW() ";

        if (!isset($data['quantity'])) {
            $data['quantity'] = (int) $this->config->get('wk_dropship_aliexpress_quantity');
        }

        $sql = 'INSERT INTO '.DB_PREFIX."product SET model = '".$this->db->escape($data['product_name'])."', price = '".$this->db->escape(preg_replace('/[^0-9.]/', '', trim($data['price'], '.')))."', quantity = '".(int) $data['quantity']."', status = '".$this->config->get('wk_dropship_direct_to_store')."', date_available = ".date('Y-m-d', time() - 86400)." ,date_added = NOW() ";

        if (isset($data['ext_version']) and version_compare($data['ext_version'], '1.0.2') >= 0) {
            $defaultWeight = 0;
            $defaultWeightClass = 1;
            if ($this->config->get('wk_dropship_aliexpress_default_weight')) {
                $defaultWeight = $this->config->get('wk_dropship_aliexpress_default_weight');
            }

            if (isset($data['default_weight']) && (float)$data['default_weight'] > 0) {
                $defaultWeight = $data['default_weight'];
            }
            $sql = $sql . ' ,weight="' . $defaultWeight . '"';

            if ($this->config->get('wk_dropship_aliexpress_default_weight_class')) {
                $defaultWeightClass = $this->config->get('wk_dropship_aliexpress_default_weight_class');
            }

            if (isset($data['default_weight_class']) && (float)$data['default_weight_class'] > 0) {
                $defaultWeightClass = $data['default_weight_class'];
            }
            $sql = $sql . ' ,weight_class_id="' . $defaultWeightClass . '"';
        }

        $this->db->query($sql);
        $product_id = $this->db->getLastId();
        $dropshipPrice = $this->getDropshipPrice($product_id);
        if ($dropshipPrice) {
            $this->db->query('UPDATE '.DB_PREFIX."product SET price = '".$this->db->escape($dropshipPrice)."' WHERE product_id = '".(int) $this->db->escape($product_id)."' ");
        }

        $response_desc = '';
        if (isset($data['description_url']) && !empty($data['description_url'])) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, html_entity_decode($data['description_url'], ENT_QUOTES, 'UTF-8'));
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $response_desc = curl_exec($curl);

            curl_close($curl);
        }

        //replace keyword code starts here

        if (!empty($this->config->get('wk_dropship_aliexpress_keyword'))) {
            foreach ($this->config->get('wk_dropship_aliexpress_keyword') as $key => $keyword) {
                $data['product_name'] = str_replace($keyword, '', $data['product_name']);
                $response_desc = str_replace($keyword, '', $response_desc);
                $data['meta_title'] = str_replace($keyword, '', $data['meta_title']);
                $data['meta_description'] = str_replace($keyword, '', $data['meta_description']);
                $data['meta_keyword'] = str_replace($keyword, '', $data['meta_keyword']);
            }
        }

        //repalce keyword ends here

        // adding description
        /*$sql = 'INSERT INTO '.DB_PREFIX."product_description SET product_id = '".$product_id."', name = '".$this->db->escape($data['product_name'])."', description = '".$this->db->escape($response_desc)."', meta_title = '".$this->db->escape($data['meta_title'])."', meta_description = '".$this->db->escape($data['meta_description'])."', meta_keyword = '".$this->db->escape($data['meta_keyword'])."', language_id = '".(int) $this->config->get('wk_dropship_aliexpress_language')."' ";*/
        // remove meta_title
        $aliExpressLangs = $this->config->get('wk_dropship_aliexpress_language');
        if (is_array($aliExpressLangs) == false) {
            $aliExpressLangs = [$aliExpressLangs];
        }
        foreach ($aliExpressLangs as $aliExpressLang) {
            $sql = 'INSERT INTO '.DB_PREFIX."product_description SET
                product_id = '".$product_id."',
                name = '".$this->db->escape($data['product_name'])."',
                slug = '".$this->db->escape((new Slugify)->slug($data['product_name']))."',
                description = '".$this->db->escape($response_desc)."',
                meta_description = '".$this->db->escape($data['meta_description'])."',
                meta_keyword = '".$this->db->escape($data['meta_keyword'])."',
                language_id = '".(int) $aliExpressLang."' ";

            $this->db->query($sql);
        }

        // Adding to the store
        $sql = 'INSERT INTO '.DB_PREFIX."product_to_store SET product_id = '".$this->db->escape($product_id)."', store_id = '".(int) $this->config->get('wk_dropship_aliexpress_store')."' ";
        $this->db->query($sql);

        // Downloading image from aliexpress and saving locally
        $image = '';
        if (isset($data['imageUrl']) && $data['imageUrl']) {
            $image = $this->downloadImage($data['imageUrl'], $product_id, $product_id.$data['product_id']);
        }

        if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
            foreach ($data['images'] as $key => $multi_image_url) {
                $this->downloadImages($multi_image_url, $key, $product_id, $product_id.$data['product_id'].'_'.$key);
            }
        }

        // Saving data for warehouse usage
        $sql = 'INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product SET ali_product_id = '".$this->db->escape($data['product_id'])."', product_id = '".(int) $this->db->escape($product_id)."', product_url = '".$this->db->escape($data['product_url'])."', name = '".$this->db->escape($data['product_name'])."' ";
        $this->db->query($sql);

        return $product_id;
    }

    public function addAliexpressProductOption($data)
    {
        $product_id = $data['product_id'];

        if (isset($data['customOption']) && is_array($data['customOption']) && $data['customOption']) {
            $product_details = $this->db->query('SELECT * FROM `'.DB_PREFIX."product` WHERE product_id = '".(int) $this->db->escape($product_id)."'")->row;

            $priceByPriceRule = $this->priceByPriceRule($data['customOption']['price']);

            if ($priceByPriceRule) {
                $variation_price = $priceByPriceRule;
            } else {
                $variation_price = $data['customOption']['price'];
            }

            if ($product_details['price'] < $variation_price) {
                $price = $variation_price - $product_details['price'];
                $price_prefix = '+';
            } else {
                $price = $product_details['price'] - $variation_price;
                $price_prefix = '-';
            }

            if (isset($data['customOption']['quantity'])) {
                $quantity = $data['customOption']['quantity'];
            } else {
                $quantity = $this->config->get('wk_dropship_aliexpress_quantity');
            }

            $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['comb'])."', variation_name = '".$this->db->escape($data['customOption']['text'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."'");

            $variation_id = $this->db->getLastId();
            $combination_options = explode('_', trim($data['customOption']['comb'], '_'));
            if ($combination_options) {
                foreach ($combination_options as $key => $combination_option) {
                    $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_variation_option SET variation_id = '".(int) $variation_id."', option_value_id = '".(int) $combination_option."', product_id = '".(int) $product_id."' ");
                }
            }
        }

        return true;
    }

    public function addOptionsToCore($options, $product_id)
    {
        if ($options) {
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($options as $key => $option) {
                // Checking option exist or not on the basis of aliexpress option details
                $isExist = $this->alixOptionExist($option);
                
                if (!$isExist) {
                    // Adding option to opencart core option
                    $option_id = $this->createCoreOption($option, $languages);
                } else {
                    $option_id = $isExist['oc_option_id'];
                     $this->InsertOptionIfNotExist($option_id);
                     $this->updateOrInsertOptionDesc($languages,$option_id,$option['option_name']);
                }
                // setting option on the product
                $product_option_id = $this->setOptionOnProduct($option_id, $product_id);
                $last_image_key=$this->getLastKeyImage($product_id);

                if (isset($option['option_values']) && $option['option_values']) {
                    foreach ($option['option_values'] as $key => $option_value) {
                        // Checking option value exist or not on the basis of aliexpress option and option value details
                        $isValueExist = $this->alixOptionValueExist($option, $option_value,$option_id);
                        if (!$isValueExist || !$this->checkOcOptionValueExist($isValueExist['oc_option_value_id'])) {
                            // Adding option value to opencart core option added above
                            $option_value_id = $this->createCoreOptionValue($option_id, $option, $option_value, $languages);
                        } else {
                            $option_value_id = $isValueExist['oc_option_value_id'];
                        }
                        // setting option value on the product
                        $product_option_value_id = $this->setOptionValueOnProduct($option_id, $option_value_id, $product_id, $product_option_id);
                        //add option image to product
                        if(isset($option_value['option_image']))
                        {
                          $this->downloadImages($option_value['option_image'], ($key+$last_image_key), $product_id, $product_id.$option_value['option_value_id'].'_'.($key+$last_image_key));
                          if(\Extension::isinstalled('product_option_image_pro'))
                           {
                            $image_name='aliexpress/catalog/'.$product_id.$option_value['option_value_id'].'_'.($key+$last_image_key).'.jpg';
                            $this->linkOptionWithImage($product_id,$product_option_id,$product_option_value_id,$image_name);
                           }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function alixOptionExist($option)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_product_option WHERE alix_option_id = '".(int) $option['option_id']."' && value = '".$this->db->escape($option['option_name'])."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function alixOptionValueExist($option, $option_value,$option_id)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_product_option_value WHERE oc_option_id = '".(int) $option_id."' && alix_option_id = '".(int) $option['option_id']."' && alix_option_value_id = '".(int) $option_value['option_value_id']."' && value = '".$this->db->escape($option_value['option_value_name'])."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function createCoreOption($option, $languages)
    {
        $this->db->query('INSERT INTO `'.DB_PREFIX."option` SET type = 'select' ");
        $option_id = $this->db->getLastId();

        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option SET oc_option_id = '".(int) $option_id."', alix_option_id = '".(int) $option['option_id']."' ,value = '".$this->db->escape($option['option_name'])."' ");

        foreach ($languages as $key => $language) {
            $this->db->query('INSERT INTO '.DB_PREFIX."option_description SET option_id = '".(int) $option_id."', language_id = '".(int) $language['language_id']."', name = '".$this->db->escape($option['option_name'])."'  ");
        }

        return $option_id;
    }

    public function createCoreOptionValue($option_id, $option, $option_value, $languages)
    {
        $this->db->query('INSERT INTO '.DB_PREFIX."option_value SET option_id = '".(int) $option_id."' ");
        $option_value_id = $this->db->getLastId();

        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option_value SET oc_option_id = '".(int) $option_id."', alix_option_id = '".(int) $option['option_id']."', oc_option_value_id = '".(int) $option_value_id."', alix_option_value_id = '".(int) $option_value['option_value_id']."', value = '".$this->db->escape($option_value['option_value_name'])."' ");

        foreach ($languages as $key => $language) {
            $this->db->query('INSERT INTO '.DB_PREFIX."option_value_description SET option_value_id = '".(int) $option_value_id."', option_id = '".(int) $option_id."', language_id = '".(int) $language['language_id']."', name = '".$this->db->escape($option_value['option_value_name'])."'  ");
        }

        return $option_value_id;
    }

    public function setOptionOnProduct($option_id, $product_id)
    {
        $this->db->query('INSERT INTO '.DB_PREFIX."product_option SET product_id = '".(int) $product_id."', option_id = '".(int) $option_id."', required = '1' ");

        return $this->db->getLastId();
    }

    public function setOptionValueOnProduct($option_id, $option_value_id, $product_id, $product_option_id)
    {
        $this->db->query('INSERT INTO '.DB_PREFIX."product_option_value SET product_option_id = '".(int) $product_option_id."', product_id = '".(int) $product_id."', option_id = '".(int) $option_id."', option_value_id = '".(int) $option_value_id."', quantity = '".(int) $this->config->get('wk_dropship_aliexpress_quantity')."', subtract = '0', price = '0', price_prefix = '+', points = '0', points_prefix = '+', weight = '0', weight_prefix = '+'  ");

        return $this->db->getlastId();
    }

    public function addAliexpressProductAttribute($data, $update = false)
    {
        $aliExpressLangs = $this->config->get('wk_dropship_aliexpress_language');
        if (is_array($aliExpressLangs) == false) {
            $aliExpressLangs = [$aliExpressLangs];
        }
        $product_id = $data['product_id'];
        // Create attribute and adding attribute value to the product
        if (isset($data['customAttribute']) && $data['customAttribute'] && $this->config->get('wk_dropship_aliexpress_attribute_group_id')) {
            foreach ($data['customAttribute'] as $key => $attrDetails) {
                $checkAlxAttrExist = $this->db->query('SELECT * FROM `'.DB_PREFIX."attribute_description`
                    WHERE name = '".$this->db->escape($attrDetails['attrName'])."'
                    AND language_id IN (".implode(',', $aliExpressLangs).")")->row;

                if (!$checkAlxAttrExist) {
                    $this->db->query('INSERT INTO '.DB_PREFIX."attribute SET attribute_group_id = '".(int) $this->config->get('wk_dropship_aliexpress_attribute_group_id')."', sort_order = '".(int) $this->db->escape($key)."'");

                    $attribute_id = $this->db->getLastId();

                    foreach ($aliExpressLangs as $aliExpressLang) {

                        $this->db->query('INSERT INTO '.DB_PREFIX."attribute_description SET
                            attribute_id = '".(int) $this->db->escape($attribute_id)."',
                            language_id = '".(int) $aliExpressLang."',
                            name = '".$this->db->escape($attrDetails['attrName'])."'");
                    }
                } else {
                    $attribute_id = $checkAlxAttrExist['attribute_id'];
                }

                foreach ($aliExpressLangs as $aliExpressLang) {

                    $checkAlxAttrProductExist = $this->db->query('SELECT * FROM `' . DB_PREFIX . "product_attribute`
                    WHERE product_id = '" . (int)$this->db->escape($product_id) . "' AND
                    attribute_id = '" . (int)$this->db->escape($attribute_id) . "' AND
                    language_id = '" . (int)$aliExpressLang . "'")->row;

                    if (!$checkAlxAttrProductExist) {
                        $this->db->query('INSERT INTO ' . DB_PREFIX . "product_attribute SET
                            product_id = '" . (int)$this->db->escape($product_id) . "',
                            attribute_id = '" . (int)$this->db->escape($attribute_id) . "',
                            language_id = '" . (int)$aliExpressLang . "',
                            text = '" . $this->db->escape($attrDetails['attrValue']) . "'");

                    } else {
                        if ($update) {
                            $this->db->query('UPDATE ' . DB_PREFIX . "product_attribute SET
                                text = '" . $this->db->escape($attrDetails['attrValue']) . "' WHERE
                                product_id = '" . (int)$this->db->escape($product_id) . "' AND
                                attribute_id = '" . (int)$this->db->escape($attribute_id) . "' AND
                                language_id = '" . (int)$aliExpressLang . "'");
                        }
                    }
                }
            }
        }

        return true;
    }

    public function addAliexpressProductReview($data)
    {
        $product_id = $data['product_id'];
        $this->db->query('INSERT INTO '.DB_PREFIX."review SET author = '".$this->db->escape($data['name'])."', customer_id = '0', product_id = '".$this->db->escape((int) $product_id)."', text = '".$this->db->escape($data['comment'])."', rating = '".$this->db->escape((int) $data['rating'])."', status = '".$this->db->escape((int) $this->config->get('wk_dropship_aliexpress_review_status'))."', date_added = NOW()");

        return true;
    }

    public function addAliexpressProductWarehouse($data)
    {
        $product_id = $data['product_id'];

        if (isset($data['sellerName']) && !empty($data['sellerName'])) {
            $checkSellerExist = $this->db->query('SELECT id FROM `'.DB_PREFIX."warehouse_aliexpress_seller` WHERE seller_name = '".$this->db->escape($data['sellerName'])."'")->row;

            if (!$checkSellerExist) {
                $this->db->query('INSERT INTO `'.DB_PREFIX."warehouse_aliexpress_seller` SET seller_name = '".$this->db->escape($data['sellerName'])."', date_added = NOW()");

                $aliexpress_seller_id = $this->db->getLastId();
            } else {
                $aliexpress_seller_id = $checkSellerExist['id'];
            }

            $this->db->query('UPDATE `'.DB_PREFIX."warehouse_aliexpress_product` SET aliexpress_seller_id = '".(int) $this->db->escape($aliexpress_seller_id)."' WHERE product_id= '".(int) $this->db->escape($product_id)."'");
        }

        return true;
    }

    private function createImagesDirectory()
    {
        if (!\Filesystem::isDirExists('image/aliexpress/catalog')) {
            \Filesystem::createDir('image/aliexpress/catalog');
            \Filesystem::setPath('image/aliexpress/catalog')->changeMod('writable');
        }
    }

    public function downloadImage($url, $product_id, $image_name)
    {
        $this->createImagesDirectory();
        $url = str_replace('_220x220xz.jpg_.webp', '', $url);
        $url = str_replace('_220x220.jpg', '', $url);
        if (false === strpos($url, '://')) {
            $url = 'http://'.$url;
        }
        $image_name = 'aliexpress/catalog/'.$image_name.'.jpg';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $image_content = curl_exec($ch);
        if (false === $image_content) {
            $image_content = '';
            $image_name = '';
        } else {
            \Filesystem::setPath('image/' . $image_name)->put($image_content);
        }
        curl_close($ch);
        $this->db->query('UPDATE '.DB_PREFIX."product SET image = '".$this->db->escape($image_name)."' WHERE product_id = '".(int) $this->db->escape($product_id)."' ");

        return $image_name;
    }

    public function downloadImages($url, $sort_order, $product_id, $image_name)
    {
        $this->createImagesDirectory();
        $image_name = 'aliexpress/catalog/'.$image_name.'.jpg';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $image_content = curl_exec($ch);
        if (false === $image_content) {
            $image_content = '';
            $image_name = '';
        } else {
            \Filesystem::setPath('image/' . $image_name)->put($image_content);
        }
        curl_close($ch);
        $this->db->query('INSERT INTO '.DB_PREFIX."product_image SET image = '".$this->db->escape($image_name)."', product_id = '".(int) $this->db->escape($product_id)."', sort_order = '".(int) $this->db->escape($sort_order)."'");
    }

    public function getWarehouseProductPrice($warehouse_id, $product_id)
    {
        $result = $this->db->query('SELECT price FROM '.DB_PREFIX."warehouse_product wp WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."' AND product_id = '".(int) $this->db->escape($product_id)."' ")->row;
        if ($result) {
            return $result['price'];
        } else {
            return false;
        }
    }

    public function priceByPriceRule($productPrice)
    {
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."price_rule WHERE price_from <= '".(int) $this->db->escape($productPrice)."' AND price_to >= '".(int) $this->db->escape($productPrice)."' AND channel = 'ali' ORDER BY rule_id DESC ")->row;

        if ($result) {
            $price = $productPrice;
            if ('p' == $result['method_type']) {
                $added_price = $this->getPriceByPercentage($productPrice, $result['amount'], $result['operation_type']);
            } else {
                $added_price = $result['amount'];
            }
            if ($added_price) {
                switch ($result['operation_type']) {
            case '*':
            $price = $price * $added_price;
            break;
            case '+':
            $price = $price + $added_price;
            break;
            case '-':
            $price = $price - $added_price;
            break;
          }
            }
            if ($price) {
                return $price;
            } else {
                return false;
            }
        }
    }

    public function getDropshipPrice($product_id)
    {
        $productInfo = $this->getProductInfoForDropshippingPrice($product_id);
        if ($productInfo) {
            foreach ($productInfo as $key => $value) {
                $result = $this->db->query('SELECT * FROM '.DB_PREFIX."price_rule WHERE price_from <= '".(int) $this->db->escape($value['price'])."' AND price_to >= '".(int) $this->db->escape($value['price'])."' AND channel = 'ali' ORDER BY rule_id DESC ")->row;
                if ($result) {
                    $price = $value['price'];
                    if ('p' == $result['method_type']) {
                        $added_price = $this->getPriceByPercentage($value['price'], $result['amount'], $result['operation_type']);
                    } else {
                        $added_price = $result['amount'];
                    }
                    if ($added_price) {
                        switch ($result['operation_type']) {
                            case '*':
                            $price = $price * $added_price;
                            break;
                            case '+':
                            $price = $price + $added_price;
                            break;
                            case '-':
                            $price = $price - $added_price;
                            break;
                        }
                    }
                    if ($price) {
                        return $price;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }

    public function getProductInfoForDropshippingPrice($product_id)
    {
        $sql = 'SELECT p.price FROM '.DB_PREFIX."product p WHERE p.product_id = '".(int) $this->db->escape($product_id)."' ";
        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getPriceByPercentage($price, $value, $operation_type)
    {
        $toBeAdded = ($price * $value) / 100;

        return $toBeAdded;
    }

    public function checkIsCommonShippingMethod($warehouses, $code)
    {
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_shippings ws WHERE code = '".$this->db->escape($code)."' AND warehouse_id IN (".implode($warehouses, ',').') ')->row;
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getWarehouseDetailsByProductId($productId, $quantity, $address)
    {
        $warehouse = array();
        $sql = 'SELECT * FROM '.DB_PREFIX.'warehouse_product wp LEFT JOIN '.DB_PREFIX."warehouse w ON (w.warehouse_id=wp.warehouse_id) WHERE wp.product_id = '".(int) $this->db->escape($productId)."' AND wp.approved = 1 ";
        $result = $this->db->query($sql)->rows;
        if (1 == count($result)) {
            if ($this->checkWarehouseHasQuantity($result[0], $quantity)) {
                $warehouse = $result[0];
            }
        } elseif (count($result) > 1) {
            $sql .= " AND zone_id = '".(int) $this->db->escape($address['zone_id'])."' AND country_id = '".(int) $this->db->escape($address['country_id'])."' ";
            $nearestLocated = $this->db->query($sql)->rows;
            if ($nearestLocated) {
                if (1 == count($nearestLocated) && $this->checkWarehouseHasQuantity($nearestLocated[0], $quantity)) {
                    $warehouse = $nearestLocated[0];
                } else {
                    $warehouse = $this->getNearestWarehouse($nearestLocated, $quantity, $address);
                }
            } else {
                $warehouse = $this->getNearestWarehouse($result, $quantity, $address);
            }
        }

        return $warehouse;
    }

    public function getNearestWarehouse($warehouses, $quantity, $address)
    {
        $selectWarehouse = array();
        foreach ($warehouses as $key => $warehouse) {
            if (!$this->checkWarehouseHasQuantity($warehouse, $quantity)) {
                continue;
            }
            $distanceIndex = $this->getDistance($warehouse['latitude'], $warehouse['longitude'], $address['customer_latitude'], $address['customer_longitude'], 'K');
            $selectWarehouse[$key] = $distanceIndex;
        }
        if ($selectWarehouse) {
            asort($selectWarehouse);
            reset($selectWarehouse);
            $first_key = key($selectWarehouse);

            return $warehouses[$first_key];
        } else {
            return false;
        }
    }

    public function checkWarehouseHasQuantity($warehouse, $quantity)
    {
        $sql = 'SELECT SUM(quantity) as sold_quantity FROM '.DB_PREFIX."warehouse_order WHERE product_id = '".(int) $this->db->escape($warehouse['product_id'])."' && warehouse_id = '".(int) $this->db->escape($warehouse['warehouse_id'])."' ";
        $sold = $this->db->query($sql)->row;
        if ($sold && ($warehouse['quantity'] - $sold['sold_quantity']) < $quantity) {
            return false;
        }

        return true;
    }

    protected function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $unit)
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch ($unit) {
            case 'm': break;
            case 'K': $distance = $distance * 1.609344;
        }

        return round($distance, 2);
    }

    /**
     * [getWarehouse get warehouse id].
     *
     * @param [type] $product_id   [product id]
     * @param [type] $warehouse_id [warehouse id]
     *
     * @return [type] [array]
     */
    public function getWarehouse($product_id, $warehouse_id)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX.'warehouse_product cfcg LEFT JOIN `'.DB_PREFIX.'warehouse` cf ON (cfcg.warehouse_id = cf.warehouse_id) LEFT JOIN '.DB_PREFIX."product_description cfd ON (cfcg.product_id = cfd.product_id) WHERE cfcg.product_id= '".(int) $this->db->escape($product_id)."' AND cfcg.warehouse_id='".(int) $this->db->escape($warehouse_id)."'";

        $detail = $this->db->query($sql);

        return $detail->row;
    }

    /**
     * [getExtensions get extensions].
     *
     * @param [type] $code [extension code]
     *
     * @return [type] [array]
     */
    public function getExtensions($code)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."extension WHERE `code` = '".$this->db->escape($code)."'");

        return $query->row;
    }

    /**
     * [getWarehousePosition get warehouse position].
     *
     * @return [type] [array]
     */
    public function getWarehousePosition($shipping_address)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse WHERE status = '1' ")->rows;
        // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "warehouse WHERE zone_id = '".(int)$shipping_address['zone_id']."' AND country_id = '".(int)$shipping_address['country_id']."' AND status = '1' ")->rows;
        return $query;
    }

    //Update code starts here
    public function updateProductGeneralInfo($data)
    {
        $query = $this->db->query('SELECT product_id FROM '.DB_PREFIX."warehouse_aliexpress_product WHERE ali_product_id='".$this->db->escape($data['product_id'])."'")->row;

        $product_id = $query['product_id'];

        $this->db->query('UPDATE '.DB_PREFIX."warehouse_aliexpress_product SET  name='".$this->db->escape($data['product_name'])."' WHERE product_id='".(int) $product_id."' AND ali_product_id='".$this->db->escape($data['product_id'])."'");

        $this->db->query('UPDATE '.DB_PREFIX."product SET model = '".$this->db->escape($data['product_name'])."', price = '".$this->db->escape(preg_replace('/[^0-9.]/', '', trim($data['price'], '.')))."', quantity = '".(int) $data['quantity']."', status = '".$this->config->get('wk_dropship_direct_to_store')."' WHERE product_id='".(int) $product_id."'");

        $dropshipPrice = $this->getDropshipPrice($product_id);
        if ($dropshipPrice) {
            $this->db->query('UPDATE '.DB_PREFIX."product SET price = '".$this->db->escape($dropshipPrice)."' WHERE product_id = '".(int) $this->db->escape($product_id)."' ");
        }

        $response_desc = '';
        if (isset($data['description_url']) && !empty($data['description_url'])) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, html_entity_decode($data['description_url'], ENT_QUOTES, 'UTF-8'));
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $response_desc = curl_exec($curl);

            curl_close($curl);
        }

        //replace keyword code starts here

        if (!empty($this->config->get('wk_dropship_aliexpress_keyword'))) {
            foreach ($this->config->get('wk_dropship_aliexpress_keyword') as $key => $keyword) {
                $data['product_name'] = str_replace($keyword, '', $data['product_name']);
                $response_desc = str_replace($keyword, '', $response_desc);
                $data['meta_title'] = str_replace($keyword, '', $data['meta_title']);
                $data['meta_description'] = str_replace($keyword, '', $data['meta_description']);
                $data['meta_keyword'] = str_replace($keyword, '', $data['meta_keyword']);
            }
        }

        //repalce keyword ends here

        /*$this->db->query('UPDATE '.DB_PREFIX."product_description SET name ='".$this->db->escape($data['product_name'])."', description = '".$this->db->escape($response_desc)."',meta_title = '".$this->db->escape($data['meta_title'])."', meta_description = '".$this->db->escape($data['meta_description'])."',  meta_keyword = '".$this->db->escape($data['meta_keyword'])."' WHERE language_id = '".(int) $this->config->get('wk_dropship_aliexpress_language')."' AND product_id='".(int) $product_id."'");*/
        // remove meta_title

        $aliExpressLangs = $this->config->get('wk_dropship_aliexpress_language');
        if (is_array($aliExpressLangs) == false) {
            $aliExpressLangs = [$aliExpressLangs];
        }

        foreach ($aliExpressLangs as $aliExpressLang) {
            $isDescriptionExists = $this->db->query("SELECT name FROM ".DB_PREFIX."product_description WHERE language_id = '".(int) $aliExpressLang."' AND product_id = '".(int) $product_id."'");

            if ($isDescriptionExists->row) {
                $this->db->query('UPDATE ' . DB_PREFIX . "product_description SET name ='" . $this->db->escape($data['product_name']) . "', description = '" . $this->db->escape($response_desc) . "', meta_description = '" . $this->db->escape($data['meta_description']) . "',  meta_keyword = '" . $this->db->escape($data['meta_keyword']) . "' WHERE language_id = '" . (int)$aliExpressLang . "' AND product_id='" . (int)$product_id . "'");
            }else{
                $sql = 'INSERT INTO '.DB_PREFIX."product_description SET
                product_id = '".$product_id."',
                name = '".$this->db->escape($data['product_name'])."',
                slug = '".$this->db->escape((new Slugify)->slug($data['product_name']))."',
                description = '".$this->db->escape($response_desc)."',
                meta_description = '".$this->db->escape($data['meta_description'])."',
                meta_keyword = '".$this->db->escape($data['meta_keyword'])."',
                language_id = '".(int) $aliExpressLang."' ";

                $this->db->query($sql);
            }

        }

        // Downloading image from aliexpress and saving locally
        $image = '';
        if (isset($data['imageUrl']) && $data['imageUrl']) {
            $image = $this->downloadImage($data['imageUrl'], $product_id, $product_id.$data['product_id']);
        }

        if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
            $this->db->query('DELETE FROM '.DB_PREFIX."product_image WHERE product_id = '".(int) $product_id."'");

            foreach ($data['images'] as $key => $multi_image_url) {
                $this->downloadImages($multi_image_url, $key, $product_id, $product_id.$data['product_id'].'_'.$key);
            }
        }

        return $product_id;
    }

    public function updateOptionsToCore($options, $product_id)
    {
        if ($options) {
            $this->db->query('DELETE FROM '.DB_PREFIX."product_option WHERE product_id = '".(int) $product_id."'");
            $this->db->query('DELETE FROM '.DB_PREFIX."product_option_value WHERE product_id = '".(int) $product_id."'");
            if(\Extension::isinstalled('product_option_image_pro'))
            {
            $this->db->query('DELETE FROM '.DB_PREFIX."poip_option_image WHERE product_id = '".(int) $product_id."'");
            }
            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($options as $key => $option) {
                // Checking option exist or not on the basis of aliexpress option details
                $isExist = $this->alixOptionExist($option);
                if (!$isExist) {
                    // Adding option to opencart core option
                    $option_id = $this->createCoreOption($option, $languages);
                } else {
                    $option_id = $isExist['oc_option_id'];
                    $this->InsertOptionIfNotExist($option_id);
                    $this->updateOrInsertOptionDesc($languages,$option_id,$option['option_name']);
                }
                // setting option on the product
                $product_option_id = $this->setOptionOnProduct($option_id, $product_id);

                //get last image key inserted
                $last_image_key=$this->getLastKeyImage($product_id);
                if (isset($option['option_values']) && $option['option_values']) {
                    foreach ($option['option_values'] as $key => $option_value) {
                        // Checking option value exist or not on the basis of aliexpress option and option value details
                        $isValueExist = $this->alixOptionValueExist($option, $option_value,$option_id);
                        if (!$isValueExist) {
                            // Adding option value to opencart core option added above
                            $option_value_id = $this->createCoreOptionValue($option_id, $option, $option_value, $languages);
                        } else {
                            $option_value_id = $isValueExist['oc_option_value_id'];
                            $this->InsertOptionValueIfNotExist($option_id,$option_value_id);
                            $this->updateOrInsertOptionValueDesc($languages,$option_value_id,$option_id,$option_value['option_value_name']);

                        }
                        // setting option value on the product
                        $product_option_value_id = $this->setOptionValueOnProduct($option_id, $option_value_id, $product_id, $product_option_id);
                       
                        //add option image to product
                        if(isset($option_value['option_image']))
                        {
                          $this->downloadImages($option_value['option_image'], ($key+$last_image_key), $product_id, $product_id.$option_value['option_value_id'].'_'.($key+$last_image_key));
                          if(\Extension::isinstalled('product_option_image_pro'))
                           {
                            $image_name='aliexpress/catalog/'.$product_id.$option_value['option_value_id'].'_'.($key+$last_image_key).'.jpg';
                            $this->linkOptionWithImage($product_id,$product_option_id,$product_option_value_id,$image_name);
                           }
                        }
                      
                    }
                }
            }
        }

        return true;
    }

    public function UpdateAliexpressProductOption($data)
    {
        $product_id = $data['product_id'];

        if (isset($data['customOption']) && is_array($data['customOption']) && $data['customOption']) {
            $product_details = $this->db->query('SELECT * FROM `'.DB_PREFIX."product` WHERE product_id = '".(int) $this->db->escape($product_id)."'")->row;
            $priceByPriceRule = $this->priceByPriceRule($data['customOption']['price']);

            if ($priceByPriceRule) {
                $variation_price = $priceByPriceRule;
            } else {
                $variation_price = $data['customOption']['price'];
            }

            if ($product_details['price'] < $variation_price) {
                $price = $variation_price - $product_details['price'];
                $price_prefix = '+';
            } else {
                $price = $product_details['price'] - $variation_price;
                $price_prefix = '-';
            }

            if (isset($data['customOption']['quantity'])) {
                $quantity = $data['customOption']['quantity'];
            } else {
                $quantity = $this->config->get('wk_dropship_aliexpress_quantity');
            }

            $check_aliexpress_product_variation = $this->db->query('SELECT id FROM '.DB_PREFIX."warehouse_aliexpress_product_variation WHERE product_id ='".(int) $product_id."' AND variation_text = '".$this->db->escape($data['customOption']['comb'])."' AND variation_name = '".$this->db->escape($data['customOption']['text'])."'")->row;

            if ($check_aliexpress_product_variation && (isset($check_aliexpress_product_variation['id']) && $check_aliexpress_product_variation['id'])) {
                $this->db->query('UPDATE '.DB_PREFIX."warehouse_aliexpress_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['comb'])."', variation_name = '".$this->db->escape($data['customOption']['text'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."' WHERE id ='".(int) $check_aliexpress_product_variation['id']."'");
            } else {
                $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['comb'])."', variation_name = '".$this->db->escape($data['customOption']['text'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."'");

                $variation_id = $this->db->getLastId();
                $combination_options = explode('_', trim($data['customOption']['comb'], '_'));
                if ($combination_options) {
                    foreach ($combination_options as $key => $combination_option) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_variation_option SET variation_id = '".(int) $variation_id."', option_value_id = '".(int) $combination_option."', product_id = '".(int) $product_id."' ");
                    }
                }
            }
        }

        return true;
    }

    public function deleteAliexpressProductReview($data)
    {
        $product_id = $data['product_id'];

        $this->db->query('DELETE FROM '.DB_PREFIX."review WHERE product_id = '".(int) $product_id."'");
    }

    public function getOrderOptions($order_id, $order_product_id)
    {
        // $query = $this->db->query("SELECT aliexpress_option_value_id FROM " . DB_PREFIX . "order_option oo LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN " . DB_PREFIX . "warehouse_aliexpress_option_value wapov ON (pov.option_value_id = wapov.option_value_id) WHERE oo.order_id = '" . (int)$order_id . "' AND oo.order_product_id = '" . (int)$order_product_id . "'")->rows;
        // $aliexress_product_options = array();
        // foreach ($query as $value) {
        //  if (isset($value['aliexpress_option_value_id']) && $value['aliexpress_option_value_id']) {
        //      $aliexress_product_options[] = $value['aliexpress_option_value_id'];
        //  } else {
        //      $aliexress_product_options = array();
        //      $query = $this->db->query("SELECT wapov.alix_option_value_id FROM " . DB_PREFIX . "order_option oo LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN " . DB_PREFIX . "warehouse_aliexpress_product_option_value wapov ON (pov.option_value_id = wapov.oc_option_value_id) WHERE oo.order_id = '" . (int)$order_id . "' AND oo.order_product_id = '" . (int)$order_product_id . "'")->rows;
        //      foreach ($query as $value) {
        //          if (isset($value['alix_option_value_id']) && $value['alix_option_value_id']) {
        //              $aliexress_product_options[] = $value['alix_option_value_id'];
        //          }
        //      }
        //  }
        // }

        $query = $this->db->query('SELECT wapov.* FROM '.DB_PREFIX.'order_option oo LEFT JOIN '.DB_PREFIX.'product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_product_option_value wapov ON (pov.option_value_id = wapov.oc_option_value_id) WHERE oo.order_id = '".(int) $order_id."' AND oo.order_product_id = '".(int) $order_product_id."'")->rows;
        $aliexress_product_options = array();
        foreach ($query as $value) {
            if (isset($value['alix_option_value_id']) && $value['alix_option_value_id']) {
                $aliexress_product_options[] = $value['alix_option_value_id'];
            }
        }

        return $aliexress_product_options;
    }

    public function getAlixProductId($order_id, $order_product_id)
    {
        $query = $this->db->query('SELECT wap.* FROM '.DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_product wap ON (op.product_id = wap.product_id) WHERE op.order_id = '".(int) $order_id."' AND op.order_product_id = '".(int) $order_product_id."'")->row;
        if ($query) {
            return $query['ali_product_id'];
        } else {
            false;
        }
    }

    public function checkProductVariationExist($order_product_id)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX.'product_option WHERE product_id = (SELECT product_id FROM '.DB_PREFIX."order_product WHERE order_product_id = '".(int) $order_product_id."') AND option_id = '".(int) $this->config->get('wk_dropship_aliexpress_variation_option_id')."'")->row;

        return $query;
    }

    public function getOrderVariations($order_id, $order_product_id)
    {
        $query = $this->db->query('SELECT aliexpress_variation FROM '.DB_PREFIX.'order_option oo LEFT JOIN '.DB_PREFIX.'product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_variation wav ON (pov.option_value_id = wav.option_value_id) WHERE oo.order_id = '".(int) $this->db->escape($order_id)."' AND oo.order_product_id = '".(int) $this->db->escape($order_product_id)."'")->row;
        $aliexress_product_options = array();

        return $query;
    }

    public function checkValidOrderId($data)
    {
        $query = $this->db->query('SELECT product_id FROM '.DB_PREFIX."order_product WHERE order_id= '".(int) $data['order_id']."'")->rows;

        if (!empty($query)) {
            return true;
        } else {
            false;
        }
    }

    public function getOrderProductDetails($data)
    {
        $wk_product_options = array();
        $details = array();

        $product_id = $this->db->query('SELECT product_id FROM '.DB_PREFIX."warehouse_aliexpress_product WHERE ali_product_id='".$this->db->escape($data['ali_product_id'])."'")->row;

        if (!empty($product_id) && !isset($product_id['product_id'])) {
            return false;
        }

        $query = $this->db->query('SELECT order_product_id,quantity FROM '.DB_PREFIX."order_product WHERE order_id= '".(int) $data['order_id']."' AND product_id='".(int) $product_id['product_id']."'")->row;

        $details['quantity'] = $query['quantity'];

        $order_product_id = $query['order_product_id'];

        $checkProductVariationExist = $this->checkProductVariationExist($order_product_id);

        if ($checkProductVariationExist) {
            $aliexpress_variation = $this->getOrderVariations($data['order_id'], $order_product_id);

            if ($aliexpress_variation) {
                if ($wk_product_options) {
                    $wk_product_options = $wk_product_options.'_'.str_replace('_', '+', $aliexpress_variation['aliexpress_variation']);
                } else {
                    $wk_product_options = str_replace('_', '+', $aliexpress_variation['aliexpress_variation']);
                }
            }
        } else {
            $options = $this->getOrderOptions($data['order_id'], $order_product_id);

            if ($options) {
                $wk_product_options[] = implode('+', $options);
            } else {
                $wk_product_options[] = '-';
            }
        }

        $details['product_option'] = $wk_product_options;

        return $details;
    }

    public function checkOcOptionValueExist($oc_option_value_id)
    {
        return $this->db->query('SELECT * FROM `'.DB_PREFIX.'option_value` WHERE `option_value_id` = '.(int) $oc_option_value_id.'')->row;
    }
    public function InsertOptionIfNotExist($option_id)
    {
        $option=  $this->db->query('SELECT * FROM '.DB_PREFIX.'`option`  Where option_id = '.(int) $option_id)->row;
        if(empty($option))
        {
            $this->db->query('INSERT INTO `'.DB_PREFIX."option` SET option_id='".(int) $option_id."', type = 'select' ");

        }

    }
    public function updateOrInsertOptionDesc($languages,$option_id,$option_name)
    {
        foreach ($languages as $key => $language) {
            $option_description =  $this->db->query('SELECT * FROM '.DB_PREFIX.'option_description  Where option_id = '.(int) $option_id.' and language_id = '.(int) $language['language_id'])->row;
            if(empty($option_description))
            {
                $this->db->query('INSERT INTO '.DB_PREFIX."option_description SET option_id = '".(int) $option_id."', language_id = '".(int) $language['language_id']."', name = '".$this->db->escape($option_name)."'");

            }
            else
            {
                $this->db->query('UPDATE '.DB_PREFIX."option_description SET name = '".$this->db->escape($option_name)."' WHERE option_id = ".(int) $option_id." and language_id = ".(int) $language['language_id']);
            }
        }
    }

    public function InsertOptionValueIfNotExist($option_id,$option_value_id)
    {
        $option=  $this->db->query('SELECT * FROM '.DB_PREFIX.'option_value  Where option_value_id= '.(int) $option_value_id .' and option_id = '.(int) $option_id)->row;
        if(empty($option))
        {
            $this->db->query('INSERT INTO `'.DB_PREFIX."option_value` SET option_value_id = '".(int) $option_value_id."', option_id='".(int) $option_id."'");

        }

    }

    public function updateOrInsertOptionValueDesc($languages,$option_value_id,$option_id,$option_value_name)
    {
        foreach ($languages as $key => $language) {
            $option_value_description =  $this->db->query('SELECT * FROM '.DB_PREFIX.'option_value_description  Where option_value_id = '.(int) $option_value_id.' and option_id = '.(int) $option_id.' and language_id = '.(int) $language['language_id'])->row;
            if(empty($option_value_description))
            {
                $this->db->query('INSERT INTO '.DB_PREFIX."option_value_description SET option_value_id = '".(int) $option_value_id."', option_id = '".(int) $option_id."', language_id = '".(int) $language['language_id']."', name = '".$this->db->escape($option_value_name)."'");

            }
            else
            {
                $this->db->query('UPDATE '.DB_PREFIX."option_value_description SET name = '".$this->db->escape($option_value_name)."' WHERE option_value_id = ".(int) $option_value_id." and option_id = ".(int) $option_id." and language_id = ".(int) $language['language_id']);
            }
        }

    }

    public function getLastKeyImage($product_id=470)
    {
        $sql = 'SELECT sort_order FROM '.DB_PREFIX."product_image p WHERE p.product_id = '".(int) $product_id."' ORDER BY sort_order DESC Limit 1";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result['sort_order']+1;
        } else {
            return false;
        }
    }
    public function linkOptionWithImage($product_id,$product_option_id,$product_option_value_id,$image_name)
    {
        $this->db->query("INSERT INTO ".DB_PREFIX."poip_option_image
        SET product_id = ".(int)$product_id."
          , product_option_id = ".(int)$product_option_id."
          , product_option_value_id = ".(int)$product_option_value_id."
          , image = '".$this->db->escape((string)$image_name)."'
          , sort_order = 0
          ");
    }

}
