<?php
//==============================================================================
// Category & Product-Based Shipping v210.1
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

class ModelShippingCategoryProductBased extends Model {
	private $type = 'shipping';
	private $name = 'category_product_based';
	private $charge;
	
	public function getQuote($address) {
		$settings = $this->getSettings();
		
		if ($settings['testing_mode']) {
			$this->log->write(strtoupper($this->name) . ': ------------------------------ Starting testing mode ------------------------------');
		}
		
		if (empty($settings['status'])) {
			if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ': Extension is disabled');
			return;
		}
		
		$language_text = $this->language->load_json('product/product');
		
		// Set address info
		$addresses = array();
		$this->load->model('account/address');
		foreach (array('shipping', 'payment') as $address_type) {
			if (empty($address) || $address_type == 'payment') {
				$address = array();
				
				if ($this->customer->isLogged()) 										$address = $this->model_account_address->getAddress($this->customer->getAddressId());
				if (!empty($this->session->data['country_id']))							$address['country_id'] = $this->session->data['country_id'];
				if (!empty($this->session->data['zone_id']))							$address['zone_id'] = $this->session->data['zone_id'];
				if (!empty($this->session->data['postcode']))							$address['postcode'] = $this->session->data['postcode'];
				if (!empty($this->session->data['city']))								$address['city'] = $this->session->data['city'];
				
				if (!empty($this->session->data[$address_type . '_country_id']))		$address['country_id'] = $this->session->data[$address_type . '_country_id'];
				if (!empty($this->session->data[$address_type . '_zone_id']))			$address['zone_id'] = $this->session->data[$address_type . '_zone_id'];
				if (!empty($this->session->data[$address_type . '_postcode']))			$address['postcode'] = $this->session->data[$address_type . '_postcode'];
				if (!empty($this->session->data[$address_type . '_city']))				$address['city'] = $this->session->data[$address_type . '_city'];
				
				if (!empty($this->session->data['guest'][$address_type]))				$address = $this->session->data['guest'][$address_type];
				if (!empty($this->session->data[$address_type . '_address_id']))		$address = $this->model_account_address->getAddress($this->session->data[$address_type . '_address_id']);
				if (!empty($this->session->data[$address_type . '_address']))			$address = $this->session->data[$address_type.'_address'];
			}
			
			if (empty($address['address_1']))	$address['address_1'] = '';
			if (empty($address['address_2']))	$address['address_2'] = '';
			if (empty($address['city']))		$address['city'] = '';
			if (empty($address['postcode']))	$address['postcode'] = '';
			if (empty($address['country_id']))	$address['country_id'] = $this->config->get('config_country_id');
			if (empty($address['zone_id']))		$address['zone_id'] =  $this->config->get('config_zone_id');
			
			$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = " . (int)$address['country_id']);
			$address['country'] = (isset($country_query->row['name'])) ? $country_query->row['name'] : '';
			$address['iso_code_2'] = (isset($country_query->row['iso_code_2'])) ? $country_query->row['iso_code_2'] : '';
			
			$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = " . (int)$address['zone_id']);
			$address['zone'] = (isset($zone_query->row['name'])) ? $zone_query->row['name'] : '';
			$address['zone_code'] = (isset($zone_query->row['code'])) ? $zone_query->row['code'] : '';
			
			$addresses[$address_type] = $address;
			
			$addresses[$address_type]['geo_zones'] = array();
			$geo_zones_sql= "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = " . (int)$address['country_id'] . " AND (zone_id = 0 OR zone_id = " . (int)$address['zone_id'] . ") ";

			if($address['area_id']){
				$geo_zones_sql .= " AND (area_id=0 or area_id ='" .$address['area_id'] . "')";
			}

			$geo_zones_query = $this->db->query($geo_zones_sql);
			if ($geo_zones_query->num_rows) {
				foreach ($geo_zones_query->rows as $geo_zone) {
					$addresses[$address_type]['geo_zones'][] = $geo_zone['geo_zone_id'];
				}
			} else {
				$addresses[$address_type]['geo_zones'] = array(0);
			}
		}
		
