<?php
require_once('jwt_helper.php');
class ControllerApiMobileSettings extends Controller {
    public function GeneralSettings(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $infoPage_id = $this->config->get('mapp_infopage');

                $infoPageName = "";

                if ($infoPage_id != 0) {
                    $this->load->model('catalog/information');

                    $information_info = $this->model_catalog_information->getInformation($infoPage_id);

                    if ($information_info) {
                        $infoPageName = $information_info['title'];
                    }
                }

                $json['MainColor'] = $this->config->get('mapp_main_color');

                $logo_image = $this->config->get('mapp_logo_image');

                if (!empty($logo_image) && $logo_image && \Filesystem::isExists('image/' . $logo_image)) {
                    $json['LogoURL'] = \Filesystem::getUrl('image/' . $logo_image);
                } else {
                    $json['LogoURL'] = \Filesystem::getUrl('image/no_image.jpg');
                }

                $this->load->model('localisation/language');
                $language_id = $this->config->get('config_language_id');
                $language = $this->model_localisation_language->getLanguage($language_id);
                $languages = $this->model_localisation_language->getLanguages();
                $languages = array_column($languages, 'code');

                $json['StoreSlogan'] = $this->config->get('mapp_store_slogan');
                $json['FooterInfo'] = html_entity_decode($this->config->get('mapp_footerinfo'));
                $json['StoreName'] = $this->config->get('config_name');
                $json['InfoPageName'] = $infoPageName;
                $json['LanguageCode'] = $language['code'];
                $json['languages'] = $languages;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function MobileSlideShow() {
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json['Slides'] = array();

                $mapp_mobile_slides = $this->config->get('mapp_mobile_slides');

                for ($i = 0; $i < count($mapp_mobile_slides['image']); $i++) {
                    $category_name = 'none';

                    if ($mapp_mobile_slides['href_type'][$i] == 'category') {
                        $this->load->model('catalog/category');

                        $category_info = $this->model_catalog_category->getCategory($mapp_mobile_slides['href_id'][$i]);

                        $category_name = $category_info['name'];
                    }

                    $json['Slides'][] = array(
                        'image' => \Filesystem::getUrl('image/' . $mapp_mobile_slides['image'][$i]),
                        'href_type' => $mapp_mobile_slides['href_type'][$i],
                        'href_id' => $mapp_mobile_slides['href_id'][$i],
                        'category_name' => $category_name
                    );
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function SwitchLanguage() {
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $language_code = $params->language_code;

                $this->session->data['language'] = $language_code;

                $this->model_account_api->updateSession($encodedtoken);

                $json['Success'] = "1";
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function SwitchCurrency() {
        try {
            $this->load->language('api/login');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $currency_code = $params->currency_code;

                if(array_key_exists($currency_code,$this->currency->getCurrencies())){
                    $this->session->data['currency'] = $currency_code;
                    $this->model_account_api->updateSession($encodedtoken);

                    $json['Success'] = "1";
                    $json['Message'] = $this->language->get('currency_updated');
                }else{
                    $json['Success'] = "0";
                    $json['Message'] = $this->language->get('currency_not_supported');
;                }

            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function ProductPromoBlock() {
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $block_id = $params->block_id;

                $productPromoType = "none";
                $json['Products'] = array();

                switch ($block_id) {
                    case "1":
                        $productPromoType = $this->config->get('mapp_firstpromo');
                        break;
                    case "2":
                        $productPromoType = $this->config->get('mapp_secondpromo');
                        break;
                }

                $productsLimit = 10;

                $this->load->model('catalog/product');

                $data = array(
                    'sort'  => 'p.date_added',
                    'order' => 'DESC',
                    'start' => 0,
                    'limit' => $productsLimit
                );

                $results = array();

                switch ($productPromoType) {
                    case "none":

                        break;
                    case "latest":
                        $results = $this->model_catalog_product->getProducts($data);
                        break;
                    case "special":
                        $results = $this->model_catalog_product->getProductSpecials($data);
                        break;
                    case "bestseller":
                        $results = $this->model_catalog_product->getBestSellerProducts($productsLimit);
                        break;
                    case "featured":
                        $mapp_featured_products = explode(',', $this->config->get('mapp_featured_product'));

                        foreach ($mapp_featured_products as $product_id) {
                            $results[$product_id] = $this->model_catalog_product->getProduct($product_id);
                        }
                        break;
                }

                $this->load->model('tool/image');

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], 250, 250);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.jpg', 250, 250);
                    }

                    if ($this->customer->isCustomerAllowedToViewPrice()) {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $special = false;
                    }
                      ///check permissions to view Add to Cart Button
						$viewAddToCart = true;
						$hidCartConfig = $this->config->get('config_hide_add_to_cart');
						$cutomerAddCartPermission= $this->customer->isCustomerAllowedToAddToCart();
						if(($result['quantity'] <=0 && $hidCartConfig) || !$cutomerAddCartPermission)
						{
							$viewAddToCart = false;
						}	
                    $json['Products'][] = array(
                        'product_id' => $result['product_id'],
                        'image' => $image,
                        'name' => $result['name'],
                        'price' => $price,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'special' => $special,
                        'short_description' => (mb_substr(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
                    );
                }

                $json['BlockType'] = $productPromoType;
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function FeaturedCategories() {
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json['Categories'] = array();

                $this->load->model('catalog/category');

                $mapp_featured_categories = explode(',', $this->config->get('mapp_featured_category'));

                foreach ($mapp_featured_categories as $category_id) {
                    $category_info = $this->model_catalog_category->getCategory($category_id);

                    if (!empty($category_info) && $category_info['image'] && file_exists(DIR_IMAGE . $category_info['image'])) {
                        $image = \Filesystem::getUrl('image/' . $category_info['image']);
                    } else {
                        $image = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    if ($category_info) {
                        $json['Categories'][$category_id] = array(
                            'category_id' => $category_info['category_id'],
                            'image' => $image,
                            'name' => $category_info['name'],
                        );
                    }
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }

    public function SelectedCategories() {
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $json['Categories'] = array();

                $this->load->model('catalog/category');

                if ($this->config->get('mapp_ava_category') != "") {
                    $mapp_ava_categories = explode(',', $this->config->get('mapp_ava_category'));

                    foreach ($mapp_ava_categories as $category_id) {
                        $category_info = $this->model_catalog_category->getCategory($category_id);

                        if (!empty($category_info) && $category_info['image'] && \Filesystem::isExists('image/' . $category_info['image'])) {
                            $image = \Filesystem::getUrl('image/' . $category_info['image']);
                        } else {
                            $image = \Filesystem::getUrl('image/no_image.jpg');
                        }

                        if ($category_info) {
                            $json['Categories'][$category_id] = array(
                                'category_id' => $category_info['category_id'],
                                'image' => $image,
                                'name' => $category_info['name'],
                            );
                        }
                    }
                }
                else {
                    $main_categories = $this->model_catalog_category->getCategories(0);

                    foreach($main_categories as $main_category) {
                        if (!empty($main_category) && $main_category['image'] && \Filesystem::isExists('image/' . $main_category['image'])) {
                            $image = \Filesystem::getUrl('image/' . $main_category['image']);
                        } else {
                            $image = \Filesystem::getUrl('image/no_image.jpg');
                        }

                        $json['Categories'][] = array (
                            'category_id' => $main_category['category_id'],
                            'image' => $image,
                            'name' => $main_category['name']
                        );
                    }
                }
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');

            $this->response->setOutput(json_encode($json));
        }
        catch(Exception $e) {
            $json['Error'] = "500";
            $json['Message'] = $this->language->get('internal_error');
            $this->response->setOutput(json_encode($json));
        }
    }
}