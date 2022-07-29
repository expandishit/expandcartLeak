<?php

use ExpandCart\Foundation\Support\Facades\Filesystem;
use ExpandCart\Foundation\Filesystem\{Directory, File};

class ControllerProductProduct extends Controller {
	private $error = array();

	/**
	 * @return mixed
	 */
	public function calculateCurtainSellerCost()
	{
		if (strtolower($this->request->server['REQUEST_METHOD']) != 'post') {
			$product_id = array_key_exists('product_id', $this->request->post) ? $this->request->post['product_id'] : 0;
			return $this->url->redirect('product/product', ['product_id' => $product_id]);
		}

		$selling_type = strtolower($this->request->post['selling_type']);

		$this->load->model('module/curtain_seller');

		$data = $this->model_module_curtain_seller->calculateTotal($selling_type);

		$this->response->setOutput(json_encode(['data' => $data]));
	}

	public function index() {

		// unset($this->session->data['cart']['1385-kk']); die();

		$this->document->addStyle('expandish/view/theme/default/css/global-product.css');
		if($this->config->get('enable_save_product_options')){
			$this->document->addScript('expandish/view/theme/default/js/products/product_options.js');
		}
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
		$this->load->model('setting/setting');
		$this->data['integration_settings'] = $this->model_setting_setting->getSetting('integrations');

		$this->language->load_json('product/product', true);
		$this->language->load_json('module/product_designer');


		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

        $this->initializer([
            'security/throttling'
        ]);

        $this->data['recaptcha'] = [
            'status' => $this->throttling->reCaptchaStatus(),
            'site-key' => $this->throttling->reCaptchaSiteKey()
        ];

        if ($this->data['recaptcha']['status'] == 1) {

            $this->data['recaptchaId'] = 'button-review';

            $this->data['languageCode'] = $this->config->get('config_language');

            /*$this->document->addInlineScript(function () {
                return $this->renderDefaultTemplate('template/security/recaptcha.expand');
            });*/
        }

		// load product model
		$this->load->model('catalog/product');

		// check size chart
		if (\Extension::isInstalled('size_chart') && $this->config->get('size_chart_app_status')) {
			$this->load->model('module/size_chart');
			$this->data['size_charts'] = $this->model_module_size_chart->getChart();
			$this->data['product_categories'] = array_column($this->model_catalog_product->getCategories($this->request->get['product_id']), 'category_id');
		}

 		//Check if Stock Forecasting Quantity App installed & enabled
        if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
            $this->data['stock_forecasting_app_available'] = TRUE;
            
            $this->load->model('module/stock_forecasting');
            $result = $this->model_module_stock_forecasting->getProductStockForecasting($this->request->get['product_id']);
            $this->data['stock_forecasting_available_dates'] = array_column($result, 'day');
            $this->language->load_json('module/stock_forecasting');            
        }

		// Knawat Drop shippment api
		// Syncornize product data in case of product view

		$this->load->model('setting/setting');

