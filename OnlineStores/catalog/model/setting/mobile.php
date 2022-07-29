<?php
class ModelSettingMobile extends Model {
    public function GetLanguages()
    {
        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."language WHERE status = '1'");
        $languages = array();

        foreach ($query->rows as $result)
        {
            $languages[$result['code']] = $result;
        }
        return $languages;
    }

    public function getPageSections($pageCodeName,$with_product_options=false, $categories_options = []) {
        $data = array();
        $data['PageCodeName'] = $pageCodeName;
        $exclude_disabled_products = false;
        if ($pageCodeName == 'home')
            $exclude_disabled_products = true;
        $data['Sections'] = array();
        $query = $this->db->query("SELECT ecsection.id SectionId,
                                    ecsection.CodeName SectionCodeName,
                                    ecsection.IsCollection
                                    FROM ecsection
                                    JOIN ecregion ON ecsection.RegionId = ecregion.id
                                    JOIN ecpage ON ecregion.PageId = ecpage.id
                                    JOIN ectemplate ON ecpage.TemplateId = ectemplate.id
                                    WHERE ectemplate.CodeName = 'mobile-app'
                                    AND ecpage.CodeName = '$pageCodeName'
                                    AND ecregion.CodeName = 'content'
                                    AND ecsection.Type = 'live'
                                    ORDER BY ecsection.SortOrder ASC");
        $sections = $query->rows;
        foreach ($sections as $section) {
            $objSection = array();
            //$objSection['id'] = $section['SectionId'];
            $objSection['SectionCodeName'] = $section['SectionCodeName'];
            //$objSection['Fields'] = array();

            //$objSection['Collections']['Fields'] = array();
            //$sectionFields = array();
            //$sectionCollections = array();
            $query = $this->db->query("SELECT ecobjectfield.id FieldId,
                                        ecobjectfield.CodeName FieldCodeName,
                                        ecobjectfield.Type FieldType,
                                        ecobjectfieldval.`Value` FieldValue
                                        FROM ecobjectfield
                                        JOIN ecobjectfieldval ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                        WHERE ecobjectfield.ObjectId = '" . $section['SectionId'] . "'
                                        AND ecobjectfield.ObjectType = 'ECSECTION'
                                        AND ecobjectfieldval.Lang='" . $this->config->get('config_language') . "'");
            foreach($query->rows as $field) {
                if($field['FieldType'] == "image") {
                    $objSection[$field['FieldCodeName']] = \Filesystem::getUrl('image/' .str_replace(' ', '%20',$field['FieldValue']));
                } elseif ($field['FieldType'] == "tags-product") {
                    $width = $height = 200;
                    // do not resize images in case Home Page
                    if ($pageCodeName == 'home')
                        $width=$height="0";
                    $objSection[$field['FieldCodeName']] = $this->getFeaturedProducts($field['FieldValue'] , $width ,$height , $exclude_disabled_products,$with_product_options);
                } elseif ($field['FieldType'] == "tags-category") {
                    $objSection[$field['FieldCodeName']] = $this->getFeaturedCategories($field['FieldValue']);
                } elseif($field['FieldType'] == "product-or-category") {
                    if(strpos($field['FieldValue'], "product:") === 0) {
                        $linkType = "product";
                        $linkId =
                            $this->getFeaturedProducts(str_replace("product:", "", $field['FieldValue']),
                                200,
                                200,
                                $exclude_disabled_products,$with_product_options);
                        $linkId = $linkId[0]["product_id"];
                    } elseif(strpos($field['FieldValue'], "category:") === 0) {
                        $linkType = "category";
                        $linkId = $this->getFeaturedCategories(str_replace("category:", "", $field['FieldValue']));
                        $linkId = $linkId[0]["category_id"];
                    }elseif(strpos($field['FieldValue'], "link:") === 0) {
                        $linkType = "link";
                        $linkId = "1";
                    } else {
                        $linkType = "";
                        $linkId = "";
                    }

                    $objSection[$field['FieldCodeName'] . "Type"] = $linkType;
                    $objSection[$field['FieldCodeName'] . "Id"] = $linkId;
                } elseif ($field['FieldType'] == "page") {
                    if($field['FieldValue'] != "" && $field['FieldValue'] > 0) {
                        $this->load->model('catalog/information');
                        $information_info = $this->model_catalog_information->getInformation($field['FieldValue']);
                        $objSection["infopageId"] = $field['FieldValue'];
                        $objSection[$field['FieldCodeName']] = $information_info['title'];
                    } else {
                        $objSection["infopageId"] = 0;
                        $objSection[$field['FieldCodeName']] = "";
                    }
                } else {
                    $objSection[$field['FieldCodeName']] = strip_tags(htmlspecialchars_decode($field['FieldValue']));
                }
//                $objField = array();
//                $objField['id'] = $field['FieldId'];
//                $objField['CodeName'] = $field['FieldCodeName'];
//                $objField['Type'] = $field['FieldType'];
//                $objField['Value'] = $field['FieldType'] != "image" ? $field['FieldValue'] : HTTP_IMAGE . $field['FieldValue'];
//                $objField['Products'] = $field['FieldType'] == "tags-product" ? $this->getFeaturedProducts($field['FieldValue']) : array();
//                $objField['Categories'] = $field['FieldType'] == "tags-category" ? $this->getFeaturedCategories($field['FieldValue']) : array();
//                //TODO: add categories and products to the field
//                $objSection['Fields'][] = $objField;
                if (isset($objSection['title']) && !empty($objSection['title']))
                    $objSection['title'] = strip_tags(htmlspecialchars_decode($objSection['title']));

            }

            if($section['IsCollection'] == 1) {
                $objSection['Collections'] = array();

                $query = $this->db->query("SELECT eccollection.id CollectionId
                                        FROM eccollection
                                        WHERE eccollection.SectionId = '" . $section['SectionId'] . "'
                                        AND eccollection.IsDefault = 0 ORDER BY SortOrder");
                $Collections = $query->rows;
                foreach ($Collections as $collection) {
                    $objCollection = array();
                    //$objCollection['id'] = $collection['CollectionId'];
                    //$objCollection['Fields'] = array();

                    $query = $this->db->query("SELECT ecobjectfield.id FieldId,
                                        ecobjectfield.CodeName FieldCodeName,
                                        ecobjectfield.Type FieldType,
                                        ecobjectfieldval.`Value` FieldValue
                                        FROM ecobjectfield
                                        JOIN ecobjectfieldval ON ecobjectfield.id = ecobjectfieldval.ObjectFieldId
                                        WHERE ecobjectfield.ObjectId = '" . $collection['CollectionId'] . "'
                                        AND ecobjectfield.ObjectType = 'ECCOLLECTION'
                                        AND ecobjectfieldval.Lang='" . $this->config->get('config_language') . "'");
                    foreach ($query->rows as $field) {

                        if ($field['FieldType'] == "image") {
                           $objCollection[$field['FieldCodeName']] = \Filesystem::getUrl('image/' .str_replace(' ', '%20',$field['FieldValue']));
                        } elseif ($field['FieldType'] == "tags-product") {
                            $objCollection[$field['FieldCodeName']] =
                                $this->getFeaturedProducts($field['FieldValue'] ,
                                    200,
                                    200,
                                    $exclude_disabled_products,$with_product_options);
                        } elseif ($field['FieldType'] == "tags-category") {
                            if($pageCodeName != "categories") {
                                $objCollection[$field['FieldCodeName']] = $this->getFeaturedCategories($field['FieldValue']);
                            } else {
                                $category = $this->getFeaturedCategories($field['FieldValue'], 200, 200, $categories_options);
                                $objCollection["category_id"] = $category[0]['category_id'];
                                $objCollection["seller_id"] = $category[0]['seller_id'];
                                $objCollection["products"] = $category[0]['products'];
                            }
                        } elseif($field['FieldType'] == "product-or-category") {
                            if(strpos($field['FieldValue'], "product:") === 0) {
                                $linkType = "product";
                                $linkId =
                                    $this->getFeaturedProducts(str_replace("product:", "", $field['FieldValue']),
                                        200,
                                        200,
                                        $exclude_disabled_products,$with_product_options);
                                $linkId = $linkId[0]["product_id"];
                            } elseif(strpos($field['FieldValue'], "category:") === 0) {
                                $linkType = "category";
                                $linkId = $this->getFeaturedCategories(str_replace("category:", "", $field['FieldValue']));
                                $linkId = $linkId[0]["category_id"];
                            }elseif(strpos($field['FieldValue'], "link:") === 0) {
                                $linkType = "link";
                                $linkId = "1";
                            }else {
                                $linkType = "";
                                $linkId = "";
                            }

                            $objCollection[$field['FieldCodeName'] . "Type"] = $linkType;
                            $objCollection[$field['FieldCodeName'] . "Id"] = $linkId;
                        } elseif ($field['FieldType'] == "page") {
                            if($field['FieldValue'] != "" && $field['FieldValue'] > 0) {
                                $this->load->model('catalog/information');
                                $information_info = $this->model_catalog_information->getInformation($field['FieldValue']);
                                $objSection["infopageId"] = $field['FieldValue'];
                                $objSection[$field['FieldCodeName']] = $information_info['title'];
                            } else {
                                $objSection["infopageId"] = 0;
                                $objSection[$field['FieldCodeName']] = "";
                            }
                        } else {
                            $objCollection[$field['FieldCodeName']] = strip_tags(htmlspecialchars_decode($field['FieldValue']));
                        }
//                    $objField = array();
//                    $objField['id'] = $field['FieldId'];
//                    $objField['CodeName'] = $field['FieldCodeName'];
//                    $objField['Type'] = $field['FieldType'];
//                    $objField['Value'] = $field['FieldType'] != "image" ? $field['FieldValue'] : HTTP_IMAGE . $field['FieldValue'];
//                    $objField['Products'] = $field['FieldType'] == "tags-product" ? $this->getFeaturedProducts($field['FieldValue']) : array();
//                    $objField['Categories'] = $field['FieldType'] == "tags-category" ? $this->getFeaturedCategories($field['FieldValue']) : array();
//                    //TODO: add categories and products to the field
//                    $objCollection['Fields'][] = $objField;
                    }
                    $objSection['Collections'][] = $objCollection;
                }
            }
            $data['Sections'][] = $objSection;
        }

        if($pageCodeName == "categories") {
            return array("PageCodeName" => $pageCodeName, "Categories" => $data['Sections'][0]['Collections']);
        } elseif($pageCodeName == "settings") {
            unset($data['Sections'][0]['SectionCodeName']);

            $this->load->model('localisation/language');
            $language_id = $this->config->get('config_language_id');
            $language = $this->model_localisation_language->getLanguage($language_id);
            $data['Sections'][0]['languagecode'] = $language['code'];
            $this->load->model('account/customer_group');
            $data['Sections'][0]['customer_groups'] = $this->model_account_customer_group->getCustomerGroups();
            return $data['Sections'][0];
        } else {
            return $data;
        }
    }

    private function getFeaturedProducts($product_ids_csv, $thumb_width=200, $thumb_height=200,$exclude_disabled_products=false,$with_product_options=false) {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

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
        $getProducts = $this->model_catalog_product->getProductsByIds($product_ids);
        foreach ($getProducts as $product_id => $product_info) {

            if ($product_info) {
                if ($exclude_disabled_products && !$product_info['status'])
                    continue;
                $description = substr(strip_tags(htmlspecialchars_decode($product_info['description'])), 0, 100);
                if ($product_info['image']) {
                    if ($thumb_width === "0" && $thumb_height === "0"){
                        if ($this->isProductImageNotFound($product_info['image']))
                            $image = \Filesystem::getUrl('image/no_image.jpg');
                        else {
                            $image = \Filesystem::getUrl('image/'.$product_info['image']);
                        }

                    }else
                        $image = $this->model_tool_image->resize($product_info['image'], $thumb_width, $thumb_height);

                } else {
                    $image = false;
                }
                $images = $this->model_catalog_product->getProductImages($product_info['product_id']);

                if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                    if ($thumb_width === "0" && $thumb_height === "0"){
                        if ($this->isProductImageNotFound($images[0]['image']))
                            $images = \Filesystem::getUrl('image/no_image.jpg');
                        else{
                            $images = \Filesystem::getUrl('image/'.$images[0]['image']);
                        }

                    }else
                        $images = $this->model_tool_image->resize($images[0]['image'], $thumb_width, $thumb_height);
                }

                if ($this->customer->isCustomerAllowedToViewPrice()) {
                    $price = $this->currency->format($this->tax->calculate($product_info['price'],
                        $product_info['tax_class_id'], $this->config->get('config_tax')) , '', '', false);

                        $float_product_price = $this->currency->format($this->tax->calculate(
                            $product_info['price'],
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')
                        ),'','',false);
                } else {
                    $price = $float_product_price = false;
                }

                if ((float)$product_info['special']) {
                    $special = $this->currency->format($this->tax->calculate($product_info['special'],
                        $product_info['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                        $float_special = $this->currency->currentValue($this->tax->calculate(
                            $product_info['special'],
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')
                        ));
                       if($float_special){
                           $decimalPlaceVal = $this->currency->getDecimalPlace();
                           $decimalPlace =  (!empty($decimalPlaceVal) && is_numeric($decimalPlaceVal)) ? $decimalPlaceVal : 2;
                           $float_special= round($float_special, $decimalPlace);
                       }
                } else {
                    $special = $float_special = false;
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
                ///////////////////////////
                $options = array();
                if($with_product_options){
                    foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
                        if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
                            $option_value_data = array();

                            foreach ($option['option_value'] as $k  =>  $option_value) {
                               $product_option_value_id =  $option_value['product_option_value_id'];
                                if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                                    if ($this->isCustomerAllowedToViewPrice && (float)$option_value['price']) {
                                        $price = $this->currency->format($this->tax->calculate(
                                            $option_value['price'],
                                            $product_info['tax_class_id'],
                                            $this->config->get('config_tax')
                                        ));

                                        $float_price = $this->currency->format($this->tax->calculate(
                                            $option_value['price'],
                                            $product_info['tax_class_id'],
                                            $this->config->get('config_tax')
                                        ),'','',false);
                                    } else {
                                        $price = $float_price = false;
                                    }

                                    $this->load->model('tool/image');
                                    if($this->data['option_images'][$product_option_value_id][0]['image'])
                                        $product_option_image = $this->data['option_images'][$product_option_value_id][0]['image'];
                                    elseif ($option_value['image'])
                                        $product_option_image = $option_value['image'];
                                    else
                                        $product_option_image ="no_image.jpg";
                                    $option_value_data[] = array(
                                        'product_option_value_id' => $option_value['product_option_value_id'],
                                        'option_value_id'         => $option_value['option_value_id'],
                                        'name'                    => $option_value['name'],
                                        'image_thumb'             => $this->model_tool_image->resize($product_option_image, 50, 50),
                                        'image'                   => \Filesystem::getUrl('image/'.$product_option_image),
                                        'price'                   => $price,
                                        'float_price'             => $float_price,
                                        'currency'                => $this->currency->getCode(),
                                        'price_prefix'            => $option_value['price_prefix']
                                    );

                                }
                            }
                            usort($option_value_data,function($first,$second){
                                return $first['product_option_value_id'] > $second['product_option_value_id'];
                            });

                            $options[] = array(
                                'product_option_id' => $option['product_option_id'],
                                'product_option_value' => '0',
                                'option_id'         => $option['option_id'],
                                'name'              => $option['name'],
                                'type'              => $option['type'],
                                'option_value'      => $option_value_data,
                                'required'          => $option['required']
                            );
                        } elseif ($option['type'] == 'text_dis' || $option['type'] == 'text' || $option['type'] == 'textarea_dis' || $option['type'] == 'textarea' || $option['type'] == 'file_dis' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
                            $options[] = array(
                                'product_option_id' => $option['product_option_id'],
                                'product_option_value' => '0',
                                'option_id'         => $option['option_id'],
                                'name'              => $option['name'],
                                'type'              => $option['type'],
                                'option_value'      => $option['option_value'],
                                'required'          => $option['required']
                            );
                        }
                    }                       
                }
                ////////////////////////////////
                $products[] = array(
                    'product_id'        => $product_info['product_id'],
                    'thumb'   	        => $image,
                    'name'    	        => $product_info['name'],
                    'price'   	        => $price,
                    'special' 	        => $special,
                    'float_price'       => $float_product_price,
                    'float_special'     => $float_special,
                    'currency'          => $this->currency->getCode(),
                    'special_enddate'   => $product_info['special_enddate'],
                    'model'             => $model,
                    'model_url'         => $this->url->link('product/manufacturer/product', 'manufacturer_id=' . $model_url),
                    'rating'            => $rating,
                    'short_description' => $description,
                    'description'       => $product_info['description'],
                    'reviews'           => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                    'reviews_count'     => (int)$product_info['reviews'],
                    'href'    	        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                    'thumb_swap'        => $images,
                    'saving'	        => $savingAmount,
                    'quantity'          => $product_info['quantity'],
                    'stock_status'      => $product_info['stock_status'],
                    'date_available'    => $product_info['date_available'],
                    'general_use'       => $product_info['general_use'],
                    'product_options'   => $options,
                );
            }
        }
        return $products;
    }

    private function getFeaturedCategories($category_paths_csv, $thumb_width=200, $thumb_height=200, $categories_options = []) {

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        $categories = array();

        $category_paths = explode(',', $category_paths_csv);
        $limit = count($category_paths);
        ###################get random products from DB if zeros######################
        if(count(array_unique($category_paths)) == 1 && $category_paths[0] == "0") {
            $query = $this->db->query("SELECT category_id FROM category ORDER BY parent_id, RAND() LIMIT $limit");
            $random_category_ids = array_column($query->rows, 'category_id');

            $generated_category_ids = array();
            while(count($generated_category_ids) <= $limit && count($random_category_ids) > 0){
                $generated_category_ids = array_merge($generated_category_ids, $random_category_ids);
            }
            $category_paths = array_slice($generated_category_ids, 0, $limit);
        }
        #############################################################################
        $config_api_catImage_full = $this->config->get('config_api_CatImage_full'); //enable retrieve category full image size (dashboard UI not implemented yet)

        foreach ($category_paths as $category_path) {
            $seller_id = "";

            $parts = explode('_', $category_path);

            $category_id = (int)array_pop($parts);

            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $description = substr(strip_tags(htmlspecialchars_decode($category_info['description'])), 0, 100);

                #------------------------------------------
                # GET SELLER ID FROM CATEGORY DESCRIPTION
                # -----------------------------------------
                preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', htmlspecialchars_decode($category_info['description']), $matches);
                $query_str = parse_url(htmlspecialchars_decode($matches[2][0]), PHP_URL_QUERY);
                parse_str($query_str, $query_params);

                if($query_params['seller_id'] != null){
                    $seller_id = $query_params['seller_id'];
                }
                # ---------------------------------------

                    if(isset($config_api_catImage_full) && $config_api_catImage_full == 1)
                        $image = \Filesystem::getUrl('image/' .str_replace(' ', '%20',$category_info['image']));
                    else
                        $this->model_tool_image->resize($category_info['image'], $thumb_width, $thumb_height);

                    $imageIcon = $this->model_tool_image->resize($category_info['icon'], $thumb_width, $thumb_height);
                    $categoryName = strip_tags(htmlspecialchars_decode($category_info['name']));

                // get category products
                $products = [];
                if ($categories_options['withProducts']) {
                    $products = $this->model_catalog_product->getProducts([
                        'filter_category_id' => $category_id,
                        'limit' => $categories_options['limit'] ? $categories_options['limit'] : 10,
                        'sort' => $categories_options['sort'] ? $categories_options['sort'] : null 
                    ]);
                    $products = array_values($products);
                    if ($products){
                        $products =  array_map(function ($product){
                             $product['image'] = \Filesystem::getUrl('image/' . $product['image']);
                             return $product;
                        } , $products);
                    }
                }

                $categories[] = array(
                    'category_id'       => $category_info['category_id'],
                    'thumb'   	        => $image,
                    'icon'              => $imageIcon,
                    'name'    	        => $categoryName,
                    'short_description' => $description,
                    'seller_id'         => $seller_id,
                    'href'    	        => $this->url->link('product/category', 'path=' . $category_path),
                    'products'          => $products,
                );
            }
        }

        return $categories;
    }

    public function getSetting($settingCodename) {
        if(!$this->settings)
            $this->settings = $this->getPageSections("settings");

        return $this->settings[$settingCodename];
    }

    public function isProductImageNotFound($productImage){
        return ( \Filesystem::isExists('image/' . $productImage) == false);
    }

}