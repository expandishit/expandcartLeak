<?php
require_once('jwt_helper.php');
class ControllerApiProduct extends Controller
{
    protected $isCustomerAllowedToViewPrice;

    public function __construct($registry)
    {
		parent::__construct($registry);
		$this->isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
    }

    public function index()
    {
        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        try {
            $this->load->language('api/login');
            $this->load->language('product/product');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('catalog/product');
            

            if (!isset($this->session->data['api_id'])&& !isset($this->request->post['safe_access'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                /*
                 *
                 * Query Parameters
                 *
                 */

                $data['categories_ids'] = $params->categories_ids && is_array($params->categories_ids) ? $params->categories_ids : null;
                $data['brands_ids']     = $params->brands_ids && is_array($params->brands_ids) ? $params->brands_ids : null;
                $data['sellers_ids']    = $params->sellers_ids && is_array($params->sellers_ids) ? $params->sellers_ids : null;
                $data['filterText']     = $params->filter_text;
                $data['limit']          = $params->limit;
                $data['product_id']     = $params->product_id;
                $data['start']          = $params->start;
                $data['sort']           = $params->sort;
                $data['order']          = $params->order;
                $data['starting_price'] = $params->starting_price;
                $data['ending_price']   = $params->ending_price;
                $data['seller_id']   = $params->seller_id;
                $data['trips']   = $params->trips;
                $data['deals']          = isset($params->deals) ? (boolean)$params->deals : null;
                $data['filter_text_each_word'] = is_bool($params->filter_text_each_word) ? $params->filter_text_each_word : true ; //default is on

                $results = $this->model_catalog_product->getProductsV2($data);

                //Rename and Casting Some Fields
                foreach ($results as $key => $result) {
                    $result['image'] = \Filesystem::getUrl('image/' . $result['image']);
                    $result['brand_image'] = \Filesystem::getUrl('image/' . $result['brand_image']);

                    if ($this->isCustomerAllowedToViewPrice) {
                        $result['price'] = $this->currency->format($this->tax->calculate($result['price'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);
                    } else {
                        $price = null;
                    }
                     ///check permissions to view Add to Cart Button
                     $result['viewAddToCartBtn'] = true;
                     $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                     if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                     {
                        $result['viewAddToCartBtn']  = false;
                     }

                    if ($result['special'] != null) {
                        $result['special'] = $this->currency->format($this->tax->calculate((float)$result['special'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);
                    }
    
                    $result['brand'] = [
                        'id' => $result['brand_id'],
                        'name' => $result['brand'],
                        'image' => $result['brand_image'],
                    ];
                    unset($result['brand_id']);
                    unset($result['brand_image']);

                    $result['currency'] = $this->currency->getCode();
                    $result['description'] = html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8');
                    $result['short_description'] = html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8');
                    $result['quantity'] = (int)$result['quantity'];
                    $result['rating'] = (int)$result['rating'];
                    $result['reward'] = (int)$result['reward'];
                    $result['reviews'] = (int)$result['reviews'];
                    $result['price'] = (float)$result['price'];
                    $result['special'] = (float)$result['special'];
                    $result['intensity'] = (float)$result['general_use'];

                    if((isset($data['seller_id'])) &&(\Extension::isInstalled('multiseller'))
                    &&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)) {
                    $this->load->model('multiseller/seller');
                    $this->load->model('module/trips');
                    $result['trip_data']=$this->model_module_trips->getTripProduct((int)$result['product_id']); 
                    $result['product_categories']=$this->model_catalog_product->getProductCategories((int)$result['product_id']);
                    }
                   

                    $results[$key] = $result;
                }
                 if($results){
                    $json['status'] = true;
                    $json['message'] = 'successfully query';
                    $json['data'] = $results;
                 }else{
                    $json['status'] = false;
                    if((\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)) {
                    $json['message'] = $this->language->get('text_notfound');}
                    $json['data'] = $results;
                 }
                
                
                $json['products_count'] = count($results);
                $json['data'] = $results;

                /*
                 *
                 * Adding Some Extra Key if product_id was sent
                 *
                 */

                if (isset($data['product_id'])) {
                    $results = $results[0];
                    if ($results['quantity'] <= 0) {
                        $results['availability'] = $results['stock_status'];
                    } elseif ($this->config->get('config_stock_display')) {
                        $results['availability'] = $results['quantity'];
                    } else {
                        $results['availability'] = $this->language->get('text_instock');
                    }

                    $product_images = $this->model_catalog_product->getProductImages($results['product_id']);
                    $images = [];
                    foreach ($product_images as $image) {
                        $images[] = \Filesystem::getUrl('image/' . $image['image']); //HTTP_IMAGE . $image['image'];
                    }

                    $options = $this->productOptions($results['product_id'], $results['tax_class_id']);

                    $results['images'] = $images;
                    $results['options'] = $options;

                    $json['data'] = $results;
                    $json['message'] = 'successfully query';
                }
            }
        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Internal Server Error';
        }

        $this->response->setOutput(json_encode($json));
    }


    private function productOptions($product_id, $tax_class_id)
    {
        return $this->model_catalog_product->getProductOptionsV2($product_id, $tax_class_id);
    }


    public function LatestProducts()
    {
        try {
            $this->load->language('product/product');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {

                $productsLimit = $params->productsLimit;
                $this->load->model('catalog/product');
                $this->load->model('tool/image');

                $json['products'] = array();

                /*$data = array(
                    'sort'  => 'p.date_added',
                    'order' => 'DESC',
                    'start' => 0,
                    'limit' => $productsLimit
                );*/

                $results = $this->model_catalog_product->getLatestProducts($productsLimit);

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], 50, 50); //width,height
                    } else {
                        $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
                    }

                    //this for swap image

                    $images = $this->model_catalog_product->getProductImages($result['product_id']);

                    if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                        $images = $images[0]['image'];
                    }

                    //

                    if ($this->isCustomerAllowedToViewPrice) {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $special = false;
                    }

                    if ($this->config->get('config_review_status')) {
                        $rating = $result['rating'];
                    } else {
                        $rating = false;
                    }

                    if ($result['price'] > 0) {
                        $savingAmount = round((($result['price'] - $result['special']) / $result['price']) * 100, 0);
                    } else {
                        $savingAmount = 0;
                    }
                    ///check permissions to view Add to Cart Button
                    $viewAddToCart = true;
                    $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                    if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                    {
                        $viewAddToCart = false;
                    }

                    $json['products'][] = array(
                        'product_id' => $result['product_id'],
                        'thumb' => $image,
                        'name' => $result['name'],
                        'price' => $price,
                        'rental_price' => $rental_price,
                        'special' => $special,
                        'rating' => $rating,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'description' => (html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
                        'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                        'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                        // for swap image
                        'thumb_swap' => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
                        //
                        // for saving percentage
                        'saving' => $savingAmount,
                        //
                    );
                }
            }


            //$json=$this->session;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            //$json['cookie'] = $_COOKIE[$this->session->getId()];
            $this->response->setOutput(json_encode($json));

        } catch (Exception $e) {

        }
    }

    public function BestSellerProducts()
    {
        try {
            $this->load->language('product/product');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                //$this->language->load('product/product');
                $this->load->model('catalog/product');

                //$productsLimit = $params->productsLimit;
                //$json['products'] = $this->model_catalog_product->getBestSellerProducts($productsLimit);
                //$product_info = $this->cart->getProducts();

                $productsLimit = $params->productsLimit;
                $this->load->model('catalog/product');
                $this->load->model('tool/image');

                $json['products'] = array();

                $results = $this->model_catalog_product->getBestSellerProducts($productsLimit);

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], 50, 50);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
                    }

