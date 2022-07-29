<?php
class ControllerCheckoutCart extends Controller
{
    private $error = [];
	public function index()
	{
		// unset($this->session->data['stock_forecasting_cart']);
		$this->language->load_json('checkout/cart');
		$this->language->load_json('product/product');

		$this->load->model('setting/setting');
		$this->data['integration_settings'] = $this->model_setting_setting->getSetting('integrations');
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		if( isset($this->session->data['error_stock_forecasting']) ){
			$this->data['error_stock_forecasting'] = $this->session->data['error_stock_forecasting'];
			unset($this->session->data['error_stock_forecasting']);
		}

		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = array();
		}

		if (!isset($this->session->data['subscription'])) {
			$this->session->data['subscription'] = [];
		}

		if (isset($this->request->post['apply_coupon'])){
			if (isset($this->request->post['coupon']) && $this->validateCoupon() ) {
				$this->session->data['coupon'] = $this->request->post['coupon'];

				$this->session->data['success'] = $this->language->get('text_coupon');

				$this->redirect($this->url->link('checkout/cart'));
			}
		}

		if( isset($this->session->data['auction_product']) ){
			$auction_product = $this->session->data['auction_product'];
			$this->data['auction_product'] = $auction_product;
		}

		// Update
		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				if( isset($this->session->data['auction_product']) && $this->session->data['auction_product']['product_id'] == $key ){
					$this->cart->update($key, 1);
				}
				elseif( \Extension::isinstalled('stock_forecasting') &&
				 $this->config->get('stock_forecasting_status') == 1){	
				 	$this->language->load_json('module/stock_forecasting');				
					
					$this->load->model('module/stock_forecasting');
					$allowed_quantity =  $this->model_module_stock_forecasting->getProductStockForecastingByDate($key, $this->session->data['stock_forecasting_cart'][$key]['stock_forecasting_delivery_date']);
					
					if($value > $allowed_quantity){
						//Don't increase it and return error (out of stock)
						$this->session->data['error_stock_forecasting'] = $this->language->get('error_stock_forecasting_quantity');
					}else{
						$this->cart->update($key, $value);
						if($value <= 0) unset($this->session->data['stock_forecasting_cart'][$key]);					
					}
				}else{
					$this->cart->update($key, $value);
				}
			}
			
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$this->redirect($this->url->link('checkout/cart'));
		}

		// Remove
		if (isset($this->request->get['remove'])) {
			$this->cart->remove($this->request->get['remove']);

			unset($this->session->data['vouchers'][$this->request->get['remove']]);
			unset($this->session->data['subscription']);

			$this->session->data['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

            // check if game ball app installed
            if(\Extension::isInstalled('gameball')){
                $this->load->model('module/gameball/settings');
                // check if app status is active
                if($this->model_module_gameball_settings->isActive()){
                    if($this->customer->isLogged())
                    {
                        $this->data['customer_name'] = $this->customer->getFirstName()." ".$this->customer->getLastName();
                        $customer_id = $this->customer->getId();
                    }else{
                        $customer_id = 0;
                    }

                    $eventData['events']['remove_from_cart'] = "";
                    $eventData['playerUniqueId'] = $customer_id;
                    $this->model_module_gameball_settings->sendGameballEvent($eventData);

                }
            }

            if(count($this->session->data['cart']))
				$this->redirect($this->url->link('checkout/cart'));
			else
				$this->redirect($this->url->link('common/home'));
		}

        // clear
        if (isset($this->request->get['clear'])) {
            $this->cart->clear();
            
            unset($this->session->data['stock_forecasting_cart']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['subscription']);

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            $this->redirect($this->url->link('checkout/cart'));
        }


		// Voucher
		if (isset($this->request->post['voucher']) && $this->validateVoucher()) {
			$this->session->data['voucher'] = $this->request->post['voucher'];

			$this->session->data['success'] = $this->language->get('text_voucher');

			$this->redirect($this->url->link('checkout/cart'));
		}

		// Reward
		if (isset($this->request->post['reward']) && $this->validateReward()) {
			$this->session->data['reward'] = abs($this->request->post['reward']);

			$this->session->data['success'] = $this->language->get('text_reward');

			$this->redirect($this->url->link('checkout/cart'));
		}

		// Shipping
		if (isset($this->request->post['shipping_method']) && $this->validateShipping()) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

			$this->session->data['success'] = $this->language->get('text_shipping');

			$this->redirect($this->url->link('checkout/cart'));
		}

		$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

		if ($queryRewardPointInstalled->num_rows) {
			$this->load->model('rewardpoints/spendingrule');
			$this->load->model('rewardpoints/shoppingcartrule');
			$this->model_rewardpoints_spendingrule->getSpendingPoints();
			$this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
		}

		$this->document->setTitle($this->language->get('heading_title'));
		//$this->document->addScript('expandish/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		//$this->document->addStyle('expandish/view/javascript/jquery/colorbox/colorbox.css');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/cart'),
			'text'      => $this->language->localize('text_cart','heading_title'),
			'separator' => $this->language->get('text_separator')
		);

		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])
			|| !empty($this->session->data['subscription']) ) {

			$points = $this->customer->getRewardPoints();

			$points_total = 0;
			foreach ($this->cart->getProducts() as $product) {
				if ($product['points']) {
					$points_total += $product['points'];
				}
			}
			$this->data['points'] =  $points;

			$this->data['points_total'] = $points_total;
			$this->load->model('module/rental_products/settings');
     		$this->load->model('catalog/product');
			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];

			} 
			// check if rental product app is installed and product exceeded quantity
			elseif($this->model_module_rental_products_settings->isActive()) {
				foreach ( $this->cart->getProducts() as $product ) {
					$disabled_days = $this->model_catalog_product->getRentDisabledDates($product['rentData']['range']['from'],$product['rentData']['range']['to'],$product['product_id'],$product['stock_quantity'],$product['quantity']);
					if(count($disabled_days))
						$this->data['error_warning'] = $this->language->get('error_stock_another');
				}
				
				// check for quanitity as the user acn change at the cart " bug "
			}
			elseif (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$this->data['error_warning'] = $this->language->get('error_stock_another');
			}

			else {
				$this->data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$this->data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$this->data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}

			//$this->data['action'] = $this->url->link('checkout/cart');

			if ($this->config->get('config_cart_weight')) {
				$this->data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$this->data['weight'] = '';
			}

			$this->load->model('tool/image');

            $this->data['products'] = array();

            //warehouses
			$warehouse_setting = $this->config->get('warehouses');
            $warehouses_shipping_setting = $this->config->get('warehouses_shipping');
			$this->data['warehouses'] = false;
            $wrMissingProducts = [];
			if(
				isset($warehouse_setting) &&
				$warehouse_setting['status'] == 1
                &&isset($warehouses_shipping_setting) &&
                $warehouses_shipping_setting['status'] == 1
				&& isset($this->session->data['warehouses_products']) &&
				count($this->session->data['warehouses_products']['products']) > 0
			){
				$products = $this->session->data['warehouses_products']['products'];
                $missing_products = $this->session->data['warehouses_products']['missing_products'];
				$combined_wrs_costs = $this->session->data['warehouses_products']['wrs_costs'];
				$this->data['wrs_names']  = $this->session->data['warehouses_products']['wrs_name'];
				$this->data['wrs_duration']  = $this->session->data['warehouses_products']['wrs_duration'];
				$this->data['warehouses'] = true;
			}else{
				$products = $this->cart->getProducts();
			}

			/*****************/

            /// Prize draw app
            $this->load->model('module/prize_draw');
            $prize_draw_status = $this->model_module_prize_draw->isActive();

			$pd_custom_total_price = 0;
			$this->language->load_json('module/product_designer');

           $enable_save_product_options = $this->config->get('enable_save_product_options');

			foreach ($products as $key => $product) {
				//Change the original product price to the Auction winning highest bid price
				$product['price'] = $product['product_id'] === $auction_product['product_id'] ? $auction_product['price'] : $product['price'];
				$product['quantity'] = $product['product_id'] === $auction_product['product_id'] ? 1 : $product['quantity'];
				

				$pd_application = 0;

				//Prize draw app
                $prize_draw = null;
                $consumed_percentage = 0;
                if($prize_draw_status){
                    $prize_draw = $this->model_module_prize_draw->getProductPrize($product['product_id']) ?? null;
                    if($prize_draw['image'])
                        $prize_draw['image'] = $this->model_tool_image->resize($prize_draw ['image'], 500, 500);

                    $ordered_count = $this->load->model_module_prize_draw->getOrderedCount($product['product_id']);
                    $consumed_percentage = ( ((int)$ordered_count * 100) / (int)$product['quantity'] ) ?? 0;
                }

				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$this->data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

                if ($this->config->get('enable_order_maximum_quantity') && $product['maximum'] > 0 && $product['maximum'] < $product_total) {
                    $this->data['error_warning'] = sprintf($this->language->get('error_maximum_quantity'), $product['name'], $product['maximum']);
                }

                $productHrefWithOptions = '';
				$option_data = array();
				$product_option_value_ids = array();
				foreach ($product['option'] as $option) {
                    $product_option_value_ids[] = $option['product_option_value_id'];
					if ($option['type'] != 'file') {
						$value = $option['option_value'];
					} else {
						$filename = $this->encryption->decrypt($option['option_value']);

						$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
					}
					if ($option['type'] == 'pd_application') {
						$pd_application = $option['custom_id'];
						$pd_tshirtId = $option['tshirtId'];
					} else {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                            'title' => $value,
							'quantity' => $option['quantity']
                        );
                        $productHrefWithOptions .= "&custom_product_options=1&option[".$option['product_option_id']."]=".$option['product_option_value_id'];
					}
				}

                $pd_custom_price = [];

				if ($pd_application) {
					$product['image'] = 'modules/pd_images/merge_image/' . $pd_application . '__front.png';

					$pdOpts = $this->db->query(
						'select * from ' . DB_PREFIX . 'tshirtdesign where did="' . $pd_tshirtId . '"'
					);
					if ($pdOpts->num_rows > 0) {
						$pd_front = json_decode(html_entity_decode($pdOpts->row['front_info']), true)[0]['custom_price'];
						$pd_back = json_decode(html_entity_decode($pdOpts->row['back_info']), true)[0]['custom_price'];
					} else {
						$pd_front = 0;
						$pd_back = 0;
					}
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], 100, 100);
				} else {
					$image = '';
				}

				// Display prices
				if ($isCustomerAllowedToViewPrice) {

					if (isset($pd_front)) {
						//                        $product['price'] += $pd_front;
						$pd_custom_total_price += $pd_front;
						$pd_custom_total_price *= $product['quantity'];
					}

					if (isset($pd_back)) {
						//                        $product['price'] += $pd_back;
						$pd_custom_total_price += $pd_back;
						$pd_custom_total_price *= $product['quantity'];
					}
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));

				} else {
					$price = false;
				}

				// Display prices
				if ($isCustomerAllowedToViewPrice) {
					$total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
				} else {
					$total = false;
				}
				// data sent to checkout/order model to get inserted
				if (isset($product['rentData'])) {
					$product['rentData']['range'] = array_map(function ($value) {
						return date("Y-m-d", $value);
					}, $product['rentData']['range']);
				}

				// $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

				if ($this->data['integration_settings']['mn_criteo_status']) {
					$criteo_price = $product['price'];
					$pos_dot = stripos($criteo_price, '.');
					$pos_comma = stripos($criteo_price, ',');
					$criteo_price = preg_replace("/[^\d.,]/", "", $criteo_price);
					if ($pos_dot !== false and $pos_comma !== false) {
						if ($pos_dot > $pos_comma) {
							$criteo_price = preg_replace("/[,]/", "", $criteo_price);
						} else {
							$criteo_price = preg_replace("/[.]/", "", $criteo_price);
							$criteo_price = preg_replace("/[,]/", ".", $criteo_price);
						}
					} else if ($pos_dot === false and $pos_comma !== false) {
						if (strlen(explode(',', $criteo_price)[1]) >= 3) {
							$criteo_price = preg_replace("/[,]/", "", $criteo_price);
						} else {
							$criteo_price = preg_replace("/[,]/", ".", $criteo_price);
						}
					} else if ($pos_dot !== false and $pos_comma === false) {
						if (strlen(explode('.', $criteo_price)[1]) >= 3) {
							$criteo_price = preg_replace("/[.]/", "", $criteo_price);
						}
					}
				}
				if($enable_save_product_options){
                    $href = urldecode($this->url->link('product/product', 'product_id=' . $product['product_id'].$productHrefWithOptions));
                }else{
                    $href = $this->url->link('product/product', 'product_id=' . $product['product_id']);
                }

                foreach ($product['bundlesData'] as $key => $bundle) {
					if ($bundle['image']) {
						$product['bundlesData'][$key]['thumb']  = $this->model_tool_image->resize($bundle['image'], 100, 100);
					} else {
						$product['bundlesData'][$key]['thumb'] = '';
					}
                }

				$this->data['products'][$key] = array(
					'product_id' => \Extension::isInstalled('multiseller') ? $product['product_id'] : 0,
					'original_id'	=> $product['product_id'],
					'key'      => $product['key'],
					'thumb'    => $image,
					'name'     => $product['name'],
					'description'	=> strip_tags($product['description']),
					'alt_name' => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'stock'    => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'   => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'    => $price,
					"criteo_price"	=> $criteo_price,
					'total'    => $total,
					'total_float'    => $product['total'],
					'href'     => $href,
					'remove'   => $this->url->link('checkout/cart', 'remove=' . $product['key']),
			        'rentData' => $product['rentData'],
			        'bundlesData' => $product['bundlesData'],
			        'pricePerMeterData'=> $product['pricePerMeterData'],
			        'fifaCardsData'=> $product['fifaCardsData'],
			        'main_price'=> isset($product['main_price']) ? $this->currency->format($product['main_price']) : NULL,
			        'remaining_amount'=> isset($product['remaining_amount']) ? $this->currency->format($product['remaining_amount']) : NULL,
			        'printingDocument'=> $product['printingDocument'],
			        'warehouse' => $product['warehouse'] ? $product['warehouse'] : '',
                    'prize_draw' => $prize_draw,
                    'consumed_percentage' => $consumed_percentage <= 100 ? $consumed_percentage : 100,
                    'has_options' => $this->model_catalog_product->checkIfProductHasOptions($product['product_id']),
                    'allProductOptions' => $this->model_catalog_product->getProductOptions($product['product_id']),
                    'keyReplaced' => str_replace([":","="],"_",$product['key']),
                    'is_knawat_product' => $this->model_catalog_product->isKnawatProduct($product['product_id']),
                    'product_option_value_ids'=> $product_option_value_ids
				);
				
				if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
                	$this->data['products'][$key]['delivey_date']  = $this->session->data['stock_forecasting_cart'][$product['key']]['stock_forecasting_delivery_date'];
                	$this->data['stock_forecasting_app_installed'] = TRUE;
            	}
			}
			//Show warehouses warning
			if($this->data['warehouses'] && count($missing_products)){
                $wrWarning = '- '. $this->language->get('error_no_warehouses').':';
                foreach ($missing_products as $wrPrd){
                    $wrWarning .= '<li>'.$wrPrd.'</li>';
                }
                $this->data['error_warning'] .= $wrWarning;
                unset($this->session->data['warehouses_products']['missing_products']);
            }

			if ($this->MsLoader->isInstalled()) {
				$sellerErrors = [];
				$cart_sellers = [];
				foreach ($this->data['products'] as &$product) {
					$product['seller'] = $this->MsLoader->MsSeller->getSellerByProductId($product['product_id']);

					if ($product['seller']['minimum_order'] > 0) {
						if (!isset($cart_sellers[$product['seller']['seller_id']])) {
							$cart_sellers[$product['seller']['seller_id']] = $product['seller'];
						}

						$cart_sellers[$product['seller']['seller_id']]['total_cart'] += $product['total_float'];

						if ($this->config->get('msconf_enable_seller_name_in_cart_view') == 1) {
							$product['seller_name'] = $product['seller']['nickname'];
						}
					}
				}

				//Check for minimum amount for each seller
				foreach ($cart_sellers as $cart_seller) {
					if ($cart_seller['total_cart'] < $cart_seller['minimum_order']) {
						$sellerErrors[] = sprintf(
							$this->language->get('error_minimum_order'),
							$cart_seller['nickname'],
							$cart_seller['minimum_order'],
							$cart_seller['total_cart']
						);
					}
				}

				if (empty($this->data['error_warning'])) {
					$this->data['error_warning'] = implode('<br/>', $sellerErrors);
				} else {
					$this->data['error_warning'] .= "<br>" . implode('<br/>', $sellerErrors);
				}

				foreach ($this->data['products'] as &$product) {
					if (
						$cart_sellers[$product['seller']['seller_id']]['total_cart'] < $product['seller']['minimum_order'] &&
						$product['seller']['view_minimum_alert'] == 1
					) {
						$product['name'] = '<span style="color: tomato;">*** ' . $product['name'] . '</span>';
					}
				}
            }

			//combined warehouses cost
            if($this->data['warehouses']){
            	foreach ($this->data['products'] as $prd) {
	            	$warehouseCostValue = $combined_wrs_costs[$prd['warehouse']];
	            	$combined_wrs_costs_format[$prd['warehouse']] = $this->currency->format($this->tax->calculate($warehouseCostValue, $this->config->get('weight_tax_class_id'), $this->config->get('config_tax')));
	            	///////////////////////////////////////////////////////
	            }
	            $this->data['combined_wrs_costs'] = $combined_wrs_costs_format;
            }/////////////////////////

            //Subscription
			$this->data['subscription'] = [];
			if (!empty($this->session->data['subscription'])) {
				$this->data['subscription'][0] = [
					'key'         => $this->session->data['subscription']['id'],
					'description' => $this->session->data['subscription']['title'],
					'amount'      => $this->currency->format($this->session->data['subscription']['amount']),
					'quantity'    => 1 ,
					'remove'      => $this->url->link('checkout/cart', 'remove=' . $this->session->data['subscription']['id'])
				];

			}


			// Gift Voucher
			$this->data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$v_quantity = $voucher['quantity'] ? $voucher['quantity'] : 1;

					$this->data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],

						'amount'      => $this->currency->format($voucher['amount'] * $v_quantity),
						'quantity'    => $v_quantity ,
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)

					);
				}
			}

			if (isset($this->request->post['next'])) {
				$this->data['next'] = $this->request->post['next'];
			} else {
				$this->data['next'] = '';
			}

			$this->data['coupon_status'] = $this->config->get('coupon_status');

			if (isset($this->request->post['coupon'])) {
				$this->data['coupon'] = $this->request->post['coupon'];
			} elseif (isset($this->session->data['coupon'])) {
				$this->data['coupon'] = $this->session->data['coupon'];
			} else {
				$this->data['coupon'] = '';
			}

			$this->data['voucher_status'] = $this->config->get('voucher_status');

			if (isset($this->request->post['voucher'])) {
				$this->data['voucher'] = $this->request->post['voucher'];
			} elseif (isset($this->session->data['voucher'])) {
				$this->data['voucher'] = $this->session->data['voucher'];
			} else {
				$this->data['voucher'] = '';
			}

			$this->data['reward_status'] = ($points && $points_total && $this->config->get('reward_status'));

			if (isset($this->request->post['reward'])) {
				$this->data['reward'] = $this->request->post['reward'];
			} elseif (isset($this->session->data['reward'])) {
				$this->data['reward'] = $this->session->data['reward'];
			//                echo '<pre>';
			//                var_dump($this->data); die();
						} else {
				$this->data['reward'] = '';
			}

			$this->data['shipping_status'] = $this->config->get('shipping_status') && $this->config->get('shipping_estimator') && $this->cart->hasShipping();

			if (isset($this->request->post['country_id'])) {
				$this->data['country_id'] = (int) $this->request->post['country_id'];
			} elseif (isset($this->session->data['shipping_country_id'])) {
				$this->data['country_id'] = $this->session->data['shipping_country_id'];
			} else {
				$this->data['country_id'] = $this->config->get('config_country_id');
			}

			$this->load->model('localisation/country');

			$this->data['countries'] = $this->model_localisation_country->getCountries();

			if (isset($this->request->post['zone_id'])) {
				$this->data['zone_id'] = (int) $this->request->post['zone_id'];
			} elseif (isset($this->session->data['shipping_zone_id'])) {
				$this->data['zone_id'] = $this->session->data['shipping_zone_id'];
			} else {
				$this->data['zone_id'] = '';
			}

			if (isset($this->request->post['postcode'])) {
				$this->data['postcode'] = $this->request->post['postcode'];
			} elseif (isset($this->session->data['shipping_postcode'])) {
				$this->data['postcode'] = $this->session->data['shipping_postcode'];
			} else {
				$this->data['postcode'] = '';
			}

			if (isset($this->request->post['shipping_method'])) {
				$this->data['shipping_method'] = $this->request->post['shipping_method'];
			} elseif (isset($this->session->data['shipping_method'])) {
				$this->data['shipping_method'] = $this->session->data['shipping_method']['code'];
			} else {
				$this->data['shipping_method'] = '';
			}

			// Totals
			$this->load->model('setting/extension');

			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			// Display prices
			if ($isCustomerAllowedToViewPrice) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if($this->config->get('hide_shipping_cart') && $result['code'] == 'shipping'){
	                    continue;
	                }
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);

						$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
					}

					$sort_order = array();

					foreach ($total_data as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $total_data);
				}
			}


            if ($pd_custom_total_price > 0) {
                $this->reArrangeTotalData($total_data, $total, $pd_custom_total_price);
            }

			$this->data['totals'] = $total_data;


			$this->data['currency_symbols'] = [
				'left' => $this->currency->getSymbolLeft(),
				'right' => $this->currency->getSymbolRight()
			];

			//Create order in case of PayPal
			$paypalSettings = $this->config->get('paypal_status');
                        
			if ($paypalSettings == 1) {

                $this->load->model("localisation/country");
                $languageCode = $this->config->get("config_language");
                $countryResult = $this->model_localisation_country->getCountry($this->config->get("config_country_id"));
                $locale = $languageCode . "_" . $countryResult["iso_code_2"];

				$this->data['paypal_enabled']     = 1;
                $this->data['merchantId'] = $this->config->get('paypal_merchant_id');
                
				$urlParameters = [
								'client-id' 	  => $this->expandClientId,
								'disable-funding' => "bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo", 
								//'merchant-id'   => $this->data['merchantId'], 
								'currency' 		  => $this->allowed_currencies[0],
								'components' 	  => 'buttons,marks',
								'intent' 		  => 'capture',
								'commit' 		  => 'true'
							];
							
					//--- payment before onboarding 
				if($this->config->has('paypal_payment_before_onboarding')){
					//if this config not set this mean we are proceed the payment before merchant onboarding 
					// so no merchant-id can provided yet
				
					$urlParameters['merchant-id'] = $this->config->has('paypal_merchant_id') ? $this->data['merchantId'] : BILLING_DETAILS_EMAIL;

					$canUseCard = ($this->config->has('paypal_merchant_id') 
									&& empty($this->config->get('paypal_mail_error'))
									&& empty($this->config->get('paypal_oauth_error'))
									&& empty($this->config->get('paypal_receivable_error'))
									&& empty($this->config->get('paypal_send_only_error'))
									);
					
					
					if(!$canUseCard){
						$urlParameters['disable-funding']	.= ",card";
					}

				} else {
					// payment after onboarding 
					$urlParameters['merchant-id'] = $this->data['merchantId'];
				}
				
				$this->data['paypal_endpoint_js'] = "https://www.paypal.com/sdk/js?" .http_build_query($urlParameters);
				
				$this->data["paypal_button_color"] = $this->config->get("paypal_button_color");

				$this->getChild('module/quickcheckout/modify_order', ['is_from_cart' => 1]);
			}
			////////////////////////////////////////

			//$this->data['continue'] = $this->url->link('common/home');

			//$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

			if ($this->data['integration_settings']['mn_criteo_status']) {
				// Criteo
				$this->data['criteo_email'] = "";

				if ($this->customer->isLogged()) {
					$this->data['criteo_email'] = $this->customer->getEmail();
				}
			}

            if($this->customer->isLogged())
            {
                $customer_id = $this->customer->getId();
            }else{
                $customer_id = 0;
            }
            // check if game ball app installed
            if(\Extension::isInstalled('gameball')){
                $this->load->model('module/gameball/settings');
                // check if app status is active
                if($this->model_module_gameball_settings->isActive()){
                    $eventData['events']['view_cart']['products_count'] = (string)$this->cart->countProducts();
                    $eventData['events']['view_cart']['total'] = (string)$this->cart->getTotal();
                    $eventData['playerUniqueId'] = $customer_id;
                    $this->model_module_gameball_settings->sendGameballEvent($eventData);
                }
            }

			if (file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/cart.expand')) {
				$this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/cart.expand';
			} else {
				$this->template = $this->config->get('config_template') . '/template/checkout/cart.expand';
			}


		} else {
			if ($this->request->get['src']=='mailMsg' && !$this->customer->isLogged()) {
				$this->session->data['cart_redirect'] = $this->url->link('checkout/cart', '', 'SSL');
				$this->redirect($this->url->link('account/login', '', 'SSL'));
			} 

            $this->data['heading_title'] = $this->language->get('heading_title');

            $this->data['text_error'] = $this->language->get('text_empty');

            $this->data['continue'] = $this->url->link('common/home');

            unset($this->session->data['success']);

            if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/error/not_found.expand';
            }
            else {
                $this->template = $this->config->get('config_template') . '/template/error/not_found.expand';
            }

        }
        
        // tabby promo for cart 
        if (\Extension::isInstalled('tabby_pay_later') 
            && !!($tabbySetting = $this->config->get('tabby_pay_later')) 
            && $tabbySetting['status'] == 1
            /* && $tabbySetting['show_promo_image_in_cart_page'] == 1 */
        ) {
            $this->data['tabby_setting'] = array_merge($tabbySetting, [
                'currency' => $this->currency->getCode(),
                'price' => $this->currency->currentValue($total),
                'lang' => $this->config->get('config_language'),
                'size' => 'narrow', // or wide
            ]);
        }
    
        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render_ecwig());
	}

	public function update_quantity(){
		$json = array();

		if(!empty($this->request->post['key']) && !empty($this->request->post['quantity'])){
			if( isset($this->session->data['auction_product']) && $this->session->data['auction_product']['key'] == $key ){
				$this->cart->update($this->request->post['key'], 1);
			}
			else{
				$this->cart->update($this->request->post['key'], $this->request->post['quantity']);

				//Stock forecasting app
				if( $this->request->post['quantity'] <= 0 && isset($this->session->data['stock_forecasting_cart'][$this->request->post['key']]) ) {
     				unset($this->session->data['stock_forecasting_cart'][$this->request->post['key']]);
				}
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$json['success'] = 1;
			$this->response->setOutput(json_encode($json));
		}
		else{
			$json['success'] = 0;
			$json['error'] = 'missing arguments';
			$this->response->setOutput(json_encode($json));
		}
	}

	public function updateCartQuantity(){
		$this->language->load_json('checkout/cart');
		$json = array();

		if(!empty($this->request->post['key']) ){
			if($this->request->post['decrease'] == 1)
				$new_quantity = $this->session->data['cart'][$this->request->post['key']] - 1;
			elseif($this->request->post['increase'] == 1)
				$new_quantity = $this->session->data['cart'][$this->request->post['key']] + 1;

			if($new_quantity >= 1) 
				$this->cart->update($this->request->post['key'], $new_quantity);
			else{
				$this->cart->remove($this->request->post['key']);
				
				//Stock forecasting app
				if(isset($this->session->data['stock_forecasting_cart'][$this->request->post['key']]) ) {
     				unset($this->session->data['stock_forecasting_cart'][$this->request->post['key']]);
				}
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$json['success'] = 1;
			$json['message'] = $this->language->get('text_updated');
			$json['product_count'] = $new_quantity;
			$this->response->setOutput(json_encode($json));
		}
		else{
			$json['success'] = 0;
			$json['error'] = 'missing arguments';
			$this->response->setOutput(json_encode($json));
		}
	}

	public function reArrangeTotalData(&$total_data, &$total, $pd_custom_total_price)
	{
		array_unshift($total_data, [
			'code' => 'pd_custom_design',
			'title' => $this->language->get('pd_custom_design_cart_title'),
			'text' => $this->currency->format($pd_custom_total_price),
			'value' => $pd_custom_total_price,
			'sort_order' => 5
		]);

		$tmp = array_pop($total_data);
		$tmp['text'] = $this->currency->format($pd_custom_total_price + $tmp['value']);
		$tmp['value'] = $pd_custom_total_price + $tmp['value'];

		array_push($total_data, $tmp);
		$total += $pd_custom_total_price;
	}

	protected function validateCoupon()
	{

        $this->load->model('checkout/coupon');

		$coupon_info = $this->model_checkout_coupon->getCoupon($this->request->post['coupon']);
        $details = json_decode($coupon_info['details'],true);

		if (!$coupon_info) {
			$this->error['warning'] = $this->language->get('error_coupon');
		}
		if (!$coupon_info['status']){
            $this->error['warning']  = $coupon_info['error_message'];
        }

        if ($this->cart->getSubTotal() < $coupon_info['minimum_to_apply']) {
            $this->error['warning'] = sprintf(
                $this->language->get('error_coupon_less_than_minimum'),
                $this->currency->format($coupon_info['minimum_to_apply'])
            );
        }

        //check minimum
        if ($coupon_info['type'] == 'B' && $details["buy_option"]=='purchase' && $details["buy_amount"] > $this->cart->getSubTotal() )
        {
            $this->error['warning'] = sprintf(
                $this->language->get('error_coupon_less_than_minimum'),
                $this->currency->format($coupon_info['minimum_to_apply'])
            );
        }


		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateVoucher()
	{
		$this->load->model('checkout/voucher');

		$voucher_info = $this->model_checkout_voucher->getVoucher($this->request->post['voucher']);

		if (!$voucher_info) {
			$this->error['warning'] = $this->language->get('error_voucher');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateReward()
	{
		$points = $this->customer->getRewardPoints();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		if (empty($this->request->post['reward'])) {
			$this->error['warning'] = $this->language->get('error_reward');
		}

		if ($this->request->post['reward'] > $points) {
			$this->error['warning'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
		}

		if ($this->request->post['reward'] > $points_total) {
			$this->error['warning'] = sprintf($this->language->get('error_maximum'), $points_total);
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateShipping()
	{
		if (!empty($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$this->error['warning'] = $this->language->get('error_shipping');
			}
		} else {
			$this->error['warning'] = $this->language->get('error_shipping');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function count()
	{
		$json = array();

		$json['product_count'] = $this->cart->countProducts();

		$this->response->setOutput(json_encode($json));
	}

	public function add()
	{
	    // check comming from
        $comming_from = isset($this->request->post['comming_from']) ? $this->request->post['comming_from'] : NULL;
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		// visitor should login/register before adding products to cart
		if ($this->config->get('login_before_add_to_cart') == 1 && !$this->customer->isLogged()) {

            $product_id = $this->request->post['product_id'] ? '&product_id='.$this->request->post['product_id'] : '';
            $redirectWithParams = $this->url->link('product/product', $product_id, 'ssl');
            $this->session->data['redirectWithParams'] = $redirectWithParams;


            $json['redirect'] = $this->url->link('account/login');
            $json['message']  = true;
            $json['error']['warning']=$this->language->get('ms_should_Log_in');
			$this->response->setOutput(json_encode($json));

			return;
		}

		$this->language->load_json('checkout/cart');

		//removed the below line because it corrupt the language of this function
        //if u want to add some language, please add it in checkout/cart
		//$this->language->load_json('product/product');

		$json = array();

		if (isset($this->request->post['product_id'])) {

            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

		//Check if MS Messaging seller installed
		$this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();
        if($multiseller) {
			//get product seller id
			$seller_id = $this->MsLoader->MsProduct->getSellerId($product_id);

        	//Check if MS Messaging seller installed and product related to a seller
        	$multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
        	$replaceAddToCart = $this->model_multiseller_status->is_replace_addtocart();

	        if(	$multisellerMessaging &&
	        	$seller_id &&
	        	$seller_id != (int)$this->customer->getId()&&
                $replaceAddToCart
	         ){
	            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/messagingseller', 'seller_id='.$seller_id.'&product_id=' . $this->request->post['product_id']));

	            $this->response->setOutput(json_encode($json));
	            return;
	        }
	        ////////////////////////////////////////
        }
	    /////////////////////////////////////////


		// Knawat Drop shippment api
		// Syncornize product data before adding to the cart.


		$this->load->model('setting/setting');
		if ($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']) {
			$app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);
			require_once $app_dir . "controller/module/knawat_dropshipping.php";
			$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping($this->registry);
			$this->controller_module_knawat_dropshipping->before_add_to_cart($product_id);
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);
		$product_affiliate_link_status = $this->config->get('product_affiliate_link_status');


        if($multiseller) {
            if ($seller_id && $seller_id == (int)$this->customer->getId() && $this->customer->isLogged()) {
                $this->language->load_json('multiseller/multiseller');
                $product_info = false;
                $json['error']['seller'] = $this->language->get('ms_error_seller_product');
            }
        }
		if ($product_info) {
            if($product_affiliate_link_status && $product_info['affiliate_link']) {
                //$json['redirect'] = str_replace('&amp;', '&', $product_info['affiliate_link']);
                $json['affiliate_link'] = $product_info['affiliate_link'];
                $json['success'] = 'affiliate_link';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $this->load->model('module/price_per_meter/settings');
	        if ($this->model_module_price_per_meter_settings->isActive() && $this->request->post['notDetailsPage']) {
	           if($product_info['price_meter_data'] && $product_info['price_meter_data']['main_status'] == 1){
	            	$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
	           }
            }
            // minimum deposit
            $this->load->model('module/minimum_deposit/settings');
            if($this->model_module_minimum_deposit_settings->isActive() && !empty($this->request->post["minimum_deposit_customer_price"]) && $this->request->post["minimum_deposit_customer_price"] != '0'){
                $defaultCurrency = $this->config->get('config_currency');
                $userCurrency = $this->currency->getCode();
                // if the user currency different than the default one then convert it
                if($defaultCurrency !== $userCurrency){
                    $this->request->post["minimum_deposit_customer_price"] = number_format(round($this->currency->convert($this->request->post["minimum_deposit_customer_price"], $userCurrency, $defaultCurrency), 2),
                        2, '.', ''
                    );
                }
                if ($this->request->post["minimum_deposit_customer_price"] <  (float)$product_info['minimum_deposit_price'] ) {
                    $json['error']['minimum_deposit']  =  sprintf($this->language->get('error_minimum_deposit_max'),(float)$this->request->post['minimum_deposit_price'] );
                }
            }

            //todo allow out of stock per product
			if ($product_info['quantity'] == 0  && $this->config->get('config_stock_checkout') == 0) {
                $json['error']['quantity']  =  $this->language->get('error_quantity');

            }elseif (isset($this->request->post['quantity'])){
				if($product_info['unlimited']){
					$quantity = $this->request->post['quantity'];
				}else{
					if( ($this->cart->getProductQuantity($product_id) + $this->request->post['quantity']) > $product_info['quantity'] && !$this->config->get('config_stock_checkout') ){
						$json['error']['quantity']  = sprintf($this->language->get('error_quantity_not_available'), $product_info['quantity'] );            		
					}else{
						$quantity = $this->request->post['quantity'];
					}
				}
            }
			else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
				if(isset($this->request->post['option_quantity'])){ //products builds
					foreach($option as $k=>$v){
						if(!is_array($option[$k])){ //to exclude multi select options
							$option[$k] = $option[$k].':'.$this->request->post['option_quantity'][$k];
						}
					}
				}
			} else {
				$option = array();
			}

            $this->load->model('module/rental_products/settings');
		    if($this->model_module_rental_products_settings->isActive()  ){ 
				//to make sure rental and reservation app is installed
				if (isset($this->request->post['dateRange']['from']) && isset($this->request->post['dateRange']['to'])
					&& !empty($this->request->post['dateRange']['from']) && !empty($this->request->post['dateRange']['to'])){
					// check if these days span has a disabled day and return error instead of adding to cart
					$from = strtotime($this->request->post['dateRange']['from']);
					$to = strtotime($this->request->post['dateRange']['to']);
                	$disabled_days = $this->model_catalog_product->getRentDisabledDates($from,$to,$product_id,$product_info['quantity'],$quantity);
                	if(count($disabled_days)){
						$json['error']['rent'] = $this->language->get('error_rental_days');
					}
				
					$option['rentalRange'] = array_map('strtotime', $this->request->post['dateRange']);
				}else{
					if($product_info['transaction_type'] == 2){
						//product is for rent and
						// the user pressed add to cart from category/products page without the rental dates
						$json['redirect'] = $this->url->link('product/product&product_id=' . $product_id);
					}
				}
		    }
			
            $this->load->model('module/product_bundles/settings');
            if ($this->model_module_product_bundles_settings->isActive() && isset($this->request->post['product_bundles']) ) {
				$option['productBundles'] = true;
			}			

			if (isset($this->request->post['pricePerMeter'])) {
				$option['pricePerMeterRange'] = $this->request->post['pricePerMeter'];
			}

			if (isset($this->request->post['fifaCards']))
            {
                $option['fifaCardsOptions'] = $this->request->post['fifaCards'];
                $stats_array = array();
                $final_stats_array = array();
                if (isset($option['fifaCardsOptions']['stats']) && !empty($option['fifaCardsOptions']['stats']))
                {
                    foreach ($option['fifaCardsOptions']['stats'] as $stat)
                    {
                        $stats_array[] = $stat;
                    }
                    $chunked_arrays = array_chunk($stats_array, 2, false);
                    foreach ($chunked_arrays as  $value)
                    {
                        if (!empty($value[1]) && !empty($value[0]))
                        {
                            $final_stats_array[] = ['key' => $value[1], 'value' => $value[0]];
                        }
                    }
                }
                elseif(!isset($option['fifaCardsOptions']['stats']))
                {
                    // default stats array
                    $final_stats_array = array(
                        ['key' => "PAC", 'value' => 99],
                        ['key' => 'SHO', 'value' => 99],
                        ['key' => 'PAS', 'value' => 99],
                        ['key' => 'DRI', 'value' => 99],
                        ['key' => 'DEF', 'value' => 99],
                        ['key' => 'PHY', 'value' => 99],
                    );
                }
                $option['fifaCardsOptions']['stats'] = array_values($final_stats_array);

                if (isset($option['fifaCardsOptions']['is_goal_keeper']) && $option['fifaCardsOptions']['is_goal_keeper'] == 1)
                {
                    $option['fifaCardsOptions']['is_goal_keeper'] = 'Yes';
                }
                else
                {
                    $option['fifaCardsOptions']['is_goal_keeper'] = 'No';
                }
            }


			//Printing Document
			$this->load->model('module/printing_document');
			if ($this->model_module_printing_document->isActive() && $product_info['printable'] == 1) {
				if ($this->request->post['print_pages']) {
					$option['print_pages'] = $this->request->post['print_pages'];
				} else {
					$json['error']['print_pages'] = $this->language->get('error_print_pages');
				}

				// if ($this->request->post['print_copies']) {
				// 	$option['print_copies'] = $this->request->post['print_copies'];
				// } else {
				// 	$json['error']['print_copies'] = $this->language->get('error_print_copies');
				// }
			}
			////////////////////////

			// Aliexpress
            
			if (\Extension::isInstalled('aliexpress_dropshipping')  && $this->config->get('module_wk_dropship_status')) {
			    // check if product is ali express product
			    if($this->cart->checkAliProductExists($product_id))
                {

                    if ($option) {
                        $this->load->model('checkout/warehouse');
                        foreach ($option as $key => $value) {
                            $wk_option = array();
                            $wk_option['product_option_value_id'] = $value;
                            $wk_option['product_option_id'] = $key;
                            $wk_result = $this->cart->getAlixOptionByProductOption($wk_option);
                            if ($wk_result && isset($wk_result['alix_option_value_id']) && $wk_result['alix_option_value_id']) {
                                $wk_options[] = $wk_result['alix_option_value_id'];
                            }
                        }
                        if (isset($wk_options) && $wk_options) {
                            if (is_array($wk_options) && count($wk_options) > 1) {
                                $single = false;
                            } else {
                                $single = true;
                            }
                            $wk_options = implode(',', $wk_options);
                            $wk_result = $this->cart->getOptionPrice($wk_options, $product_id, $single);
                            if (!$wk_result) {
                                $json['error']['option_variation'] = "Warning: This combination is not available right now, please try again later!";
                            }
                        }
                    }
                }
			}


            // EBay

            if (\Extension::isInstalled('ebay_dropshipping')  && $this->config->get('module_wk_ebay_dropship_status')) {
                $this->load->model('checkout/commerce');
                // check if product is ebay product
                if($this->model_checkout_commerce->checkEbayProductExists($product_id))
                {
                    if ($option) {
                        foreach ($option as $key => $value) {
                            $wk_option = array();
                            $wk_option['product_option_value_id'] = $value;
                            $wk_option['product_option_id'] = $key;
                            $wk_result = $this->model_checkout_commerce->getEbayOptionByProductOption($wk_option);
                            if ($wk_result && isset($wk_result['ebay_option_value_id']) && $wk_result['ebay_option_value_id']) {
                                $wk_ebay_options[] = $wk_result['ebay_option_value_id'];
                            }
                        }
                        if (isset($wk_ebay_options) && $wk_ebay_options) {
                            if (is_array($wk_ebay_options) && count($wk_ebay_options) > 1) {
                                $single = false;
                            } else {
                                $single = true;
                            }
                            $wk_ebay_options = implode(',', $wk_ebay_options);
                            $wk_result = $this->model_checkout_commerce->getEbayOptionPrice($wk_ebay_options, $product_id, $single);
                            if (!$wk_result) {
                                $json['error']['option_variation'] = "Warning: This combination is not available right now, please try again later!";
                            }
                        }
                    }
                }
            }

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			/**
			 * Check if Sku app is installed to validate product sku option quantity.
			 */
            $this->load->model('module/product_variations');
            $productsoptions_sku_installed_by = $this->config->get('productsoptions_sku_installed_by');
			if ($this->config->get('productsoptions_sku_status')
                ) {
				foreach ($option as $product_option_value_id) {

					if (!is_array($product_option_value_id) && filter_var($product_option_value_id, FILTER_VALIDATE_INT)) { //here we check if product id is integer and not array before get product data from database
						$option_value_ids[] = $this->model_catalog_product->getProductOptionValue($product_id, $product_option_value_id)['option_value_id'];
					} else {
						foreach ($product_option_value_id as $value) {
							if (is_numeric($value)) {
								$option_value_ids[] = $this->model_catalog_product->getProductOptionValue($product_id, $value)['option_value_id'];
							}
						}
					}
				}

				sort($option_value_ids);
				$option_value_ids = implode(",", $option_value_ids);
				$product_options_sku = $this->model_catalog_product->getProductVariationSku($product_id, $option_value_ids);
				if (count($product_options_sku) > 0 && (int) $product_options_sku['product_quantity'] == 0 && $option_value_ids) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_option_sku_quantity'));
				}
			}
				//Limit orders from one seller
				
				if ($this->MsLoader->isInstalled() && \Extension::isInstalled('delivery_slot') && $this->config->get('delivery_slot')['status'] == 1
		         && $this->config->get('msconf_delivery_slots_to_sellers'))
				{ 
					$cart_products=$this->cart->getProducts();
					if($cart_products)
					{
					$currenCartProductSellerID=$this->MsLoader->MsProduct->getSellerId($this->request->post['product_id']);	
					foreach($cart_products as $cartproduct)
					{ 
						$cart_product_seller_id[]=$this->MsLoader->MsProduct->getSellerId($cartproduct['product_id']);
						if(isset($this->request->post['new_order_from_new_seller'])){
							$this->cart->remove($cartproduct['product_id']);
						 }
					}
					if (!(in_array($currenCartProductSellerID, $cart_product_seller_id)))
					{
						if(! isset($this->request->post['new_order_from_new_seller'])){
						$sellerArray = array_unique($cart_product_seller_id);
		            	$seller_id=$sellerArray[0];
						$cart_product_seller_name=$this->MsLoader->MsProduct->getSellerName($seller_id);
		 				$json['limit_order_from_one_Seller'] = 1;
						$json['btn_new_order_limit_order']=$this->language->get('btn_new_order_limit_order');
						$json['btn_cancel_limit_order']=$this->language->get('btn_cancel_limit_order');
					    $json['text_limit_order_from_one_Seller'] = sprintf(
						$this->language->get('text_limit_order_from_one_Seller'),$cart_product_seller_name);
						$json['error']['warning'] = sprintf($this->language->get('text_limit_order_from_one_Seller_redirect_product'),$cart_product_seller_name, $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
						}
			     	} 
				  }
				}
				///limit order product or trip in case trips app installed
				if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') 
				&& $this->config->get('trips')['status']==1) ) 
				{   
					$this->load->model('module/trips');
                    $isATrip = $this->model_module_trips->IsTrip($this->request->post['product_id']);
					$tripProducts=
					$cart_products=$this->cart->getProducts();
					if($cart_products)
					{
						$cartContainsTripFlag =false;
						foreach($cart_products as $cartproduct)
					    { 
						$cartContainsTrip = $this->model_module_trips->IsTrip($cartproduct['product_id']);
						if($cartContainsTrip){
							$cartContainsTripFlag =true;
						}
					    }
						if( $isATrip || $cartContainsTripFlag )
					     {
						$json['trip_booking_cart_contains_products'] = 1;
						$json['text_trips_products_cart']=$this->language->get('text_trips_products_cart');
						$json['text_cart_dialog_continue']=$this->language->get('text_cart_dialog_continue');
						$json['text_cart_dialog_cart']=$this->language->get('text_cart_dialog_cart');
						$json['cart_link'] = $this->url->link('checkout/checkout');
						$json['error']['warning'] = sprintf($this->language->get('text_trips_products_cart'));
						 }
						
					}

				}
			/////////////////////////////////////////////////////////////////////////////////
			if (!$json) {
				
				$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

				if ($queryRewardPointInstalled->num_rows) {
					$this->load->model('rewardpoints/spendingrule');
					$this->load->model('rewardpoints/shoppingcartrule');
					$this->model_rewardpoints_spendingrule->getSpendingPoints();
					$this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
				}

				if (array_key_exists("pricePerMeterRange", $option)) {
					$ppm_settings = $this->model_module_price_per_meter_settings->getSettings();

					if (!empty($ppm_settings['default_unit']) && $ppm_settings['default_unit'] != 'm') {
						$option['pricePerMeterRange']['width'] = (string) ((float) $option['pricePerMeterRange']['width'] / 100);
						$option['pricePerMeterRange']['length'] = (string) ((float) $option['pricePerMeterRange']['length'] / 100);
					}
				}

                // minimum deposit
                if (isset($this->request->post["minimum_deposit_customer_price"])){
                    $option['minimum_deposit_customer_price'] = $this->request->post["minimum_deposit_customer_price"] ;
                }

				$this->load->model("module/curtain_seller");
				$curtain_seller_product_settings = $product_info['price_meter_data']['curtain_seller'];

				if ($this->model_module_curtain_seller->isEnabled() && !empty($curtain_seller_product_settings) && !empty($this->request->post['curtain_seller'])) {
					$curtain_seller_post_data = $option['curtain_seller'] = $this->request->post['curtain_seller']['options'];

					$curtain_seller_total = $this->model_module_curtain_seller->calculateTotal($curtain_seller_post_data['selling_type'], $this->request->post['product_id']);
					$option['curtain_seller']['total'] = $curtain_seller_total['cost'];
				}
                // if customer comming from cart remove product before insert it again
                if($comming_from == "cart" && isset($this->request->post['product_key'])){
                    $this->cart->remove($this->request->post['product_key']);
                }


				//Check if Stock Forecasting Quantity App installed & enabled
	        	if( \Extension::isinstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
					$this->language->load_json('module/stock_forecasting');
					//validate before add
					if( empty($this->request->post['stock_forecasting_date']) ){
						$json['error']['stock_forecasting_date_empty'] = $this->language->get('error_stock_forecasting_empty');
						$this->response->setOutput(json_encode($json));
						return;
					}

	        		$this->load->model('module/stock_forecasting');
	        		//Check delivery date
	                if(isset($this->session->data['stock_forecasting_cart'][$this->request->post['product_id']]) && $this->request->post['stock_forecasting_date'] != $this->session->data['stock_forecasting_cart'][$this->request->post['product_id']]['stock_forecasting_delivery_date']){
						$json['error']['stock_forecasting_date'] = $this->language->get('error_stock_forecasting_date');
						//Do not Add this item, because it's invalid...
						$this->response->setOutput(json_encode($json));
						return;
	                }

	                $stock_forecasting_new_quantity     = $this->request->post['quantity'];
					$stock_forecasting_current_quantity = $this->cart->getProductQuantity($this->request->post['product_id']);
					$allowed_quantity =  $this->model_module_stock_forecasting->getProductStockForecastingByDate($this->request->post['product_id'], $this->request->post['stock_forecasting_date']);
	
					if( ($stock_forecasting_new_quantity + $stock_forecasting_current_quantity) > $allowed_quantity ){
						$json['error']['stock_forecasting_quantity'] = $this->language->get('error_stock_forecasting_quantity');
						//Do not Add this item, because it's invalid...
						$this->response->setOutput(json_encode($json));
						return;
					}
				}


				// like4card

	        	if( \Extension::isInstalled('like4card') && ($this->config->get('like4card_app_status') == 1) && ($this->cart->countProducts() >=10) ){
					$json['error']['like4card_quantity'] = $this->language->get('error_like4card_quantity');
					$this->response->setOutput(json_encode($json));
	        		return;
				}
				/// Cart Add Action
				$this->cart->add($this->request->post['product_id'], $quantity, $option);
				
				
                if($this->customer->isLogged())
                {
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
                        $productCategories = $this->model_catalog_product->getCategoriesWithDesc($this->request->post['product_id']);
                        $productCategoriesArray = [];
                        if(is_array($productCategories)){
                            foreach ($productCategories as $key=>$category){
                                foreach ($category['category_description'] as $category_description)
                                    $productCategoriesArray[] = $category_description['name'];
                            }
                        }

                        $app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);
                        $eventData['events']['add_to_cart']['product_id'] = (string)$this->request->post['product_id'];
                        $eventData['events']['add_to_cart']['category'] = (string)implode(",",$productCategoriesArray);
                        $eventData['events']['add_to_cart']['price'] = (float)$product_info['price'];
                        $eventData['events']['add_to_cart']['stock'] = (string)$product_info['quantity'];
                        $eventData['events']['add_to_cart']['weight'] = (float)$product_info['weight'];
                        $eventData['playerUniqueId'] = $customer_id;
                        $this->model_module_gameball_settings->sendGameballEvent($eventData);
                    }
                }

				// << Product Option Image PRO module
				$this->load->model('module/product_option_image_pro');
				$poip_options = array();
				foreach ($option as $po_id => $pov_id) {
					$poip_options[] = array('product_option_id' => $po_id, 'product_option_value_id' => $pov_id);
				}
				$product_info['image'] = $this->model_module_product_option_image_pro->getProductCartImage((int) $product_info['product_id'], $poip_options, $product_info['image']);
				// >> Product Option Image PRO module

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));
				$json['text_cart_dialog'] = $this->language->get('text_cart_dialog');
				$json['text_cart_dialog_cart'] = $this->language->get('text_cart_dialog_cart');
				$json['text_cart_dialog_continue'] = $this->language->get('text_cart_dialog_continue');
				$json['cart_link'] = $this->url->link('checkout/checkout');
				$json['enable_order_popup'] = $this->config->get('config_order_popup');
				
	        	if( \Extension::isInstalled('stock_forecasting') && $this->config->get('stock_forecasting_status') == 1 ){
					//return new quantity
					// $json['product_cart_current_quantity'] = $this->cart->getProductQuantity($this->request->post['product_id']);
					//Set the selected delivery date....
					$this->session->data['stock_forecasting_cart'][$this->request->post['product_id']]['stock_forecasting_delivery_date'] = $this->request->post['stock_forecasting_date'];
				}

				unset($this->session->data['shipping_method']);
				// Clear shipping address to avoid unmatching between cached shipping address and default shipping address
				unset($this->session->data['payment_address']);
				unset($this->session->data['shipping_address']);
				//////////////////////////////////////////////////
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$total_data = array();
				$total = 0;
				$taxes = $this->cart->getTaxes();

				// Display prices
				if ($isCustomerAllowedToViewPrice) {

					// --------------- Shipping Method ---------------

					// Grab default zone id and country id to form a fake address in case the user isn't logged in
					if (!$this->customer->isLogged()) {
						$customer_address['zone_id'] = $this->config->get('config_zone_id');
						$customer_address['country_id'] = $this->config->get('config_country_id');
					} else {
						// The customer is logged in so we are fetching his address

						$customer_address_id = $this->customer->getAddressId();
						$this->load->model('account/address');
						$customer_address = $this->model_account_address->getAddress($customer_address_id);

						// what if the customer is logged in but doesn't have addresses?
						// Then we fallback to the default country and zone ids ;)
						if (empty($customer_address['country_id'])) {
							$customer_address['country_id'] = $this->config->get('config_country_id');
						}

						if (empty($customer_address['zone_id'])) {
							$customer_address['zone_id'] = $this->config->get('config_zone_id');
						}
					}
              
					$this->session->data['shipping_methods'] = $this->get_shipping_methods($customer_address);
					

					$this->session->data['default_shipping_method'] = null;

					if (!empty($this->session->data['shipping_methods'])) {
						if (!isset($this->session->data['shipping_method'])) {
							$first = current($this->session->data['shipping_methods']);
							$first = (is_array($first['quote'])) ? current($first['quote']) : $first['quote'];

							$this->session->data['default_shipping_method'] = $this->session->data['shipping_method'] = $first;
						} else {
							$this->session->data['default_shipping_method'] = $this->session->data['shipping_method'];
						}
					}
					// --------------- /Shipping Method ---------------

					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('total/' . $result['code']);

							$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
						}

						$sort_order = array();

						foreach ($total_data as $key => $value) {
							$sort_order[$key] = $value['sort_order'];
						}

						array_multisort($sort_order, SORT_ASC, $total_data);
					}
				}

				$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0) + (isset($this->session->data['subscription']) ? 1 : 0), $this->currency->format($total));
				$json['product_count'] = $this->cart->countProducts();
				$total_quantity = 0;
	            foreach ($this->session->data['cart'] as $key => $quantity) {
					$p = explode(':', $key);
					if($p[0] == $product_info['product_id']){
						$total_quantity += $quantity;
					}
				}
				$json['added_product_total_quantity'] = $total_quantity;
			} else {
				// in case the add to cart button pressed outside product page and there are required product options redirect to product's page
				parse_str(html_entity_decode(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY)), $queryString);
				if (isset($json['error']['option']) && !isset($json['redirect']) && (strpos($this->request->server['HTTP_REFERER'], 'route=product/product') === false || $queryString['product_id'] != $product_info['product_id']))
				{
				    $json['options_error'] = 1;
				    if($comming_from != "cart"){
                        $json['redirect'] = $this->url->link('product/product&product_id=' . $product_id);
                    }

				}
			}

			// Facebook Pixel Tracking
            $facebook_pixel_settings=$this->model_setting_setting->getSetting('integrations');

			if (in_array('Add to Basket', $facebook_pixel_settings['mn_integ_fbp_action'])) {

                $catalog_id = "0";
                if (\Extension::isInstalled('facebook_import')) {
                    $catalog_id = $this->model_catalog_product->getFacebookCatalogId()['catalog_id'];
                }
				$json['fb_data_track'] = [
					'event' => 'AddToCart',
					'ip_address' => $this->request->server['REMOTE_ADDR'],
					'product_name' => $product_info['name'],
                    'content_type'=> 'product',
                    'content_ids'=> "['".$product_info['product_id']."']",
                    'value' => ($product_info['special'] ?? $product_info['price']),
                    'currency'=> $this->currency->getCode(),
                    'product_catalog_id'=> $catalog_id,
				];
			}

		}
		// check if user comming from cart redirect him back to cart
        if($comming_from == "cart"){
            $json['redirect'] = $this->url->link('checkout/cart');
        }

        if(isset($json['options_error'])){
        	$popup_template_exists = false;
        	if(file_exists(DIR_TEMPLATE . 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/product_popup.expand')) {
                $this->template = 'customtemplates/' . STORECODE . '/' . $this->config->get('config_template') . '/template/checkout/product_popup.expand';
        		$popup_template_exists = true;
            }
            elseif(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/product_popup.expand') || file_exists(DIR_CUSTOM_TEMPLATE . $this->config->get('config_template') . '/template/checkout/product_popup.expand')){
                $this->template = $this->config->get('config_template') . '/template/checkout/product_popup.expand';
        		$popup_template_exists = true;
            }

            if($popup_template_exists){
            	$this->request->get = array_merge($this->request->get, ['product_id' => $this->request->post['product_id']]);
	        	$this->data = $this->getBaseData('product/product');
	        	$json['popup_html'] = $this->render_ecwig();
            }
        }

		$this->response->setOutput(json_encode($json));
	}

	public function product_checkout()
	{
		$isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();

		// visitor should login/register before adding products to cart
		if ($this->config->get('login_before_add_to_cart') == 1 && !$this->customer->isLogged()) {

            $product_id = $this->request->get['product_id'] ? '&product_id='.$this->request->get['product_id'] : '';
            $redirectWithParams = $this->url->link('product/product', $product_id, 'ssl');
            $this->session->data['redirectWithParams'] = $redirectWithParams;

            $this->redirect($this->url->link('account/login'));
		}

		if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        } else {
            $product_id = 0;
        }

		//Check if MS Messaging seller installed
		$this->load->model('multiseller/status');
        $multiseller = $this->model_multiseller_status->is_installed();
        if($multiseller) {
			//get product seller id
			$seller_id = $this->MsLoader->MsProduct->getSellerId($product_id);

        	//Check if MS Messaging seller installed and product related to a seller
        	$multisellerMessaging = $this->model_multiseller_status->is_messaging_installed();
        	$replaceAddToCart = $this->model_multiseller_status->is_replace_addtocart();

	        if(	$multisellerMessaging &&
	        	$seller_id &&
	        	$seller_id != (int)$this->customer->getId()&&
                $replaceAddToCart
	         ){
	         	$this->redirect(str_replace('&amp;', '&', $this->url->link('account/messagingseller', 'seller_id='.$seller_id.'&product_id=' . $this->request->get['product_id'])));
	        }
        }

		$this->load->model('setting/setting');
		if ($this->model_setting_setting->getSetting('knawat_dropshipping_status')['status']) {
			$app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);
			require_once $app_dir . "controller/module/knawat_dropshipping.php";
			$this->controller_module_knawat_dropshipping = new ControllerModuleKnawatDropshipping($this->registry);
			$this->controller_module_knawat_dropshipping->before_add_to_cart($product_id);
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);
		$product_affiliate_link_status = $this->config->get('product_affiliate_link_status');


        if($multiseller) {
            if ($seller_id && $seller_id == (int)$this->customer->getId() && $this->customer->isLogged()) {
                $this->language->load_json('multiseller/multiseller');
                $product_info = false;
	           	$this->redirect(str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->get['product_id'])));
            }
        }
		if ($product_info) {
            $this->load->model('module/price_per_meter/settings');
	        if ($this->model_module_price_per_meter_settings->isActive() && $this->request->get['notDetailsPage']) {
	           if($product_info['price_meter_data'] && $product_info['price_meter_data']['main_status'] == 1){
	           		$this->redirect(str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->get['product_id'])));
	           }
            }
            // minimum deposit
            $this->load->model('module/minimum_deposit/settings');
            if($this->model_module_minimum_deposit_settings->isActive() && !empty($this->request->get["minimum_deposit_customer_price"]) && $this->request->get["minimum_deposit_customer_price"] != '0'){
                if ($this->request->get["minimum_deposit_customer_price"] <  (float)$this->request->get['minimum_deposit_price'] ) {
	           		$this->redirect(str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->get['product_id'])));
                }
            }
            //todo allow out of stock per product
			if ($product_info['quantity'] == 0  && $this->config->get('config_stock_checkout') == 0) {
	           	$this->redirect(str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->get['product_id'])));
            }
			else {
				$quantity = 1;
			}

			$option = array();		

			//Printing Document
			$this->load->model('module/printing_document');
			if ($this->model_module_printing_document->isActive() && $product_info['printable'] == 1) {
           		$this->redirect($this->url->link('product/product&product_id=' . $product_id));
			}

			// Aliexpress
            
			if (\Extension::isInstalled('aliexpress_dropshipping')  && $this->config->get('module_wk_dropship_status')) {
			    // check if product is ali express product
			    if($this->cart->checkAliProductExists($product_id))
                {
                	$this->redirect($this->url->link('product/product&product_id=' . $product_id));
                }
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$this->redirect($this->url->link('product/product&product_id=' . $product_id));
				}
			}

			/**
			 * Check if Sku app is installed to validate product sku option quantity.
			 */
            $this->load->model('module/product_variations');
            $productsoptions_sku_installed_by = $this->config->get('productsoptions_sku_installed_by');
			if ($this->config->get('productsoptions_sku_status')
                ) {
				foreach ($option as $product_option_value_id) {

					if (!is_array($product_option_value_id) && filter_var($product_option_value_id, FILTER_VALIDATE_INT)) { //here we check if product id is integer and not array before get product data from database
						$option_value_ids[] = $this->model_catalog_product->getProductOptionValue($product_id, $product_option_value_id)['option_value_id'];
					} else {
						foreach ($product_option_value_id as $value) {
							if (is_numeric($value)) {
								$option_value_ids[] = $this->model_catalog_product->getProductOptionValue($product_id, $value)['option_value_id'];
							}
						}
					}
				}

				sort($option_value_ids);
				$option_value_ids = implode(",", $option_value_ids);
				$product_options_sku = $this->model_catalog_product->getProductVariationSku($product_id, $option_value_ids);
				if (count($product_options_sku) > 0 && (int) $product_options_sku['product_quantity'] == 0 && $option_value_ids) {
	           		$this->redirect($this->url->link('product/product&product_id=' . $product_id));
				}
			}

			$queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

			if ($queryRewardPointInstalled->num_rows) {
				$this->load->model('rewardpoints/spendingrule');
				$this->load->model('rewardpoints/shoppingcartrule');
				$this->model_rewardpoints_spendingrule->getSpendingPoints();
				$this->model_rewardpoints_shoppingcartrule->getShoppingCartPoints();
			}

			if (array_key_exists("pricePerMeterRange", $option)) {
				$ppm_settings = $this->model_module_price_per_meter_settings->getSettings();

				if (!empty($ppm_settings['default_unit']) && $ppm_settings['default_unit'] != 'm') {
					$option['pricePerMeterRange']['width'] = (string) ((float) $option['pricePerMeterRange']['width'] / 100);
					$option['pricePerMeterRange']['length'] = (string) ((float) $option['pricePerMeterRange']['length'] / 100);
				}
			}

			$this->cart->add($this->request->get['product_id'], $quantity, $option);

            if($this->customer->isLogged())
            {
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

                    $app_dir = str_replace('system/', 'expandish/', DIR_SYSTEM);
                    $eventData['events']['add_to_cart']['product_id'] = (string)$this->request->get['product_id'];
                    $eventData['events']['add_to_cart']['category'] = (string)implode(",",$productCategoriesArray);
                    $eventData['events']['add_to_cart']['price'] = (float)$product_info['price'];
                    $eventData['events']['add_to_cart']['stock'] = (string)$product_info['quantity'];
                    $eventData['events']['add_to_cart']['weight'] = (float)$product_info['weight'];
                    $eventData['playerUniqueId'] = $customer_id;
                    $this->model_module_gameball_settings->sendGameballEvent($eventData);
                }
            }

			// << Product Option Image PRO module
			$this->load->model('module/product_option_image_pro');
			$poip_options = array();
			foreach ($option as $po_id => $pov_id) {
				$poip_options[] = array('product_option_id' => $po_id, 'product_option_value_id' => $pov_id);
			}
			$product_info['image'] = $this->model_module_product_option_image_pro->getProductCartImage((int) $product_info['product_id'], $poip_options, $product_info['image']);
			// >> Product Option Image PRO module

			unset($this->session->data['shipping_method']);
			// Clear shipping address to avoid unmatching between cached shipping address and default shipping address
			unset($this->session->data['payment_address']);
			unset($this->session->data['shipping_address']);
			//////////////////////////////////////////////////
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);

			// Totals
			$this->load->model('setting/extension');

			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			// Display prices
			if ($isCustomerAllowedToViewPrice) {


				if (!$this->customer->isLogged()) {
					$customer_address['zone_id'] = $this->config->get('config_zone_id');
					$customer_address['country_id'] = $this->config->get('config_country_id');
				} else {
					// The customer is logged in so we are fetching his address

					$customer_address_id = $this->customer->getAddressId();
					$this->load->model('account/address');
					$customer_address = $this->model_account_address->getAddress($customer_address_id);

					// what if the customer is logged in but doesn't have addresses?
					// Then we fallback to the default country and zone ids ;)
					if (empty($customer_address['country_id'])) {
						$customer_address['country_id'] = $this->config->get('config_country_id');
					}

					if (empty($customer_address['zone_id'])) {
						$customer_address['zone_id'] = $this->config->get('config_zone_id');
					}
				}
          
				$this->session->data['shipping_methods'] = $this->get_shipping_methods($customer_address);
				

				$this->session->data['default_shipping_method'] = null;

				if (!empty($this->session->data['shipping_methods'])) {
					if (!isset($this->session->data['shipping_method'])) {
						$first = current($this->session->data['shipping_methods']);
						$first = (is_array($first['quote'])) ? current($first['quote']) : $first['quote'];

						$this->session->data['default_shipping_method'] = $this->session->data['shipping_method'] = $first;
					} else {
						$this->session->data['default_shipping_method'] = $this->session->data['shipping_method'];
					}
				}
				// --------------- /Shipping Method ---------------

				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);

						$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
					}

					$sort_order = array();

					foreach ($total_data as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $total_data);
				}
			}

		}

		if(isset($this->request->get['psid'])){
			$this->session->data['psid'] = $this->request->get['psid'];
		}

		$this->redirect($this->url->link('checkout/checkout'));
	}


	private function get_shipping_methods($shipping_address)
	{
		$quote_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status') && $result['code']!='seller_based') {
				$this->load->model('shipping/' . $result['code']);

				$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

				if ($quote) {
					$quote_data[$result['code']] = array(
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}

		$sort_order = array();

		foreach ($quote_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $quote_data);

		return $quote_data;
	}


	public function quote()
	{
		$this->language->load_json('checkout/cart');

		$json = array();

		if (!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if (!$this->cart->hasShipping()) {
			$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		}

		if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
			$json['error']['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$json['error']['zone'] = $this->language->get('error_zone');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry((int) $this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 10)) {
			$json['error']['postcode'] = $this->language->get('error_postcode');
		}

		if (!$json) {
			$this->tax->setShippingAddress((int) $this->request->post['country_id'], (int) $this->request->post['zone_id']);

			// Default Shipping Address
			$this->session->data['shipping_country_id'] = (int) $this->request->post['country_id'];
			$this->session->data['shipping_zone_id'] = (int) $this->request->post['zone_id'];
			$this->session->data['shipping_postcode'] = $this->request->post['postcode'];

			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$this->load->model('localisation/zone');

			//Get EN language ID
			$lang_id = null;
			if ($this->config->get('config_language') == 'ar') {
				$this->load->model('localisation/language');
				$languages = $this->model_localisation_language->getLanguages();
				if ($languages['en']['language_id'])
					$lang_id = $languages['en']['language_id'];
			}
			///////////////////

			$zone_info = $this->model_localisation_zone->getZone((int) $this->request->post['zone_id'], $lang_id);

			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$address_data = array(
				'firstname'      => '',
				'lastname'       => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city'           => '',
				'zone_id'        => (int) $this->request->post['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => (int) $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);

			$quote_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);

					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($address_data);

					if ($quote) {
						$quote_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $quote_data);

			$this->session->data['shipping_methods'] = $quote_data;

			if ($this->session->data['shipping_methods']) {
				$json['shipping_method'] = $this->session->data['shipping_methods'];
			} else {
				$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	public function country()
	{
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function zone()
	{
		$output = '<option value="0">' . $this->language->get('text_all_zones') . '</option>';

		$this->load->model('localisation/zone');

		$results = $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']);

		foreach ($results as $result) {
			$output .= '<option value="' . $result['zone_id'] . '"';

			if ($this->request->get['zone_id'] == $result['zone_id']) {
				$output .= ' selected="selected"';
			}

			$output .= '>' . $result['name'] . '</option>';
		}

		$this->response->setOutput($output);
	}

	public function coupon()
	{

		$this->language->load_json('checkout/cart');
		$this->language->load_json('product/product');

		if (!$this->validateCoupon()) {
			$json['error'] = true;
			$json['success'] = $this->error['warning'];
		} else {
			$this->session->data['coupon'] = $this->request->post['coupon'];
			$json['error'] = false;
			$json['success'] = $this->language->get('text_coupon');
		}

		return $this->response->setOutput(json_encode($json));
	}

	public function removeSubscriptionPlan(){
		unset($this->session->data['subscription']);
		$this->redirect($this->url->link('checkout/checkout'));
	}

	public function getTotalWithShipping(){
		$this->load->model('setting/extension');

        $total_data = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();

        // Display prices
        $isCustomerAllowedToViewPrice = $this->customer->isCustomerAllowedToViewPrice();
        if ($isCustomerAllowedToViewPrice) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if($this->config->get('hide_shipping_cart') && $result['code'] == 'shipping'){
                    continue;
                }
                if ($this->config->get($result['code'] . '_status')) {
                    $this->load->model('total/' . $result['code']);

                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
                }

                $sort_order = array();

                foreach ($total_data as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $total_data);
            }
        }

        $key = array_search('total', array_column($total_data, 'code'));
        $this->data['cart_total'] = $total_data[$key]['text'];

        $json['success'] = true;
		$json['total'] = $total_data[$key]['text'];

		return $this->response->setOutput(json_encode($json));
	}

}