		// Set order totals if necessary
		if ($this->type != 'total') {
			$order_totals_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'total' ORDER BY `code` ASC");
			$order_totals = $order_totals_query->rows;
			
			$sort_order = array();
			foreach ($order_totals as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			array_multisort($sort_order, SORT_ASC, $order_totals);

			// shifting shipping code to the end of array
			$shippingKey = array_search('shipping', array_column($order_totals, 'code'));
			if($shippingKey !== false){
				$totalObject = $order_totals[$shippingKey];
				unset($order_totals[$shippingKey]);
				$order_totals[]=$totalObject;
			}
			
			$total_data = array();
			$order_total = 0;
			$taxes = $this->cart->getTaxes();
			
			foreach ($order_totals as $ot) {
				if ($ot['code'] == $this->type) break;
				if (!$this->config->get($ot['code'] . '_status')) continue;
				$this->load->model('total/' . $ot['code']);
				$this->{'model_total_' . $ot['code']}->getTotal($total_data, $order_total, $taxes);
			}
		}
		
		// Loop through charges
		$this->load->model('catalog/product');
		
		$cart_products = $this->cart->getProducts();
		if (version_compare(VERSION, '2.1', '>=')) {
			foreach ($cart_products as &$cart_product) {
				$cart_product['key'] = $cart_product['cart_id'];
			}
		}
		
		$currency = $this->session->data['currency'];
		$customer_id = (int)$this->customer->getId();
		$customer_group_id = (version_compare(VERSION, '2.0') < 0) ? (int)$this->customer->getCustomerGroupId() : (int)$this->customer->getGroupId();
		$default_currency = $this->config->get('config_currency');
		$distance = 0;
		$language = $this->session->data['language'];
		$store_id = (int)$this->config->get('config_store_id');
		
		$charges = array();
		

		///Warehouse Check
		//$warehouse_setting = $this->config->get('warehouses');
		//////////////////

		foreach ($settings['charge'] as $charge) {
			// Set up basic charge data
			$charge['testing_mode'] = $settings['testing_mode'];
			
			if (!empty($charge['title_admin'])) {
				$charge['title'] = $charge['title_admin'];
			} elseif (!empty($charge['title_' . $language])) {
				$charge['title'] = $charge['title_' . $language];
			} elseif (!empty($charge['group'])) {
				$charge['title'] = '(Group ' . $charge['group'] . ')';
			} else {
				$charge['title'] = '';
			}
			
			if (empty($charge['group'])) {
				$charge['group'] = 0;
			}
			if (empty($charge['type'])) {
				$charge['type'] = str_replace(array('_based', '_fee'), '', $this->name);
			}
			
			$this->charge = $charge;
			
			if ((int)$charge['group'] < 0) {
				if ($this->charge['testing_mode']) {
					$this->log->write(strtoupper($this->name) . ': "' . $this->charge['title'] . '" disabled because it has a negative Group value');
				}
				continue;
			}
			
			// Compile rules and rule sets
			$rule_list = (!empty($charge['rule'])) ? $charge['rule'] : array();
			$rule_sets = array();
			
			foreach ($rule_list as $rule) {
				if (isset($rule['type']) && $rule['type'] == 'rule_set') {
					$rule_sets[] = $settings['rule_set'][$rule['value']]['rule'];
				}
			}
			
			foreach ($rule_sets as $rule_set) {
				$rule_list = array_merge($rule_list, $rule_set);
			}
			
			$rules = array();
			
			
			foreach ($rule_list as $rule) {
				if (empty($rule['type'])) continue;
				
				if (isset($rule['value'])) {
					if (in_array($rule['type'], array('attribute', 'attribute_group', 'category', 'manufacturer', 'product'))) { //'warehouse'
						$value = substr($rule['value'], strrpos($rule['value'], '[') + 1, -1);
					} else {
						$value = $rule['value'];
					}
				} else {
					$value = 1;
				}
				
				if (!isset($rule['comparison'])) $rule['comparison'] = '';
				$comparison = ($rule['type'] == 'option') ? substr($rule['comparison'], strrpos($rule['comparison'], '[') + 1, -1) : $rule['comparison'];
				$rules[$rule['type']][$comparison][] = $value;
			}
			$this->charge['rules'] = $rules;

			// Perform settings overrides
			if (!empty($defaults)) {
				foreach ($defaults as $key => $value) {
					$this->config->set($key, $value);
				}
			}
			
			$defaults = array();
			
			if (isset($rules['setting_override'])) {
				foreach ($rules['setting_override'] as $setting => $override) {
					$defaults[$setting] = $this->config->get($setting);
					$this->config->set($setting, $override[0]);
					
					if ($setting == 'config_address') {
						$distance = 0;
					}
				}
			}
			
			// Check date/time criteria
			if ($this->ruleViolation('day', strtolower(date('l'))) ||
				$this->ruleViolation('date', date('Y-m-d')) ||
				$this->ruleViolation('time', date('H:i'))
			) {
				continue;
			}
			
			// Check location criteria
			if (isset($rules['location_comparison'])) {
				$location_comparison = $rules['location_comparison'][''][0];
			} else {
				$location_comparison = ($this->type == 'shipping' || empty($addresses['payment']['postcode'])) ? 'shipping' : 'payment';
			}
			$address = $addresses[$location_comparison];
			$postcode = $address['postcode'];
			
			if (isset($rules['city'])) {
				$this->commaMerge($rules['city']);
				$this->charge['rules']['city'] = $rules['city'];
			}
			//Trim city because, rules in DB are also trimmed so we can compare successfully 
			if ($this->ruleViolation('city', trim(strtolower($address['city'])))) {
				continue;
			}
			if ($this->ruleViolation('geo_zone', $address['geo_zones'])) {
				continue;
			}
			
			if ((isset($rules['distance']) || $charge['type'] == 'distance') && !$distance) {
				$context = stream_context_create(array('http' => array('ignore_errors' => '1')));
				$store_address = html_entity_decode(preg_replace('/\s+/', '+', $this->config->get('config_address')), ENT_QUOTES, 'UTF-8');
				$customer_address = $address['address_1'] . ' ' . $address['address_2'] . ' ' . $address['city'] . ' ' . $address['zone'] . ' ' . $address['country'] . ' ' . $address['postcode'];
				$customer_address = html_entity_decode(preg_replace('/\s+/', '+', $customer_address), ENT_QUOTES, 'UTF-8');
				
				if (isset($settings['distance_calculation']) && $settings['distance_calculation'] == 'driving') {
					$directions = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin=' . $store_address . '&destination=' . $customer_address, false, $context));
					if (empty($directions->routes)) {
						sleep(1);
						$directions = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/directions/json?origin=' . $store_address . '&destination=' . $customer_address, false, $context));
						if (empty($directions->routes)) {
							$this->log->write(strtoupper($this->name) . ': The Google directions service returned the error "' . $directions->status . '" for origin "' . $store_address . '" and destination "' . $customer_address . '"');
							continue;
						}
					}
					$distance = $directions->routes[0]->legs[0]->distance->value / 1609.344;
				} else {
					if ($this->config->get('config_geocode')) {
						$xy = explode(',', $this->config->get('config_geocode'));
						$x1 = $xy[0];
						$y1 = $xy[1];
					} else {
						$geocode = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $store_address, false, $context));
						if (empty($geocode->results)) {
							sleep(1);
							$geocode = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $store_address, false, $context));
							if (empty($geocode->results)) {
								$this->log->write(strtoupper($this->name) . ': The Google geocoding service returned the error "' . $geocode->status . '" for address "' . $store_address . '"');
								continue;
							}
						}
						$x1 = $geocode->results[0]->geometry->location->lat;
						$y1 = $geocode->results[0]->geometry->location->lng;
					}
					
