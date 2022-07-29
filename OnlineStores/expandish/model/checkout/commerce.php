<?php

use ExpandCart\Foundation\String\Slugify;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ModelCheckoutCommerce extends Model
{

    public function addEbayProductGeneral($data)
    {
        // Checking for redundancy
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."commerce_ebay_product WHERE ebay_product_id = '".$this->db->escape($data['product_id'])."' ")->row;
        if ($result) {
            return false;
        }

        // Main product addition
        // $sql = "INSERT INTO ".DB_PREFIX."product SET model = '" . $this->db->escape($data['product_name']) . "', price = '" . $this->db->escape(preg_replace("/[^0-9.]/", "", trim($data['price'],'.') )) . "', quantity = '" . (int)$this->config->get('wk_dropship_ebay_quantity') . "', status = '" . $this->config->get('wk_ebay_dropship_direct_to_store') . "', date_added = NOW() ";

        if (!isset($data['quantity']) || (isset($data['quantity']) && $data['quantity'] == 0)) {
            $data['quantity'] = (int) $this->config->get('wk_dropship_ebay_quantity');
        }

        //check if usd is installed
        if($this->currency->has('USD')){
            $current_currency = $this->config->get('config_currency');
            if($current_currency != 'USD'){
                $data['price'] = $this->currency->convert($data['price'], 'USD', $current_currency);
            }
        }

        //check for profit
        $profit = (int) $this->config->get('wk_dropship_ebay_profit');
        $cost_price = $data['price'];
        if ($profit) {
            $profit_value = ($profit/100) * $data['price'];
            $data['price'] += $profit_value;
        }

        $sql = 'INSERT INTO '.DB_PREFIX."product SET model = '".$this->db->escape($data['product_model'])."', price = '".$this->db->escape($data['price'])."', cost_price = '".$this->db->escape($cost_price)."', quantity = '".(int) $data['quantity']."', status = '".$this->config->get('wk_ebay_dropship_direct_to_store')."', date_available = ".date('Y-m-d', time() - 86400)." ,date_added = NOW() ";

        $defaultWeight = 0;
        $defaultWeightClass = 1;
        if ($this->config->get('wk_dropship_ebay_default_weight')) {
            $defaultWeight = $this->config->get('wk_dropship_ebay_default_weight');
        }

        if (isset($data['default_weight']) && (float)$data['default_weight'] > 0) {
            $defaultWeight = $data['default_weight'];
        }
        $sql = $sql . ' ,weight="' . $defaultWeight . '"';

        if ($this->config->get('wk_dropship_ebay_default_weight_class')) {
            $defaultWeightClass = $this->config->get('wk_dropship_ebay_default_weight_class');
        }

        if (isset($data['default_weight_class']) && (float)$data['default_weight_class'] > 0) {
            $defaultWeightClass = $data['default_weight_class'];
        }
        $sql = $sql . ' ,weight_class_id="' . $defaultWeightClass . '"';

        $this->db->query($sql);
        $product_id = $this->db->getLastId();

        //get item description
        $ebay_item = $this->getEbayProductDetailsById($data['product_id']);
        $data['product_desc'] = isset($ebay_item['success']) ? $ebay_item['item']->description??$ebay_item['item']->shortDescription : '';

        //replace keyword code starts here
        if (!empty($this->config->get('wk_dropship_ebay_keyword'))) {
            foreach ($this->config->get('wk_dropship_ebay_keyword') as $key => $keyword) {
                $data['product_name'] = str_replace($keyword, '', $data['product_name']);
                $data['product_desc'] = str_replace($keyword, '', $data['product_desc']);
                $data['meta_title'] = str_replace($keyword, '', $data['meta_title']);
                $data['meta_description'] = str_replace($keyword, '', $data['meta_description']);
                $data['meta_keyword'] = str_replace($keyword, '', $data['meta_keyword']);
            }
        }

        //repalce keyword ends here
        //  // remove meta_title
        $eBayLangs = $this->config->get('wk_dropship_ebay_language');
        if (is_array($eBayLangs) == false) {
            $eBayLangs = [$eBayLangs];
        }


        foreach ($eBayLangs as $eBayLang) {
            $sql = 'INSERT INTO '.DB_PREFIX."product_description SET
                product_id = '".$product_id."',
                name = '".$this->db->escape($data['product_name'])."',
                slug = '".$this->db->escape((new Slugify)->slug($data['product_name']))."',
                description = '".$this->db->escape($data['product_desc'])."',
                meta_description = '".$this->db->escape($data['meta_description'])."',
                meta_keyword = '".$this->db->escape($data['meta_keyword'])."',
                language_id = '".(int) $eBayLang."' ";

            $this->db->query($sql);
        }

        // Adding to the store
        $sql = 'INSERT INTO '.DB_PREFIX."product_to_store SET product_id = '".$this->db->escape($product_id)."', store_id = '".(int) $this->config->get('wk_dropship_ebay_store')."' ";
        $this->db->query($sql);

        // Downloading image from ebay and saving locally
        $image = '';
        if (isset($data['imageUrl']) && $data['imageUrl']) {
            $image = $this->downloadImage($data['images'][0], $product_id, $product_id.$data['product_id']);
        }
        if (isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {

            foreach ($data['images'] as $key => $multi_image_url) {
                $this->downloadImages($multi_image_url, $key, $product_id, $product_id.$data['product_id'].'_'.$key);
            }
        }

        // Saving data for commerce usage
        $sql = 'INSERT INTO '.DB_PREFIX."commerce_ebay_product SET ebay_product_id = '".$this->db->escape($data['product_id'])."', product_id = '".(int) $this->db->escape($product_id)."', product_url = '".$this->db->escape($data['product_url'])."', name = '".$this->db->escape($data['product_name'])."' ";
        $this->db->query($sql);

        return $product_id;
    }

    public function addEbayProductOption($data)
    {
        $product_id = $data['product_id'];

        if (isset($data['customOption']) && is_array($data['customOption']) && $data['customOption']) {
            $product_details = $this->db->query('SELECT * FROM `'.DB_PREFIX."product` WHERE product_id = '".(int) $this->db->escape($product_id)."'")->row;
            $variation_price = $data['customOption']['price'] ?? 5;

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
                $quantity = $this->config->get('wk_dropship_ebay_quantity');
            }

            //check if usd is installed
            if($this->currency->has('USD')){
                $current_currency = $this->config->get('config_currency');
                if($current_currency != 'USD'){
                    $price = $this->currency->convert($price, 'USD', $current_currency);
                }
            }

            $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['value'])."', variation_name = '".$this->db->escape($data['customOption']['name'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."'");

            $variation_id = $this->db->getLastId();
            $combination_options = explode('_', trim($data['customOption']['value'], '_'));
            if ($combination_options) {
                foreach ($combination_options as $key => $combination_option) {
                    $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_variation_option SET variation_id = '".(int) $variation_id."', option_value_id = '".(int) $combination_option."', product_id = '".(int) $product_id."' ");
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
                // Checking option exist or not on the basis of ebay option details
                $isExist = $this->ebayOptionExist($option);
                if (!$isExist) {
                    // Adding option to opencart core option
                    $option_id = $this->createCoreOption($option, $languages);
                } else {
                    $option_id = $isExist['oc_option_id'];
                }
                // setting option on the product
                $product_option_id = $this->setOptionOnProduct($option_id, $product_id);
                if (isset($option['option_values']) && $option['option_values']) {
                    foreach ($option['option_values'] as $key2 => $option_value) {
                        // Checking option value exist or not on the basis of ebay option and option value details
                        $isValueExist = $this->ebayOptionValueExist($option, $option_value);
                        if (!$isValueExist || !$this->checkOcOptionValueExist($isValueExist['oc_option_value_id'])) {
                            // Adding option value to opencart core option added above
                            $option_value_id = $this->createCoreOptionValue($option_id, $option, $option_value, $languages);
                        } else {
                            $option_value_id = $isValueExist['oc_option_value_id'];
                        }
                        // setting option value on the product
                        $product_option_value_id = $this->setOptionValueOnProduct($option_id, $option_value_id, $product_id, $product_option_id);
                    }
                }
            }
        }

        return true;
    }

    public function ebayOptionExist($option)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."commerce_ebay_product_option WHERE ebay_option_id = '".(int) $option['option_id']."' && value = '".$this->db->escape($option['option_name'])."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function ebayOptionValueExist($option, $option_value)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."commerce_ebay_product_option_value WHERE ebay_option_id = '".(int) $option['option_id']."' && ebay_option_value_id = '".(int) $option_value['option_value_id']."' && value = '".$this->db->escape($option_value['option_value_name'])."' ";
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

        $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_option SET oc_option_id = '".(int) $option_id."', ebay_option_id = '".(int) $option['option_id']."' ,value = '".$this->db->escape($option['option_name'])."' ");

        foreach ($languages as $key => $language) {
            $this->db->query('INSERT INTO '.DB_PREFIX."option_description SET option_id = '".(int) $option_id."', language_id = '".(int) $language['language_id']."', name = '".$this->db->escape($option['option_name'])."'  ");
        }

        return $option_id;
    }

    public function createCoreOptionValue($option_id, $option, $option_value, $languages)
    {
        $this->db->query('INSERT INTO '.DB_PREFIX."option_value SET option_id = '".(int) $option_id."' ");
        $option_value_id = $this->db->getLastId();

        $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_option_value SET oc_option_id = '".(int) $option_id."', ebay_option_id = '".(int) $option['option_id']."', oc_option_value_id = '".(int) $option_value_id."', ebay_option_value_id = '".(int) $option_value['option_value_id']."', value = '".$this->db->escape($option_value['option_value_name'])."' ");

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
        $this->db->query('INSERT INTO '.DB_PREFIX."product_option_value SET product_option_id = '".(int) $product_option_id."', product_id = '".(int) $product_id."', option_id = '".(int) $option_id."', option_value_id = '".(int) $option_value_id."', quantity = '".(int) $this->config->get('wk_dropship_ebay_quantity')."', subtract = '0', price = '0', price_prefix = '+', points = '0', points_prefix = '+', weight = '0', weight_prefix = '+'  ");

        return $this->db->getlastId();
    }

    public function addEbayProductAttribute($data, $update = false)
    {
        $eBayLangs = $this->config->get('wk_dropship_ebay_language');
        if (is_array($eBayLangs) == false) {
            $eBayLangs = [$eBayLangs];
        }
        $product_id = $data['product_id'];
        // Create attribute and adding attribute value to the product
        if (isset($data['customAttribute']) && $data['customAttribute'] && $this->config->get('wk_dropship_ebay_attribute_group_id')) {
            foreach ($data['customAttribute'] as $key => $attrDetails) {
                $checkEbayAttrExist = $this->db->query('SELECT * FROM `'.DB_PREFIX."attribute_description`
                    WHERE name = '".$this->db->escape($attrDetails['attrName'])."'
                    AND language_id IN (".implode(',', $eBayLangs).")")->row;

                if (!$checkEbayAttrExist) {
                    $this->db->query('INSERT INTO '.DB_PREFIX."attribute SET attribute_group_id = '".(int) $this->config->get('wk_dropship_ebay_attribute_group_id')."', sort_order = '".(int) $this->db->escape($key)."'");

                    $attribute_id = $this->db->getLastId();

                    foreach ($eBayLangs as $eBayLang) {

                        $this->db->query('INSERT INTO '.DB_PREFIX."attribute_description SET
                            attribute_id = '".(int) $this->db->escape($attribute_id)."',
                            language_id = '".(int) $eBayLang."',
                            name = '".$this->db->escape($attrDetails['attrName'])."'");
                    }
                } else {
                    $attribute_id = $checkEbayAttrExist['attribute_id'];
                }

                $checkEbayAttrProductExist = $this->db->query('SELECT * FROM `'.DB_PREFIX."product_attribute`
                    WHERE product_id = '".(int) $this->db->escape($product_id)."' AND
                    attribute_id = '".(int) $this->db->escape($attribute_id)."' AND
                    language_id IN ('".implode(',', $eBayLangs)."')")->row;

                if (!$checkEbayAttrProductExist) {
                    foreach ($eBayLangs as $eBayLang) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."product_attribute SET
                            product_id = '".(int) $this->db->escape($product_id)."',
                            attribute_id = '".(int) $this->db->escape($attribute_id)."',
                            language_id = '".(int) $eBayLang."',
                            text = '".$this->db->escape($attrDetails['attrValue'])."'");
                    }
                } else {
                    if ($update) {
                        foreach ($eBayLangs as $eBayLang) {
                            $this->db->query('UPDATE '.DB_PREFIX."product_attribute SET
                                text = '".$this->db->escape($attrDetails['attrValue'])."' WHERE
                                product_id = '".(int) $this->db->escape($product_id)."' AND
                                attribute_id = '".(int) $this->db->escape($attribute_id)."' AND
                                language_id = '".(int) $eBayLang."'");
                        }
                    }
                }
            }
        }

        return true;
    }

    public function addEbayProductCommerce($data)
    {
        return true;
    }

    private function createImagesDirectory()
    {
        if (!\Filesystem::isDirExists('image/ebay/catalog')) {
            \Filesystem::createDir('image/ebay/catalog');
            \Filesystem::setPath('image/ebay/catalog')->changeMod('writable');
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
        $image_name = 'ebay/catalog/'.$image_name.'.jpg';

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
        $image_name = 'ebay/catalog/'.$image_name.'.jpg';

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

    //Update code starts here
    public function updateProductGeneralInfo($data)
    {
        $query = $this->db->query('SELECT product_id FROM '.DB_PREFIX."commerce_ebay_product WHERE ebay_product_id='".$this->db->escape($data['product_id'])."'")->row;

        $product_id = $query['product_id'];

        if (!isset($data['quantity']) || (isset($data['quantity']) && $data['quantity'] == 0)) {
            $data['quantity'] = (int) $this->config->get('wk_dropship_ebay_quantity');
        }

        //check if usd is installed
        if($this->currency->has('USD')){
            $current_currency = $this->config->get('config_currency');
            if($current_currency != 'USD'){
                $data['price'] = $this->currency->convert($data['price'], 'USD', $current_currency);
            }
        }

        //check for profit
        $profit = (int) $this->config->get('wk_dropship_ebay_profit');
        $cost_price = $data['price'];
        if ($profit) {
            $profit_value = ($profit/100) * $data['price'];
            $data['price'] += $profit_value;
        }

        $this->db->query('UPDATE '.DB_PREFIX."commerce_ebay_product SET  name='".$this->db->escape($data['product_name'])."' WHERE product_id='".(int) $product_id."' AND ebay_product_id='".$this->db->escape($data['product_id'])."'");

        $this->db->query('UPDATE '.DB_PREFIX."product SET model = '".$this->db->escape($data['product_model'])."', price = '".$this->db->escape($data['price'])."', cost_price = '".$this->db->escape($cost_price)."', quantity = '".(int) $data['quantity']."', status = '".$this->config->get('wk_ebay_dropship_direct_to_store')."' WHERE product_id='".(int) $product_id."'");

        //get item description
        $ebay_item = $this->getEbayProductDetailsById($data['product_id']);
        $data['product_desc'] = isset($ebay_item['success']) ? $ebay_item['item']->description??$ebay_item['item']->shortDescription : '';

        //replace keyword code starts here
        if (!empty($this->config->get('wk_dropship_ebay_keyword'))) {
            foreach ($this->config->get('wk_dropship_ebay_keyword') as $key => $keyword) {
                $data['product_name'] = str_replace($keyword, '', $data['product_name']);
                $data['product_desc'] = str_replace($keyword, '', $data['product_desc']);
                $data['meta_title'] = str_replace($keyword, '', $data['meta_title']);
                $data['meta_description'] = str_replace($keyword, '', $data['meta_description']);
                $data['meta_keyword'] = str_replace($keyword, '', $data['meta_keyword']);
            }
        }

        //repalce keyword ends here

        /*$this->db->query('UPDATE '.DB_PREFIX."product_description SET name ='".$this->db->escape($data['product_name'])."', description = '".$this->db->escape($response_desc)."',meta_title = '".$this->db->escape($data['meta_title'])."', meta_description = '".$this->db->escape($data['meta_description'])."',  meta_keyword = '".$this->db->escape($data['meta_keyword'])."' WHERE language_id = '".(int) $this->config->get('wk_dropship_ebay_language')."' AND product_id='".(int) $product_id."'");*/
        // remove meta_title

        $eBayLangs = $this->config->get('wk_dropship_ebay_language');
        if (is_array($eBayLangs) == false) {
            $eBayLangs = [$eBayLangs];
        }

        foreach ($eBayLangs as $eBayLang) {
            $this->db->query('UPDATE '.DB_PREFIX."product_description SET name ='".$this->db->escape($data['product_name'])."', description = '".$this->db->escape($data['product_desc'])."', meta_description = '".$this->db->escape($data['meta_description'])."',  meta_keyword = '".$this->db->escape($data['meta_keyword'])."' WHERE language_id = '".(int) $eBayLang."' AND product_id='".(int) $product_id."'");
        }

        // Downloading image from ebay and saving locally
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

            $this->load->model('localisation/language');
            $languages = $this->model_localisation_language->getLanguages();
            foreach ($options as $key => $option) {
                // Checking option exist or not on the basis of ebay option details
                $isExist = $this->ebayOptionExist($option);
                if (!$isExist) {
                    // Adding option to opencart core option
                    $option_id = $this->createCoreOption($option, $languages);
                } else {
                    $option_id = $isExist['oc_option_id'];
                }
                // setting option on the product
                $product_option_id = $this->setOptionOnProduct($option_id, $product_id);
                if (isset($option['option_values']) && $option['option_values']) {
                    foreach($option['option_values'] as $value_key => $option_value) {
                        // Checking option value exist or not on the basis of ebay option and option value details
                        $isValueExist = $this->ebayOptionValueExist($option, $option_value);
                        if (!$isValueExist) {
                            // Adding option value to opencart core option added above
                            $option_value_id = $this->createCoreOptionValue($option_id, $option, $option_value, $languages);
                        } else {
                            $option_value_id = $isValueExist['oc_option_value_id'];
                        }
                        // setting option value on the product
                        $product_option_value_id = $this->setOptionValueOnProduct($option_id, $option_value_id, $product_id, $product_option_id);
                    }
                }
            }
        }

        return true;
    }

    public function UpdateEbayProductOption($data)
    {
        $product_id = $data['product_id'];

        if (isset($data['customOption']) && is_array($data['customOption']) && $data['customOption']) {
            $product_details = $this->db->query('SELECT * FROM `'.DB_PREFIX."product` WHERE product_id = '".(int) $this->db->escape($product_id)."'")->row;
            $variation_price = $data['customOption']['price'];

            if ($product_details['price'] < $variation_price) {
                $price = $variation_price - $product_details['price'];
                $price_prefix = '+';
            } else {
                $price = $product_details['price'] - $variation_price;
                $price_prefix = '-';
            }

            //check if usd is installed
            if($this->currency->has('USD')){
                $current_currency = $this->config->get('config_currency');
                if($current_currency != 'USD'){
                    $price = $this->currency->convert($price, 'USD', $current_currency);
                }
            }

            if (isset($data['customOption']['quantity'])) {
                $quantity = $data['customOption']['quantity'];
            } else {
                $quantity = $this->config->get('wk_dropship_ebay_quantity');
            }

            $check_ebay_product_variation = $this->db->query('SELECT id FROM '.DB_PREFIX."commerce_ebay_product_variation WHERE product_id ='".(int) $product_id."' AND variation_text = '".$this->db->escape($data['customOption']['comb'])."' AND variation_name = '".$this->db->escape($data['customOption']['text'])."'")->row;

            if ($check_ebay_product_variation && (isset($check_ebay_product_variation['id']) && $check_ebay_product_variation['id'])) {
                $this->db->query('UPDATE '.DB_PREFIX."commerce_ebay_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['comb'])."', variation_name = '".$this->db->escape($data['customOption']['text'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."' WHERE id ='".(int) $check_ebay_product_variation['id']."'");
            } else {
                $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_variation SET product_id = '".(int) $product_id."', variation_text = '".$this->db->escape($data['customOption']['comb'])."', variation_name = '".$this->db->escape($data['customOption']['text'])."', price = '".(float) $price."', price_prefix = '".$this->db->escape($price_prefix)."',quantity='".(int) $quantity."'");

                $variation_id = $this->db->getLastId();
                $combination_options = explode('_', trim($data['customOption']['comb'], '_'));
                if ($combination_options) {
                    foreach ($combination_options as $key => $combination_option) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."commerce_ebay_product_variation_option SET variation_id = '".(int) $variation_id."', option_value_id = '".(int) $combination_option."', product_id = '".(int) $product_id."' ");
                    }
                }
            }
        }

        return true;
    }


    public function addOptionsToCorecheckOcOptionValueExist($oc_option_value_id)
    {
        return $this->db->query('SELECT * FROM `'.DB_PREFIX.'option_value` WHERE `option_value_id` = '.(int) $oc_option_value_id.'')->row;
    }

    /**
     * check if product is ebay product
     *
     *
     * @return boolean
     */
    public function checkEbayProductExists($product_id)
    {
        $sql = 'SELECT product_id FROM '.DB_PREFIX.'commerce_ebay_product WHERE product_id = '.(int) $product_id.' ';
        $result = $this->db->query($sql)->row;
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getEbayOptionPrice($options, $product_id, $single)
    {
        if ($single) {
            $sql = 'SELECT DISTINCT(variation_id) FROM '.DB_PREFIX.'commerce_ebay_product_variation_option WHERE option_value_id IN ('.$this->db->escape($options).") AND product_id = '".(int) $product_id."' GROUP BY variation_id ";
        } else {
            $sql = 'SELECT DISTINCT(variation_id) FROM '.DB_PREFIX.'commerce_ebay_product_variation_option WHERE option_value_id IN ('.$this->db->escape($options).") AND product_id = '".(int) $product_id."' GROUP BY variation_id HAVING COUNT(variation_id) > 1 ";
        }
        $result = $this->db->query($sql)->row;
        if ($result) {
            $options = implode('_', explode(',', $options));
            $result = $this->db->query('SELECT * FROM '.DB_PREFIX."commerce_ebay_product_variation WHERE product_id = '".(int) $product_id."' AND quantity > '0' AND variation_text='".$this->db->escape($options)."'")->row;
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getEbayOptionByProductOption($data)
    {
        $sql = 'SELECT pov.product_id,waov.ebay_option_value_id FROM '.DB_PREFIX.'product_option_value pov LEFT JOIN '.DB_PREFIX."commerce_ebay_product_option_value waov ON (waov.oc_option_id = pov.option_id && waov.oc_option_value_id = pov.option_value_id) WHERE pov.product_option_value_id = '".(int) $data['product_option_value_id']."' && pov.product_option_id = '".(int) $data['product_option_id']."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return $result;
        }
    }

    /************* Get Ebay credentials *************/
    function getEbayCredentials(){

        return $this->db->query('SELECT `key` ,`value` FROM `'.DB_PREFIX.'setting` WHERE `key` = "wk_dropship_ebay_client_id" OR `key` = "wk_dropship_ebay_client_secret"')->rows;
    }

    /************* Get Ebay Auth Token *************/
    function getEbayAccessToken($client_id,$client_secret){

        try{

            $url        = "https://api.ebay.com/identity/v1/oauth2/token";
            $body       = ["grant_type"=>"client_credentials","scope"=>"https://api.ebay.com/oauth/api_scope"];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
            $response       = curl_exec($ch);
            $http_status    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body           = json_decode(substr($response, $headerSize));
            curl_close($ch);

            if($http_status != 200)
                return null;

            return $body;
        } catch (\Exception $e) {}

//        var_dump( $response);
//        die();
        return $response;
    }

    /************* Get EbayProduct Details *************/
    function getEbayProductDetailsById($item_id){

        //get credentials
        $key    = "";
        $secret = "";
        $credential = array_column($this->getEbayCredentials(), 'value', 'key');

        if(isset($credential['wk_dropship_ebay_client_id']))
            $key = $credential['wk_dropship_ebay_client_id'];

        if(isset($credential['wk_dropship_ebay_client_secret']))
            $secret = $credential['wk_dropship_ebay_client_secret'];

        if(!$key || !$secret)
            return ['error'=>true, 'msg'=>'please check your ebay configuration'];

        //get token
        $token_response = $this->getEbayAccessToken($key, $secret);
        if(!$token_response)
            return ['error'=>true, 'msg'=>'please check your ebay configuration'];


        //get product details
//        $url        = "https://api.ebay.com/buy/browse/v1/item/v1%7C".$item_id."%7C0";
        $url        = "https://api.ebay.com/buy/browse/v1/item/get_item_by_legacy_id?legacy_item_id=".$item_id;
        $headers = array(
            "Accept: application/json",
            "Authorization: Bearer {$token_response->access_token}",
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response       = curl_exec($ch);
        $http_status    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body           = json_decode(substr($response, $headerSize));
        curl_close($ch);

        if($http_status != 200){
            //get item by group
            $url        = "https://api.ebay.com/buy/browse/v1/item/get_items_by_item_group?item_group_id=".$item_id;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response       = curl_exec($ch);
            $http_status    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body           = json_decode(substr($response, $headerSize));
            curl_close($ch);
            if($http_status != 200)
                return ['error'=>true, 'msg'=>'please check your ebay configuration'];

            $item = $body->items[0];
            $item->item_options = $this->setEbayItemMultiOption($body->items);
            $item->meta_tags = $this->getEbayItemMetaData($item->itemWebUrl);
            $item->additionalImages = $this->setEbayItemAdditionalImages($body->items, $item->image->imageUrl);

            return ['success'=>true,'item'=>$item];

        }
        $body->item_options = $this->setEbayItemSingleOption($body);
        $body->meta_tags = $this->getEbayItemMetaData($body->itemWebUrl);
        return ['success'=>true,'item'=>$body];

    }

    /************* Set item single option *************/
    function setEbayItemSingleOption($item){

        //set price depend on user configuration
        $price_type         = $this->config->get('wk_dropship_ebay_price_type');
        $price              = $item->price->value;
        if($price_type == 'max'){
            if($item->marketingPrice){
                $price = $item->marketingPrice->originalPrice->value;
            }
        }
        $options            = array();
        $option_value_id    = 1;
        $selected_options   = [];

        //check for color
        if($item->color && !in_array($item->color,$selected_options)){
            $option                 = new stdClass;
            $option->option_id      = 27;
            $option->option_name    = "color";
            $option->option_values  = array();

            $option_value       = new stdClass;
            $option_value->id   = $option_value_id++;
            $option_value->name = $item->color;
            $option_value->price = $price;

            $option->option_values[] = $option_value;
            $selected_options[]=$item->color;
            $options []= $option;
        }

        //check for size
        if($item->size && !in_array($item->size,$selected_options)){
            $option                 = new stdClass;
            $option->option_id      = 29;
            $option->option_name    = "size";
            $option->option_values  = array();

            $option_value           = new stdClass;
            $option_value->id       = $option_value_id++;
            $option_value->name     = $item->size;
            $option_value->price    = $price;

            $option->option_values[] = $option_value;
            $selected_options[]=$item->size;
            $options []= $option;
        }

        return $options;
    }


    /************* Set item multi options *************/
    function setEbayItemMultiOption($items){


        $options            = array();
        $option_value_id    = 1;
        $selected_options   = [];
        $color_options      = [];
        $size_options       = [];

        //set price depend on user configuration
        $price_type         = $this->config->get('wk_dropship_ebay_price_type');


        foreach($items as $item) {

            //set price
            $price = $item->price->value;
            if ($price_type == 'max') {
                if ($item->marketingPrice) {
                    $price = $item->marketingPrice->originalPrice->value;
                }
            }

            //check for color
            if ($item->color && !in_array($item->color, $selected_options)) {

                $option_value           = new stdClass;
                $option_value->id       = $option_value_id++;
                $option_value->name     = $item->color;
                $option_value->price    = $price;

                $color_options[]        = $option_value;
                $selected_options[]     = $item->color;
            }

            //check for size
            if ($item->size && !in_array($item->size, $selected_options)) {
                $option_value           = new stdClass;
                $option_value->id       = $option_value_id++;
                $option_value->name     = $item->size;
                $option_value->price    = $price;

                $size_options[]         = $option_value;
                $selected_options[]     = $item->size;
            }
        }

        if(count($color_options)){
            $option                 = new stdClass;
            $option->option_id      = 27;
            $option->option_name    = "color";
            $option->option_values  = $color_options;

            $options []= $option;
        }


        if(count($size_options)){
            $option                 = new stdClass;
            $option->option_id      = 29;
            $option->option_name    = "size";
            $option->option_values  = $size_options;

            $options []= $option;
        }
        return $options;
    }

    /************* Set item multi options *************/
    function setEbayItemAdditionalImages($items, $base_image){

        $unique_images = [$base_image];
        $images            = array();
        foreach($items as $item) {
            if(!in_array($item->image->imageUrl, $unique_images)) {
                $new_image = new stdClass;
                $new_image->imageUrl = $item->image->imageUrl;
                $images[] = $new_image;
                $unique_images[]=$item->image->imageUrl;
            }
            foreach($item->additionalImages ?? [] as $im){
                if(!in_array($im->imageUrl, $unique_images)) {
                    $new_additional_image = new stdClass;
                    $new_additional_image->imageUrl = $im->imageUrl;
                    $images[] = $new_additional_image;
                    $unique_images[]=$im->imageUrl;
                }
            }
        }
        return $images;
    }

    /************* Get item meta data *************/
    function getEbayItemMetaData($url){

        $tags= get_meta_tags($url);
        return [
            'description'   => isset($tags['description']) ? $tags['description'] : '',
            'title'         => isset($tags['twitter:title']) ? $tags['twitter:title'] : '',
            'tags'          => $tags,
        ];
    }



}