                    //this for swap image

                    $images = $this->model_catalog_product->getProductImages($result['product_id']);

                    if(isset($images[0]['image']) && !empty($images[0]['image'])){
                        $images =$images[0]['image'];
                    }

                    //

                    if ($this->isCustomerAllowedToViewPrice) {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $special = false;
                    }

                    if ($this->config->get('config_review_status')) {
                        $rating = $result['rating'];
                    } else {
                        $rating = false;
                    }

                    if ($result['price'] > 0) {
                        $savingAmount = round((($result['price'] - $result['special'])/$result['price'])*100, 0);
                    }
                    else {
                        $savingAmount = 0;
                    }
                     ///check permissions to view Add to Cart Button
						$viewAddToCart = true;
						$hidCartConfig = $this->config->get('config_hide_add_to_cart');
						if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
						{
							$viewAddToCart = false;
						}

                    $json['products'][] = array(
                        'product_id' => $result['product_id'],
                        'thumb'   	 => $image,
                        'name'    	 => $result['name'],
                        'price'   	 => $price,
                        'special' 	 => $special,
                        'rating'     => $rating,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'description'=> (html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
                        'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                        'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                        // for swap image
                        'thumb_swap'  => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
                        //
                        // for saving percentage
                        'saving'	=> $savingAmount,
                        //
                    );
                }
            }


            //$json=$this->session;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            //$json['cookie'] = $_COOKIE[$this->session->getId()];
            $this->response->setOutput(json_encode($json));

        } catch (Exception $e) {

        }
    }

    public function PopularProducts()
    {
        try {
            $this->load->language('product/product');
            $json = array();
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;

            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                //$this->language->load('product/product');
                //$this->load->model('catalog/product');

                //$productsLimit = $params->productsLimit;
                //$json['products'] = $this->model_catalog_product->getPopularProducts($productsLimit);

                $productsLimit = $params->productsLimit;
                $this->load->model('catalog/product');
                $this->load->model('tool/image');

                $json['products'] = array();

                $results = $this->model_catalog_product->getPopularProducts($productsLimit);

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], 50, 50); //width,height
                    } else {
                        $image = $this->model_tool_image->resize('no_image.jpg', 50, 50);
                    }

                    //this for swap image

                    $images = $this->model_catalog_product->getProductImages($result['product_id']);

                    if (isset($images[0]['image']) && !empty($images[0]['image'])) {
                        $images = $images[0]['image'];
                    }

                    //

                    if ($this->isCustomerAllowedToViewPrice) {
                        $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $price = false;
                    }

                    if ((float)$result['special']) {
                        $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
                    } else {
                        $special = false;
                    }

                    if ($this->config->get('config_review_status')) {
                        $rating = $result['rating'];
                    } else {
                        $rating = false;
                    }

                    if ($result['price'] > 0) {
                        $savingAmount = round((($result['price'] - $result['special']) / $result['price']) * 100, 0);
                    } else {
                        $savingAmount = 0;
                    }
                    ///check permissions to view Add to Cart Button
                    $viewAddToCart = true;
                    $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                    if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                    {
                        $viewAddToCart = false;
                    }

                    $json['products'][] = array(
                        'product_id' => $result['product_id'],
                        'thumb' => $image,
                        'name' => $result['name'],
                        'price' => $price,
                        'special' => $special,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'rating' => $rating,
                        'description' => (html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
                        'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                        'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                        // for swap image
                        'thumb_swap' => $this->model_tool_image->resize($images, $this->config->get('config_image_product_width'), $this->config->get('config_image_product_height')),
                        //
                        // for saving percentage
                        'saving' => $savingAmount,
                        //
                    );
                }
            }


            //$json=$this->session;
            $this->response->addHeader('Content-Type: application/json');
            $this->response->addHeader('Access-Control-Allow-Origin: *');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
            $this->response->addHeader('Access-Control-Allow-Credentials: true');
            //$json['cookie'] = $_COOKIE[$this->session->getId()];
            $this->response->setOutput(json_encode($json));

        } catch (Exception $e) {

        }
    }

    public function CategoryProducts() {

    }
   
    public function GetProductInfo() {
        try {
            $this->load->language('product/product');
            $this->load->model('tool/image');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $with_recommended_products = $params->with_recommended_products ?? 0;
            $with_options  = $params->with_options  ?? 0;
            $extra_data=['with_options' => $with_options,];

            $this->load->model('account/api');
            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $product_id  = $params->product_id;
                $language_id = $this->_getLanguageId($params->locale); //should be 'en', 'ar', 'ar-SA' ... etc
                $product_currency = $this->currency->getCode();
				
                $this->load->model('catalog/product');

                $product_info = $this->model_catalog_product->getProduct($product_id, $language_id);
                $json['Product'] = array();

                if ($product_info) {
                    if ($product_info['image']) {
                        $image = \Filesystem::getUrl('image/' . $product_info['image']);
                    } else {
                        $image = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    if ($this->isCustomerAllowedToViewPrice) {
                        $product_price = $this->currency->format($this->tax->calculate(
                            $product_info['price'],
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')
                        ));

                        $float_product_price = $this->currency->format($this->tax->calculate(
                            $product_info['price'],
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')
                        ),'','',false);

                        $rental_price = $this->currency->format($product_info['price'], '', '', false);

                    } else {
                        $product_price = $float_product_price = $rental_price = false;
                    }

                    if ((float)$product_info['special']) {
                        $special = $this->currency->format($this->tax->calculate(
                            $product_info['special'],
                            $product_info['tax_class_id'],
                            $this->config->get('config_tax')
                        ));
                        // as per mobile team request the float_decimal attribute
                        // should be the same  value as special attribute but without currency
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

                    if ($product_info['quantity'] <= 0) {
                        $stock = $product_info['stock_status'];
                    } elseif ($this->config->get('config_stock_display')) {
                        $stock = $product_info['quantity'];
                    } else {
                        $stock = $this->language->get('text_instock');
                    }


                    $product_discounts = $this->model_catalog_product->getProductDiscounts($product_id);

                    $images = array();

                    $results = $this->model_catalog_product->getProductImages($product_id);

                    foreach ($results as $result) {
                        $images[] = array(
                            'url' => \Filesystem::getUrl('image/' . $result['image'])
                        );
                    }

                    $options = array();
                    $this->load->model('module/product_option_image_pro');
                    $this->data['option_images']['enabled'] = $this->model_module_product_option_image_pro->installed();
                    if ($this->data['option_images']['enabled']) {
                        $this->data['option_images'] = $this->model_module_product_option_image_pro->getProductOptionImagesByValues($product_id);
                    }
                
                    $options=$this->model_catalog_product->getProductsOptions($product_id);
                    
                     $this->load->model('module/trips');
                    if($this->model_module_trips->isTripsAppInstalled()) {
                        
                        $tripData= $this->model_module_trips->getTripProduct($product_id);  
                    }

                    if(\Extension::isInstalled('advanced_product_attributes') && $this->config->get('advanced_product_attribute_status')){
                           //Product attribes
                            $attributes = $this->model_catalog_product->getProductAttributes($product_id);
                            //////
                    }
                    $productCategories=$this->model_catalog_product->getProductCategories($product_id);
                    $this->load->model('module/rental_products/settings');
                    // check if rental app is active and the product transaction_type is reservation then get disabled days
                    if ($this->model_module_rental_products_settings->isActive() && $product_info['transaction_type'] == 2 ) {
                        $rental_product = 1;
                        $rentalProducts = $this->model_module_rental_products_settings->getSettings();
                        $max_rental_days = ($rentalProducts['max_rental_days'] > 0 ? $rentalProducts['max_rental_days'] : false);
                        $from = time();
                        $to = $from + ($rentalProducts['max_rental_days'] * 24 * 60 * 60) ;
                        // return disabled_days in json format to be used in datepicker js view code
                        $disabled_days = json_encode($this->model_catalog_product->getRentDisabledDates($from,$to,$product_id,$product_info['quantity']));

                    } else {
                        $rental_product = 0;
                    }

                    // check product bundles App installed or not
                    $this->load->model('module/product_bundles/settings');
                    if($this->model_module_product_bundles_settings->isActive()){
                        // get the product bundles
                        $this->data['product_bundles'] = $this->model_catalog_product->getProductBundles($product_info['product_id']);
                    }
                    
                    $related_products = $this->model_catalog_product->getRelatedProducts($product_id,$extra_data);
                    if (count($related_products) == 0) {
                        $this->load->model('module/related_products');
                        $relatedProducts = $this->model_module_related_products;
        
                        if ($relatedProducts->isActive()) {
                            $relatedProductsCatalog['categories'] = $relatedProducts->getRelatedProductsByCategory($product_id);
                            $relatedProductsCatalog['manufacturers'] = $relatedProducts->getRelatedProductsByManufacturer( $product_info['manufacturer_id'], $product_id );
                            $relatedProductsCatalog['keywords'] = $relatedProducts->getRelatedProductsByKeyword($product_info['tag'],$product_id);
                            $related_products = $relatedProducts->mergeCatalog($relatedProductsCatalog);

                            foreach ($related_products as $key => $related_product) {
                                if ($related_product['image']) {
                                    $relatedProductImage = $this->model_tool_image->resize($related_product['image'], 250, 250);
                                } else {
                                    $relatedProductImage = $this->model_tool_image->resize('no_image.jpg', 250, 250);
                                }
                                $related_products[$key]['image'] = $relatedProductImage;
                                $related_products[$key]['price'] = $this->_formatProductPrice(
                                    $related_products[$key]['price'],
                                    $related_products[$key]['tax_class_id']
                                );
                                $related_products[$key]['float_price'] = $this->_formatProductPrice(
                                    $related_products[$key]['price'],
                                    $related_products[$key]['tax_class_id'],
                                    false
                                );
								$related_products[$key]['currency'] = $product_currency;

                            }
                        }
                    }
                    ///////////////////////////
                    if($with_recommended_products){
                       $recommended_products=$this->model_catalog_product->getRecommendedProducts($extra_data);
                    }
                    ////////////////////////////////////////
                    //Sales Booster
                    $sales_booster = $this->getProductSalesBooster($product_info['sls_bstr']);
                    ///////////////
                    ///check permissions to view Add to Cart Button
                    $viewAddToCart = true;
                    $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                    if(($product_info['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                    {
                        $viewAddToCart = false;
                    }


                    $product_info['description'] = $this->model_catalog_product->_replaceUnsecureUrlWithSecureUrlFromHtmlTags($product_info['description']);
                    $doc = new DOMDocument();
                    $doc->loadHTML(mb_convert_encoding($product_info['description'], 'HTML-ENTITIES', 'UTF-8'));
                    $description = $doc->saveHTML();
                    $seller_id = $this->model_catalog_product->getProductSellerId($product_info['product_id'])['seller_id'];
                    $seller_name =($seller_id)?$this->MsLoader->MsProduct->getSellerName($seller_id):'';
                    $json['Product'] = array(
                        'product_id' => $product_info['product_id'],
                        'name' => $product_info['name'],
                        'short_description' => (mb_substr(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
                        'description' => html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),
                        'fixed_description' => html_entity_decode($description, ENT_QUOTES, 'UTF-8'),
                        'rating' => (int)$product_info['reviews'],
                        'product_code' => $product_info['model'],
                        'brand' => $product_info['manufacturer'],
                        'brand_id' => $product_info['manufacturer_id'],
                        'brand_image' => HTTPS_IMAGE .$product_info['manufacturerimg'],
                        'availability' => $stock,
                        'price' => $product_price,
                        'float_price' => $float_product_price,
                        'special' => $special,
                        'float_special' => $float_special,
                        'rental_info' => array(
                            'rental_product' => $rental_product ?? 0,
                            'max_rental_days' => $max_rental_days ?? 0,
                            'disabled_days' => $disabled_days ?? 0,
                            'rental_price' => $rental_price ?? 0,
                        ),
                        'bundles_info' => array(
                            'product_bundles_status' => isset($this->data['product_bundles']) ? true : false ,
                            'product_bundles' => $this->data['product_bundles'] ?? NULL,
                        ),
                        'currency' => $product_currency,
                        'image' => $image,
                        'product_images' => $images,
                        'product_discount' => $product_discounts,
                        'product_options' => $options?:[],
                        'product_categories'=>$productCategories,
                        'trip_data' => $tripData,
                        'related_product' => $related_products,
                        'recommended_product' => $recommended_products,
                        'share_links' => "",
                        'quantity' => $product_info['quantity'],
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'attributes' => $attributes,
                        'seller_id' => $seller_id,
                        'seller_name'=>$seller_name,
                        'is_multiseller' => empty($seller_id) ? false : true,
                        'sales_booster' => $sales_booster ,
                        'unlimited'     => $product_info['unlimited'],
                    );

                    $seller_info = $this->model_catalog_product->getProductSellerId($product_info['product_id']);

                    if($seller_info) {
                        $json['Product']['seller_id']  = $seller_info['seller_id'];
                        $json['Product']['is_multiseller']  = true;
                        $json['Product']['seller_name']  = $seller_info['nickname'];
                    }

                    if(\Extension::isInstalled('widebot') && $this->config->get('widebot')['status']){
                        $json['Product']['wb_description'] = trim( preg_replace( '/[\s|&nbsp;]+/mu', ' ',strip_tags($product_info['description']) ));
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

        }
    }

    public function GetCategoryProducts(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');

            if (!isset($this->session->data['api_id'])) {
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $search_string = $this->request->post['search_string'];
                $start = $params->start ?: $this->request->post['start'];
                $limit = $params->limit ?: $this->request->post['limit'];
                $filter_option = $params->filter_option ?: $this->request->post['filter_option'];
                $sort_criteria = $params->sort_criteria ?: $this->request->post['sort_criteria'];
                $sort_order = $params->sort_order ?: $this->request->post['sort_order'];

                $json['Products'] = array();

                $this->load->model('catalog/product');

                $data = array(
                    'filter_name' => $search_string,
                    'sort'  => 'p.date_added',
                    'order' => 'DESC',
                    'sort_order' => $sort_order ? $sort_order : 'DESC',
                    'start' => $start,
                    'limit' => $limit,
                    'filter_option' => $filter_option
                );

                if (isset($this->request->post['brand_id']) || isset($params->brand_id)) {
                    $brandId = $params->brand_id ?: $this->request->post['brand_id'];
                    if (filter_var($brandId, FILTER_VALIDATE_INT) == true && $brandId > 0) {
                        $data['filter_manufacturer_id'] = $brandId;
                    }
                }

                $results = $this->model_catalog_product->getProducts($data);
                if ($sort_criteria) {
                    usort($results, function($a, $b) use ($sort_criteria, $sort_order) {
                        if ($a[$sort_criteria] == $b[$sort_criteria]) {
                            return 0;
                        }
                        if ($sort_order == 'DESC') {
                            return ($a[$sort_criteria] > $b[$sort_criteria]) ? -1 : 1;
                        } else {
                            return ($a[$sort_criteria] < $b[$sort_criteria]) ? -1 : 1;
                        }
                    });
                }

                $this->load->model('tool/image');

                foreach ($results as $result) {
                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], 250, 250);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.jpg', 250, 250);
                    }

                    if ($this->isCustomerAllowedToViewPrice) {
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
                    if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                    {
                        $viewAddToCart = false;
                    }

                    $json['Products'][] = array(
                        'product_id' => $result['product_id'],
                        'image' => $image,
                        'name' => $result['name'],
                        'price' => $price,
                        'special' => $special,
                        'viewAddToCartBtn'=>$viewAddToCart,
                        'short_description' => (mb_substr(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
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

        }
    }

    public function new_filter()
    {
        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        try {
            $this->load->language('api/login');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('catalog/product');


            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                /*
                *
                * Query Parameters
                *
                */

                $data['categories_ids'] = $params->categories_ids && is_array($params->categories_ids) ?
                    $params->categories_ids : null;
                $data['brands_ids'] = $params->brands_ids && is_array($params->brands_ids) ?
                    $params->brands_ids : null;
                $data['filterText'] = $this->processArabicNumbers($params->filterText);
                $data['limit'] = $params->limit;
                $data['start'] = $params->start;
                // you can sort by any index of $sort_data array found at model_catalog_product->new_filter
                $data['sort'] = $params->sort;
                $data['order'] = $params->order;
                $data['starting_price'] = $params->starting_price;
                $data['ending_price'] = $params->ending_price;
                $deals_set = $params->deals;
                $data['deals'] = isset($deals_set) ? (boolean)$deals_set : null;
                $filter_option = $params->filter_option ?: $this->request->post['filter_option'];
                $sort_criteria = $params->sort_criteria ?: $this->request->post['sort_criteria'];
                $sort_order = $params->sort_order ?: $this->request->post['sort_order'];
                $filter_option_quantity = $params->filter_option_quantity ?: $this->request->post['filter_option_quantity'];
                $data['filter_option'] = $filter_option;
                $data['filter_option_quantity'] = $filter_option_quantity;
                $data['sort_order'] = $sort_order ? $sort_order : 'DESC';
                $data['offer_categories'] = $params->offer_categories ?: $this->request->post['offer_categories'];
                $with_options = $params->with_options  ?? 0;
                
                if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
                &&$this->config->get('trips')['status']==1)) {
                $data['filterbyArea'] = $this->processArabicNumbers($params->filterbyArea);
                $data['trips'] = $params->trips;
                $data['popular'] = $params->popular;
                $data['from_date'] = $params->from_date;
                $data['to_date'] = $params->to_date;
                }

                /*
                 * get Sub categories of categories
                */
                $SubCategoryArray = [];
                if($data['categories_ids'] && !$params->categories_self){
                    $this->load->model('catalog/category');
                    $sub_categories = $this->model_catalog_category->getCategories($data['categories_ids']);

                    $data['categories_ids'] = array_merge($data['categories_ids'] , array_column($sub_categories, 'category_id') );

                    foreach ($sub_categories as $sub_category) {
                        $SubCategoryArray[] = array(
                            'category_id' => $sub_category['category_id'],
                            'name' => $sub_category['name'],
                            'image' => \Filesystem::getUrl('image/' . (empty($sub_category['image']) ? 'no_image.png' : $sub_category['image'])),
                            'icon_image' => \Filesystem::getUrl('image/' . (empty($sub_category['icon']) ? 'no_image.png' : $sub_category['icon']))
                        );

                    }
                }

                $filtered = $this->model_catalog_product->new_filter($data);
                $results = $filtered['products'];
                $categories = [];
                if (isset($data['offer_categories'])) {

                    $categories = $this->model_catalog_category->getCategories(array_unique(array_column($results, 'category_id')));
                    $categories = array_map(function($category) {
                        return [
                            'category_id' => $category['category_id'],
                            'name' => $category['name'],
                            'image' => \Filesystem::getUrl('image/' . (empty($category['image']) ? 'no_image.png' : $category['image'])),
                            'icon_image' => \Filesystem::getUrl('image/' . (empty($category['icon']) ? 'no_image.png' : $category['icon']))
                        ];
                    }, $categories);
                }

                if ($sort_criteria) {
                    usort($results, function($a, $b) use ($sort_criteria, $sort_order) {
                        if ($a[$sort_criteria] == $b[$sort_criteria]) {
                            return 0;
                        }
                        if ($sort_order == 'DESC') {
                            return ($a[$sort_criteria] > $b[$sort_criteria]) ? -1 : 1;
                        } else {
                            return ($a[$sort_criteria] < $b[$sort_criteria]) ? -1 : 1;
                        }
                    });
                }
                /*
                *
                * Rename and Casting Some Fields
                *
                */
                $price_array = [];
                $brands_array = [];

                foreach ($results as $key => $result) {
                    
                    if ($result['image']) {
                        $result['image'] = \Filesystem::getUrl('image/' . $result['image']);
                    } else {
                        $result['image'] = \Filesystem::getUrl('image/' . 'no_image.jpg');
                    }

                    if ($result['brand_image']) {
                        $result['brand_image'] = \Filesystem::getUrl('image/' . $result['brand_image']);
                    } else {
                        $result['brand_image'] = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    $price = null;
                    $price_with_currency = null;
                    $float_product_price=null;
                    $float_special=null;
                    if ($this->isCustomerAllowedToViewPrice) {
                        $price_with_currency = $this->currency->format($this->tax->calculate($result['price'],
                            $result['tax_class_id'], $this->config->get('config_tax')));
                        $price = $this->currency->format($this->tax->calculate($result['price'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                            $float_product_price = $this->currency->format($this->tax->calculate(
                                $result['price'],
                                $result['tax_class_id'],
                                $this->config->get('config_tax')
                            ),'','',false);
                    }

                    if ($result['special'] != null) {
                        $result['special_with_currency'] = $this->currency->format($this->tax->calculate((float)$result['special'],
                            $result['tax_class_id'], $this->config->get('config_tax')));
                        $result['special'] = $this->currency->format($this->tax->calculate((float)$result['special'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                            $float_special = $this->currency->currentValue($this->tax->calculate(
                                $result['special'],
                                $result['tax_class_id'],
                                $this->config->get('config_tax')
                            ));
                           if($float_special){
                               $decimalPlaceVal = $this->currency->getDecimalPlace();
                               $decimalPlace =  (!empty($decimalPlaceVal) && is_numeric($decimalPlaceVal)) ? $decimalPlaceVal : 2;
                               $float_special= round($float_special, $decimalPlace);
                           }
                    }
                    $result['product_categories']=$this->model_catalog_product->getProductCategories((int)$result['product_id']);
                    $this->load->model('module/trips');
                    if($this->model_module_trips->isTripsAppInstalled()) 
                    {
                        $result['trip_data']=$this->model_module_trips->getTripProduct((int)$result['product_id']); 
                    }
                
                    $result['brand'] = [
                        'id' => $result['brand_id'],
                        'name' => $result['brand'],
                        'image' => $result['brand_image'],
                    ];

                    if($result['brand_id'])
                        $brands_array[] = $result['brand'];

                    $result['brand'] = [
                        'id' => $result['brand_id'],
                        'name' => $result['brand'],
                        'image' => $result['brand_image'],
                    ];
                    $options = null;
                    if($with_options)
                    {
                        $options=$this->model_catalog_product->getProductsOptions($result['product_id']);
                    }
                    unset($result['brand_id']);
                    unset($result['brand_image']);
                    ///check permissions to view Add to Cart Button
                    $result['viewAddToCartBtn'] = true;
                    $hidCartConfig = $this->config->get('config_hide_add_to_cart');
                    if(($result['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
                    {
                        $result['viewAddToCartBtn'] = false;
                    }

                    $result['currency'] = $this->currency->getCode();
                    $result['description'] = html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8');
                    $result['short_description'] = html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8');
                    $result['quantity'] = (int)$result['quantity'];
                    $result['rating'] = (int)$result['rating'];
                    $result['reward'] = (int)$result['reward'];
                    $result['reviews'] = (int)$result['reviews'];
                    $result['price'] = $price;
                    $result['float_price'] = $float_product_price;
                    $result['price_with_currency'] = $price_with_currency;
                    $result['special'] = $result['special'];
                    $result['float_special'] = $float_special;
                    $result['general_use'] = $result['general_use'];
                    $result['product_options'] = $options ? $options : [];

                    $results[$key] = $result;
                    $price_array[] = $result['price'];
                }

                $total_products = $this->model_catalog_product->getTotalProducts();
                $json['items'] = $results;
                $json['starting_price'] = (float)(min($price_array) != false) ? min($price_array) : 0;
                $json['ending_price'] = (float)(max($price_array) != false) ? max($price_array) : 0;
                $json['total_result'] =  count($results);
                $json['total_filtered'] =  $filtered['totalFiltered'] ?? $json['total_result'];
                $json['total_products'] =  (int)$total_products;
                $json['brands'] = $brands_array;
                $json['subcategories'] = $SubCategoryArray;
                $json['categories'] = $categories;
                $json['message'] = 'successfully query';
                $json['filter_option'] = $filter_option;

            }
        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }

        $this->response->setOutput(json_encode($json));
    }

    /**
     * This method is creating and Arabic to English numbers map
     * as and assoc array, it splits the input string into characters
     * then starts to loop and check if a character is a key in the map
     * if the key exists it sets the value of current loop char to the English
     * value that evaluates to the arabic number in the map
     */
    private function processArabicNumbers($string)
    {
        if (empty($string)) {
            return $string;
        }
        $arabicToEnglishMap = [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9'
        ];
        $processedString = mb_str_split($string);
        foreach($processedString as $index => $char)
        {
            if (isset($arabicToEnglishMap[$char])) {
                $processedString[$index] = $arabicToEnglishMap[$char];
            }
        }
        $processedString = implode('', $processedString);
        return $processedString;
    }

    public function productClassificationBrands(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');


            if (!isset($this->session->data['api_id'])) {

                $json['error']['warning'] = $this->language->get('error_permission');
            } else {

                $json['brands'] = array();

                $this->load->model('module/product_classification/brand');

                $results = $this->model_module_product_classification_brand->getBrands();

                foreach ($results as $result) {

                    $json['brands'][] = array(
                        'brand_id' => $result['pc_brand_id'],
                        'name' => $result['name']
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
        }
    }

    public function productClassificationGetModelsByBrand(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');


            if (!isset($this->session->data['api_id'])) {

                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $brand_id = $params->brand_id  ?: $this->request->post['brand_id'];

                $json['models'] = array();

                $this->load->model('module/product_classification/brand');

                $results = $this->model_module_product_classification_brand->getModelsByBrandId($brand_id);

                foreach ($results as $result) {

                    $json['models'][] = array(
                        'model_id' => $result['pc_model_id'],
                        'name' => $result['name']
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
        }
    }

    public function productClassificationGetYearsByModel(){
        try {
            $this->load->language('api/login');
            $json = array();

            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');


            if (!isset($this->session->data['api_id'])) {

                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                $model_id = $params->model_id  ?: $this->request->post['model_id'];


                $json['years'] = array();

                $this->load->model('module/product_classification/model');

                $results = $this->model_module_product_classification_model->getYearsByModelId($model_id);

                foreach ($results as $result) {

                    $json['years'][] = array(
                        'year_id' => $result['pc_year_id'],
                        'name' => $result['name']
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
        }
    }

    public function productClassificationFilter()
    {
        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        try {
            $this->load->language('api/login');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $this->load->model('account/api');
            $this->load->model('catalog/product');


            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            } else {
                /*
                *
                * Query Parameters
                *
                */

                $data['brand_id'] = $params->brand_id  ?: $this->request->post['brand_id'];
                $data['model_id'] = $params->model_id  ?: $this->request->post['model_id'];
                $data['year_id'] = $params->year_id  ?: $this->request->post['year_id'];

                $data['filterText'] = $this->processArabicNumbers($params->filterText);
                $data['limit'] = $params->limit;
                $data['start'] = $params->start;
                $data['sort'] = $params->sort;
                $data['order'] = $params->order;
                $data['starting_price'] = $params->starting_price;
                $data['ending_price'] = $params->ending_price;
                $deals_set = $params->deals;
                $data['deals'] = isset($deals_set) ? (boolean)$deals_set : null;
                $results = $this->model_catalog_product->product_classification_filter($data);

                /*
                *
                * Rename and Casting Some Fields
                *
                */
                $price_array = [];

                foreach ($results as $key => $result) {
                    if ($result['image']) {
                        $result['image'] = \Filesystem::getUrl('image/' . $result['image']);
                    } else {
                        $result['image'] = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    if ($result['brand_image']) {
                        $result['brand_image'] = \Filesystem::getUrl('image/' . $result['brand_image']);
                    } else {
                        $result['brand_image'] = \Filesystem::getUrl('image/no_image.jpg');
                    }

                    if ($this->isCustomerAllowedToViewPrice) {
                        $result['price'] = $this->currency->format($this->tax->calculate($result['price'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);
                    } else {
                        $price = null;
                    }

                    if ($result['special'] != null) {
                        $result['special'] = $this->currency->format($this->tax->calculate((float)$result['special'],
                            $result['tax_class_id'], $this->config->get('config_tax')), '', '', false);
                    }

                    $result['brand'] = [
                        'id' => $result['brand_id'],
                        'name' => $result['brand'],
                        'image' => $result['brand_image'],
                    ];
                    unset($result['brand_id']);
                    unset($result['brand_image']);

                    $result['currency'] = $this->currency->getCode();
                    $result['description'] = html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8');
                    $result['short_description'] = html_entity_decode($result['short_description'], ENT_QUOTES, 'UTF-8');
                    $result['quantity'] = (int)$result['quantity'];
                    $result['rating'] = (int)$result['rating'];
                    $result['reward'] = (int)$result['reward'];
                    $result['reviews'] = (int)$result['reviews'];
                    $result['price'] = (float)$result['price'];
                    $result['special'] = (float)$result['special'];

                    $results[$key] = $result;
                    $price_array[] = $result['price'];
                }
                $json['items'] = $results;
                $json['starting_price'] = (float)(min($price_array) != false) ? min($price_array) : 0;
                $json['ending_price'] = (float)(max($price_array) != false) ? max($price_array) : 0;
                $json['message'] = 'successfully query';

            }
        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }

        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get sales booster data of a product
     * @param $sls_bstr
     * @return array
     */
    private function getProductSalesBooster($sls_bstr){
        //Sales Booster
        $this->load->model('module/sales_booster');
        $selfInfo = false;
        $sales_booster = [];
        if($sls_bstr != ''){
            $selfInfo = true;
            $sls_bstrData = json_decode($sls_bstr, true);
        }
        if ($this->model_module_sales_booster->isActive() && ($this->model_module_sales_booster->isForceApply() || $sls_bstrData['status'] == '1')) {
            $sales_booster = $this->model_module_sales_booster->getSettings();

            //Validate Video URL
            if($sales_booster['video_url'] && $this->validateVideo($sales_booster['video_url'])){
                $vid = str_replace('watch?v=', 'embed/', $sales_booster['video_url']);
                $vid = str_replace('youtu.be', 'youtube.com/embed', $vid);
                $sales_booster['video_url'] = $vid;
            }

            if($sales_booster['desc_header_layout']){
                $sales_booster['hLayout'] = $this->model_module_sales_booster->getLayout($sales_booster['desc_header_layout']);
                $sales_booster['hLayout']['description'] = html_entity_decode($sales_booster['hLayout']['description'], ENT_QUOTES, 'UTF-8');
            }

            if($sales_booster['desc_footer_layout']){
                $sales_booster['fLayout'] = $this->model_module_sales_booster->getLayout($sales_booster['desc_footer_layout']);
                $sales_booster['fLayout']['description'] = html_entity_decode($sales_booster['fLayout']['description'], ENT_QUOTES, 'UTF-8');
            }

            if($sales_booster['free_html'][$this->config->get('config_language_id')]){
                $sales_booster['f_html'] = html_entity_decode($sales_booster['free_html'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
            }

            $st_lv_min = $sales_booster['stock_level_min'] ? $sales_booster['stock_level_min'] : 2;
            $st_lv_max = $sales_booster['stock_level_max'] ? $sales_booster['stock_level_max'] : 8;

            $rc_today_min = $sales_booster['sold_min'] ? $sales_booster['sold_min'] : 10;
            $rc_today_max = $sales_booster['sold_max'] ? $sales_booster['sold_max'] : 30;

            $sales_booster['recieved_today']     = rand($rc_today_min, $rc_today_max);
            $sales_booster['stock_level_val']    = rand($st_lv_min, $st_lv_max);
            $sales_booster['stock_level_bar']    = rand(20, 80);

            $sales_booster['count_dowen'] = $sales_booster['count_dowen'] ? $sales_booster['count_dowen'] : 4;
            $sales_booster['count_reset'] = $sales_booster['count_reset'] ? ($sales_booster['count_reset'] * 60 * 1000) : (30 * 60 * 1000);

            if($selfInfo){
                if($sls_bstrData['video'] && $this->validateVideo($sls_bstrData['video'])){
                    $vid = str_replace('watch?v=', 'embed/', $sls_bstrData['video']);
                    $vid = str_replace('youtu.be', 'youtube.com/embed', $vid);
                    $sales_booster['video_url'] = $vid;
                }

                if($sls_bstrData['free_html'][$this->config->get('config_language_id')])
                    $sales_booster['f_html'] = html_entity_decode($sls_bstrData['free_html'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
            }
        }

        return $sales_booster;
    }

    /**
     * validate video url
     * @param $url
     * @return bool
     */
    private function validateVideo($url){
        $validVideo = false;
        $validVideos = ['youtube.com', 'youtu.be'];

        $vidsCount = count($validVideos);
        for($i=0; $i<= $vidsCount; $i++){
            if(strpos($url, $validVideos[$i]) === false ){
                continue;
            }
            else{
                $validVideo = true;
                break;
            }

        }
        return $validVideo;
    }


    public function getProductVariations() {

        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            return $this->response->setOutput(json_encode($json));
        }

        $this->load->model('catalog/product');

        $data['lang_id'] = $this->config->get('config_language_id');

        $product_id  = $this->request->get['product_id'];
        $product_variations = $this->model_catalog_product->getProductVariationSku($product_id);
        $product_variations = array_map(function($variation) use ($data) {
        $variation['option_values'] = $this->model_catalog_product->getProductVariationOptionValuesDetailed(
            $data['lang_id'], $variation['option_value_ids']
        );
        return $variation;
        }, $product_variations);

        $this->response->setOutput(json_encode([
            'status' => 'ok',
            'data' => compact('product_variations')
        ]));
    }

    public function getProductsOptions() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            return $this->response->setOutput(json_encode($json));
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->load->model('catalog/product');
            $options = $this->model_catalog_product->getProductsOptionsByCategory($data['category_id']);
            $json = [
                'status' => 'ok',
                'data' => compact('options')
            ];
        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function reviewProduct() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('product/product');

            if (!isset($data['product']) || !$data['product']) {
				$json['error']['product'] = $this->language->get('error_product');
            }

            if (!isset($data['name']) || (utf8_strlen($data['name']) < 3) || (utf8_strlen($data['name']) > 25)) {
				$json['error']['name'] = $this->language->get('error_name');
			}

			if (!isset($data['text']) || (utf8_strlen($data['text']) < 3) || (utf8_strlen($data['text']) > 1000)) {
				$json['error']['text'] = $this->language->get('error_text');
			}

			if (!isset($data['rating']) || empty($data['rating'])) {
				$json['error']['rating'] = $this->language->get('error_rating');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {

                $this->load->model('catalog/product');
                $this->model_catalog_product->addReview($data);
                $json = [
                    'status' => 'ok'
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }


    public function getProductReviews() {

        $json = [];
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');


        if (!isset($this->session->data['api_id'])) {
            $json['error']['warning'] = $this->language->get('error_permission');
            $this->response->setOutput(json_encode($json));
        }

        try {

            $data = (array)json_decode(file_get_contents('php://input'));

            $this->language->load('product/product');

            if (!isset($data['product']) || !$data['product']) {
				$json['error']['product'] = $this->language->get('error_product');
            }

            if (isset($json['error']) && !empty($json['error'])) {
                $json['status'] = 'validation_error';
            } else {

                $this->load->model('catalog/product');
                $reviews = $this->model_catalog_product->getReviewsByProductId($data['product'], $data['start'], $data['limit']);
                $json = [
                    'status' => 'ok',
                    'data' => compact('reviews')
                ];
            }

        } catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Something went wrong';
        }
        $this->response->setOutput(json_encode($json));
    }

    public function autocomplete()
    {
        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        try
        {
            $this->load->language('api/login');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $searchText =  trim($params->search_text);
            $with_categories  = $params->with_categories  ?? 0;
            $this->load->model('account/api');
            $this->load->model('catalog/product');
            
            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            }
            else {
                if ($this->customer->isLogged() || $params->app=='ebasket') {
                    $products = $this->model_catalog_product->search($searchText);   
                    $json = [];
                if($products)
                {
                    foreach($products as $product)
                    {   
                        if($with_categories)
                           $productCategories=$this->model_catalog_product->getProductCategories($product['product_id']);
                        else $productCategories=[];

                        $json['products'][] = [
                            'link'          => $this->url->link('product/product&product_id=' . $product['product_id'], '', 'SSL'),
                            'name'          => $product['name'],
                            'description'   => isset($product['description']) ? strip_tags($product['description']) : '',
                            'image'         => \Filesystem::getUrl('image/' . $product['image']),
                            'price'         => isset($product['price']) ? $this->currency->format($product['price'], $this->config->get('config_currency')) : '',
                            'quantity'      => $product['quantity'],
                            'special'       => isset($product['special']) ? $this->currency->format($product['special'], $this->config->get('config_currency')) : '',
                            'categories'     => $productCategories
                        ]; 
                        
                    }
                }else{
                    $json['products']=[];
                }
                
                    
                }
                else
                {
                    $json['error'] = "not registered!";
                }

            }
        }
        catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Internal Server Error';
        }
        $this->response->setOutput(json_encode($json));
    }

    //Get Language id by it's locale code..
    private function _getLanguageId($locale){
        $this->load->model('localisation/language');
        return $this->model_localisation_language->getLanguageByLocale($locale)['language_id'];
    }

    public function compare()
    {
        $json = array();
        $this->response->addHeader('Content-Type: application/json');
        $this->response->addHeader('Access-Control-Allow-Origin: *');
        $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, x-xsrf-token');
        $this->response->addHeader('Access-Control-Allow-Credentials: true');
        try
        {
            $this->load->language('api/login');
            $this->language->load_json('product/compare');
            $params = json_decode(file_get_contents('php://input'));
            $encodedtoken = $params->token;
            $products_ids =  $params->products_ids;
            $with_options  = $params->with_options  ?? 0;
            $this->load->model('account/api');
            $this->load->model('catalog/product');
            if (!isset($this->session->data['api_id'])) {
                http_response_code(400);
                $json['error']['warning'] = $this->language->get('error_permission');
            }
            else {
                if ($this->customer->isLogged() || $params->app=='ebasket' ) {
                    $json = [];
                    ///////////////////////////////////////////////////////////////////////////////
                    if($products_ids){
                    foreach ($products_ids as $product_id) {
                        $product_info = $this->model_catalog_product->getProduct($product_id);
                        if ($product_info) {
                            if ($product_info['image']) {
                                $image = \Filesystem::getUrl('image/' . $product_info['image']);
                            } else {
                                $image = false;
                            }
                            $price = false;
                            $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
                            if ($isCustomerAllowedToViewPrice) {
                                $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                            }
                            $special = false;
                            if ((float)$product_info['special'] && $isCustomerAllowedToViewPrice) {
                                $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')));
                            }

                            if ($product_info['quantity'] <= 0) {
                                $availability = $product_info['stock_status'];
                            } elseif ($this->config->get('config_stock_display')) {
                                $availability = $product_info['quantity'];
                            } else {
                                $availability = $this->language->get('text_instock');
                            }

                            $stock_status = '';
                            if ($product_info['quantity'] <= 0) {
                                $stock_status = $product_info['stock_status'];
                            }
   
                            if($with_options){
                            $options=$this->model_catalog_product->getProductsOptions($product_id);
                            }

                            $json['products'][$product_id] = array(
                                'product_id'   => $product_info['product_id'],
                                'name'         => $product_info['name'],
                                'image'        => $product_info['image'],
                                'thumb'        => $image,
                                'price'        => $price,
                                'special'      => $special,
                                'full_description'  => $product_info['description'],
                                'description'  => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),"<br>"), 0, 200) . '..',
                                'model'        => $product_info['model'],
                                'manufacturer' => $product_info['manufacturer'],
                                'availability' => $availability,
                                'rating'       => (int)$product_info['rating'],
                                'reviews'      => sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']),
                                'weight'       => $this->weight->format($product_info['weight'], $product_info['weight_class_id']),
                                'length'       => $this->length->format($product_info['length'], $product_info['length_class_id']),
                                'width'        => $this->length->format($product_info['width'], $product_info['length_class_id']),
                                'height'       => $this->length->format($product_info['height'], $product_info['length_class_id']),
                                'stock_status' => $stock_status,
                                'stock_status_id' => $product_info['stock_status_id'],
                                'quantity' => $product_info['quantity'],
                                'href'         => $this->url->link('product/product', 'product_id=' . $product_id),
                                'product_options'=>  $options ? $options : [],
                            );
                        }
                    }
                    }else{
                        $json['products']=[];
                    }
                }
                else
                {
                    $json['error'] = "not registered!";
                }
            }
        }
        catch (Exception $exception) {
            http_response_code(500);
            $json['error']['warning'] = 'Internal Server Error';
        }
        $this->response->setOutput(json_encode($json));
    }


    private function _formatProductPrice($price , $taxClassId ,$format = true)
    {
        return $this->currency->format($this->tax->calculate(
            $price,
            $taxClassId,
            $this->config->get('config_tax')) , '' ,'', $format);
    }
   
  

  
}