					$geocode = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $customer_address, false, $context));
					if (empty($geocode->results)) {
						sleep(1);
						$geocode = json_decode(file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . $customer_address, false, $context));
						if (empty($geocode->results)) {
							$this->log->write(strtoupper($this->name) . ': The Google geocoding service returned the error "' . $geocode->status . '" for address "' . $customer_address . '"');
							continue;
						}
					}
					$x2 = $geocode->results[0]->geometry->location->lat;
					$y2 = $geocode->results[0]->geometry->location->lng;
					
					$distance = rad2deg(acos(sin(deg2rad($x1)) * sin(deg2rad($x2)) + cos(deg2rad($x1)) * cos(deg2rad($x2)) * cos(deg2rad($y1 - $y2)))) * 60 * 114 / 99;
				}
				
				if (isset($settings['distance_units']) && $settings['distance_units'] == 'km') {
					$distance *= 1.609344;
				}
			}
			if (isset($rules['distance'])) {
				$this->commaMerge($rules['distance']);
				
				foreach ($rules['distance'] as $comparison => $distances) {
					$in_range = $this->inRange($distance, $distances, 'distance ' . $comparison);
					
					if (($comparison == 'is' && !$in_range) || ($comparison == 'not' && $in_range)) {
						continue 2;
					}
				}
			}
			
			if (isset($rules['postcode'])) {
				$this->commaMerge($rules['postcode']);
				
				foreach ($rules['postcode'] as $comparison => $postcodes) {
					$in_range = $this->inRange($address['postcode'], $postcodes, 'postcode ' . $comparison);
					
					if (($comparison == 'is' && !$in_range) || ($comparison == 'not' && $in_range)) {
						continue 2;
					}
				}
			}
			
			// Check order criteria
			if ($this->ruleViolation('currency', $currency) ||
				$this->ruleViolation('customer_group', $customer_group_id) ||
				$this->ruleViolation('language', $language) ||
				$this->ruleViolation('store', $store_id)
			) {
				continue;
			}
			
			if (isset($rules['past_orders'])) {
				if (!$customer_id) continue;
				$past_orders_query = $this->db->query("SELECT ROUND((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(date_added)) / 86400) AS days, COUNT(*) AS quantity, SUM(total) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = " . $customer_id . " AND order_status_id > 0");
				foreach ($rules['past_orders'] as $comparison => $values) {
					if (!$this->inRange($past_orders_query->row[$comparison], $values)) {
						continue 2;
					}
				}
			}
			
			// Generate comparison values
			$cart_criteria = array(
				'length',
				'width',
				'height',
				'quantity',
				'stock',
				'total',
				'volume',
				'weight',
			);
			
			foreach ($cart_criteria as $spec) {
				${$spec.'s'} = array();
				if (isset($rules[$spec])) {
					$this->commaMerge($rules[$spec]);
				}
			}
			
			$categorys = array();
			$manufacturers = array();
			$products = array();
			
			$product_keys = array();
			
			foreach ($cart_products as $product) {
				if ($this->type == 'shipping' && !$product['shipping']) {
					$order_total -= $product['total'];
					if ($this->charge['testing_mode']) {
						$this->log->write(strtoupper($this->name) . ': ' . $product['name'] . ' (product_id: ' . $product['product_id'] . ') does not require shipping and was ignored');
					}
					continue;
				}
				
				$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = " . (int)$product['product_id']);
				
				// dimensions
				$length_class_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "length_class WHERE length_class_id = " . (int)$product['length_class_id']);
				if ($length_class_query->num_rows) {
					$lengths[$product['key']] = $this->length->convert($product['length'], $product['length_class_id'], $this->config->get('config_length_class_id'));
					$widths[$product['key']] = $this->length->convert($product['width'], $product['length_class_id'], $this->config->get('config_length_class_id'));
					$heights[$product['key']] = $this->length->convert($product['height'], $product['length_class_id'], $this->config->get('config_length_class_id'));
				} else {
					$this->log->write(strtoupper($this->name) . ': ' . $product['name'] . ' (product_id: ' . $product['product_id'] . ') does not have a valid length unit, which causes a "Division by zero" error, and means it cannot be used for dimension/volume calculations. You can fix this by re-saving the product data.');
					$lengths[$product['key']] = 0;
					$widths[$product['key']] = 0;
					$heights[$product['key']] = 0;
				}
				
				// stock
				$stocks[$product['key']] = $product_query->row['quantity'] - $product['quantity'];
				
				// quantity
				$quantitys[$product['key']] = $product['quantity'];
				
				// total
				if (isset($rules['total_value'])) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					$product_price = ($product_info['special']) ? $product_info['special'] : $product_info['price'];
					
					if ($rules['total_value'][''][0] == 'prediscounted') {
						$totals[$product['key']] = $product['total'] + ($product['quantity'] * ($product_query->row['price'] - $product_price));
					} elseif ($rules['total_value'][''][0] == 'nondiscounted') {
						$totals[$product['key']] = ($product_info['special']) ? 0 : $product['total'];
					} elseif ($rules['total_value'][''][0] == 'taxed') {
						$totals[$product['key']] = $this->tax->calculate($product['total'], $product['tax_class_id']);
					}
				} else {
					$totals[$product['key']] = $product['total'];
				}
				
				// volume
				$volumes[$product['key']] = $lengths[$product['key']] * $widths[$product['key']] * $heights[$product['key']] * $product['quantity'];
				
				// weight
				$weight_class_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "weight_class WHERE weight_class_id = " . (int)$product['weight_class_id']);
				if ($weight_class_query->num_rows) {
					$weights[$product['key']] = $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
				} else {
					$this->log->write($product['name'] . ' (product_id: ' . $product['product_id'] . ') does not have a valid weight unit, which causes a "Division by zero" error, and means it cannot be used for weight calculations. You can fix this by re-saving the product data.');
					$weights[$product['key']] = 0;
				}
				
				// categories
				$category_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = " . (int)$product['product_id']);
				if ($category_query->num_rows) {
					foreach ($category_query->rows as $category) {
						$categorys[$product['key']][] = $category['category_id'];
					}
				} else {
					$categorys[$product['key']][] = 0;
				}
				
				// manufacturer
				$manufacturers[$product['key']][] = $product_query->row['manufacturer_id'];
				
				// products
				$products[$product['key']][] = $product['product_id'];
				
				// Check item criteria (entire cart comparisons)
				$this->charge['testing_mode'] = false;
				foreach ($cart_criteria as $spec) {
					if (isset($rules['adjust']['item_' . $spec])) {
						foreach ($rules['adjust']['item_' . $spec] as $adjustment) {
							${$spec.'s'}[$product['key']] += (strpos($adjustment, '%')) ? ${$spec.'s'}[$product['key']] * (float)$adjustment / 100 : (float)$adjustment;
						}
					}
					
					if (isset($rules[$spec]['entire_any'])) {
						if (!$this->inRange(${$spec.'s'}[$product['key']], $rules[$spec]['entire_any'], $spec . ' of any item in entire cart')) {
							continue 2;
						}
					}
					
					if (isset($rules[$spec]['entire_every'])) {
						if (!$this->inRange(${$spec.'s'}[$product['key']], $rules[$spec]['entire_every'], $spec . ' of every item in entire cart')) {
							continue 3;
						}
					}
				}
				$this->charge['testing_mode'] = $settings['testing_mode'];
				
				// Check product criteria
				if (isset($rules['category']) && $this->ruleViolation('category', $categorys[$product['key']])) {
					continue;
				}
				
				if (isset($rules['manufacturer']) && $this->ruleViolation('manufacturer', $product_query->row['manufacturer_id'])) {
					continue;
				}
				
				if (isset($rules['product']) && $this->ruleViolation('product', $product['product_id'])) {
					continue;
				}

				//////////////////// Warehouse Charge
				/*$warehouse_id = [];

				if(isset($warehouse_setting) && $warehouse_setting['status'] == 1){
					$warehouse_query = $this->db->query("SELECT warehouse_id FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = " . (int)$product['product_id'] . " AND status = 1 ");
									
					if($warehouse_query->num_rows > 0){
						$warehouses_ids = [];
						foreach ($warehouse_query->rows as $idx => $warehouse) {
							$warehouses_ids[] = $warehouse['warehouse_id'];
						}
					}
				}

				if (isset($rules['warehouse']) && $this->ruleViolation('warehouse', $warehouses_ids)) {
					continue;
				}*/
				///////////////////////////////
				
				// Check item criteria (eligible item comparisons)
				$this->charge['testing_mode'] = false;
				foreach ($cart_criteria as $spec) {
					if (isset($rules[$spec]['any'])) {
						if (!$this->inRange(${$spec.'s'}[$product['key']], $rules[$spec]['any'], $spec . ' of any item')) {
							continue 2;
						}
					}
					
					if (isset($rules[$spec]['every'])) {
						if (!$this->inRange(${$spec.'s'}[$product['key']], $rules[$spec]['every'], $spec . ' of every item')) {
							continue 3;
						}
					}
				}
				$this->charge['testing_mode'] = $settings['testing_mode'];
				
				// product passed all rules and is eligible for charge
				$product_keys[] = $product['key'];
			}

			// Check product group rules
			if (isset($rules['product_group'])) {

				$row_disabled_text = '"' . $this->charge['title'] . '" disabled';
				
				$list_types = array(
					'category',
					'manufacturer',
					'product',
				);
				
				foreach ($list_types as $list_type) {
					${$list_type . 's_array'} = array();
					foreach (${$list_type . 's'} as $list) {
						${$list_type . 's_array'} = array_merge(${$list_type . 's_array'}, $list);
					}
				}
				
				$eligible_products = array();
				$ineligible_products = array();
				foreach ($rules['product_group'] as $comparison => $product_group_ids) {
					$rule_satisfied = false;
					
					foreach ($product_group_ids as $product_group_id) {
						if (empty($settings['product_group'][$product_group_id]['member'])) continue;
						
						$product_group_rule_text = 'cart has items from ' . ($comparison == 'none' ? 'none of the' : $comparison) . ' members of ' . $settings['product_group'][$product_group_id]['name'];
						unset($members_array);
						
						foreach ($settings['product_group'][$product_group_id]['member'] as $member) {
							$bracket = strrpos($member, '[');
							$colon = strrpos($member, ':');
							$member_type = substr($member, $bracket + 1, $colon - $bracket - 1);
							$members_array[$member_type][] = substr($member, $colon + 1, -1);
						}
						
						foreach ($members_array as $type => $members) {
							// Check "all" and "onlyall" comparisons
							if (($comparison == 'all' || $comparison == 'onlyall') && array_diff($members, ${$type.'s_array'})) {
								if ($this->charge['testing_mode']) {
									$this->log->write(strtoupper($this->name) . ': ' . $row_disabled_text . ' for violating product group rule "' . $product_group_rule_text . '", due to missing ' . $type . '_id(s) "' . implode(', ', array_diff($members, ${$type.'s_array'})) . '"');
								}
								continue 4;
							}
							
							// Check product eligibility
							foreach ($cart_products as $product) {
								if ((($comparison == 'onlyany' || $comparison == 'onlyall') && array_diff(${$type.'s'}[$product['key']], $members)) ||
									($comparison == 'none' && array_intersect(${$type.'s'}[$product['key']], $members))
								) {
									if ($this->charge['testing_mode']) {
										$this->log->write(strtoupper($this->name) . ': ' . $row_disabled_text . ' for violating product group rule "' . $product_group_rule_text . '"');
									}
									continue 5;
								} elseif (($comparison != 'not' && $comparison != 'none' && !array_intersect(${$type.'s'}[$product['key']], $members)) ||
									(($comparison == 'not' || $comparison == 'none') && !array_diff(${$type.'s'}[$product['key']], $members))
								) {
									$ineligible_products[] = $product['key'];
								} else {
									$rule_satisfied = true;
									$eligible_products[] = $product['key'];
								}
							}
						}
					}
					
					// Check that rule has at least one matching product
					if (!$rule_satisfied) {
						continue 2;
					}
				}
					
				// Remove ineligible products
				foreach ($ineligible_products as $ineligible_key) {
					if (in_array($ineligible_key, $eligible_products)) continue;
					foreach ($product_keys as $index => $product_key) {
						if ($product_key == $ineligible_key) unset($product_keys[$index]);
					}
				}
			}
			
			// Check for empty product list
			if (empty($product_keys) && empty($this->session->data['vouchers'])) {
				if ($this->charge['testing_mode']) {
					$this->log->write(strtoupper($this->name) . ': "' . $this->charge['title'] . '" disabled for having no eligible products');
				}
				continue;
			}
			
			// Check cart criteria and generate total comparison values
			$single_foreign_currency = (isset($rules['currency']['is']) && count($rules['currency']['is']) == 1 && $default_currency != $currency) ? $rules['currency']['is'][0] : '';
			foreach ($cart_criteria as $spec) {
				// note: cart_comparison to be added here if requested
				if ($spec == 'total' && isset($rules['total_value']) && $rules['total_value'][''][0] == 'shipping_cost') {
					$total = $shipping_cost;
					$cart_total = $shipping_cost;
				} elseif ($spec == 'total' && isset($rules['total_value']) && $rules['total_value'][''][0] == 'total') {
					$total = $order_total;
					$cart_total = $order_total;
				} else {
					${$spec} = 0;
					foreach ($product_keys as $product_key) {
						${$spec} += ${$spec.'s'}[$product_key];
					}
					${'cart_'.$spec} = array_sum(${$spec.'s'});
				}
				
				if ($spec == 'total' && $single_foreign_currency) {
					$total = $this->currency->convert($total, $default_currency, $single_foreign_currency);
				}
				
				if (isset($rules['adjust']['cart_' . $spec])) {
					foreach ($rules['adjust']['cart_' . $spec] as $adjustment) {
						${$spec} += (strpos($adjustment, '%')) ? ${$spec} * (float)$adjustment / 100 : (float)$adjustment;
						${'cart_'.$spec} += (strpos($adjustment, '%')) ? ${'cart_'.$spec} * (float)$adjustment / 100 : (float)$adjustment;
					}
				}
				
				if (isset($rules[$spec]['cart'])) {
					if (!$this->inRange(${$spec}, $rules[$spec]['cart'], $spec . ' of cart')) {
						continue 2;
					}
				}
				
				if (isset($rules[$spec]['entire_cart'])) {
					if (!$this->inRange(${'cart_'.$spec}, $rules[$spec]['entire_cart'], $spec . ' of entire cart')) {
						continue 2;
					}
				}
			}
			
			// Calculate the charge

			////Check if warehouse charge is set by seller
			/*if(isset($warehouse_setting) && $warehouse_setting['status'] == 1 && isset($rules['warehouse'])){
				if(isset($rules['warehouse']['is']))
					$wr_id = $rules['warehouse']['is'][0];
				else if(isset($rules['warehouse']['not']))
					$wr_id = $rules['warehouse']['not'][0];

				if($wr_id){
					$wr_query = $this->db->query("SELECT seller_id, seller_charge FROM " . DB_PREFIX . "warehouses WHERE id = " . (int)$wr_id);
					if($wr_query->num_rows == 1 && $wr_query->row['seller_id'] != 0){
						$charge['charges'] = $wr_query->row['seller_charge'];
					}
				}
			}*/
			////////////////////////////////////////////

			$rate_found = false;
			$this->charge['testing_mode'] = false;
			$brackets = (!empty($charge['charges'])) ? array_filter(explode(',', str_replace(array("\n", ',,'), ',', $charge['charges']))) : array(0);
			
			if ($charge['type'] == 'flat') {
				
				$cost = (strpos($charge['charges'], '%')) ? $total * (float)$charge['charges'] / 100 : (float)$charge['charges'];
				$rate_found = true;
				
			} elseif ($charge['type'] == 'peritem') {
				
				$cost = (strpos($charge['charges'], '%')) ? $total * (float)$charge['charges'] / 100 : (float)$charge['charges'] * $quantity;
				$rate_found = true;
				
			} elseif ($charge['type'] == 'price') {
				
				$cost = 0;
				$rate_found = false;
				foreach ($cart_products as $product) {
					if (!in_array($product['key'], $product_keys)) continue;
					
					$product_cost = $this->calculateBrackets($brackets, $charge['type'], $product['price'], $product['quantity'], $product['price']);
					
					if ($product_cost !== false) {
						$cost += $product_cost;
						$rate_found = true;
					}
				}
				
			} elseif (in_array($charge['type'], array('distance', 'postcode', 'quantity', 'total', 'volume', 'weight'))) {
				
				$cost = $this->calculateBrackets($brackets, $charge['type'], ${$charge['type']}, $quantity, $total);
				if ($cost !== false) {
					$rate_found = true;
				}
				
			}
			
			if (!$rate_found) {
				if ($settings['testing_mode']) {
					$this->log->write(strtoupper($this->name) . ': "' . $this->charge['title'] . '" disabled for not matching any of "' . implode(', ', $brackets) . '"');
				}
				continue;
			}
			
			// Adjust charge
			if (isset($rules['adjust']['charge'])) {
				foreach ($rules['adjust']['charge'] as $adjustment) {
					$cost += (strpos($adjustment, '%')) ? $cost * (float)$adjustment / 100 : (float)$adjustment;
				}
			}
			if (isset($rules['round'])) {
				foreach ($rules['round'] as $comparison => $values) {
					$round = $values[0];
					if ($comparison == 'nearest') {
						$cost = round($cost / $round) * $round;
					} elseif ($comparison == 'up') {
						$cost = ceil($cost / $round) * $round;
					} elseif ($comparison == 'down') {
						$cost = floor($cost / $round) * $round;
					}
				}
			}
			if (isset($rules['min'])) {
				$cost = max($cost, $rules['min'][''][0]);
			}
			if (isset($rules['max'])) {
				$cost = min($cost, $rules['max'][''][0]);
			}
			if ($single_foreign_currency) {
				$cost = $this->currency->convert($cost, $single_foreign_currency, $default_currency);
			}
			
			// Add to charge array
			$replace = array('[distance]', '[postcode]', '[quantity]', '[total]', '[volume]', '[weight]');
			$with = array(round($distance, 2), $postcode, round($quantity, 2), round($total, 2), round($volume, 2), round($weight, 2));
			
			$charges[strtolower($charge['group'])][] = array(
				'title'			=> str_replace($replace, $with, html_entity_decode($charge['title_' . $language], ENT_QUOTES, 'UTF-8')),
				'charge'		=> (float)$cost,
				'tax_class_id'	=> isset($rules['tax_class']) ? $rules['tax_class'][''][0] : (!empty($settings['tax_class_id']) ? $settings['tax_class_id'] : 0),
			);
			
			// Restore setting defaults
			foreach ($defaults as $key => $value) {
				$this->config->set($key, $value);
			}
			
		} // end charge loop
		
		// Combine charges
		$quote_data = array();

		if (empty($settings['combination']) || empty($settings['combination'][key($settings['combination'])]['formula'])) {
			//	$total_charge=0;
			foreach ($charges as $group_value => $group) {
				foreach ($group as $rate) {
					if ($this->type == 'shipping' && $rate['charge'] < 0) continue;
				//	$total_charge+=$rate['charge'];			
					
					$taxed_charge = $this->tax->calculate($rate['charge'], $rate['tax_class_id'], $this->config->get('config_tax'));
					
					$quote_data[$this->name . '_' . count($quote_data)] = array(
						'code'			=> $this->name . '.' . $this->name . '_' . count($quote_data),
						'sort_order'	=> $group_value,
						'title'			=> $rate['title'],
						'cost'			=> $taxed_charge, //$rate['charge']
						'type'         => 'custom_cost',
						'value'			=> $rate['charge'],
						'tax_class_id'	=> $rate['tax_class_id'],
						'text'			=> $this->currency->format($taxed_charge),
					);
				}
			}
			/*
			$taxed_charge = $this->tax->calculate($total_charge, $rate['tax_class_id'], $this->config->get('config_tax'));
			$quote_data[$this->name . '_' . count($quote_data)] = array(
				'code'			=> $this->name . '.' . $this->name . '_' . count($quote_data),
				'sort_order'	=> $group_value,
				'title'			=> $rate['title'],
				'cost'			=> $total_charge,
				'value'			=> $total_charge,
				'tax_class_id'	=> $rate['tax_class_id'],
				'text'			=> $this->currency->format($taxed_charge),
			);
			*/
		} elseif (!empty($charges)) {
			
			foreach ($settings['combination'] as $combination) {
				if (empty($combination['formula'])) continue;
				
				$formula_array = preg_split('/[\(,\)]/', str_replace(' ', '', strtolower($combination['formula'])));
				
				$tax_class_id = 0;
				
				foreach ($charges as $group) {
					foreach ($group as $rate) {
						$tax_class_id = max($tax_class_id, $rate['tax_class_id']);
					}
				}
				
				$title_prefix = '';
				$titles = array();
				
				$current_function = '';
				$current_title = '';
				$current_charge = '';
				
				foreach ($formula_array as $piece) {
					if (empty($piece)) {
						$titles[] = $current_title;
						$current_function = '';
						$current_title = '';
						$current_charge = '';
					}
					if (in_array($piece, array('sum', 'max', 'min', 'avg'))) {
						$current_function = $piece;
					}
					if (empty($charges[$piece])) {
						continue;
					}
					if ($current_function == 'max' || $current_function == 'min') {
						foreach ($charges[$piece] as $rate) {
							if ($current_charge === '' || ($current_function == 'max' && $rate['charge'] >= $current_charge) || ($current_function == 'min' && $rate['charge'] <= $current_charge)) {
								$current_title = $rate['title'];
								$current_charge = $rate['charge'];
							}
						}
					} else {
						if (empty($combination['title']) || $combination['title'] == 'single') {
							$titles = array($charges[$piece][0]['title']);
						} else {
							foreach ($charges[$piece] as $rate) {
								//$title_prefix = ($formula_array[0] != 'sum') ? strtoupper($formula_array[0]) . ': ' : '';
								if ($combination['title'] == 'combined') {
									$titles[] = $rate['title'];
								} else {
									$titles[] = $rate['title'] . ' (' . $this->currency->format($this->tax->calculate($rate['charge'], $tax_class_id, $this->config->get('config_tax'))) . ')';
								}
							}
						}
					}
				}

				$i = 0;
				$cost = $this->calculateFormula($charges, $formula_array, $i);
				$taxed_charge = $this->tax->calculate($cost, $tax_class_id, $this->config->get('config_tax'));
				
				if ($cost === false || ($this->type == 'shipping' && $cost < 0) || ($this->type == 'total' && $cost == 0)) continue;
				
				$quote_data[$this->name . '_' . count($quote_data)] = array(
					'code'			=> $this->name . '.' . $this->name . '_' . count($quote_data),
					'sort_order'	=> (isset($combination['sort_order']) ? $combination['sort_order'] : 0),
					'title'			=> $title_prefix . implode(' + ', array_filter($titles)),
					'cost'			=> $taxed_charge, //$cost
					'value'			=> $cost,
					'type'         => 'custom_cost',
					'tax_class_id'	=> $tax_class_id,
					'text'			=> $this->currency->format($taxed_charge),
				);
			}
			
		}
		
		$sort_order = array();
		foreach ($quote_data as $key => $value) $sort_order[$key] = $value['sort_order'];
		array_multisort($sort_order, SORT_ASC, $quote_data);
		
		foreach ($quote_data as $quote) {
			$quote['code'] = $this->name;
			$quote['sort_order'] = $settings['sort_order'];
			
			$total_data[] = $quote;
			
			if ($quote['tax_class_id']) {
				foreach ($this->tax->getRates($quote['cost'], $quote['tax_class_id']) as $tax_rate) {
					$taxes[$tax_rate['tax_rate_id']] = (isset($taxes[$tax_rate['tax_rate_id']])) ? $taxes[$tax_rate['tax_rate_id']] + $tax_rate['amount'] : $tax_rate['amount'];
				}
			}
			
			$order_total += $quote['cost'];
		}
		
		if ($settings['testing_mode']) {
			$this->log->write(strtoupper($this->name) . ': ------------------------------ Ending testing mode ------------------------------');
		}

		//Check if Hide Shipping Option Enabled in category_product_based shipping
		unset($this->session->data['will_not_shipped_product']);
		
		if($settings['hide_shipping'] == 1){
			// here we save the id of the unshipped products to tell the user to remove them from cart
			$cart_ids = array_keys($this->session->data['cart']);
			// $product_keys array contains the will shipped products, $cart_ids array contains all the cart products
			$will_not_shipped_products = array_values(array_diff($cart_ids,$product_keys));
			// get the first will not shipped product name
			if(count($will_not_shipped_products)) {
				$productsNames = '';
				foreach ($will_not_shipped_products as $pId) {
					$productsNames .= $cart_products[$pId]['name'] .', ';
				}

				$this->session->data['will_not_shipped_product'] = rtrim($productsNames, ', ');
			}

			// when hide_shipping is enabled, it means that each product must have an applied rule, otherwise we do not show any shipping method
			if(count($product_keys) != count($this->cart->getProducts()))
				return array();
		}

		if ($this->type == 'shipping' && $quote_data  ) {
			$replace = array('[distance]', '[postcode]', '[quantity]', '[total]', '[volume]', '[weight]');
			$with = array(round($distance, 2), $postcode, round($cart_quantity, 2), round($cart_total, 2), round($cart_volume, 2), round($cart_weight, 2));
			
			return array(
				'code'			=> $this->name,
				'title'			=> str_replace($replace, $with, html_entity_decode($settings['heading_' . $language], ENT_QUOTES, 'UTF-8')),
				'quote'			=> $quote_data,
				'sort_order'	=> $settings['sort_order'],
				'error'			=> false
			);
		} else {
			return array();
		}
	}
	
	//------------------------------------------------------------------------------
	// Private functions
	//------------------------------------------------------------------------------
	private function getSettings() {
		$settings = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			$split_key = preg_split('/_(\d+)_?/', str_replace($this->name . '_', '', $setting['key']), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
				if (count($split_key) == 1)	$settings[$split_key[0]] = $value;
			elseif (count($split_key) == 2)	$settings[$split_key[0]][$split_key[1]] = $value;
			elseif (count($split_key) == 3)	$settings[$split_key[0]][$split_key[1]][$split_key[2]] = $value;
			elseif (count($split_key) == 4)	$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]] = $value;
			else 							$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]][$split_key[4]] = $value;
		}
		
		return $settings;
	}
	
	private function commaMerge(&$rule) {
		$merged_rule = array();
		foreach ($rule as $comparison => $values) {
			$merged_rule[$comparison] = array();
			foreach ($values as $value) {
				$merged_rule[$comparison] = array_merge($merged_rule[$comparison], array_map('trim', explode(',', strtolower($value))));
			}
		}
		$rule = $merged_rule;
	}
	
	private function ruleViolation($rule, $value) {
		$violation = false;
		$rules = $this->charge['rules'];
		$function = (is_array($value)) ? 'array_intersect' : 'in_array';
		
		if (isset($rules[$rule]['after']) && strtotime($value) < min(array_map('strtotime', $rules[$rule]['after']))) {
			$violation = true;
			$comparison = 'after';
		}
		if (isset($rules[$rule]['before']) && strtotime($value) > max(array_map('strtotime', $rules[$rule]['before']))) {
			$violation = true;
			$comparison = 'before';
		}
		if (isset($rules[$rule]['is']) && !$function($value, $rules[$rule]['is'])) {
			$violation = true;
			$comparison = 'is';
		}
		if (isset($rules[$rule]['not']) && $function($value, $rules[$rule]['not'])) {
			$violation = true;
			$comparison = 'not';
		}
		
		if ($this->charge['testing_mode'] && $violation) {
			$this->log->write(strtoupper($this->name) . ': "' . $this->charge['title'] . '" disabled for violating rule "' . $rule . ' ' . $comparison . ' ' . implode(', ', $rules[$rule][$comparison]) . '" with value "' . (is_array($value) ? implode(',', $value) : $value) . '"');
		}
		
		return $violation;
	}
	
	private function inRange($value, $range_list, $charge_type = '') {
		$in_range = false;
		
		foreach ($range_list as $range) {
			if ($range == '') continue;
			
			$range = (strpos($range, '::')) ? explode('::', $range) : explode('-', $range);
			
			if (strpos($charge_type, 'distance') === 0) {
				if (empty($range[1])) {
					array_unshift($range, 0);
				}
				if ($value >= (float)$range[0] && $value <= (float)$range[1]) {
					$in_range = true;
				}
			} elseif (strpos($charge_type, 'postcode') === 0) {
				$postcode = preg_replace('/[^A-Z0-9]/', '', strtoupper($value));
				$from = preg_replace('/[^A-Z0-9]/', '', strtoupper($range[0]));
				$to = (isset($range[1])) ? preg_replace('/[^A-Z0-9]/', '', strtoupper($range[1])) : $from;
				
				if (strlen($from) < strlen($postcode)) $from = str_pad($from, strlen($from) + 3, ' ');
				if (strlen($to) < strlen($postcode)) $to = str_pad($to, strlen($to) + 3, preg_match('/[A-Z]/', $postcode) ? 'Z' : '9');
				
				$postcode = substr_replace(substr_replace($postcode, ' ', -3, 0), ' ', -2, 0);
				$from = substr_replace(substr_replace($from, ' ', -3, 0), ' ', -2, 0);
				$to = substr_replace(substr_replace($to, ' ', -3, 0), ' ', -2, 0);
				
				if (strnatcasecmp($postcode, $from) >= 0 && strnatcasecmp($postcode, $to) <= 0) {
					$in_range = true;
				}
			} else {
				if ($charge_type != 'option' && $charge_type != 'other product data' && !isset($range[1])) {
					$range[1] = 999999999;
				}
				
				if ((count($range) > 1 && $value >= $range[0] && $value <= $range[1]) || (count($range) == 1 && $value == $range[0])) {
					$in_range = true;
				}
			}
		}
		
		if ($this->charge['testing_mode'] && (strpos($charge_type, ' not') ? $in_range : !$in_range)) {
			$this->log->write(strtoupper($this->name) . ': "' . $this->charge['title'] . '" disabled for violating rule "' . $charge_type . (strpos($charge_type, ' not') ? ' is not ' : ' is ') . implode(', ', $range_list) . '" with value "' . $value . '"');
		}
		
		return $in_range;
	}
	
	private function calculateBrackets($brackets, $charge_type, $comparison_value, $quantity, $total) {
		$to = 0;
		
		foreach ($brackets as $bracket) {
			$bracket = str_replace(array('::', ':'), array('-', '='), $bracket);
			
			$bracket_pieces = explode('=', $bracket);
			if (count($bracket_pieces) == 1) {
				array_unshift($bracket_pieces, ($charge_type == 'postcode') ? '0-ZZZZ' : '0-999999');
			}
			
			$from_and_to = explode('-', $bracket_pieces[0]);
			if (count($from_and_to) == 1) {
				array_unshift($from_and_to, ($charge_type == 'postcode') ? $from_and_to[0] : $to);
			}
			$from = trim($from_and_to[0]);
			$to = trim($from_and_to[1]);
			
			$cost_and_per = explode('/', $bracket_pieces[1]);
			$per = (isset($cost_and_per[1])) ? (float)$cost_and_per[1] : 0;
			
			$top = min($to, $comparison_value);
			$bottom = (isset($this->charge['rules']['cumulative'])) ? $from : 0;
			$difference = ($charge_type == 'postcode' || $charge_type == 'price') ? $quantity : $top - $bottom;
			$multiplier = ($per) ? ceil($difference / $per) : 1;
			
			if (!isset($cost) || !isset($this->charge['rules']['cumulative'])) {
				$cost = 0;
			}
			$cost += (strpos($cost_and_per[0], '%')) ? (float)$cost_and_per[0] * $multiplier * $total / 100 : (float)$cost_and_per[0] * $multiplier;
			
			$in_range = $this->inRange($comparison_value, array($from . '-' . $to), $charge_type);
			if ($in_range) {
				return $cost;
			}
		}
		
		return false;
	}
	
	private function calculateFormula($charges, $formula_array, &$i) {
		$settings = $this->getSettings();
		
		$groups = array();
		foreach ($settings['charge'] as $charge) {
			if (empty($charge['group'])) continue;
			$groups[] = strtolower($charge['group']);
		}
		array_unique($groups);
		
		$costs = array();
		
		$calculation = $formula_array[$i];
		$i++;
		
		while ($i < count($formula_array)) {
			$piece = $formula_array[$i];
			if ($piece == '') break;
			if (in_array($piece, array('sum', 'max', 'min', 'avg'))) {
				$calculation_result = $this->calculateFormula($charges, $formula_array, $i);
				if ($calculation_result !== false) $costs[] = $calculation_result;
			} elseif (!empty($charges[$piece])) {
				$group_costs = array();
				foreach ($charges[$piece] as $rate) {
					$group_costs[] = $rate['charge'];
				}
				$costs[] = $this->arrayCalculation($calculation, $group_costs);
			} elseif (!in_array($piece, $groups)) {
				$costs[] = (float)$piece;
			}
			$i++;
		}
		
		return $this->arrayCalculation($calculation, $costs);
	}
	
	private function arrayCalculation($calculation, $array) {
		if (empty($array)) {
			return false;
		} elseif ($calculation == 'sum') {
			return array_sum($array);
		} elseif ($calculation == 'max') {
			return max($array);
		} elseif ($calculation == 'min') {
			return min($array);
		} elseif ($calculation == 'avg') {
			return array_sum($array) / count($array);
		}
	}
}
?>
