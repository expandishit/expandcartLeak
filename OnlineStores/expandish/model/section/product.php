<?php
class ModelSectionProduct extends Model {
    
    protected $isCustomerAllowedToViewPrice;
	
    public function __construct($registry)
    {
		parent::__construct($registry);
		$this->isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
    }
    public function getFeaturedProducts($product_ids_csv, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $product_ids = explode(',', $product_ids_csv);
        $limit = count($product_ids);
        ###################get random products from DB if zeros######################
        if(count(array_unique($product_ids)) == 1 && $product_ids[0] == "0") {
            $query = $this->db->query("SELECT product_id FROM product WHERE status = '1' AND date_available <= NOW() ORDER BY RAND() LIMIT $limit");
            $random_product_ids = array_column($query->rows, 'product_id');

            $generated_product_ids = array();
            while(count($generated_product_ids) <= $limit && count($random_product_ids) > 0){
                $generated_product_ids = array_merge($generated_product_ids, $random_product_ids);
            }
            $product_ids = array_slice($generated_product_ids, 0, $limit);
        }
        #############################################################################
        $returned_products = $this->model_catalog_product->getProductsByIds($product_ids);

        //Get config once
        $show_seller = $this->config->get('show_seller');
        $show_attr   = $this->config->get('config_show_attribute');
        $show_opt_id   = $this->config->get('config_show_option');
        $config_language_id = $this->config->get('config_language_id');
        
        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');
        $is_custom_stock_status_colors = $this->config->get('config_custom_stock_status_colors');
        $this->load->model('setting/setting');
        foreach ($returned_products as $product_info) {
            //$product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info && $product_info['product_id'] != "") {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];
                $image_exists =  $this->model_tool_image->imageExist($images);
                $thump_swap = '';
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }

                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    if($product_info['price'] == -1){
                        $price = -1;
                    }else{

                        $price = $this->currency->format($product_info['tax_class_id'] ? $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')): $product_info['price']);
                    }
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0 && (int)$product_info['special'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $seller = false;
                // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                if(\Extension::isInstalled('multiseller') && $show_seller){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product_info['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
                }

                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$product_info['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");
                    
                    if($product_attribute->row){
                       $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];  
                   }
                }
                ///
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                if ($product_info['quantity'] <= 0) {
                    $stock = $product_info['stock_status'];
                    $stock_status  = $product_info['stock_status'];
                } elseif ($this->config->get('config_stock_display')) {
                    if($product_info['unlimited']){
                        $stock = $this->language->get('text_unlimited_quantity');
                        $stock_status = $this->language->get('text_unlimited_quantity');
                    }
                    else{
                        $stock = $product_info['quantity'];
                        $stock_status  = $product_info['quantity'];
                    }
                } else {
                    $stock = $this->language->get('text_instock');
                }
                if($is_custom_stock_status_colors){
                    $product_info['stock_status_color'] = $this->model_setting_setting->getStockStatus($product_info['stock_status_id'])['current_color'];
                }
                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock'             => $stock,
                    'stock_status'      => $stock_status,
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']) : '',
                    'attribute' => $show_attribute,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }
        return $products;
    }

    public function getBestSellerProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $products_info = $this->model_catalog_product->getBestSellerProducts($limit);
        $show_opt_id   = $this->config->get('config_show_option');

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //                $description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $image_exists =  $this->model_tool_image->imageExist($images);
                $thump_swap = '';
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }

                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];
                
                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }

        return $products;
    }

    public function getLatestProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();
        
        $defaultSortOrder = $this->config->get('config_products_default_sorting_column') ? $this->config->get('config_products_default_sorting_column') : 'date_added';

        $data = array(
            'sort'  => 'p.' . $defaultSortOrder,
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit
        );

        $products_info = $this->model_catalog_product->getProducts($data);
      
        //Get config once
        $show_seller = $this->config->get('show_seller');
        $show_attr   = $this->config->get('config_show_attribute');
        $show_opt_id   = $this->config->get('config_show_option');
        $config_language_id = $this->config->get('config_language_id');

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $image_exists =  $this->model_tool_image->imageExist($images);
                $thump_swap = '';
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $seller = false;
                // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                if(\Extension::isInstalled('multiseller') && $show_seller){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product_info['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
                }

                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . (int)$product_info['product_id'] . "' AND pa.attribute_id = '" . (int)$show_attr . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");
                    
                    if($product_attribute->row){
                       $show_attribute = $product_attribute->row['name'].': '.$product_attribute->row['text'];  
                   }
                }

                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                ///
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']) : '',
                    'attribute' => $show_attribute,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? '',
                    'thumb_path'        => $product_info['image'],
                );
            }
        }
        return $products;
    }

    public function getSpecialProducts($limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $data = array(
            'sort'  => 'pd.name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );

        $products_info = $this->model_catalog_product->getProductSpecials($data);
        $show_opt_id   = $this->config->get('config_show_option');

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                //Prize draw
                $consumed_percentage = 0;
                if($product_info['prize_draw']){
                    $consumed_percentage = $this->product_consumed_percentage($product_info['product_id'], $product_info['quantity']);

                    if($product_info['prize_draw']['image'])
                        $product_info['prize_draw']['image'] = $this->model_tool_image->resize($product_info['prize_draw']['image'], $thumb_width, $thumb_height);
                }

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }
               
                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $image_exists =  $this->model_tool_image->imageExist($images);
                $thump_swap = '';
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                ///
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap ,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'prize_draw'        => $product_info['prize_draw'],
                    'consumed_percentage' => $consumed_percentage,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }

        return $products;
    }

    public function getProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height, $get_cat_ids = false) {
        $data = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit,
            'filter_category_id' => $category_id
        );

        return $this->getProdutByCategoryBrand($data, $thumb_width, $thumb_height, $get_cat_ids)['products'];
    }

    public function getBestSellerProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $products_info = $this->model_catalog_product->getBestSellerProductsByCategoryId($category_id, $limit);

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');
        $show_attr   = $this->config->get('config_show_attribute');
        $show_opt_id   = $this->config->get('config_show_option');
        $config_language_id = $this->config->get('config_language_id');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $thump_swap = '';
                $image_exists =  $this->model_tool_image->imageExist($images);
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $seller = false;
                // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                if(\Extension::isInstalled('multiseller') && $this->config->get('show_seller')){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product_info['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
                }
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                ///
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $show_attribute = $this->product_attribute((int)$product_info['product_id'], $show_attr);
                }
                ///
                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']) : '',
                    'attribute' => $show_attribute,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }
        return $products;
    }

    public function getLatestProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $data = array(
            'sort'  => 'date_added',
            'order' => 'DESC'
        );
        
        $products_info = $this->model_catalog_product->getLatestProductsByCategoryId($category_id, $limit, $data);

        //Get config once
        $show_seller = $this->config->get('show_seller');
        $show_attr   = $this->config->get('config_show_attribute');
        $show_opt_id   = $this->config->get('config_show_option');
        $config_language_id = $this->config->get('config_language_id');

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $thump_swap = '';
                $image_exists =  $this->model_tool_image->imageExist($images);
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }  

                $seller = false;
                // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                if(\Extension::isInstalled('multiseller') && $show_seller){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product_info['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
                }

                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $show_attribute = $this->product_attribute((int)$product_info['product_id'], $show_attr);
                }
                ///
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }

                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']) : '',
                    'attribute' => $show_attribute,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }

        return $products;
    }

    public function getSpecialProductsByCategoryId($category_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $data = array(
            'sort'  => 'pd.name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );

        $products_info = $this->model_catalog_product->getProductSpecialsByCategoryId($category_id, $data);

        //Get config once
        $show_seller = $this->config->get('show_seller');
        $show_attr   = $this->config->get('config_show_attribute');
        $show_opt_id   = $this->config->get('config_show_option');
        $config_language_id = $this->config->get('config_language_id');

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $thump_swap = '';
                $image_exists =  $this->model_tool_image->imageExist($images);
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $seller = false;
                // $queryMultiseller = $this->db->query("SELECT extension_id FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");
                if(\Extension::isInstalled('multiseller') && $show_seller){
                    $seller_id = $this->MsLoader->MsProduct->getSellerId($product_info['product_id']);
                    $seller    = $this->MsLoader->MsSeller->getSeller($seller_id);
                }

                //Check show attribute status
                $show_attribute = false;
                if($show_attr){
                    $show_attribute = $this->product_attribute((int)$product_info['product_id'], $show_attr);
                }
                ///
                // get default option for home page
                $option_details = false;
                if($show_opt_id){
                   $option_details = $this->getProductOptionValues($product_info['product_id'],$show_opt_id);
                }
                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'has_seller' => $seller ? 1 : 0,
                    'seller_nickname' => $seller ? $seller['ms.nickname'] : '',
                    'seller_href' => $seller ? $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']) : '',
                    'attribute' => $show_attribute,
                    'option' => $option_details,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }
        return $products;
    }

    public function getPrizeProducts($prize_id, $limit, $thumb_width, $thumb_height) {
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $data = array(
            'sort'  => 'product_id',
            'order' => 'ASC',
            'start' => 0,
            'limit' => $limit
        );

        $products_info = $this->model_catalog_product->getPrizeProducts($prize_id, $limit, $data);

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');

        foreach ($products_info as $product_info) {
            if ($product_info) {
                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                //Prize draw
                $consumed_percentage = 0;
                if($product_info['prize_draw']){
                    $consumed_percentage = $this->product_consumed_percentage($product_info['product_id'], $product_info['quantity']);

                    if($product_info['prize_draw']['image'])
                        $product_info['prize_draw']['image'] = $this->model_tool_image->resize($product_info['prize_draw']['image'], $thumb_width, $thumb_height);
                }

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $thump_swap = '';
                $image_exists =  $this->model_tool_image->imageExist($images);
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        =>  $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'prize_draw'        => $product_info['prize_draw'],
                    'consumed_percentage' => $consumed_percentage,
                    'general_use'   => $product_info['general_use'] ?? ''
                );
            }
        }

        return $products;
    }
    //get product consumed in percentage
    private function product_consumed_percentage($product_id, $product_quantity){
        $this->load->model('module/prize_draw', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product_id);

        $consumed_percentage = ( ((int)$ordered_count * 100) / (int)$product_quantity ) ?? 0;

        return $consumed_percentage;
    }

    //get product attribute or all attributes
    private function product_attribute($product_id, $attribute){
        $config_language_id = $this->config->get('config_language_id');

        if($attribute == 'all'){
            $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . $product_id . "' AND pa.language_id = '" . (int)$config_language_id ."'");
            if($product_attribute->num_rows){
                return $product_attribute->rows;
            }
        }else{
            $product_attribute = $this->db->query("SELECT pa.text , ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id ='" . (int)$config_language_id ."')  WHERE pa.product_id = '" . $product_id . "' AND pa.attribute_id = '" . (int)$attribute . "' AND pa.language_id = '" . (int)$config_language_id ."' limit 1");
            if($product_attribute->row){
                return $product_attribute->row['name'].': '.$product_attribute->row['text'];
            }
        }

        return false;
    }

    public function getProductsByBrandId($brand_id, $limit, $thumb_width, $thumb_height) {

        $data = array(
            'sort'  => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit,
            'filter_manufacturer_id' => $brand_id,
            'get_products_categories' => 1
        );

        $result = $this->getProdutByCategoryBrand($data, $thumb_width, $thumb_height);

        return ['products' => $result['products'], 'categories' => $result['categories']];
    }

    private function getProdutByCategoryBrand($data, $thumb_width, $thumb_height, $get_cat_ids = false){
        $this->load->model('catalog/product', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
        $this->load->model('tool/image', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');

        $products = array();

        $products_info = $this->model_catalog_product->getProducts($data);

        if(isset($data['get_products_categories'])){
            $get_products_categories = true;
            $products_ids = [];
        }

        //Login Display Prices
        $config_customer_price = $this->config->get('config_customer_price');
        foreach ($products_info as $product_info) {
            if ($product_info) {

                //Collect product categories ids
                if($get_products_categories){
                    $products_ids[] = $product_info['product_id'];
                }

                //$description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);

                //Prize draw
                $consumed_percentage = 0;
                if($product_info['prize_draw']){
                    $consumed_percentage = $this->product_consumed_percentage($product_info['product_id'], $product_info['quantity']);

                    if($product_info['prize_draw']['image'])
                        $product_info['prize_draw']['image'] = $this->model_tool_image->resize($product_info['prize_draw']['image'], $thumb_width, $thumb_height);
                }

                if ($product_info['image']) {
                    $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);
                } else {
                    $image = false;
                }

                // $images = $this->model_catalog_product->getProductImages($product_info['product_id']);
                $images = $product_info['product_images'];

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    $images = $images[0]['image'];
                }
                $image_exists =  $this->model_tool_image->imageExist($images);
                $thump_swap = '';
                if($image_exists){
                    $thump_swap = $this->model_tool_image->resize($images, $thumb_width, $thumb_height);
                }
                $price = false;
                if ($this->isCustomerAllowedToViewPrice) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                $special = false;
                if ((float)$product_info['special'] && ($this->isCustomerAllowedToViewPrice)) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                }

                if ($this->config->get('config_review_status')) {
                    $rating = $product_info['rating'];
                } else {
                    $rating = false;
                }

                if ($product_info['price'] > 0) {
                    $savingAmount = round((($product_info['price'] - $product_info['special']) / $product_info['price']) * 100, 0);
                }
                else {
                    $savingAmount = 0;
                }

                $model = $product_info['manufacturer'];
                $model_url = $product_info['manufacturer_id'];

                if ($product_info['quantity'] <= 0) {
                    $stock = $product_info['stock_status'];
                } elseif ($this->config->get('config_stock_display')) {
                    $stock = $product_info['quantity'];
                } else {
                    $stock = $this->language->get('text_instock');
                }

                //List product categories ids
                $categories_ids = [];
                if($get_cat_ids)
                    $categories_ids = $this->model_catalog_product->getCategories($product_info['product_id']);

                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'             => $image,
                    'name'              => $product_info['name'],
                    'price'             => $price,
                    'special'           => $special,
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $product_info['meta_description'],
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $thump_swap,
                    'saving'            => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $stock,
                    'stock_status_id'   => $product_info['stock_status_id'],
                    'stock_status_color'=> $product_info['stock_status_color'],
                    'date_available'    => $product_info['date_available'],
                    'manufacturer_id'   => $product_info['manufacturer_id'],
                    'manufacturer'      => $product_info['manufacturer'],
                    'manufacturerimg'   => $product_info['manufacturerimg'],
                    'in_wishlist'       => $product_info['in_wishlist'],
                    'in_cart'           => $product_info['in_cart'],
                    'display_price'     => $this->display_price($product_info['product_id']),
                    'viewAddToCart'     => $this->checkStockAvailability($product_info),
                    'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
                    'prize_draw'        => $product_info['prize_draw'],
                    'consumed_percentage' => $consumed_percentage,
                    'subtract_stock'      => $this->config->get('product_default_subtract_stock'),
                    'general_use'   => $product_info['general_use'] ?? '',
                    'categories_ids' => $categories_ids
                );
            }
        }

        //Get categories of selected products
        if($get_products_categories && count($products_ids)){
            $this->load->model('section/category', false, DIR_ONLINESTORES . FRONT_FOLDER_NAME . '/');
            $categories = $this->model_section_category->getCategoryByProductIds($products_ids, $thumb_width, $thumb_height);
        }

        return ['products' => $products, 'categories' => $categories];
    }
    public function getProductOptionValues($product_id, $option_id) {
        $product_option_data = array();
        // check if the product has the default option included or not
        $option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE o.option_id='".$option_id."'  AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY po.sort_order,o.sort_order");
        $product_option = $option_query->row;
        // check if the product has the default option to be shown or not
        if(empty($product_option))
            return false;

        if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image' || $product_option['type'] == 'product') {
                $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY pov.sort_order");
            
            foreach ($product_option_value_query->rows as $product_option_value) {
                                

                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id'         => $product_option_value['option_value_id'],
                    'name'                    => $product_option_value['name'],
                    'image'                   => \Filesystem::getUrl('image/'.$product_option_value['image']),
                    //'quantity'                => $product_option_value['quantity'],
                    //'subtract'                => $product_option_value['subtract'],
                    //'price'                   => $product_option_value['price'],
                   // 'price_prefix'            => $product_option_value['price_prefix'],
                   // 'weight'                  => $product_option_value['weight'],
                   // 'weight_prefix'           => $product_option_value['weight_prefix'],
                   // 'valuable_id'             => $product_option_value['valuable_id']
                );
            }

            $product_option_data = array(
                'product_option_id' => $product_option['product_option_id'],
                'option_id'         => $product_option['option_id'],
                'name'              => $product_option['name'],
                'type'              => $product_option['type'],
                'option_values'      => $product_option_value_data,
                'custom_option'     => $product_option['custom_option'],
                'required'          => $product_option['required']
            );
        } else {
            $product_option_data = array(
                'product_option_id' => $product_option['product_option_id'],
                'option_id'         => $product_option['option_id'],
                'name'              => $product_option['name'],
                'type'              => $product_option['type'],
                'option_values'      => $product_option['option_value'],
                'custom_option'     => $product_option['custom_option'],
                'required'          => $product_option['required']
            );
        }
        return $product_option_data;
    }

    private function checkStockAvailability($product_info){
        $hideCartConfig = $this->config->get('config_hide_add_to_cart');
        if(($product_info['quantity'] <=0 && $hideCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart){
            return false;
        }
        else{
            return true;
        }
    }

    private function display_price($product_id){
        $this->load->model('catalog/product');
        $product_options = $this->model_catalog_product->getProductOptions($product_id);
        foreach ($product_options as $product_option) {
            if ($product_option['required']) {
                return false;
            }
        }
        return true;
    }
}
?>