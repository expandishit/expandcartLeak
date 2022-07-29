<?php
class Cart {
	private $config;
	private $db;
	private $load;
	private $data = array();
    private $cartProductUid;

	public static $product_status = 1;

	/**
	 * will be set to order id sent from order edit page in admin panel 
	 * 
	 * @var int $order_id
	 */
	public static $manual_order_id = 0;
	
  	public function __construct($registry) {
		$this->load = $registry->get('load');
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->db = $registry->get('db');
		$this->tax = $registry->get('tax');
		$this->weight = $registry->get('weight');
		$this->currency = $registry->get('currency');

		if (!isset($this->session->data['cart']) || !is_array($this->session->data['cart'])) {
      		$this->session->data['cart'] = array();
    	}
	}

    public function getRewardPoints()
    {
		/**
		 * Check if customer is a visitor or logged customer to get reward points of customer
		 * if he/she is logged customer otherwise he/she don't have reward points.
		 */
		if($this->customer->isLogged()){
			$points = 0;

			foreach ( $this->getProducts() as $product )
			{
				if ( $product['reward'] )
				{
					$points += (int) $product['reward'];
				}	
			}
			return $points;
		
		}
		return false;
    }

    public function add1($product_id, $qty = 1, $option, $profile_id = '', $winner_id) {
        $key = (int)$product_id . ':';

        if ($option) {
            $key .= base64_encode(serialize($option)) . ':';
        }  else {
            $key .= ':';
        }

        if ($profile_id) {
            $key .= (int)$profile_id;
        }else {
            $key .= ':';
        }

        if ($winner_id) {
            $key .= (int)$winner_id;
        }

        if ((int)$qty && ((int)$qty > 0)) {
            if (!isset($this->session->data['cart'][$key])) {
                $this->session->data['cart'][$key] = (int)$qty;
            } else {
                $this->session->data['cart'][$key] += (int)$qty;
            }
            $this->customer->updateActs();
        }

        $this->data = array();
    }