		if($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']){
			// get update product option
			$updateProductOption = $this->config->get('module_knawat_dropshipping_product_update_status');
			if(!$updateProductOption || strtolower($updateProductOption) === 'off'){
				$app_dir = str_replace( 'system/', 'expandish/', DIR_SYSTEM );

				require_once $app_dir."controller/module/knawat_dropshipping.php";
				$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping( $this->registry );
				$this->controller_module_knawat_dropshipping->after_single_product();
			}

		}

		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$this->data['breadcrumbs'][] = array(
						'text'      => $category_info['name'],
						'href'      => $this->url->link('product/category', 'path=' . $path),
						'separator' => $this->language->get('text_separator')
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$this->data['breadcrumbs'][] = array(
					'text'      => $category_info['name'],
					'href'      => $this->url->link('product/category', 'path=' . $this->request->get['path']),
					'separator' => $this->language->get('text_separator')
				);
			}
		}

		// get product options
		$this->data['selectedOptions'] = isset($this->request->get['option']) ? $this->request->get['option'] : [] ;



		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_brand'),
				'href'      => $this->url->link('product/manufacturer'),
				'separator' => $this->language->get('text_separator')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$this->data['breadcrumbs'][] = array(
					'text'	    => $manufacturer_info['name'],
					'href'	    => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url),
					'separator' => $this->language->get('text_separator')
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_search'),
				'href'      => $this->url->link('product/search', $url),
				'separator' => $this->language->get('text_separator')
			);
		}
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
        
        if ($this->preAction->isActive()) {
            if (isset($this->request->get['slug']) && !isset($this->request->get['product_id'])) {
                $name = $this->request->get['slug'];
                $data = $this->model_catalog_product->getProductByName($name);
                if ($data) {
                    $this->request->get['product_id'] = $data->row['product_id'];
                }
            }
        }

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {
            //$queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

            //Check if MS is installed

			$paypalSettings = $this->config->get('paypal_status');
			$paypalAddOnProduct = $this->config->get("paypal_addOnProduct");
			$hideCart = $this->config->get("config_hide_add_to_cart");
			if ($paypalSettings == 1 && $paypalAddOnProduct == 1 ) {
				if( (empty($hideCart)  && $product_info["quantity"] > 0) || ($product_info["quantity"] <= 0 && empty($hideCart)) || (!empty($hideCart) && $product_info["quantity"] > 0) ) {
					$this->load->model("localisation/country");
					$languageCode = $this->config->get("config_language");
					$countryResult = $this->model_localisation_country->getCountry($this->config->get("config_country_id"));
					$locale = $languageCode . "_" . $countryResult["iso_code_2"];

					$this->data['paypal_enabled'] = 1;
					$this->data['merchantId'] = $this->config->get('paypal_merchant_id');
					$this->data['paypal_endpoint_js'] = "https://www.paypal.com/sdk/js?" . http_build_query(['client-id' => PAYPAL_MERCHANT_CLIENTID, 'merchant-id' => $this->data['merchantId'] , 'currency' => "USD"]);
					$this->data["paypal_button_color"] = $this->config->get("paypal_button_color");
				}

			}

	        $this->load->model('multiseller/status');
	        $multiseller = $this->model_multiseller_status->is_installed();
            if($multiseller)
            {
				if ( \Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1
				&& $this->config->get('msconf_delivery_slots_to_sellers'))
			   { 
				$this->document->addScript('expandish/view/javascript/product/seller_new_order.js');
			   }
                $this->document->addScript('expandish/view/javascript/multimerch/dialog-sellercontact.js');
                $this->document->addStyle('expandish/view/theme/default/template/multiseller/stylesheet/multiseller.css');
                $this->data = array_merge($this->data, $this->language->load_json('multiseller/multiseller'));
                $this->load->model('localisation/country');
                $this->load->model('localisation/zone');
                $this->load->model('tool/image');

                $seller_id = $this->MsLoader->MsProduct->getSellerId($this->request->get['product_id']);
                $seller = $this->MsLoader->MsSeller->getSeller($seller_id);

                if (!$seller) {
                    $this->data['seller'] = NULL;
                } else {
                	//Manage show totlas
					$this->data['sellers_totals'] = $this->config->get('msconf_sellers_totals');
					///////////////////

                    $this->data['seller'] = array();

            		$this->data['seller']['display'] = true;
                	if(!$this->config->get("show_seller")){
                		$this->data['seller']['display'] = false;
                	}

                    //Check if MS Messaging seller, Replace Add To Cart installed
                	$multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
                	//$multisellerReplcAddtoCart = $this->model_multiseller_status->is_replace_addtocart();
                	//$multisellerReplcContactForm = $this->model_multiseller_status->is_replace_contactform();

                	// messaging or chat flag
			        if($multisellerMessaging && $seller_id != (int)$this->customer->getId() )
			            $this->data['seller']['messaging'] = true; // chating option

					///check permissions to view Add to Cart Button
						$this->data['viewAddToCart'] = true;
						$hidCartConfig = $this->config->get('config_hide_add_to_cart');
						if(($product_info['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
						{
							$this->data['viewAddToCart'] = false;
						}

					// check  disable price
					$multisellerDisablePrice = $this->model_multiseller_status->disable_price();
					if ($multisellerDisablePrice)
						$this->data['seller']['disable_price'] = true;



                    if (!empty($seller['ms.avatar'])) {
                        $this->data['seller']['thumb'] = $this->MsLoader->MsFile->resizeImage($seller['ms.avatar'], $this->config->get('msconf_seller_avatar_product_page_image_width'), $this->config->get('msconf_seller_avatar_product_page_image_height'));
                    } else {
                        $this->data['seller']['thumb'] = $this->MsLoader->MsFile->resizeImage('ms_no_image.jpg', $this->config->get('msconf_seller_avatar_product_page_image_width'), $this->config->get('msconf_seller_avatar_product_page_image_height'));
					}

					if($this->config->get("msconf_show_country") === null || $this->config->get("msconf_show_country") == 1){
						$show_seller_country = true;
					} else {
                    	$show_seller_country = false;
					}

                    $country = $show_seller_country ? $this->model_localisation_country->getCountry($seller['ms.country_id']) : null;

                    if (!empty($country)) {
                        $this->data['seller']['country'] = $country['name'];
                    } else {
                        $this->data['seller']['country'] = NULL;
					}

					if($this->config->get("msconf_show_city") === null || $this->config->get("msconf_show_city") == 1){
						$show_seller_city = true;
					} else {
                    	$show_seller_city = false;
					}

                    $zone = $show_seller_city ? $this->model_localisation_zone->getZone($seller['ms.zone_id']) : null;

                    if (!empty($zone)) {
                        $this->data['seller']['zone'] = $zone['name'];
                    } else {
                        $this->data['seller']['zone'] = NULL;
                    }

                    if($this->config->get("msconf_show_seller_company") === null || $this->config->get("msconf_show_seller_company") == 1){
						$show_seller_company = true;
					}
                    else{
                    	$show_seller_company = false;
					}

                    if (!empty($seller['ms.company']) && $show_seller_company) {
                        $this->data['seller']['company'] = $seller['ms.company'];
                    } else {
                        $this->data['seller']['company'] = NULL;
                    }

                    if (!empty($seller['ms.website'])) {
                        $this->data['seller']['website'] = $seller['ms.website'];
                    } else {
                        $this->data['seller']['website'] = NULL;
                    }

                    $this->data['seller']['nickname'] = $seller['ms.nickname'];
                    $this->data['seller']['seller_id'] = $seller['seller_id'];

                    $this->data['seller']['href'] = $this->url->link('seller/catalog-seller/profile', 'seller_id=' . $seller['seller_id']);

                    $this->data['seller']['total_sales'] = $this->MsLoader->MsSeller->getSalesForSeller($seller['seller_id']);
                    $this->data['seller']['total_products'] = $this->MsLoader->MsProduct->getTotalProducts(array(
                        'seller_id' => $seller['seller_id'],
                        'product_status' => array(MsProduct::STATUS_ACTIVE)
                    ));

                    ///Custom Fields Data
                    if (!empty($seller['ms.custom_fields'])) {
                    	$seller_custom_fields = unserialize($seller['ms.custom_fields']);


                    	$data_custom_fields = $this->config->get('msconf_seller_data_custom');
						$config_language_id = $this->config->get('config_language_id');

                    	$custom_fields = [];
                    	foreach ($seller_custom_fields as $key => $field) {
                    		$custom_fields[] = ['label' => $data_custom_fields[$key]['title'][$config_language_id], 'value' => $field ];
                    	}

                    	if(count($custom_fields)){
                    		$this->data['seller']['is_custom_fields'] = true;
                    		$this->data['seller']['custom_fields'] = $custom_fields;
                    	}

                    }
                }

                $this->data['ms_product_attributes'] = $this->MsLoader->MsAttribute->getProductAttributes($this->request->get['product_id'], array('multilang' => 0, 'attribute_type'=> array(MsAttribute::TYPE_TEXT, MsAttribute::TYPE_TEXTAREA, MsAttribute::TYPE_DATE, MsAttribute::TYPE_DATETIME, MsAttribute::TYPE_TIME), 'mavd.language_id' => 0));
                $this->data['ms_product_attributes'] = array_merge($this->data['ms_product_attributes'], $this->MsLoader->MsAttribute->getProductAttributes($this->request->get['product_id'], (array())));

                $lang_id = $this->config->get('config_language_id');
				// $seller_title = $this->config->get('msconf_seller_title');
				// $this->data['seller_title']  = $this->language->get('Seller_Info');

 				$this->load->model('seller/seller');
                $seller_title = $this->model_seller_seller->getSellerTitle();
				$this->data['seller_title']  = sprintf($this->language->get('Seller_Info'), $seller_title);


			    //Seller-location data
				if ($seller){
					$this->data['seller']['location'] = $seller['ms.seller_location'];
				}
	        	$this->data['google_api_key'] = $this->config->get('msconf_seller_google_api_key');
            }

			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $product_info['name'],
				'href'      => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id']),
				'separator' => $this->language->get('text_separator')
			);

            $this->document->setTitle($product_info['name']);

            if ($product_info['price'] > 0) {
                $savingAmount = round((($product_info['price'] - $product_info['special'])/$product_info['price'])*100, 0);
            }
            else {
                $savingAmount = 0;
            }

            $this->data['saving'] = $savingAmount;

            $this->data['price_meter_data'] = $product_info['price_meter_data'];


            // Load auto meta tags module
            $this->load->model('module/auto_meta_tags');
            // grab auto meta tags settings
            $auto_meta_tags_module_status = $this->model_module_auto_meta_tags->get_setting_by_key('auto_meta_tags_enable_module');

			$this->data['show_meta_tags'] = $this->config->get('show_meta_tag');
			$this->data['show_meta_desc'] = ($this->config->get('show_meta_desc') !== NULL ) ? $this->config->get('show_meta_desc') : 1;

            if ( $auto_meta_tags_module_status == '1' )
            {
				// set description
				$meta_description = $this->model_module_auto_meta_tags->generate_description($product_info);
				$this->document->setDescription($meta_description);
				$meta_keywords = $product_info['tag'] = $this->model_module_auto_meta_tags->generate_meta_keywords($product_info);
				$this->document->setKeywords($meta_keywords);

			} else {
                $this->document->setDescription($product_info['meta_description']);
                $this->document->setKeywords($product_info['tag']);
            }

			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			//$this->document->addScript('expandish/view/javascript/jquery/tabs.js');
			//$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
			//$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

			//$this->data['heading_title'] = $product_info['name'];

			//$this->data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);

			$this->load->model('catalog/review');
			$this->load->model('multiseller/status');
	        $multiseller = $this->model_multiseller_status->is_installed();
            if($multiseller) {
				//Get Seller title from setting table
				$this->load->model('seller/seller');
				$seller_title = $this->model_seller_seller->getSellerTitle();
				$product_title = $this->model_seller_seller->getProductTitle();
				$products_title = $this->model_seller_seller->getProductsTitle();

				//$this->data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

				$this->data['seller_info'] = sprintf($this->language->get('Seller_Info'), $seller_title);

				$this->data['seller_notes'] = $product_info['seller_notes'];
			}

			$this->data['product_id'] = $this->request->get['product_id'];
			$this->data['product_name'] = $product_info['name'];
			$this->data['sku'] = $product_info['sku'];
            $this->data['date_available'] = $product_info['date_available'];
			$this->data['manufacturer_id'] = $product_info['manufacturer_id'];
			$this->data['manufacturer'] = $product_info['manufacturer'];
			$this->data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
            $this->data['manufacturerimg'] = $product_info['manufacturerimg'];
			$this->data['manufacturer_href'] = $this->url->link(
				'product/manufacturer/info',
				'manufacturer_id=' . $product_info['manufacturer_id']
			);
			$this->data['model'] = $product_info['model'];
			$this->data['reward'] = $product_info['reward'];
			$this->data['points'] = $product_info['points'];
			$this->data['preparation_days'] = $product_info['preparation_days'];
			$this->data['general_use'] = $product_info['general_use'] ?? '';
			$this->data['quantity'] = $product_info['quantity'];
			$this->data['unlimited'] = $product_info['unlimited'];
			
			if (!isset($this->session->data['wishlist'])) {
                $this->session->data['wishlist'] = array();
            }
            $this->data['in_wishlist'] = in_array($this->request->get['product_id'], $this->session->data['wishlist']) ? 1 : 0;

            $cart_products = [];
            foreach ($this->session->data['cart'] as $key => $quantity) {
				$p = explode(':', $key);
				if(isset($cart_products[$p[0]])){
					$cart_products[$p[0]] += $quantity;
					continue;
				}
				$cart_products[$p[0]] = $quantity;
			}
            $this->data['in_cart'] = array_key_exists($this->request->get['product_id'], $cart_products) ? $cart_products[$this->request->get['product_id']] : 0;

			$this->data['viewProductBundles'] = false;
			// check product bundles installed or not, and also the product is not added is a bundle before
	        $this->load->model('module/product_bundles/settings');
			if($this->model_module_product_bundles_settings->isActive()){
				$this->data['viewProductBundles'] = true;
				// get the product bundles
				$this->data['product_bundles'] = $this->model_catalog_product->getProductBundles($product_info['product_id']);
				// $this->data['product_bundles']['bundle_discount'] // this attribute holds the discount percentage for each product bundle
			}

			///check permissions to view Add to Cart Button
			$this->data['viewAddToCart'] = true;

			$hidCartConfig = $this->config->get('config_hide_add_to_cart');

			if(($product_info['quantity'] <=0 && $hidCartConfig) || !$this->customer->isCustomerAllowedToAdd_cart)
			{
				// Add to Cart Button
				$this->data['viewAddToCart'] = false;
				// add product bundles Button
			    $this->data['viewProductBundles'] = false;
			}

			if($this->customer->isLogged())
			{
				$this->data['customer_name'] = $this->customer->getFirstName()." ".$this->customer->getLastName();
				$customer_id = $this->customer->getId();
			}else{
				$customer_id = 0;
			}

			// check if game ball app installed
			if(\Extension::isInstalled('gameball')){
				$this->load->model('module/gameball/settings');
				// check if app status is active
				if($this->model_module_gameball_settings->isActive()){

					// get product categories
					$productCategories = $this->model_catalog_product->getCategoriesWithDesc($this->request->get['product_id']);
					$productCategoriesArray = [];
					if(is_array($productCategories)){
						foreach ($productCategories as $key=>$category){
							foreach ($category['category_description'] as $category_description)
								$productCategoriesArray[] = $category_description['name'];
						}
					}

					$eventData['events']['view_product']['product_id'] = (string)$this->request->get['product_id'];
					$eventData['events']['view_product']['brand'] = (string)$product_info['manufacturer'];
					$eventData['events']['view_product']['category'] = (string)implode(",",$productCategoriesArray);
					$eventData['events']['view_product']['price'] = (float)$product_info['price'];
					$eventData['events']['view_product']['stock'] = (string)$product_info['quantity'];
					$eventData['events']['view_product']['weight'] = (float)$product_info['weight'];
					$eventData['playerUniqueId'] = $customer_id;
					$this->model_module_gameball_settings->sendGameballEvent($eventData);
				}
			}

			$langCode = strtolower( $this->language->get('code') );
			if ( $langCode != 'en' ) {
				$productModelText = $this->config->get('text_product_model_' . $langCode);
			} else {
				$productModelText = $this->config->get('text_product_model');
			}

			$this->data['text_product_model'] = ! empty( $productModelText ) ? $productModelText . ':' : sprintf( $this->language->get('text_model') , $product_title);

			$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

			if($queryRewardPointInstalled->num_rows) {
				$this->load->model('rewardpoints/catalogrule');
				$setting = $this->model_setting_setting->getSetting('reward_points');
				$this->data['show_point_detail'] = $setting['show_point_detail'];
				$this->data['reward']=$this->model_rewardpoints_catalogrule->getPoints($this->request->get['product_id']);
			}


            $this->load->model('module/rental_products/settings');

            // check if rental app is active then get disabled days to disable them at the datepicker calendar at product view
            if ($this->model_module_rental_products_settings->isActive()) {
                $this->data['rentalProducts'] = 1;
                $this->data['transaction_type'] = $product_info['transaction_type'];
                $rentalProducts = $this->model_module_rental_products_settings->getSettings();
                $this->data['max_rental_days'] = (
                    $rentalProducts['max_rental_days'] > 0 ? $rentalProducts['max_rental_days'] : false
                );
                $from = time();
                $to = $from + ($rentalProducts['max_rental_days'] * 24 * 60 * 60) ;
                // return disabled_days in json format to be used in datepicker js view code
                $this->data['disabled_days'] = json_encode($this->model_catalog_product->getRentDisabledDates($from,$to,$product_id,$this->data['quantity']));

            } else {
                $this->data['rentalProducts'] = 0;
            }

            $this->data['language_id'] = $this->config->get('config_language_id');

            //Printing document
            $this->load->model('module/printing_document');
            if ($this->model_module_printing_document->isActive()) {
            	$this->data['printingDocument'] = 1;
            	$covers = $this->model_module_printing_document->getSettings();
            	$this->data['covers'] = count($covers) ? $covers['cover'] : 0;

            }else {
                $this->data['printingDocument'] = 0;
            }
            $this->data['printable'] = $product_info['printable'];

            //Price/meter
            $this->load->model('module/price_per_meter/settings');
            if ($this->model_module_price_per_meter_settings->isActive()) {
            	$this->data['pricePerMeters'] = 1;
				$pricePerMeters = $this->model_module_price_per_meter_settings->getSettings();
				$this->data['price_per_meter_settings'] = $pricePerMeters;
            }else {
                $this->data['pricePerMeters'] = 0;
			}

            //Fifa Cards
			$this->load->model('module/fifa_cards/settings');
			if ($this->model_module_fifa_cards_settings->isActive())
			{
				$this->data['fifaCards'] = 1;
				$fifaCards = $this->model_module_fifa_cards_settings->getSettings();
				$this->data['fifa_cards_settings'] = $fifaCards;

				$this->load->model('module/fifa_cards/fifa_cards_clubs');
				$this->data['fifa_clubs'] = $this->model_module_fifa_cards_fifa_cards_clubs->getClubs($this->config->get('config_language_id'));

				$this->load->model('localisation/country');
				$this->data['countries'] = $this->model_localisation_country->getCountries($this->config->get('config_language_id'));
			}
			else
			{
				$this->data['fifaCards'] = 0;
			}

			// minimum deposit
			$this->load->model('module/minimum_deposit/settings');

			$this->data['minimum_deposit_status']  = $this->model_module_minimum_deposit_settings->isActive();
			
			if ($this->data['minimum_deposit_status']) {
				$this->data['minimum_deposit_setting'] = $this->model_module_minimum_deposit_settings->getSettings();
				if ($this->data['minimum_deposit_setting']['md_status_deposit'] == 1) {
					$this->data['minimum_deposit_price_float']= (float)$product_info['minimum_deposit_price'];
					$this->data['minimum_deposit_price'] = $this->currency->format($product_info['minimum_deposit_price']);
					$this->data['minimum_deposit_price_model'] = $product_info['minimum_deposit_price'];
				} else {
					$this->data['minimum_deposit_price_float']= (float)$this->data['minimum_deposit_setting']['md_general_input_price'];
					$this->data['minimum_deposit_price'] = $this->currency->format($this->data['minimum_deposit_setting']['md_general_input_price']);
					$this->data['minimum_deposit_price_model'] = $this->currency->format($this->data['minimum_deposit_setting']['md_general_input_price']);
				}
			}

			// Curtain Seller
			$this->load->model("module/curtain_seller");

			if ($this->model_module_curtain_seller->isEnabled()) {
				$this->data['curtain_seller'] = $this->model_module_curtain_seller->getSettings();
				$curtain_seller_product_settings = $product_info['price_meter_data']['curtain_seller'];
				if (! empty($curtain_seller_product_settings) && $curtain_seller_product_settings['status'] == '1') {
					$this->data['curtain_seller']['product'] = $curtain_seller_product_settings;
					$this->data['curtain_seller']['isEnabled'] = true;
					$this->data['curtain_seller_calculate_cost_link'] = $this->url->link('product/product/calculateCurtainSellerCost', '', 'SSL');
				}
			}

            //Sales Booster
            $this->load->model('module/sales_booster');
            $selfInfo = false;
        	if($product_info['sls_bstr']){
        		$selfInfo = true;
        		$sls_bstrData = json_decode($product_info['sls_bstr'], true);
        	}
            if ($this->model_module_sales_booster->isActive() && ($this->model_module_sales_booster->isForceApply() || $sls_bstrData['status'] == '1') && $product_info['quantity']) {

            	$this->data['salesBooster'] = 1;
            	$sales_booster = $this->model_module_sales_booster->getSettings();

            	//Validate Video URL
            	if($sales_booster['video_url'] && $this->validateVideo($sales_booster['video_url'])){
	            	$vid = str_replace('watch?v=', 'embed/', $sales_booster['video_url']);
	    			$vid = str_replace('youtu.be', 'youtube.com/embed', $vid);
	    			$sales_booster['video_url'] = $vid;
	    		}
            	if(!empty($sls_bstrData['sound'])){
                    
                        // here we using str_replace function to make sound url work
	    		$sales_booster['sound_url'] = str_replace([' %25 ','0&26','26','%&'], ['%','&','&','&'],$sls_bstrData['sound']);
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

            	$this->data['sales_booster'] = $sales_booster;
            }else {
                $this->data['salesBooster'] = 0;
            }

            ////// Prize Draw
			$this->load->model('module/prize_draw');
            if($this->model_module_prize_draw->isActive()){
            	$product_prize = $this->model_module_prize_draw->getProductPrize($product_info['product_id']);
            	if($product_prize){
					$this->data['prize_draw'] = true;

					if($product_prize['image'])
						$product_prize['image'] = $this->model_tool_image->resize($product_prize['image'], 500, 500);

					$this->data['prize_draw_data'] = $product_prize;
				}

				// Calculate product consuming in percentage
				$ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product_info['product_id']);

            	$consumed_percentage = ( ((int)$ordered_count * 100) / (int)$product_info['quantity'] );
				$this->data['consumed_percentage'] = $consumed_percentage <= 100 ? $consumed_percentage : 100;
			}

            ////////////////////////////// Gold App
			$this->initializer(['module/gold']);
			if($this->gold->isActive()){
				$product_caliber = $this->gold->getProductCaliber($product_info['product_id']);

				if($product_caliber){
					$this->data['gold_app'] = true;
					$this->data['gold_settings'] = $this->gold->getSettings();

					$this->data['gold_product_caliber'] = [
						'caliber'       => $product_caliber['caliber'],
						'price'         => $this->currency->format( $product_caliber['price']),
						'manuf_price'   => $this->currency->format( $product_caliber['manuf_price']),
						'weight'        => $product_info['weight']
					];
				}
			}
			////////////////////////////////////////

			$this->data['product_designer'] = 0;

			if ($tshirt_module = $this->config->get('tshirt_module')) {
				$this->data['pd_custom_price']  = $product_info['pd_custom_price'];
				$this->data['pd_is_customize']  = $product_info['pd_is_customize'];
				$this->data['pd_custom_min_qty'] = $product_info['pd_custom_min_qty'];
				$this->data['pd_back_image'] = $product_info['pd_back_image'];
				$this->data['pd_options'] = $tshirt_module;

				if ($product_info['pd_is_customize'] == 1) {
					$this->data['product_designer'] = $tshirt_module['pd_status'];
					$this->data['tshirt_module'] = $tshirt_module;
				}
			}


            $statuses = $this->config->get("config_stock_status_display_badge");

			if ($product_info['quantity'] <= 0) {
				$this->data['stock'] = $product_info['stock_status'];
				 $this->data['stock_status'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				if($product_info['unlimited']){
	                $this->data['stock'] = $this->language->get('text_unlimited_quantity');
	                $this->data['stock_status'] = $this->language->get('text_unlimited_quantity');
				}
				else{
					$this->data['stock'] = $product_info['quantity'];
	                $this->data['stock_status'] = $product_info['quantity'];
				}
			} else {
				$this->data['stock'] = $this->language->get('text_instock');
			}

			$this->data['show_stock_label'] = false;
			if(in_array($product_info['stock_status_id'],$statuses)){
				$this->data['show_stock_label'] = true;
			}

			//Product Status Color
			$this->data['is_custom_stock_status_colors'] = $this->config->get('config_custom_stock_status_colors');
			$this->data['stock_status_id'] = $product_info['stock_status_id'];

			// set background color of stock status depend on stock status setting not product

			if($this->config->get('config_instock_status_id')){
				if($product_info['quantity'] > 0){
					$this->data['stock_status_color'] = $this->model_setting_setting->getStockStatus($this->config->get('config_instock_status_id'))['current_color'];
				}else{
					$this->data['stock_status_color'] = $this->model_setting_setting->getStockStatus($this->data['stock_status_id'])['current_color'];
				}
			}else{
				$this->data['stock_status_color'] = "#0aac2e";
			} 
			if($product_info['quantity'] <= 0){

				///Notify me when available App
				$this->load->model('module/product_notify_me');
				if($this->model_module_product_notify_me->isActive())
					$this->data['product_notify_me']  = true;
				//////////////////////////////

			}

			$this->load->model('tool/image');

			if ( ($product_info['image'] == NULL || $product_info['image']=="image/no_image.jpg") && file_exists(' ' . $this->config->get('product_image_without_image')) . ' '   ){
				$this->data['product_image_without_image'] =  TRUE;
				$this->data['image'] =  $this->config->get('product_image_without_image');
			}elseif ($product_info['image']) {
                $this->data['image'] = $product_info['image'];
				$this->data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height'));
                $this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_thumb_width'), $this->config->get('config_image_thumb_height'));
			} else {
                $this->data['image'] = '';
				$this->data['popup'] = '';
                $this->data['thumb'] = '';
			}

			$this->data['images'] = array();

            $product_info['product_images'] = json_decode($product_info['product_images'], true);

            // $results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
            $results = $product_info['product_images'];

            // Product Option Image PRO module <<
            // $this->request->get['pid'] - journal2 quickview
            $this->data['option_images'] = array();
            $poip_product_id = !(isset($this->request->get['product_id'])) ? $this->request->get['pid'] : $this->request->get['product_id'];

            $this->load->model('module/product_option_image_pro');
            $this->data['option_images']['enabled'] = $this->model_module_product_option_image_pro->installed();

            //$this->data['current_class'] = 'related_products';
            if ($this->data['option_images']['enabled']) {
                $this->data['option_images']['product_settings'] = $this->model_module_product_option_image_pro->getProductSettings($poip_product_id);

				$this->data['option_images']['settings'] = $this->model_module_product_option_image_pro->getSettings();

                $product_images = array();
                $results = $this->model_module_product_option_image_pro->addOptionImagesToProductImages($results, $poip_product_id, $product_images);
                $this->data['option_images']['images'] = $product_images;
                $this->data['option_images']['product_option_ids'] = $this->model_module_product_option_image_pro->getProductOptionsIdsWithImages($product_images);
                $this->data['option_images']['images_by_options'] = $this->model_module_product_option_image_pro->getProductOptionImagesByValues($poip_product_id);

				foreach ($this->data['option_images']['images_by_options'] as $key => $image) {
					# code...
					$this->data['option_images']['images_by_options'][$key][0]['thumb']=$this->model_tool_image->resize($image[0]['thumb'],50,50);
					$this->data['option_images']['images_by_options'][$key][0]['image']=$this->model_tool_image->resize($image[0]['image'],916,1150);
					$this->data['option_images']['images_by_options'][$key][0]['image_zoom']=$this->model_tool_image->resize($image[0]['image'],1000,1255);

				}

                if ( isset($_SERVER['REQUEST_URI']) ) {
                    $poip_ov_name = "&amp;poip_ov=";
                    if (strpos($_SERVER['REQUEST_URI'], $poip_ov_name) !== false) {
                        $poip_str = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $poip_ov_name)+strlen($poip_ov_name));
                        $poip_ov = "";
                        while ($poip_str != "" && strpos("0123456789", substr($poip_str, 0, 1))!==false ) {
                            $poip_ov.= substr($poip_str, 0, 1);
                            $poip_str = substr($poip_str, 1);
                        }
                        if ($poip_ov != "") {
                            $this->data['option_images']['ov'] = $poip_ov;
                        }
                    }
                }

                // не работает на mijoshop
                //$this->data['poip_ov'] = isset($this->request->get['poip_ov']) ? (int)$this->request->get['poip_ov'] : false;

            }
            //>> Product Option Image PRO module

			foreach ($results as $result) {
				$this->data['images'][] = array(
                    'title' => isset($result['title']) ? $result['title'] : '',
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width'), $this->config->get('config_image_additional_height')),
					'small_slider' => $this->model_tool_image->resize($result['image'], 200,200),
                    'image' => $result['image'],
				);
			}
			$config_tax = $this->config->get('config_tax');

			// Check if Taxes text should display or not
			$this->data['price_without_tax'] = false;
			if($config_tax == "price"){
				$this->data['price_without_tax'] = true;
			}

			$this->data['cnf_customer_price'] = false;
			$cnf_customer_price = $this->config->get('config_customer_price');
			if (($cnf_customer_price && !$this->customer->isLogged())) {
				$this->data['cnf_customer_price'] = true;
			}

			if ($isCustomerAllowedToViewPrice) {
                if ( (int) $product_info['price'] === -1 ) {
                    $this->data['price']="-1";
                }else {
                    if ( $config_tax == "price" ) {
                        $this->data['price'] = $this->currency->format( $product_info['price'] );
                    } else {
                        $this->data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $config_tax));
                    }
					$this->data['price_value'] = $this->currency->currentValue($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $config_tax));
                    $this->data['rental_price'] = $this->currency->format($product_info['price'], '', '', false);
					$this->data['price_number'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $config_tax);
                }
			} else {

				$this->data['price'] = false;
				$this->data['rental_price'] = false;
                $this->data['price_number'] = false;
			}
           
			if ($isCustomerAllowedToViewPrice){
				if (isset($product_info['special']) && (float)$product_info['special'] > 0) {
					$product_tax = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $config_tax);
					$this->data['special'] = $this->currency->format($product_tax);
					$this->data['special_value'] = $this->currency->currentValue($product_tax);
					$this->data['rental_special'] = $this->currency->format($product_info['special'], '', '', false);
	                $this->data['special_number'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $config_tax);
					$this->data['original_price'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $config_tax);
				}
			} else {
				$this->data['special'] = false;
				$this->data['rental_special'] = false;
                $this->data['special_number'] = false;
			}

			$this->data['currency_decimal_places'] = $this->currency->getDecimalPlace() ?? 2;
			$this->data['currency_code'] = $this->currency->getCode();
			$this->data['currency_symbols'] = [
			    'left' => $this->currency->getSymbolLeft(),
                'right' => $this->currency->getSymbolRight()
            ];

            if ($isCustomerAllowedToViewPrice) {
				if (!empty($config_tax) && $config_tax != "price") {
					$this->data['tax'] = $this->currency->format( (isset($product_info['special']) && (float)$product_info['special'] >= 0) ? $product_info['special'] : $product_info['price']);
					$this->data['tax_value'] = $this->currency->currentValue((isset($product_info['special']) && (float)$product_info['special'] >= 0) ? $product_info['special'] : $product_info['price']);
					$this->data['tax_included_val'] = $config_tax == "price_with_tax_text" ? true:false;
					$this->data['tax_included_text'] = $this->language->get('text_included_tax');
				}
			} else {
				$this->data['tax'] = false;
			}
			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$this->data['discounts'] = array();

			if ($isCustomerAllowedToViewPrice) {
				foreach ($discounts as $discount) {
					$this->data['discounts'][] = array(
						'quantity' => $discount['quantity'],
						'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $config_tax))
					);
				}
			}

			$this->data['options'] = array();
			$show_outofstock_option = $this->config->get('show_outofstock_option');


			// check if knawat product
			$this->data['is_knawat_product'] = $this->model_catalog_product->isKnawatProduct($this->request->get['product_id']);

			/**
			 * is_knawat_product is accept result of productsoptions_sku if install until add this variable in all themes and templates
			 * $this->data['is_productsoptions_sku'] = \Extension::isInstalled('productsoptions_sku');
			 */
			$this->data['is_knawat_product'] = ($this->data['is_knawat_product']) ?: (\Extension::isInstalled('productsoptions_sku') && $this->config->get('productsoptions_sku_status'));

			$optionImage_width  = $this->config->get('product_option_image_width') ? $this->config->get('product_option_image_width') : 50;
			$optionImage_height = $this->config->get('product_option_image_height') ? $this->config->get('product_option_image_height') : 50;

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image' || $option['type'] == 'product') {
					$option_value_data = array();

					foreach ($option['option_value'] as $option_value) {
						if (
								!$option_value['subtract']
								||
								($option_value['subtract'] && $show_outofstock_option)
								||
								($option_value['subtract'] && !$show_outofstock_option && $option_value['quantity'] > 0)
							) {
							//Applying Mass Update App discounts to product options. disable for EC-46347
/*							if($product_info['special_discount_type'])
							{	
							switch ($product_info['special_discount_type']) 
							{ 
								case "flat": // flat price
									$option_price = $option_value['price'];
									if($option_price > $product_info['special_discount_value'])
									{
										$option_price = $product_info['special_discount_value'];
									}
									break;

								case "sub": // Subtraction of base price
									$option_price = (float) $option_value['price'] - (float) $product_info['special_discount_value'];
									break;

								case "per": // percentage of base price
									$option_price = (float) $option_value['price'] * (1 - (float) $product_info['special_discount_value'] / 100);
									break;
							}
						   }else{
							$option_price = $option_value['price']; 
						   }*/
						   
							$option_price_without_tax = $option_to_apply = $this->currency->currentValue($option_value['price']);
							$option_original_price_without_tax = $option_original_price = $this->currency->currentValue($option_value['price']);
							$price = $this->currency->format($option_value['price']);

							if ( $isCustomerAllowedToViewPrice && (float)$option_value['price']) {
								if ($config_tax !== "price") {
									$option_to_apply = $this->tax->calculate($option_price_without_tax, $product_info['tax_class_id'], $config_tax);
									$option_original_price = $this->tax->calculate($option_original_price_without_tax, $product_info['tax_class_id'], $config_tax);
									$price = $this->currency->format($option_to_apply);
								}
							} else {
								$price = false;
							}
							$option_price = $option_to_apply."|".$option_price_without_tax."|".$option_original_price."|".$option_original_price_without_tax;

							$product_option_image = $this->model_tool_image->resize($option_value['image'], $optionImage_width, $optionImage_height);
							$custom_product_info = $this->model_catalog_product->getProduct($option_value['valuable_id']);
							if($option['type'] == 'product' && !$this->model_tool_image->imageExist($option_value['image'])){
								$product_option_image = $this->model_tool_image->resize($custom_product_info['image'], $optionImage_width, $optionImage_height);
								if(!$this->model_tool_image->imageExist($custom_product_info['image'])){
									$product_option_image = $this->model_tool_image->resize($this->config->get('no_image'),$optionImage_width,$optionImage_height);
								}
							}
							$product_option_Link = false;
							if ( isset($custom_product_info) && $custom_product_info != false ){
								$product_option_Link = $this->url->link('product/product', 'product_id=' . $custom_product_info['product_id']);
							}
							$option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $product_option_image,
								'price'                   => $price,
								'price_value'             => $option_price,
								'price_prefix'            => $option_value['price_prefix'],
								'price_to_apply'			=> 	$option_to_apply,
								'quantity' 				  => $option_value['quantity'],
								'product_option_Link'	  => $product_option_Link
							);
						}
					}

					$this->data['options'][] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option_value_data,
						'required'          => $option['required'],
						'custom_option'		=> $option['custom_option'] ?: ""

					);

				} elseif ($option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$this->data['options'][] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option['option_value'],
						'required'          => $option['required'],
						'custom_option'		=> $option['custom_option'] ?: ""
					);
				}
			}
			////////////////////////////////
			//Option sku relational options
			////////////////////////////////

			if ($this->config->get('productsoptions_sku_status')) {
				$this->data['opt_relational_status'] = 0;
				$this->data['productVariationSku'] = $productVariationSku = $this->model_catalog_product->getProductVariationSkuById($this->request->get['product_id']);

				if (
					$this->config->get('productsoptions_sku_relational_status')
					&& count($productVariationSku)
				) {
					$optionsMapping = $this->config->get('productsoptions_sku_option_mapping');

					if (count($optionsMapping)) {
						$relationalOptions = [];
						foreach ($optionsMapping as $mapp) {
							if($mapp['parent']!="" && $mapp['child']!="")
							$relationalOptions[$mapp['parent']] = $mapp['child'];
						}

						//Get all parents of option
						$elementsParents = [];
						$reverse = array_reverse($relationalOptions, true);

						foreach ($reverse as $parent => $child) {
							$selfOptId = $child;
							foreach ($reverse as $parent2 => $child2) {
								if ($child2 == $selfOptId) {
									$elementsParents[$child] .= $parent2 . ',';
								}
								$selfOptId = $parent2;
							}
						}
						//////////////////////

						$this->data['opt_relational_status'] = 1;
						$this->data['opt_relational_relations'] = $relationalOptions;
						$this->data['opt_relational_parents'] = array_keys($relationalOptions);
						$this->data['opt_relational_childs'] = array_values($relationalOptions);
						$this->data['opt_elements_parents'] = $elementsParents;

						$product_options = array_column($this->data['options'], 'option_id');
						foreach ($relationalOptions as $parent => $child) {
							$child_key = array_search($child, $this->data['opt_relational_childs']);
							if (array_search($parent, $product_options) === false &&  $child_key !== false) {
								unset($this->data['opt_relational_childs'][$child_key]);
							}
						}
					}
				}
			}
			////////////////////////////////
			//End Option sku relational options
			////////////////////////////////

			if ($product_info['minimum']) {
				$this->data['minimum_limit'] = $product_info['minimum'];
			} else {
				$this->data['minimum_limit'] = 1;
			}

			$this->data['review_status'] = $this->config->get('config_review_status');
			//$this->data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
            $this->data['reviews_count'] = (int)$product_info['reviews'];
			$this->data['rating'] = (int)$product_info['rating'];
			$this->data['customer_can_rate'] = true;
			if (!$this->config->get('store_reviews_app_allow_guest') && !$this->customer->getId()) {
				$this->data['customer_can_rate'] = false;
			}
			$this->data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			$this->data['og_description'] = preg_replace('/\s+/', ' ',preg_replace('#<[^>]+>#', ' ', html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')));
			
			$this->data['sub_description'] = substr(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),0,500);
            $this->data['short_description'] = $product_info['meta_description'];

			// $this->data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
			$attribute_groups = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
			$isAliExpressProduct = $this->model_catalog_product->is_aliexpress_product($this->request->get['product_id']);
			if (!$isAliExpressProduct){
				// exclude any related aliexpress attribute
				foreach ($attribute_groups as $key=> $attribute){
					if (strtolower($attribute['name']) == 'aliexpress'){
						unset($attribute_groups[$key]);
					}
				}
			}
			$this->load->model('module/advanced_product_attributes');
			$advanced_product_attributes_installed = $this->model_module_advanced_product_attributes->isInstalled();
			if($advanced_product_attributes_installed){
				// $this->data['advanced_attributes'] = $this->model_module_advanced_product_attributes->getProductAttributes($this->request->get['product_id']);
				$advanced_attributes = $this->model_module_advanced_product_attributes->getProductAttributes($this->request->get['product_id']);
			}

			$this->data['advanced_product_attributes_installed'] = $advanced_product_attributes_installed;
			//If any other theme than Carshop, then merge attributes
			if($this->config->get('config_template') != 'carshop'){
				//Merging Normal Attributes with Advanced Attributes to appear together in same place in the View template
				if(count($attribute_groups) > 0){
					for($i = 0; $i < count($attribute_groups); $i++){
						if($attribute_groups[$i]['attribute_group_id'] === $advanced_attributes[$i]['attribute_group_id']){
							$attribute_groups[$i]['attribute'] = array_merge($attribute_groups[$i]['attribute'], $advanced_attributes[$i]['attribute']);
						}
					}
				}
				else{
					$attribute_groups = $advanced_attributes;
				}

				$this->data['attribute_groups'] = $attribute_groups;
			}
			//Else put them in separate arrays
			else{
				$this->data['attribute_groups'] = $attribute_groups;
				$this->data['advanced_attribute_groups'] = $advanced_attributes;
			}




			$this->load->model('catalog/length_class');
			$length_info = $this->model_catalog_length_class->getLengthClass($product_info['length_class_id']);

			$this->data['length'] = number_format($product_info['length'], 2, '.', '');
			$this->data['width'] = number_format($product_info['width'], 2, '.', '');
			$this->data['height'] = number_format($product_info['height'], 2, '.', '');
			$this->data['length_class'] = $length_info['title'];

			$this->data['related_products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			$this->load->model('module/rotate360');
			$rotate360Installed = $this->model_module_rotate360->installed();

			if ($rotate360Installed) {
				$this->data['rotate360']['settings'] = $this->model_setting_setting->getSetting("rotate360");
				$this->data['rotate360']['images'] = $images = $this->model_module_rotate360->getImagesByProductId($this->request->get['product_id']);
				$mod_images = [];
				foreach ( $images as $image){
					$image['image_path'] =  \Filesystem::getUrl('image/' . $image['image_path']);
					$mod_images[] = $image;
				}
				$this->data['rotate360']['images'] = $mod_images;
				if ($this->data['rotate360']['settings']['rotate360_enable_module'] == 1 && $images) {
					$this->data['rotate360'] = json_encode($this->data['rotate360']);
				} else {
					$this->data['rotate360'] = 0;
				}

			}


			if (count($results) == 0) {
                $this->load->model('module/related_products');
                $relatedProducts = $this->model_module_related_products;

                if ($relatedProducts->isActive()) {

                    $relatedProductsCatalog['categories'] = $relatedProducts->getRelatedProductsByCategory(
                        $this->request->get['product_id']
                    );

                    $relatedProductsCatalog['manufacturers'] = $relatedProducts->getRelatedProductsByManufacturer(
                        $this->data['manufacturer_id'],
                        $this->request->get['product_id']
                    );

                    $relatedProductsCatalog['keywords'] = $relatedProducts->getRelatedProductsByKeyword(
                        $product_info['tag'],
                        $this->request->get['product_id']
                    );

					$results = $relatedProducts->mergeCatalog($relatedProductsCatalog);

                }
            }

			foreach ($results as $result) {
				if (!isset($result['product_id'])) {
					continue;
				}
                $prod_info = $this->model_catalog_product->getProduct($result['product_id']);

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_related_width'), $this->config->get('config_image_related_height'));
				} else {
					$image = false;
				}

				if ($isCustomerAllowedToViewPrice){
					$price = $this->currency->format($this->tax->calculate($prod_info['price'], $prod_info['tax_class_id'], $config_tax));
				} else {
					$price = false;
				}

				if ( (float) $prod_info['special'] && $isCustomerAllowedToViewPrice)
                {
					$special = $this->currency->format($this->tax->calculate($prod_info['special'], $prod_info['tax_class_id'], $config_tax));
					$savingAmount = round((($prod_info['price'] - $prod_info['special'])/$prod_info['price'])*100, 0);
				}
                else
                {
					$special = false;
				}

                if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
                } else {
					$rating = false;
				}

				if ($multiseller){
					$relatedProductsSeller = false;
					$relatedProductsseller_id = $this->MsLoader->MsProduct->getSellerId($result['product_id']);
					$relatedProductsSeller    = $this->MsLoader->MsSeller->getSeller($relatedProductsseller_id);
					//Check if MS Messaging seller, Replace Add To Cart installed
					$relatedProductsmultisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
					$relatedProductsmultisellerReplcAddtoCart = $this->model_multiseller_status->is_replace_addtocart();
					if($relatedProductsmultisellerMessaging && $relatedProductsmultisellerReplcAddtoCart && $relatedProductsseller_id != (int)$this->customer->getId() )
						$relatedProductsmessaging= true;

				}

				$this->data['related_products'][] = array(
					'product_id'      => $result['product_id'],
					'thumb'   	      => $image,
					'image'           => $result['image'],
					'date_available'  => $result['date_available'],
                    // Product Option Image PRO module <<

                    'option_images'  => $this->model_module_product_option_image_pro->getCategoryImages( $result['product_id'], "related_products" ),
                    // >> Product Option Image PRO module
					'name'    	 => $prod_info['name'],
					'price'   	 => $price,
					'special' 	 => $special,
					'saving'     => $savingAmount,
					'rating'     => $rating,
					'relatedProductsmessaging'     => $relatedProductsmessaging,
					'relatedProductsseller_id'     => $relatedProductsseller_id,
					'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),

                    'short_description' => $result['meta_description'],

					'manufacturer_id' => $result['manufacturer_id'],
					'manufacturer' => $result['manufacturer'],
					'manufacturerimg' => $result['manufacturerimg'],
					'manufacturer_href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id']),

				);
            }
			$this->data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$this->data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag),'',1)
					);
				}
			}

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

			if ( $this->data['integration_settings']['mn_criteo_status'] ) {
				// Criteo
				$this->data['criteo_email'] = "";

				if($this->customer->isLogged())
				{
					$this->data['criteo_email'] = $this->customer->getEmail();
				}
			}

            $data['wk_dropship_status'] = false;
            if($this->config->get('module_wk_dropship_status')) {
                $data['wk_dropship_status'] = true;
            }


			////////////// Hide Layouts in case of view product in iframe
	        $this->data['hide_layouts'] = false;
	        if(isset($this->request->get['iframe'])){
	            $this->data['hide_layouts'] = true;
	        }
	        ///////////////////

			///Addthis ID
			$product_addthis_id = $this->config->get('product_addthis_id');
			$this->data['product_addthis_id'] = $product_addthis_id && !empty($product_addthis_id) ? $product_addthis_id : 'ra-56c3976c6618d23f';
			/////////////

			/* GET review options */
			$languageId = $this->config->get('config_language_id');
			$this->data['review_options_text'] = $this->model_catalog_review->getReviewsOptionText($languageId);
			$this->data['review_options_rate'] = $this->model_catalog_review->getReviewsOptionRate($languageId);
 			if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/product.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/product.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/product/product.expand';
            }

            // Check if custom product app installed and active for current product
            $this->load->model('module/custom_product_editor');
            if (
                $this->model_module_custom_product_editor->isActive() &&
                (int) $product_info['custom_html_status'] ?? 0 === 1
            ) {

                $isCustomProductOptions = (bool) $this->request->get['custom_product_options'] ?? 0 === 1;

                if ($isCustomProductOptions) {
                    $isCustomProductOptionsTemplateExist = file_exists(DIR_TEMPLATE . ($template = $this->config->get('config_template') . '/template/product/product-custom-options.expand'));
                    // set template path to custom product options page
                    if ($isCustomProductOptionsTemplateExist) {
                        $this->template = $template;
                    }
                } else {
                    $this->data['custom_html'] = html_entity_decode($product_info['custom_html'], ENT_QUOTES, 'UTF-8');

                    if (!empty($this->data['custom_html']) && file_exists(DIR_TEMPLATE . ($template = 'default' . '/template/product/product-custom.expand'))) {
                        // set default template
                        $this->template = $template;
                        $this->data['custom_html'] = $this->parseCustomProductOptionsVars($this->data['custom_html']);
                        $enableDisplayHeaderAndFooter = (bool) ($product_info['display_main_page_layout'] ?? false) === true;

                        // render custom product html without main page template
                        if (!$enableDisplayHeaderAndFooter) {
                            $output = $this->render_ecwig();
                            exit($output);
                        }
                    }
                }
            }

            // Check if FIFA Cards app is installed
            if(Extension::isInstalled('fifa_cards') && $this->model_module_fifa_cards_settings->isActive())
			{
				$this->template = 'jarvis/template/product/product_fifa_cards.expand';
			}

            /* Auction App - check if product has an auction running */
            //Check if app installed
			if( \Extension::isInstalled('auctions') && $this->config->get('auctions_status') ){
				$this->language->load_json('module/auctions');

				$this->data['auctions_app_installed'] =  TRUE;

				$this->data['auctions_timezone'] = $this->config->get('auctions_timezone')?:$this->config->get('config_timezone');        
		
				//Get Auction data..
				$this->load->model('module/auctions/auction');
				$auction = $this->model_module_auctions_auction->getOneByProductId($this->request->get['product_id']);
				$this->data['auction'] = $auction;
				$this->data['product_has_auction'] = !empty($auction) ? TRUE : FALSE;
			}

            if( \Extension::isInstalled('fit_and_shop') && $this->config->get('fit_and_shop')['status']){
				$this->language->load_json('module/fitshop');
				$this->load->model('module/fit_and_shop');
				$fit_and_shop_data = $this->model_module_fit_and_shop->get_product_measurment($this->request->get['product_id']);
				if($fit_and_shop_data){
					$this->data['fit_sku'] = $fit_and_shop_data['collection_sku'];
					$this->data['fit_api_key'] = $this->config->get('fit_and_shop')['apikey'];
					$this->data['fit_and_shop_status'] = $this->config->get('fit_and_shop')['status'];
				}
			}


			if(\Extension::isInstalled('lableb') && $this->config->get('lableb')['status']){
				$settingsData = $this->config->get('lableb');
				$_SESSION['lableb_session_id'] = $_SESSION['lableb_session_id']??$this->_randomString();
				$lableb_selected_product = array();
				$lableb_selected_product['query'] = $this->request->get['query'];
				$lableb_selected_product['item_id'] = $this->request->get['product_id'].'-'.$this->config->get('config_language');
				$lableb_selected_product['item_order'] = $this->request->get['item_order'];
				$lableb_selected_product['url'] = $this->url->link('product/product&product_id=' . $this->request->get['product_id'], '', 'SSL');
				$lableb_selected_product['session_id'] = $_SESSION['lableb_session_id'];
				$lableb_selected_product['user_id'] = $this->customer->isLogged()?$this->customer->getId() :'';
				$lableb_selected_product['user_ip'] = $_SERVER['REMOTE_ADDR'];
				$lableb_selected_product['country'] = ''; 
				
				$this->load->model('module/lableb');
				if (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'lableb_search')  ) {
					$this->model_module_lableb->feedback($lableb_selected_product,'search');
				}
				if (strpos($_SERVER['REQUEST_URI'], "lableb_autoComplete")){
					$this->model_module_lableb->feedback($lableb_selected_product,'autocomplete');
				}
			}

      		$this->children = array(
				'common/footer',
				'common/header'
            );

            
            // tabby promo for product 
            if (\Extension::isInstalled('tabby_pay_later') 
                && !!($tabbySetting = $this->config->get('tabby_pay_later')) 
                && $tabbySetting['status'] == 1
                /* && $tabbySetting['show_promo_image_in_product_page'] == 1 */
            ) {
                $this->data['tabby_setting'] = $tabbySetting;
            }
            
            return $this->response->setOutput($this->render_ecwig());

		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

      		$this->data['breadcrumbs'][] = array(
        		'text'      => $this->language->get('text_error'),
				'href'      => $this->url->link('product/product', $url . '&product_id=' . $product_id),
        		'separator' => $this->language->get('text_separator')
      		);

      		$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->url->link('common/home');

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }

			$this->children = array(
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render_ecwig());
    	}
	  }

	private function _randomString($length = 20) {
		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}
	  /*public function prepare_productsoptions_sku($product_variations) {
 
		$this->load->model('catalog/product');

		$options = [];
		foreach ($product_variations as $variation) {
			$ov_ids = explode(',', $variation['option_value_ids']);

			foreach ($ov_ids as $ov_id) {
				$ov = $this->model_catalog_product->getOptionValue($ov_id);
				if (!isset($options[$ov['option_id']])) {
					$options[$ov['option_id']] = [
						'option_id' => $ov['option_id'],
						'name' => $ov['name']
					];
				}
				 
				$options[$ov['option_id']]['option_values'][$ov_id] = [
					'option_value_id' => $ov_id,
					'name' => $ov['ov_name']
				];
			}

		}
		
		return $options;
	  }*/

	  public function getProductVaritionsByOvIds() {

		$price = null;
		$data = $this->request->post;
		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($data['product_id']);

		if ($product_info['special']) {
			$price = $product_info['special'];
		}
		if (!$price && $product_info['discount']) {
			$price = $product_info['discount'];
		}

	    $product_variation = $this->model_catalog_product->getProductVaritionsByOvIds($data['product_id'], $data['ov_ids']);

		$price = $price ? $price : $product_variation['product_price'];
		if ($product_variation ) {
			if ($price > 0) {
				$product_variation['product_price'] = $this->currency->format(
					$this->tax->calculate($price,
						$product_info['tax_class_id'],
						$this->config->get('config_tax')
					), '', '', false);
			}else{
				$product_variation['product_price'] = '';
			}
		}
		return $this->response->setOutput(json_encode(compact('product_variation')));
	  }

	public function review() {
    	$this->language->load_json('product/product');

		$this->load->model('catalog/review');

		//$this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
        	$this->data['reviews'][] = array(
        		'author'     => $result['author'],
				'text'       => $result['text'],
				'rating'     => $result['rating'],
        		'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
				}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->text_first = $this->session->data['language'] == 'ar' ? '|&gt;' : '|&lt;' ;
		$pagination->text_last = $this->session->data['language'] == 'ar' ? '|&lt;' : '|&gt;' ;
		$pagination->text_next = $this->session->data['language'] == 'ar' ? '<span><i aria-hidden="true" class="fa fa-angle-left"></i></span>' : '<span><i aria-hidden="true" class="fa fa-angle-right"></i></span>' ;
		$pagination->text_prev = $this->session->data['language'] == 'ar' ? '<span><i aria-hidden="true" class="fa fa-angle-right"></i></span>' : '<span><i aria-hidden="true" class="fa fa-angle-left"></i></span>' ;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$this->data['pagination'] = $pagination->render();

        if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/review.expand')) {
            $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/product/review.expand';
        }
        else {
            $this->template = $this->config->get('config_template') . '/template/product/review.expand';
        }

		$this->response->setOutput($this->render_ecwig());
	}

	public function write() {
		$this->language->load_json('product/product');

		$this->load->model('catalog/review');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
				unset($this->request->post['name']);
			}