  	public function getProducts($language_id = null) {

		  $i = 0;
		  if (IS_EXPANDISH_FRONT) $model_account_order = $this->load->model('account/order' , ['return'=>true]);

		  if( !$language_id ) $language_id = $this->config->get('config_language_id');

		if (!$this->data) {
			$new = $this->session->data['new_products'];
			foreach ($this->session->data['cart'] as $key => $quantity) {
				$i++;
				$product = explode(':', $key);
				$product_id = $product[0];
				$stock = true;
			
				// Options
				if (isset($product[1])) {
					$options = unserialize(base64_decode($product[1]));
				} else {
					$options = array();
				}



				$queryString = "SELECT p.*,pd.name, pd.description FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) ";

				if (self::$manual_order_id != 0 &&  $new[$i]['old']=="1" ) {
						$queryString = "SELECT p.*,pd.name, pd.description,op.order_product_id, op.price AS op_price, op.order_id, op.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) ";
						$queryString .= " LEFT JOIN order_product op ON op.order_id=" . self::$manual_order_id ." AND op.product_id={$product_id}";
				}

				$queryString .= " WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$language_id . "' AND p.date_available <= NOW()";
				if (self::$product_status == 1) {
					$queryString .= " AND p.status = '1'";
				}
				$product_query = $this->db->query($queryString);
				if (isset($product_query->row['op_price']) && !empty($product_query->row['op_price']) ) {
					try {
						if ($product_query->num_rows > 1 && isset($model_account_order) && $model_account_order){
							$order_product_ids = array_column($product_query->rows , 'order_product_id');
							$prdOtionsArr = $model_account_order->getOrderOptions(self::$manual_order_id , $order_product_ids );
							$order_product_option = array_filter($prdOtionsArr, function ($optionArr) use ($options) {
								$optionIdKey= $optionArr['product_option_id'];
								$optionValId= $optionArr['product_option_value_id'];
								return (isset($options[$optionIdKey]) && ($options[$optionIdKey] ==  $optionValId));
							});
							$order_product_option = array_values($order_product_option);

							$order_product_index = array_search($order_product_option[0]['order_product_id'], array_column($product_query->rows,'order_product_id'));
							if ($order_product_index !==false)
								$product_query->row = $product_query->rows[$order_product_index];

						}

					}catch (Exception $e){
						file_put_contents(BASE_STORE_DIR . 'logs/errors.txt', $e->getMessage(), FILE_APPEND);
					}
					if ( $new[$i]['old']=="1" ) {
						$product_query->row['price'] = $product_query->row['op_price'];
					}
				}
                if ($product_query->num_rows) {
					$option_price = 0;
					$option_points = 0;
					$option_weight = 0;

					$option_data = array();

                    if((\Extension::isInstalled('aliexpress_dropshipping') && $this->config->get('module_wk_dropship_status'))) {
                        $wk_result = array();
                        $wk_option = array();
                        $wk_options = array();
                    }

					foreach ($options as $product_option_id => $option_value) {
						if(strpos($option_value, ':')){ //product builds
							$builds_option_value = explode(':', $option_value);
							$option_value = $builds_option_value[0];
							$option_quantity[$product_option_id] =  $builds_option_value[1];
						}
                        // aliexrpess code
                        if((\Extension::isInstalled('aliexpress_dropshipping') && $this->config->get('module_wk_dropship_status'))) {
                            $wk_option['product_option_value_id'] = $value;
                            $wk_option['product_option_id'] = $product_option_id;
                            $wk_result = $this->getAlixOptionByProductOption($wk_option);
                            if ($wk_result && isset($wk_result['alix_option_value_id']) && $wk_result['alix_option_value_id']) {
                                $wk_options[] = $wk_result['alix_option_value_id'];
                            }
                        }
                        // end here

						$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$language_id . "'");

						if ($option_query->num_rows) {
							if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image' || ($option_query->row['type'] == 'product' && !is_array($option_value))) {

								if ($option_query->row['type'] == 'product') {
									$option_value_query = $this->db->query("SELECT pov.option_value_id, pd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id) WHERE pov.product_option_value_id = '" . (int)$option_value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND pd.language_id = '" . (int)$language_id . "'");
								} else {
									$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$option_value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$language_id . "'");
								}

								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										if($option_quantity[$product_option_id]){
											$option_price += $option_value_query->row['price']*$option_quantity[$product_option_id];
										}else{
											$option_price += $option_value_query->row['price'];
										}
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										if($option_quantity[$product_option_id]){
											$option_price -= $option_value_query->row['price']*$option_quantity[$product_option_id];
										}else{
											$option_price -= $option_value_query->row['price'];
										}
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
										$stock = false;
									}

									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $option_value,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'option_value'            => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_quantity[$product_option_id] ? $option_quantity[$product_option_id] : $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);
								}
							} elseif (($option_query->row['type'] == 'checkbox' || $option_query->row['type'] == 'product') && is_array($option_value)) {
								foreach ($option_value as $product_option_value_id) {
									$product_option_value_ids = explode(',', $product_option_value_id);
									foreach ($product_option_value_ids as $product_option_value_id) {
										if ($option_query->row['type'] == 'product') {
											$option_value_query = $this->db->query("SELECT pov.option_value_id, pd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (ov.valuable_id = pd.product_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND pd.language_id = '" . (int)$language_id . "'");
										}
										else {
											$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "'");
										}
										if ($option_value_query->num_rows) {
											if ($option_value_query->row['price_prefix'] == '+') {
												$option_price += $option_value_query->row['price'];
											} elseif ($option_value_query->row['price_prefix'] == '-') {
												$option_price -= $option_value_query->row['price'];
											}

											if ($option_value_query->row['points_prefix'] == '+') {
												$option_points += $option_value_query->row['points'];
											} elseif ($option_value_query->row['points_prefix'] == '-') {
												$option_points -= $option_value_query->row['points'];
											}

											if ($option_value_query->row['weight_prefix'] == '+') {
												$option_weight += $option_value_query->row['weight'];
											} elseif ($option_value_query->row['weight_prefix'] == '-') {
												$option_weight -= $option_value_query->row['weight'];
											}

											if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
												$stock = false;
											}

											$option_data[] = array(
												'product_option_id'       => $product_option_id,
												'product_option_value_id' => $product_option_value_id,
												'option_id'               => $option_query->row['option_id'],
												'option_value_id'         => $option_value_query->row['option_value_id'],
												'name'                    => $option_query->row['name'],
												'option_value'            => $option_value_query->row['name'],
												'type'                    => $option_query->row['type'],
												'quantity'                => $option_quantity[$product_option_id] ? $option_quantity[$product_option_id] :$option_value_query->row['quantity'],
												'subtract'                => $option_value_query->row['subtract'],
												'price'                   => $option_value_query->row['price'],
												'price_prefix'            => $option_value_query->row['price_prefix'],
												'points'                  => $option_value_query->row['points'],
												'points_prefix'           => $option_value_query->row['points_prefix'],
												'weight'                  => $option_value_query->row['weight'],
												'weight_prefix'           => $option_value_query->row['weight_prefix']
											);
										}
									}
								}
							} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' ||
								$option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => '',
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => '',
									'name'                    => $option_query->row['name'],
									'option_value'            => $option_value,
									'type'                    => $option_query->row['type'],
									'quantity'                => '',
									'subtract'                => '',
									'price'                   => '',
									'price_prefix'            => '',
									'points'                  => '',
									'points_prefix'           => '',
									'weight'                  => '',
									'weight_prefix'           => ''
								);
							}
						}elseif ($product_option_id == 'pd_application') {
							// return plain options
							$option_data[] = array(
								'product_option_id' => $product_option_id,
								'custom_id' => $option_value['customId'],
								'productId' => $option_value['pid'],
								'type' => $product_option_id,
								'tshirtId' => $option_value['lastTshirtId'],
							);

						} elseif ($product_option_id == 'rentalRange') {
							if(is_object($option_value)){
								$option_value = get_object_vars($option_value);
							}

                            $date1 = new DateTime(date("Y-m-d", $option_value['from']));
                            $date2 = new DateTime(date("Y-m-d", $option_value['to']));
                            $diff = $date1->diff($date2);
                            $rentData[$product_id] = array(
                                'range' => $option_value,
                                'diff' => $diff->days + 1, //+1 because renting for one day makes this diff = 0,
                                'type' => $product_option_id,
                            );
                        }elseif ($product_option_id == 'productBundles') {
			                global $registry;
			                $bundleLoader = new Loader($registry);
			                $bundleLoader->model('catalog/product');
			                $product_model = $registry->get('model_catalog_product');
                            $bundles[$key] = $product_model->getProductBundles($product_id);
                        }elseif ($product_option_id == 'pricePerMeterRange') {

                        	$dimention  = $option_value['width'] * $option_value['length'];
                        	$mainPKsize = $option_value['main_package_size'];
        					$mainBoxs = ceil($dimention/$mainPKsize);
        					$underlaymen = ($mainBoxs * $mainPKsize);

                        	$doorsWidth = ( $option_value['doors'] * $option_value['doorW'] );
                        	$metalProfiles = 0;
					        if($doorsWidth){
					            $metalProfiles = ceil($doorsWidth/3);
					        }

					        $perimeter  = ( $option_value['width'] * 2 ) + ( $option_value['length'] * 2 );
					        $skiPKsize = $option_value['skirtings_package_size'];
					        $skiBoxs = ceil($perimeter/$skiPKsize);
					        $skirtings = $skiBoxs * $skiPKsize;
					        if(!$skirtings)
					            $skirtings = 0;

                            $pricePerMeterData[$product_id] = array(
                                'underlaymen' => $underlaymen,
                                'metalProfiles' => $metalProfiles,
                                'skirtings' => $skirtings,
                                'main_status' => $option_value['main_status'],
                                'main_unit' => $option_value['main_unit'],
                                'metalprofile_status' => $option_value['metalprofile_status'],
                                'skirtings_status' => $option_value['skirtings_status'],
                                'type'  => $product_option_id,
                            );

                        // Printing Document
                        }else if(in_array($product_option_id, ['print_pages', 'print_copies', 'print_cover', 'print_cover_name'])){
                        	$printingDocument[$product_id][$product_option_id] = $option_value;
                        }else if ($product_option_id == 'minimum_deposit_customer_price' && $product_option_id != '0'){
							$minimum_deposit_customer_price[$product_id] = $option_value;
						}else if ($product_option_id == 'curtain_seller') {
							$curtain_seller_option_data = [];
							$curtain_seller_option_data[$product_id] = $option_value;
						}
						elseif($product_option_id == 'fifaCardsOptions')
						{
							//$stats = array();
							$fifaCardsData[$product_id] = array(
								'player_name' => $option_value['player_name'],
								'personal_image' => $option_value['personal_image'],
								'player_position' => $option_value['player_position'],
								'player_rating' => $option_value['player_rating'],
								'is_goal_keeper' => $option_value['is_goal_keeper'],
								'existed_country_flag_image' => $option_value['existed_country_flag_image'],
								'existed_club_flag_image' => $option_value['existed_club_flag_image'],
								'stats' => array($option_value['stats']),
							);
						}
					}
                    $productsoptions_sku_status = $this->config->get('productsoptions_sku_status');
					$variation_price = 0;
                    if ($productsoptions_sku_status) {
                        if (isset($option_data)) {
                            $option_count = count($option_data);
                            if ($option_count) {
                                $options_value_ids = [];
                                foreach ($option_data as $values) {
                                    $options_value_ids[] = $values['option_value_id'];
                                }

                                sort($options_value_ids);

                                $option_sku_data = $this->db->query("SELECT product_sku, product_quantity, product_price FROM product_variations pv WHERE pv.product_id = '" . (int)$product_id . "' AND pv.option_value_ids = '" . implode(',', $options_value_ids) . "'");
                                if ($option_sku_data->row) {
                                    $variation_sku = $option_sku_data->row['product_sku'];
                                    $variation_quantity = $option_sku_data->row['product_quantity'];
                                    $variation_price = $option_sku_data->row['product_price'];
                                }
                            }
                        }
                    }
					if ($this->customer->isLogged()) {
						$customer_group_id = $this->customer->getCustomerGroupId();
						$visitor_customer_group_id=$customer_group_id;
					} else {
						$customer_group_id = "0";
						$visitor_customer_group_id = $this->config->get('config_customer_group_id') !=""? $this->config->get('config_customer_group_id') :"1";
					}

                    $dedicatedDomains = null;

                    if (IS_EXPANDISH_FRONT) {
                        $dedicatedDomains = $this->load->model(
                            'module/dedicated_domains/domain_prices', ['return' => true]
                        );
                    }

                    $price = null;

                    if (!$price) {

                        // Aliexpress code
                        if($this->config->get('module_wk_dropship_status') && isset($wk_options) && $wk_options) {
                            if(is_array($wk_options) && count($wk_options) > 1) {
                                $single = false;
                            } else {
                                $single = true;
                            }
                            $wk_options = implode(',',$wk_options);
                            $wk_combination_price = $this->getOptionPrice(
                                $wk_options,
                                $product_query->row['product_id'],
                                $single
                            );

                            if ($wk_combination_price) {
                                if(
                                    isset($wk_combination_price['price_prefix']) &&
                                    $wk_combination_price['price_prefix'] == '+'
                                ) {
                                    $product_query->row['price'] = $product_query->row['price'] + $wk_combination_price['price'];
                                } else if(
                                    isset($wk_combination_price['price_prefix']) &&
                                    $wk_combination_price['price_prefix'] == '-'
                                ) {
                                    $product_query->row['price'] = $product_query->row['price'] - $wk_combination_price['price'];
                                    if($product_query->row['price'] < 0) {
                                        $product_query->row['price'] = 0;
                                    }
                                }
                            }
                        }
                        // end here

                        $price = ( isset($this->session->data['auction_product']) && $key == $this->session->data['auction_product']['product_id'] ) ? $this->session->data['auction_product']['price'] : $product_query->row['price'];


                        // Product Discounts
                        $discount_quantity = 0;

                        foreach ($this->session->data['cart'] as $key_2 => $quantity_2) {
                            $product_2 = explode(':', $key_2);

                            if ($product_2[0] == $product_id) {
                                $discount_quantity += $quantity_2;
                            }
                        }

                        if ($dedicatedDomains && $dedicatedDomains->isActive()) {
                            $domainData = $dedicatedDomains->getDomain();

                            if ($domainData) {

                                $dedicatedDiscount = $dedicatedDomains->getProductDedicatedDiscountByQuantity(
                                    $product_id,
                                    $visitor_customer_group_id,
                                    $domainData,
                                    $quantity
                                );

                                if ($dedicatedDiscount) {
                                    $product_query->row['discount'] = $dedicatedDiscount;
                                    $price = $dedicatedDiscount;
									$variation_price = $dedicatedDiscount;
                                } else {
                                    $product_query->row['discount'] = null;
                                }

                                $dedicatedPrice = $dedicatedDomains->getProductDedicatedPrices(
                                    $product_id,
                                    $domainData
                                );

                                if ($dedicatedPrice) {
                                    if ($dedicatedDiscount) {
                                        $price = $dedicatedDiscount;
										$variation_price = $dedicatedDiscount;
                                    } else {
                                        $price = $dedicatedPrice;
										$variation_price = $dedicatedPrice;
                                    }
                                }

                                if(empty($dedicatedDiscount)){
									$dedicatedSpecial = $dedicatedDomains->getProductDedicatedSpecial(
										$product_id,
										$visitor_customer_group_id,
										$domainData
									);

									if ($dedicatedSpecial) {
										$price = $dedicatedSpecial;
										$variation_price = $dedicatedSpecial;
									}
								}

                            }
                        } else {
                        	if (self::$manual_order_id == 0 || !isset($product_query->row['op_price']) || empty($product_query->row['op_price'])) {
								$app_config_timezone = $this->config->get('config_timezone')?:'UTC';
								$now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d');
								// Product Specials
								$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$visitor_customer_group_id . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= '".$now."') AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end >= '".$now."')) ORDER BY priority ASC, price ASC LIMIT 1");

								if ($product_special_query->num_rows) {
									$price = $product_special_query->row['price'];
									$variation_price = $product_special_query->row['price'];
								}

								$product_discount_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$visitor_customer_group_id . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start < NOW()) AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

								if ($product_discount_query->num_rows) {
									$price = $product_discount_query->row['price'];
									$variation_price = $product_discount_query->row['price'];
								}
							}
                        }
                    }

					// Reward Points
					$product_reward_query = $this->db->query("SELECT points FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "'");

					if ($product_reward_query->num_rows) {
						$reward = $product_reward_query->row['points'];
					} else {
						$reward = 0;
					}


					// Advanced Reward Points App
					// ----------------------------------------
					$queryRewardPointInstalledApp = $this->db->query("SELECT `value` FROM " . DB_PREFIX . "setting WHERE `group` = 'reward_points' AND `key` = 'rwp_enabled_module'");

					if($queryRewardPointInstalledApp->num_rows && $queryRewardPointInstalledApp->row['value']){
						$queryRewardPointInstalledTotal = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

						if($queryRewardPointInstalledTotal->num_rows) {
							if ($this->config->get('reward_status')) {
								$product_reward_query = $this->db->query("SELECT p_to_r.reward_point FROM " . DB_PREFIX . "product_to_reward as p_to_r
									join " . DB_PREFIX . "catalog_rules as c_r on c_r.rule_id = p_to_r.rule_id 
									WHERE product_id = " . (int)$product_id."
									and c_r.status = 1 "
								);
								if ($product_reward_query->num_rows) {
									$now_date = date('Y-m-d');
									if(empty($product_reward_query->row['end_date']))
									{
										if($now_date < $product_reward_query->row['start_date']){
											$reward += 0;
										}
										else{
											$reward += $product_reward_query->row['reward_point'];
										}
									}
									else if($now_date >= $product_reward_query->row['start_date'] && $now_date <= $product_reward_query->row['end_date']){
										$reward += $product_reward_query->row['reward_point'];
									}
									else{
										$reward += 0;
									}
								} else {
									$reward += 0;
								}
							}
						}
						//////////////////////////////////////////

					}

					// Downloads
					$download_data = array();
					if (\Extension::isInstalled('product_attachments') && $this->config->get('product_attachments')['status'] == 1){

						$download_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download p2d LEFT JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$product_id . "' AND dd.language_id = '" . (int)$language_id . "'");

						foreach ($download_query->rows as $download) {
							$download_data[] = array(
								'download_id' => $download['download_id'],
								'name'        => $download['name'],
								'filename'    => $download['filename'],
								'mask'        => $download['mask'],
								'remaining'   => $download['remaining']
							);
						}
					}



                    if (
                        isset($variation_quantity) &&
                        $variation_quantity < $quantity &&
						( $product_query->row['subtract'] == 1 || $product_query->row['unlimited'] != 1 ) &&
                        (int)$variation_quantity > -1
                    ) {
                        $stock = false;
                    }

					// Stock
					if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < $quantity)) {
						if (!(bool)$product_query->row['subtract']  || (bool)$product_query->row['unlimited']) {
							$stock = true;
						} else {
							$stock = false;
						}
					}else{
						// check if product has codes from code generator app
						// 1 - check if app Installed
						$productCodeAppSettings =$this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'product_code_generator'");
						if($productCodeAppSettings->num_rows)
						{
							// check if product used code generator app
							$productCodeApp = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE 	product_id = '" . (int)$product_query->row['product_id'] . "' ");
							if(is_object($productCodeApp) && $productCodeApp->num_rows > 0)
							{
								// get product total avaliable code
								$productCodeAppTotal = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_code_generator  WHERE product_id = '" . (int)$product_query->row['product_id'] . "' AND is_used = 0");
								// check if product total available codes >= quantity
								$stock = ($productCodeAppTotal->num_rows < $quantity) ? false : true;
							}
						}
					}




                    if($productsoptions_sku_status) {
                        if (isset($option_data)) {
                            $option_count = count($option_data);
                            if ($option_count > 0) {
                                $model_sku = (isset($variation_sku) && $variation_sku != '') ? $variation_sku : $product_query->row['model'];
                            } else {
                                $model_sku = $product_query->row['model'];
                            }
                        } else {
                            $model_sku = $product_query->row['model'];
                        }
                    }

                    global $registry;
                    if (!IS_NEXTGEN_FRONT) {
                        // Product Option Image PRO module <<


                        //global $loader, $registry;


                        $queryAuctionModule = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'special_count_down'");
                        if ($queryAuctionModule->num_rows) {
                            if (!empty($product[3])) {
                                $winner_id = $product[3];
                                $auction_price_query = $this->db->query("SELECT price_bid FROM " . DB_PREFIX . "winner WHERE
						product_id = '" . (int)$product_id . "' AND winner_id = '" . (int)$winner_id . "'");
                                if ($auction_price_query->num_rows) {
                                    $price = $auction_price_query->row['price_bid'];
                                }
                            }
                        }
                    }

                    $poiploader = new Loader($registry);

                    $poiploader->model('module/product_option_image_pro');
                    $poip_model = $registry->get('model_module_product_option_image_pro');
                    $product_query->row['image'] = $poip_model->getProductCartImage($product_query->row['product_id'], $option_data, $product_query->row['image']);

                    if (self::$manual_order_id != 0 &&
						isset($product_query->row['op_price']) &&
						!empty($product_query->row['op_price'])
					) {
						$option_price=0;
					}
					$special_discount_type =null;
					$special_discount_value =null;

					$app_config_timezone = $this->config->get('config_timezone')?:'UTC';
					$now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d');
					//Applying Mass Update App discounts to product options.
					$product_special_discount_query = $this->db->query("SELECT discount_type,discount_value FROM " . DB_PREFIX . "product_special
															WHERE product_id = '" . (int)$product_id . "' 
															AND customer_group_id = '" . (int)$visitor_customer_group_id . "' 
															AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= '".$now."')
															AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end >= '".$now."')) 
															ORDER BY priority ASC, price ASC LIMIT 1");
					if ($product_special_discount_query->num_rows)
					{
						$special_discount_type = $product_special_discount_query->row['discount_type'];
						$special_discount_value = $product_special_discount_query->row['discount_value'];
					}
					$product_discount_query = $this->db->query("SELECT discount_type,discount_value,quantity FROM " . DB_PREFIX . "product_discount
															WHERE product_id = '" . (int)$product_id . "' 
															AND customer_group_id = '" . (int)$visitor_customer_group_id . "' 
															AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= '".$now."')
															AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end >= '".$now."')) 
															ORDER BY priority ASC, price ASC LIMIT 1");
					if ($product_discount_query->num_rows && ($quantity >= $product_discount_query->row['quantity']))
					{
						$special_discount_type = $product_discount_query->row['discount_type'];
						$special_discount_value = $product_discount_query->row['discount_value'];
					}
						if($special_discount_type && $special_discount_value && empty($dedicatedDiscount))
						{
							switch ($special_discount_type)
							{
								case "flat": // flat price
									$price = $special_discount_value;
									break;

								case "sub": // Subtraction of base price
									$price = (float) $product_query->row['price'] - (float) $special_discount_value;
									break;

								case "per": // percentage of base price
									$price = (float) $product_query->row['price']  * (1 - (float) $special_discount_value / 100);
									break;
							}
						}
					$app_config_zone = $this->config->get('config_timezone')?:'UTC';
					$now = (new DateTime('NOW', new DateTimeZone($app_config_zone)))->format('Y-m-d');

					// Product Specials
					if ($this->customer->isLogged()) {
						$visitor_customer_group_id=$customer_group_id;
					} else {
						$visitor_customer_group_id = $this->config->get('config_customer_group_id') !=""? $this->config->get('config_customer_group_id') :"1";
					}
					if(!isset($discount_quantity))
						$discount_quantity = 1;

					$product_has_discount = false;

					$product_specials_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_query->row['product_id'] . "' AND customer_group_id = '" . (int)$visitor_customer_group_id . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start <= '".$now."') AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end >= '".$now."')) ORDER BY priority ASC, price ASC LIMIT 1");
					if($product_specials_query->num_rows)
						$product_has_discount = true;


					$product_discounts_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_query->row['product_id']  . "' AND customer_group_id = '" . (int)$visitor_customer_group_id . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((IFNULL(date_start, '0000-00-00') = '0000-00-00' OR date_start < NOW()) AND (IFNULL(date_end, '0000-00-00') = '0000-00-00' OR date_end > NOW())) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");

					if ($product_discounts_query->num_rows)
						$product_has_discount = true;

					//// End of Mass Update App discounts
                    $this->data[$key] = array(
						'key'             => $key,
						'product_id'      => $product_query->row['product_id'],
						'name'            => $product_query->row['name'],
						'description'     => $product_query->row['description'],
						'model'           => $productsoptions_sku_status ? $model_sku : $product_query->row['model'],
						'shipping'        => $product_query->row['shipping'],
						'image'           => $product_query->row['image'],
						'option'          => $option_data,
						'download'        => $download_data,
						'quantity'        => $quantity, // requested quantity in cart
						'stock_quantity'  => $product_query->row['quantity'], // product quanitiy in stock
						'minimum'         => $product_query->row['minimum'],
						'maximum'         => $product_query->row['maximum'],
						'subtract'        => $product_query->row['subtract'],
						'transaction_type'=> $product_query->row['transaction_type'],
						'unlimited'        => $product_query->row['unlimited'],
						'stock'           => $stock,
						'price'           => $productsoptions_sku_status && (!$product_has_discount)&& (bool)$variation_price ? ($variation_price ) : ( $price + $option_price ) ,
						'price_percentage'=> $product_query->row['price_percentage'],
						'total'           => $productsoptions_sku_status  && (!$product_has_discount) && (bool)$variation_price ? ($variation_price * $quantity ) : (($price + $option_price) * $quantity ),
						'reward'          => $reward * $quantity,
						'points'          => ($product_query->row['points'] ? ($product_query->row['points'] + $option_points) * $quantity : 0),
						'tax_class_id'    => $product_query->row['tax_class_id'],
						'weight'          => ($product_query->row['weight'] + $option_weight) * $quantity,
						'weight_class_id' => $product_query->row['weight_class_id'],
						'length'          => $product_query->row['length'],
						'width'           => $product_query->row['width'],
						'height'          => $product_query->row['height'],
						'length_class_id' => $product_query->row['length_class_id'],
						'manufacturer_id' => $product_query->row['manufacturer_id']
					);
					// $this->data[$key]['price'] = ($price + $option_price);
	                // $this->data[$key]['total'] = (($price + $option_price) * $quantity);
					if (isset($rentData[$product_id])) {
						$rentDiff = $rentData[$product_id]['diff'];
                        $this->data[$key]['rentData'] = $rentData[$product_id];
                        $this->data[$key]['price'] = $productsoptions_sku_status  && (bool)$variation_price ? ($price + $variation_price) * $rentDiff : ($price + $option_price) * $rentDiff;
                        $this->data[$key]['total'] = $productsoptions_sku_status  && (bool)$variation_price ? (($price + $variation_price) * $rentDiff) * $quantity : (($price + $option_price) * $rentDiff) * $quantity;
                    }
					if (isset($bundles[$key]) && count($bundles[$key]) > 0 ) {

						$this->data[$key]['bundlesData'] = $bundles[$key];
						// price logic
						foreach($bundles[$key] as $bundle){
							$bundleProdDisc = $bundle['bundle_discount'] ?? 0 ;
							$bundlePriceAfterDisc = (1 - $bundleProdDisc) * $bundle['price'];
							$bundleTotalAfterDisc = (1 - $bundleProdDisc) * $quantity * $bundle['price'];
							$this->data[$key]['price'] += $bundlePriceAfterDisc;
							$this->data[$key]['total'] += $bundleTotalAfterDisc;
						}

                    }

	                if (isset($pricePerMeterData[$product_id])) {
	                    	$this->data[$key]['pricePerMeterData'] = $pricePerMeterData[$product_id];
	                    	$arrayPriceMeter = json_decode($product_query->row['price_meter_data'], true);
	                    	///calculate if per meter
	                    	if($arrayPriceMeter['main_status'] == 1){
	                    		$underlaymen = $pricePerMeterData[$product_id]['underlaymen'];
	                    		$skirtings = $pricePerMeterData[$product_id]['skirtings'];
	                    		$metalProfiles = $pricePerMeterData[$product_id]['metalProfiles'];

	                    		$addingValueMain = 0;
	                    		if($arrayPriceMeter['main_price_percentage'])
	                    			$addingValueMain = (($arrayPriceMeter['main_price_percentage']/100)*$underlaymen);
	                    		$pricrMain = ($underlaymen + $addingValueMain ) * $arrayPriceMeter['main_meter_price'];

	                    		if($pricePerMeterData[$product_id]['skirtings_status'] == 1){
	                    			$addingValueSki = 0;
	                    			if($arrayPriceMeter['skirtings_price_percentage'])
	                    				$addingValueSki = (($arrayPriceMeter['skirtings_price_percentage']/100)*$skirtings);
	                    			$pricrSki = ( $skirtings + $addingValueSki) * $arrayPriceMeter['skirtings_meter_price'];
	                    		}else{
	                    			$pricrSki = 0;
	                    		}

	                    		if($pricePerMeterData[$product_id]['metalprofile_status'] == 1){
	                    			$addingValueMetal = 0;
	                    			if($arrayPriceMeter['metalprofile_price_percentage'])
	                    				$addingValueMetal = (($arrayPriceMeter['metalprofile_price_percentage']/100)*$metalProfiles);
	                    			$pricrMetal = ( $addingValueMetal + $metalProfiles) * $arrayPriceMeter['metalprofile_meter_price'] ;
	                    		}else{
	                    			$pricrMetal = 0;
	                    		}

	                    		$totalPrices = (float)$pricrMain + (float)$pricrSki + (float)$pricrMetal;

		                        $this->data[$key]['price'] = $productsoptions_sku_status && (bool)$variation_price ? ($totalPrices + $variation_price) : ($totalPrices + $option_price);
		                        $this->data[$key]['total'] = $productsoptions_sku_status && (bool)$variation_price ? ($totalPrices + $variation_price) * $quantity : ($totalPrices + $option_price) * $quantity;
	                    	}
	                    	///////////////////////////////
					}

	                if (isset($fifaCardsData[$product_id]))
					{
						$this->data[$key]['fifaCardsData'] = $fifaCardsData[$product_id];
					}

					if (isset($curtain_seller_option_data[$product_id]) && ! empty($curtain_seller_option_data[$product_id])) {
						$curtain_seller_final_price = $curtain_seller_option_data[$product_id]['total'] + $option_price;
						$curtain_seller_final_total = $curtain_seller_final_price * $quantity;

						$this->data[$key]['price'] = $curtain_seller_final_price;
						$this->data[$key]['total'] = $curtain_seller_final_total;

						$curtain_seller_option_data[$product_id]['total'] = $this->currency->format($curtain_seller_final_total);
						$this->data[$key]['curtain_seller'] = $curtain_seller_option_data[$product_id];
					}

                    if(isset($printingDocument)){
						$this->data[$key]['printingDocument'] = $printingDocument[$product_id];
						$_price = $productsoptions_sku_status && (bool) $variation_price ? $variation_price : ($price + $option_price);
                    	$price = ($_price * $printingDocument[$product_id]['print_pages']) + $printingDocument[$product_id]['print_cover'];
						//$price = $priceTotalPages * $printingDocument[$product_id]['print_copies'];

                    	$this->data[$key]['price'] = $price;
                    	$this->data[$key]['total'] = $price * $quantity;
                    }

					if (isset($minimum_deposit_customer_price[$product_id]) && !empty($minimum_deposit_customer_price[$product_id])) {
						$final_price = $minimum_deposit_customer_price[$product_id];
						$final_total = $minimum_deposit_customer_price[$product_id] * $quantity;

						$this->data[$key]['price'] = $final_price;
						$this->data[$key]['total'] = $final_total;
						$this->data[$key]['main_price'] = $product_query->row['price'] *  $quantity ;
						$this->data[$key]['remaining_amount'] = ($product_query->row['price'] *  $quantity) -  $final_total ;
					}
				} else {
					$this->remove($key);
				}
			}

		}
		return $this->data;
  	}

  	/**
  	 * Get categories ids list of cart products
  	**/
  	public function getCategoriesIds() {
  		$ids_list = false;
  		$seesion_data = $this->session->data['cart'];
		if ($seesion_data) {
			foreach ($seesion_data as $key => $quantity) {
				$product = explode(':', $key);
				$product_ids[] = $product[0];
			}

			$queryString = "SELECT DISTINCT product_id, category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN (" . implode(',', $product_ids) . ")";

			$cats_query = $this->db->query($queryString);

			if ($cats_query->num_rows) {
				foreach ($cats_query->rows as $key => $value) {
					$ids_list[$value['product_id']][] = $value['category_id'];
				}
			}
		}
		return $ids_list;
  	}

  	public function add( $product_id, $qty = 1, $option = array() )
    {
    	if ( ! $option || !is_array( $option ) )
        {
      		$key = (int)$product_id;
    	}
        else
        {
      		$key = (int) $product_id . ':' . base64_encode( serialize( $option ) );
    	}

		if ( (int) $qty && (int) $qty > 0 )
        {
    		if ( ! isset($this->session->data['cart'][$key]) )
            {
      			$this->session->data['cart'][$key] = (int) $qty;

    		}
            else
            {
      			$this->session->data['cart'][$key] += (int) $qty;
    		}


			$this->customer->updateActs();
		}


		$this->cartProductUid = $key;

		$this->data = array();
  	}


  	public function update($key, $qty) {
    	if ((int)$qty && ((int)$qty > 0) && isset($this->session->data['cart'][$key])) {
      		$this->session->data['cart'][$key] = (int)$qty;
            $this->customer->updateActs();
    	} else {
	  		$this->remove($key);
		}

		$this->data = array();
  	}

  	public function remove($key) {
		if (isset($this->session->data['cart'][$key])) {
     		unset($this->session->data['cart'][$key]);
            $this->customer->updateActs();
  		}

		$this->data = array();
	}

  	public function clear() {
		$this->session->data['cart'] = array();
        $this->customer->updateActs();
		$this->data = array();
  	}

  	public function getWeight() {
		$weight = 0;

    	foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
      			$weight += $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
			}
		}

		return $weight;
	}

  	public function getSubTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}

		return $total;
  	}

	public function getTaxes() {
		$tax_data = array();

		foreach ($this->getProducts() as $product) {
			if ($product['tax_class_id']) {
				$tax_rates = $this->tax->getRates($product['price'], $product['tax_class_id']);

				foreach ($tax_rates as $tax_rate) {
					if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
						$tax_data[$tax_rate['tax_rate_id']] = ($tax_rate['amount'] * $product['quantity']);
					} else {
						$tax_data[$tax_rate['tax_rate_id']] += ($tax_rate['amount'] * $product['quantity']);
					}
				}
			}
		}

		return $tax_data;
  	}

  	public function getTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
		}

		return $total;
  	}
	  	
  	public function countProducts() {
		$product_total = 0;
			
		$products = $this->getProducts();
			
		foreach ($products as $product) {
			$product_total += $product['quantity'];
		}		
					
		return $product_total;
	}
	
	public function getProductQuantity($product_id) {
		$count = 0;
		$products = $this->getProducts();
			
		foreach ($products as $product) {

			if($product['product_id'] == $product_id)
				return $product['quantity'];
		}		
					
		return 0;
	}
  	public function hasProducts() {
    	return !!count($this->session->data['cart']);
  	}
  
  	public function hasStock( $return_prod_info = false, $old_product_ids = array(), $language_id = null )
    {
		$stock = true;
			
		foreach ( $this->getProducts($language_id) as $product )
        {
        	// handle rental / reservation products 
			if(!empty($product['rentData']['range']['from']) && !empty($product['rentData']['range']['to'])  && $product['transaction_type'] == 2)
			{
                global $registry;
                $rentloader = new Loader($registry);
                $rentloader->model('catalog/product');
                $rent_model = $registry->get('model_catalog_product');
				// check if these days span has a disabled day and return error instead of adding to cart
				$from = $product['rentData']['range']['from'];
				$to = $product['rentData']['range']['to'];
	        	$disabled_days = $rent_model->getRentDisabledDates($from,$to,$product['product_id'],$product['stock_quantity'],$product['quantity']);
	        	if(count($disabled_days)){
		    		$stock = false;
	                $prod_info  = $product;
	                break;
	        	}
			}

			if (!(bool)$product['subtract'] || (bool)$product['unlimited']) {
				continue;
			}

            // Check if the product is an original of the current order
            if ( $old_product_ids && ! empty( $old_product_ids ) )
            {
                if ( in_array( $product['product_id'] , $old_product_ids) )
                {
                    return true;
                }
            }

			if ( ! $product['stock'] )
            {
	    		$stock = false;
                $prod_info  = $product;
                break;
			}
		}
		
        if ( $stock === true )
        {
            $prod_info = true;
        }

    	return $return_prod_info ? $prod_info : $stock;
  	}
  
  	public function hasShipping() {
		$shipping = false;
		
		foreach ($this->getProducts() as $product) {
	  		if ($product['shipping']) {
	    		$shipping = true;
				
				break;
	  		}		
		}
		
		return $shipping;
	}
	
  	public function hasDownload() {
		$download = false;
		
		foreach ($this->getProducts() as $product) {
	  		if ($product['download']) {
	    		$download = true;
				
				break;
	  		}		
		}
		
		return $download;
	}

    public function getOptionPrice($options, $product_id, $single)
    {
        if ($single) {
            $sql = 'SELECT DISTINCT(variation_id) FROM '.DB_PREFIX.'warehouse_aliexpress_product_variation_option WHERE option_value_id IN ('.$this->db->escape($options).") AND product_id = '".(int) $product_id."' GROUP BY variation_id ";
        } else {
            $sql = 'SELECT DISTINCT(variation_id) FROM '.DB_PREFIX.'warehouse_aliexpress_product_variation_option WHERE option_value_id IN ('.$this->db->escape($options).") AND product_id = '".(int) $product_id."' GROUP BY variation_id HAVING COUNT(variation_id) > 1 ";
        }
        $result = $this->db->query($sql)->row;
        if ($result) {
//            $options = implode('_', explode(',', $options));
			$result = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_product_variation WHERE id = '". (int) $result['variation_id']."' AND product_id = '".(int) $product_id."' AND quantity > '0'")->row;
            if ($result) {
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getAlixOptionByProductOption($data)
    {
        $sql = 'SELECT pov.product_id,waov.alix_option_value_id FROM '.DB_PREFIX.'product_option_value pov LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_product_option_value waov ON (waov.oc_option_id = pov.option_id && waov.oc_option_value_id = pov.option_value_id) WHERE pov.product_option_value_id = '".(int) $data['product_option_value_id']."' && pov.product_option_id = '".(int) $data['product_option_id']."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return $result;
        }
    }

	/**
	 * check if product is ali express product
	 *
	 *
	 * @return boolean
	 */
	public function checkAliProductExists($product_id)
	{
		$sql = 'SELECT product_id FROM '.DB_PREFIX.'warehouse_aliexpress_product WHERE product_id = '.(int) $product_id.' ';
		$result = $this->db->query($sql)->row;
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * Return latest added to cart product unique id
     *
     *
     * @return string
     */
    public function getCartProductUid()
    {
        return $this->cartProductUid;
    }

    public function getProductBundlesIds($main_product_id) {
        $query = $this->db->query("SELECT pb.bundle_product_id, pb.discount FROM " . DB_PREFIX . "product_bundles pb WHERE pb.main_product_id = " . (int)$main_product_id);
        $rows = $query->rows;
        $returned_product_ids = array_column($rows, 'bundle_product_id');
        return $query->rows;
    }

}
?>