//			if ((utf8_strlen($this->request->post['text']) < 3) || (utf8_strlen($this->request->post['text']) > 1000)) {
//				$json['error'] = $this->language->get('error_text');
//				unset($this->request->post['text']);
//			}

			if(isset($this->request->post['rating']) && empty($this->request->post['rating'])){
					$json['error'] = $this->language->get('error_rating');
					unset($this->request->post['rating']);
			}
			foreach($this->request->post as $key => $value){
				if($key=='captcha' || $key=='name' || $key=='text' || $key=='rating')
					continue;
				
				if(empty($value))
					$json['error'] = $this->language->get('error_rating');
				else
					$this->request->post['rate'][$key] = $value;
					unset($this->request->post[$key]);

			}
			// add default rating value to the rest if exist
			if(!empty($this->request->post['rate'])){
				$this->request->post['rate']['rating'] = $this->request->post['rating'];
			}
			
			if (empty($this->session->data['recaptcha']) || ($this->session->data['recaptcha'] != $this->request->post['captcha'])) {
				$json['error'] = $this->language->get('error_captcha');
			}

			if (!isset($json['error'])) {
				$auto_approve_review = $this->config->get('config_review_auto_approve');
				if($auto_approve_review){
					$this->request->post['status'] = 1;
				}
				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}

			if($this->customer->isLogged())
			{
				$this->data['customer_name'] = $this->customer->getFirstName()." ".$this->customer->getLastName();
				$customer_id = $this->customer->getId();
			}else{
				$customer_id = 0;
			}

			if(\Extension::isInstalled('reward_points_pro')) {
				$this->load->model('rewardpoints/observer');
				$this->model_rewardpoints_observer->afterPostingReview($customer_id);
			}

			// check if game ball app installed
			if(\Extension::isInstalled('gameball')){
				$this->load->model('module/gameball/settings');
				// check if app status is active
				if($this->model_module_gameball_settings->isActive()){
					$this->load->model('catalog/product');
					// get product categories
					$productCategories = $this->model_catalog_product->getCategoriesWithDesc($this->request->get['product_id']);
					$productCategoriesArray = [];
					if(is_array($productCategories)){
						foreach ($productCategories as $key=>$category){
							foreach ($category['category_description'] as $category_description)
								$productCategoriesArray[] = $category_description['name'];
						}
					}

					$eventData['events']['review']['product_id'] = (string)$this->request->get['product_id'];
					$eventData['events']['review']['category'] = (string)implode(",",$productCategoriesArray);
					$eventData['playerUniqueId'] = $customer_id;
					$this->model_module_gameball_settings->sendGameballEvent($eventData);
				}
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	public function upload() {
		$this->language->load_json('product/product');

		$json = array();

		if (!empty($this->request->files['file']['name'])) {
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
        		$json['error'] = $this->language->get('error_filename');
	  		}

			// Allowed file extension types
			$allowed = array();

			$filetypes = explode(";", $this->config->get('config_file_extension_allowed'));

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

            $allowed[] = 'jpg';
            $allowed[] = 'jpeg';
            $allowed[] = 'gif';
            $allowed[] = 'png';

			if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
       		}

			// Allowed file mime types
		    $allowed = array();

			$filetypes = explode(";", $this->config->get('config_file_mime_allowed'));

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

            $allowed[] = 'image/jpeg';
            $allowed[] = 'image/pjpeg';
            $allowed[] = 'image/png';
            $allowed[] = 'image/x-png';
            $allowed[] = 'image/gif';

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json && is_uploaded_file($this->request->files['file']['tmp_name'])) {
			$file = basename($filename) . '.' . md5(mt_rand());

			// Hide the uploaded file name so people can not link to it directly.
			$json['file'] = $this->encryption->encrypt($file);

			// move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
            /*$_f = new File(($file), [
                'adapter' => 'gcs', 'base'=> STORECODE . '/downloads',
            ]);
            $u = $_f->upload($this->request->files['file']['tmp_name']);*/
            $fileName = 'downloads/' . $file;
            \Filesystem::setPath($fileName)->upload($this->request->files['file']['tmp_name']);

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function validateVideo($url){
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

	/**
	 *   Product options sku drop-dowen relational options, get valid options related to another option
	 *
	 *   @By Ali Hemeda
	 *   @param  post array $data [
		 *                       'product_id' -- Product Id
		 *                       'option_id' -- main selected option id
		 *                       'pr_op_id' -- main selected option value id
		 *                       'child_opt' -- related option id
		 *                       'parents_values' -- main selected, option value ids of it's parent option if exists
		 *                      ]
	 *   @return json
	 */
	public function getRelationalOpts(){

		$this->language->load_json('product/product');

		$data = $this->request->post;
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		//Get product tax_class_id
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProductCols($data['product_id'], ['tax_class_id']);
		$tax_class_id = $product_info ? $product_info['tax_class_id'] : 0;
		//////////////////////////

		$show_outofstock_option = $this->config->get('show_outofstock_option');
		$cnf_customer_price = $this->config->get('config_customer_price');
		$config_tax = $this->config->get('config_tax');

		$option_values = $this->model_catalog_product->getProductRelationalOptions($data);

		$ids = [];
		$product_option_value_data = [];

		foreach ($option_values as $option_value) {
			if (
				(!$option_value['subtract']
				||
				($option_value['subtract'] && $show_outofstock_option)
				||
				($option_value['subtract'] && !$show_outofstock_option && $option_value['quantity'] > 0))
				&& !in_array($option_value['product_option_value_id'], $ids)
			) {
				$option_price = $this->currency->currentValue($option_value['price']);

				if ($isCustomerAllowedToViewPrice && (float)$option_value['price']) {
					$price = $this->currency->format($this->tax->calculate($option_value['price'], $tax_class_id, $config_tax));
					$option_price = $this->tax->calculate($option_price, $tax_class_id, $config_tax);
				} else {
					$price = false;
				}


				$product_option_value_data[] = array(
					'product_option_value_id' => $option_value['product_option_value_id'],
					'option_value_id'         => $option_value['option_value_id'],
					'name'                    => $option_value['name'],
					//'image' => $option_value['image'],
					'quantity'                => $option_value['quantity'],
					'subtract'                => $option_value['subtract'],
					'price'                   => $price,
					'price_value'             => $option_price,
					'price_prefix'            => $option_value['price_prefix'],
					'weight'                  => $option_value['weight'],
					'weight_prefix'           => $option_value['weight_prefix']
				);
			}
			$ids[] = $option_value['product_option_value_id'];
		}

		$htmlOpts = '<option value="">'.$this->language->get('text_select').'</option>';
		$htmlHiddenOpts = '';

		//Prepare <select> options
		foreach ($product_option_value_data as $option_value) {
            $htmlOpts .= '<option value="'. $option_value['product_option_value_id'] .'"';
            if ($option_value['quantity'] <= 0 )
                $htmlOpts .= ' disabled="disabled"';

			$htmlOpts .= 'data-option-value-id="'. $option_value['option_value_id'] .'"';
            $htmlOpts .= '>'.$option_value['name'];

            //Option Quantity Check
            if ($option_value['quantity'] <= 0 )
                $htmlOpts .=  '('. $this->language->get('text_notavailable') .')';

            if ($this->config->get('config_show_option_prices') == '1'){
                if ($option_value['price'])
                    $htmlOpts .= '('. $option_value['price_prefix'] . $option_value['price'] .')';
            }

            $htmlOpts .= '</option>';

			$htmlHiddenOpts .= '<input type="hidden" id="select-price-'. $option_value['product_option_value_id'].'" value="'. $option_value['price_value'] .'">
                                <input type="hidden" id="select-prifex-'. $option_value['product_option_value_id'].'" value="'. $option_value['price_prefix'] .'">';

		}

        return $this->response->setOutput(json_encode(['options' => $htmlOpts, 'hiddens' => $htmlHiddenOpts]));
	}

	/**
	 *   Product add to notify me when available App
	 *
	 *   @By Ali Hemeda
	 *   @return json
	 */
	public function add_to_notify_me() {
		$this->language->load_json('product/product', true);

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		}else{
			return;
		}

		$this->load->model('module/product_notify_me');

		if ($this->customer->isLogged()) {
			$data = [
				'customer_id' => $this->customer->getId(),
				'product_id' => $product_id,
				'name' => '\''.$this->customer->getFirstName().' '.$this->customer->getLastName().'\'',
				'email' => '\''.$this->customer->getEmail().'\'',
				'phone' => '\''.$this->customer->getTelephone().'\'',
				'is_notified' => 0
			];

			$addNotify = $this->model_module_product_notify_me->addNotify($data);
			if($addNotify == 'done'){
				$json['status'] = '1';
				$json['success'] = $this->language->get('text_notifyme_success');
			}else if($addNotify == 'exists'){
				$json['status'] = '0';
				$json['success'] = $this->language->get('text_notifyme_exists');
			}
		} else {

			//Guest save data
			if(isset($this->request->post['guest'])
				&& isset($this->request->post['name'])
				&& isset($this->request->post['email'])
				&& isset($this->request->post['phone'])
			){

				if(!$this->request->post['name'] || !$this->request->post['email'] || !$this->request->post['phone'] ||
				!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])){
					$json['status'] = '0';
					$json['success'] = $this->language->get('text_notifyme_required');
				}else{
					$data = [
						'customer_id' => 0,
						'product_id' => $product_id,
						'name' => '\''.$this->request->post['name'].'\'',
						'email' => '\''.$this->request->post['email'].'\'',
						'phone' => '\''.$this->request->post['phone'].'\'',
						'is_notified' => 0
					];
					$addNotify = $this->model_module_product_notify_me->addNotify($data);
					if($addNotify == 'done'){
						$json['status'] = '1';
						$json['success'] = $this->language->get('text_notifyme_success');
					}else if($addNotify == 'exists'){
						$json['status'] = '0';
						$json['success'] = $this->language->get('text_notifyme_exists');
					}
				}
			}
			//Guest open modal
			else{
				$json['status'] = '2';
			}
		}

		$this->response->setOutput(json_encode($json));
    }

    /**
     * Replace a specific words in text that has value in the data
     *
     * @param string $html
     * @return string $html parsed
     */
    private function parseCustomProductOptionsVars(string $html)
    {
        return preg_replace_callback(
            "/{{\S+}}/m",
            function($matches) {
                $match = str_replace(["{{", "}}"], "", $matches[0]);
                return isset($this->data[$match]) ?
                    ($match === "image" ? Filesystem::getUrl('image/'. $this->data[$match]) : $this->data[$match]) :
                    $matches[0];
            },
            $html
        );
	}

	public function calculateTax(){
		if($this->request->post['cost']){
			$data['tax'] = $this->currency->format($this->request->post['cost']);
			$data['tax_value'] = $this->currency->currentValue($this->request->post['cost']);
			$this->response->setOutput(json_encode($data));
		}
	}
}
?>
