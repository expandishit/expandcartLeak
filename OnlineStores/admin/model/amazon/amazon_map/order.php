<?php

class ModelAmazonAmazonMapOrder extends Model {
		/**
		 * [getOcAmazonOrderMap to get list of mapped Amazon order with opencart order]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getOcAmazonOrderMap($data = array()){
				$sql = "SELECT *, aom.id as map_id FROM ".DB_PREFIX."amazon_order_map aom LEFT JOIN `".DB_PREFIX."order` o ON(aom.oc_order_id = o.order_id) LEFT JOIN ".DB_PREFIX."customer c ON(o.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX."customer_group_description cgd ON(c.customer_group_id = cgd.customer_group_id) LEFT JOIN ".DB_PREFIX."amazon_customer_map acm ON(c.customer_id = acm.oc_customer_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' AND cgd.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(!empty($data['account_id'])){
						$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
				}

				if(!empty($data['filter_map_id'])){
						$sql .= " AND aom.id = '".(int)$data['filter_map_id']."' ";
				}

				if(!empty($data['oc_order_id'])){
						$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
				}

				if(!empty($data['amazon_order_id'])){
						$sql .= " AND aom.amazon_order_id = '".$this->db->escape($data['amazon_order_id'])."' ";
				}

				if(!empty($data['filter_buyer_name'])){
					$sql .= " AND LCASE(acm.name) LIKE '".$this->db->escape(strtolower($data['filter_buyer_name']))."%' ";
				}

				if(!empty($data['filter_buyer_email'])){
					$sql .= " AND LCASE(acm.email) LIKE '".$this->db->escape(strtolower($data['filter_buyer_email']))."%' ";
				}

				if (isset($data['filter_order_total']) && !is_null($data['filter_order_total'])) {
					$sql .= " AND o.total LIKE '" . $this->db->escape($data['filter_order_total']) . "%'";
				}

				if(!empty($data['filter_date_added'])){
					$sql .= " AND aom.sync_date LIKE '".$this->db->escape($data['filter_date_added'])."%' ";
				}

				if(!empty($data['filter_order_status'])){
					$sql .= " AND LCASE(aom.amazon_order_status) LIKE '".$this->db->escape(strtolower($data['filter_order_status']))."%' ";
				}

					$sort_data = array(
						'aom.amazon_order_id',
						'aom.amazon_order_status',
						'o.oc_order_id',
						'o.total',
						'o.firstname',
						'o.lastname',
						'c.email'
					);

					if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
						$sql .= " ORDER BY " . $data['sort'];
					} else {
						$sql .= " ORDER BY aom.id";
					}

					if (isset($data['order']) && ($data['order'] == 'DESC')) {
						$sql .= " DESC";
					} else {
						$sql .= " ASC";
					}

					if (isset($data['start']) || isset($data['limit'])) {
							if ($data['start'] < 0) {
								$data['start'] = 0;
							}

							if ($data['limit'] < 1) {
								$data['limit'] = 20;
							}

							$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
					}
				return $this->db->query($sql)->rows;
	  }

	public function getAllOcAmazonOrderMap($start = 0, $length = 10, $filterData = null, $orderColumn = "map_id") {
		$sql_str = "SELECT *, aom.id as map_id FROM ".DB_PREFIX."amazon_order_map aom LEFT JOIN `".DB_PREFIX."order` o ON(aom.oc_order_id = o.order_id) LEFT JOIN ".DB_PREFIX."customer c ON(o.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX."customer_group_description cgd ON(c.customer_group_id = cgd.customer_group_id) LEFT JOIN ".DB_PREFIX."amazon_customer_map acm ON(c.customer_id = acm.oc_customer_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' AND cgd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		$total = $this->db->query($sql_str)->num_rows;
		$where = "";
		if (!empty($filterData['search'])) {
		  $where .= "(aom.oc_order_id like '%" . $this->db->escape($filterData['search']) . "%'
					  OR aom.amazon_order_id like '%" . $this->db->escape($filterData['search']) . "%
					  OR LCASE(acm.name) like '%" . $this->db->escape($filterData['search']) . "%
					  OR LCASE(acm.email) like '%" . $this->db->escape($filterData['search']) . "%
					  OR o.total like '%" . $this->db->escape($filterData['search']) . "%'
					  OR LCASE(aom.amazon_order_status) like '%" . $this->db->escape($filterData['search']) . "%' ) ";
		  $sql_str .= " AND " . $where;
		}
	
		$totalFiltered = $this->db->query($sql_str)->num_rows;
		$sql_str .= " ORDER by {$orderColumn} DESC";
	
		if ($length != -1) {
		$sql_str .= " LIMIT " . $start . ", " . $length;
		}
	
		$results=$this->db->query($sql_str)->rows;
	
		$data = array(
		  'data' => $results,
		  'total' => $total,
		  'totalFiltered' => $totalFiltered
		);
	
		return $data;
  
	}
		/**
		 * [getTotalOcAmazonOrderMap to get total number of mapped Amazon orders with opencart orders]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getTotalOcAmazonOrderMap($data = array()){
				$sql = "SELECT COUNT(DISTINCT aom.id) as total FROM ".DB_PREFIX."amazon_order_map aom LEFT JOIN `".DB_PREFIX."order` o ON(aom.oc_order_id = o.order_id) LEFT JOIN ".DB_PREFIX."customer c ON(o.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX."customer_group_description cgd ON(c.customer_group_id = cgd.customer_group_id) LEFT JOIN ".DB_PREFIX."amazon_customer_map acm ON(c.customer_id = acm.oc_customer_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' AND cgd.language_id = '".(int)$this->config->get('config_language_id')."' ";

				if(!empty($data['account_id'])){
						$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
				}

				if(!empty($data['filter_map_id'])){
						$sql .= " AND aom.id = '".(int)$data['filter_map_id']."' ";
				}

				if(!empty($data['oc_order_id'])){
						$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
				}

				if(!empty($data['amazon_order_id'])){
						$sql .= " AND aom.amazon_order_id = '".$this->db->escape($data['amazon_order_id'])."' ";
				}

				if(!empty($data['filter_buyer_name'])){
					$sql .= " AND LCASE(acm.name) LIKE '".$this->db->escape(strtolower($data['filter_buyer_name']))."%' ";
				}

				if(!empty($data['filter_buyer_email'])){
					$sql .= " AND LCASE(acm.email) LIKE '".$this->db->escape(strtolower($data['filter_buyer_email']))."%' ";
				}

				if (isset($data['filter_order_total']) && !is_null($data['filter_order_total'])) {
					$sql .= " AND o.total LIKE '" . $this->db->escape($data['filter_order_total']) . "%'";
				}

				if(!empty($data['filter_date_added'])){
					$sql .= " AND aom.sync_date LIKE '".$this->db->escape($data['filter_date_added'])."%' ";
				}

				if(!empty($data['filter_order_status'])){
					$sql .= " AND LCASE(aom.amazon_order_status) LIKE '".$this->db->escape(strtolower($data['filter_order_status']))."%' ";
				}

				$result = $this->db->query($sql)->row;

				return $result['total'];
	  }

		public function getFinalOrderReport($data = array(), $account_details){
			$orderArray = $response = array();
			$orderNextToken 							= $data['next_token'];
			$data['amazon_order_maximum'] = 20;
			$date = explode(" - ",$data['amazon_order_daterange']);
			
			$date_to = new \DateTime($date[1]);
			$data['amazon_order_to'] = $date_to->format('Y-m-d\TH:i:s');
			$date_from 	= new \DateTime($date[0]);
			$data['amazon_order_from']	= $date_from->format('Y-m-d\TH:i:s');
			try {
					if(isset($orderNextToken) && !empty($orderNextToken)){
							$orderLists = $this->Amazonconnector->ListOrdersByNextToken($data, $account_details['id']);

							if (isset($orderLists['ListOrdersByNextTokenResult']['NextToken'])) {
									$orderNextToken = $orderLists['ListOrdersByNextTokenResult']['NextToken'];
							} else {
									$orderNextToken = '';
							}
							if (isset($orderLists['ListOrdersByNextTokenResult']['Orders']['Order'])) {
									$orderArray = isset($orderLists['ListOrdersByNextTokenResult']['Orders']['Order'][0]) ? $orderLists['ListOrdersByNextTokenResult']['Orders']['Order'] : [$orderLists['ListOrdersByNextTokenResult']['Orders']['Order']];
							}
					}else{
							$orderLists = $this->Amazonconnector->getOrderList($data, $account_details['id']);

							if (isset($orderLists['ListOrdersResult']['NextToken'])) {
									$orderNextToken = $orderLists['ListOrdersResult']['NextToken'];
							}
							if(isset($orderLists['ListOrdersResult']['Orders']['Order'])){
									$orderArray = isset($orderLists['ListOrdersResult']['Orders']['Order'][0]) ? $orderLists['ListOrdersResult']['Orders']['Order'] : [$orderLists['ListOrdersResult']['Orders']['Order']];
							}
					}

					if(!empty($orderArray)){
							$saveImportedData = $this->saveOrderReportData($orderArray, $account_details);
							if(isset($saveImportedData['amazonOrders']) && is_array($saveImportedData['amazonOrders'])){
									$response['success'] = count($saveImportedData['amazonOrders']);
							}
							if(isset($saveImportedData['error']) && !empty($saveImportedData['error'])){
									$response['error'] = $saveImportedData['error'];
							}
							if(isset($saveImportedData['error_exception']) && !empty($saveImportedData['error_exception'])){
									$response['error_exception'] = $saveImportedData['error_exception'];
							}
					}else{
							if (isset($orderLists['error'])) {
									$response['error_exception'] = 'Warning: '.$orderLists['error'].', please try again after some time!';
							} else {
									$response['error_exception'] = $this->language->get('error_no_order_found');
							}
					}
					$response['next_token'] 	= $orderNextToken;
			} catch (\Exception $e) {
					$response['error_exception'] = 'Warning: '.$e->getMessage().', please try again after some time!';
			}
			return $response;
		}

		public function saveOrderReportData($amazonOrders = array(), $account_details){
			$orderListArray = $response = $failedOrder = array();
			$this->load->model('amazon_map/product');
			$this->load->model('catalog/product');
			$this->load->model('localisation/language');
			$this->load->model('setting/store');
			$tempdata = array();
				try {
						foreach ($amazonOrders as $key => $orderData) {
								$tempAvlImported = $this->getOrderTempData(array('item_id' => $orderData['AmazonOrderId'], 'account_id' => $account_details['id']));

								// if already exist in order_tempdata table
								if (isset($tempAvlImported[0]['amazon_order_id']) && $tempAvlImported[0]['amazon_order_id'] == $orderData['AmazonOrderId']) {
										array_push($orderListArray, $tempAvlImported[0]['amazon_order_id']);
										continue;
								}
								$firstname 			= 'Guest';
                $lastname 			= 'User';
                $shipPrice 			= 0;
                $shipMethod 		= 'From Amazon ';

								if (isset($orderData['ShipServiceLevel'])) {
                    $shipMethod .= $orderData['ShipServiceLevel'];
                }
                if (!isset($orderData['ShippingAddress'])) {
										$failedOrder[$orderData['AmazonOrderId']] = [
																		'amazon_order_id' => $orderData['AmazonOrderId'],
																		'message' 				=> 'Warning: Amazon order Id: <b>'.$orderData['AmazonOrderId'].'</b> not sync because ShippingAddress is missing for this order!',
																	];
                    continue;
                }

								$invalidOrder = false;
								$product_data = $this->getOrderProductDetails(array('amazon_order_id' => $orderData['AmazonOrderId'], 'account_details' => $account_details));

								foreach ($product_data as $key => $ordProduct) {
										if(isset($ordProduct['error_status']) && $ordProduct['error_status']){
												$failedOrder[$orderData['AmazonOrderId']] = [
																			'amazon_order_id' => $orderData['AmazonOrderId'],
																			'message' 				=> $ordProduct['message'].' for amazon order Id (<b>'.$orderData['AmazonOrderId'].'</b>)',
																		];
												$invalidOrder = true;
										}
								}
								if($invalidOrder){
										continue;
								}

								$getCurrencyId = array();
								if($this->config->get('config_currency')){
									$getCurrencyId = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE code = '" . $this->db->escape($this->config->get('config_currency')) . "'")->row;
								}
								if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
									$forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
								} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
									$forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
								} else {
									$forwarded_ip = '';
								}

								if (isset($this->request->server['HTTP_USER_AGENT'])) {
									$user_agent = $this->request->server['HTTP_USER_AGENT'];
								} else {
									$user_agent = '';
								}

								$customer_details = array(
									'BuyerName'				=> $orderData['BuyerName'],
									'BuyerEmail'			=> $orderData['BuyerEmail'],
									'ShippingAddress' => $orderData['ShippingAddress'],
								);

								$order_data = [
											'amazon_order_id'	=> $orderData['AmazonOrderId'],
											'amazon_order_status'	=> $orderData['OrderStatus'],
											'status'					=> 1,
											'invoice_no'			=> 0,
											'invoice_prefix'	=> $this->config->get('config_invoice_prefix'),
											'store_id'				=> $account_details['wk_amazon_connector_default_store'],
											'store_name'			=> $this->config->get('config_name'),
											'store_url'				=> $this->config->get('config_store_id') ? $this->config->get('config_url') : ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER),
											'customer_details'=> $customer_details,
											'customer_id'			=> 0,
											'customer_group_id'=> 0,
											'firstname' 			=> '',
											'lastname' 				=> '',
											'email' 					=> '',
											'telephone' 			=> '',
											'fax' 						=> '',
											'account_id' 			=> 0,
											'language_id' 		=> $this->config->get('config_language_id'),
											'currency_id' 		=> $getCurrencyId['currency_id'],
											'currency_code' 	=> $getCurrencyId['code'],
											'currency_value'	=> $getCurrencyId['value'],
											'order_status_id' => $this->config->get('wk_amazon_connector_order_status'),
															'shipping_firstname' 	=> '',
															'shipping_lastname' 	=> '',
															'shipping_address_1' 	=> '',
															'shipping_address_2' 	=> '',
															'shipping_city' 			=> '',
															'shipping_postcode'		=> '',
															'shipping_zone'				=> '',
															'shipping_zone_id'		=> '',
															'shipping_country'		=> '',
															'shipping_country_id'	=> '',
															'shipping_address_format'=> '',
											'shipping_method'	=> $shipMethod,
											'shipping_code' 	=> '',
											'payment_method' 	=> $orderData['PaymentMethod'],
											'payment_code' 	 	=> $orderData['PaymentMethod'],
											'products' 				=> $product_data,
											'affiliate_id' 		=> 0,
											'commission' 			=> 0,
											'marketing_id' 		=> 0,
											'tracking' 				=> '',
											'user_agent'  		=> $user_agent,
											'forwarded_ip'  	=> $forwarded_ip,
											'ip' 							=> $this->request->server['REMOTE_ADDR'],
								];

								$date_added 	= new \DateTime();
								$currentDate 	= $date_added->format('Y-m-d\TH:i:s');

								$tempdata[$orderData['AmazonOrderId']] = [
												'item_type' 					=> 'order',
												'item_id' 						=> $orderData['AmazonOrderId'],
												'item_data' 					=> json_encode($order_data),
												'amazon_product_id' 	=> $orderData['PurchaseDate'],
												'account_id' 					=> $account_details['id'],
												'added_date' 					=> $currentDate
										];

							array_push($orderListArray, $orderData['AmazonOrderId']);
						}

						if(!empty($tempdata) && isset($tempdata)){
							$getOrderSaveResult = $this->model_amazon_map_product->InsertDataInBulk($tempdata);
						}
						$response['error'] 								= $failedOrder;
            $response['amazonOrders'] 				= $orderListArray;
				} catch (\Exception $e) {
						$this->log->write('OrderedDataSave saveOrderReportData : '.$e->getMessage());
						$response['error_exception'] 			= 'Warning: '.$e->getMessage().', please try again after some time!';
            $response['amazonOrders']					= $orderListArray;
        }
				return $response;
		}

		public function getOrderTempData($data = array()){
				$sql = "SELECT td.item_id as amazon_order_id, td.* FROM ".DB_PREFIX."amazon_tempdata td LEFT JOIN ".DB_PREFIX."amazon_accounts aa ON (td.account_id = aa.id) WHERE td.item_type = 'order' ";

				if(!empty($data['item_id'])){
					$sql .= " AND td.item_id = '".$this->db->escape($data['item_id'])."' ";
				}

				if(!empty($data['account_id'])){
					$sql .= " AND td.account_id = '".(int)$data['account_id']."' AND aa.id = '".(int)$data['account_id']."' ";
				}

				$sql .= " GROUP BY amazon_order_id ";

				return $this->db->query($sql)->rows;
		}

		public function getImportedOrder($accountId, $count_type = 'single', $amazon_order_id = 0){
				$sql = "";
				if($amazon_order_id){
					$sql .= " AND item_id = '".$this->db->escape($amazon_order_id)."' ";
				}
				if($count_type == 'all'){
						$getRecord = $this->db->query("SELECT COUNT(*) as total FROM ".DB_PREFIX."amazon_tempdata WHERE item_type = 'order' AND account_id = '".(int)$accountId."' ORDER BY id ASC ")->row;
				}else{
						$getRecord = $this->db->query("SELECT * FROM ".DB_PREFIX."amazon_tempdata WHERE item_type = 'order' $sql AND account_id = '".(int)$accountId."' ORDER BY id ASC LIMIT 1 ")->row;
				}
				return $getRecord;
		}

		public function getOrderProductDetails($data = array()){
				$orderItemList = array();
				$getOrderProducts = $this->Amazonconnector->ListOrderItems($data['amazon_order_id'], $data['account_details']['id']);

				if(count($getOrderProducts)){
						$makeOrderProductArray = $amzOrderItems = array();
						if(isset($getOrderProducts[0][0])){
								$makeOrderProductArray = $getOrderProducts[0];
						}else{
								$makeOrderProductArray = $getOrderProducts;
						}
						$amzOrderItems = isset($makeOrderProductArray[0]) ? $makeOrderProductArray : [$makeOrderProductArray];
						foreach ($amzOrderItems as $key => $amzOrder) {
								$amzOrder 				= (array)$amzOrder;

								$amzOrderDetails 	= [];
								if(isset($amzOrder['ASIN']) && $amzOrder['ASIN']){
										$orderedQuantity = $amzOrder['QuantityOrdered'];

										$orderedPrice 	 = $amzOrder['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
                 if(isset($amzOrder['ShippingPrice'])) {
										$shipping_price = $amzOrder['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);
									} else {
                       $shipping_price = 0;
									}
										$tax 						= $amzOrder['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $data['account_details']['wk_amazon_connector_currency_rate']);

										// check product entry exists in amazon_tempdata regarding (amazon product ASIN) table
										$getTempProData = $this->model_amazon_map_product->getProductTempData(array('item_id' => $amzOrder['ASIN'], 'account_id' => $data['account_details']['id']));

										if(isset($getTempProData[0]['amazon_prod_id']) && $getTempProData[0]['amazon_prod_id'] == $amzOrder['ASIN']){
												//create product to opencart store
												$amzProdDetails = $this->model_amazon_map_product->createProductToOC($getTempProData[0], array('id' => $data['account_details']['id']));

												if(isset($amzProdDetails[$amzOrder['ASIN']])){
														// get product/variation product entry details
														$productEntryDetails = $this->getProductOrVariationProductFromOCStore($amzOrder['ASIN'], $data['account_details']['id']);
														if(!empty($productEntryDetails)){
																$productEntryDetails['quantity'] 			= $orderedQuantity;
																$productEntryDetails['price'] 	 			= $orderedPrice;
																$productEntryDetails['total'] 	 			= $orderedQuantity * $orderedPrice;
																$productEntryDetails['tax'] 	 				= $tax;
																$productEntryDetails['shipping_price']= $shipping_price;
																$amzOrderDetails['orderItems'] 				= $productEntryDetails;
																$amzOrderDetails['error_status']			= false;
														}
												}else{
														if(isset($amzProdDetails[0]['error'])){
																$amzOrderDetails 	= [
																		'error_status'		=> true,
																		'amazon_order_id'	=> $data['amazon_order_id'],
																		'message'					=> $amzProdDetails[0]['message'].' (Amazon ProductId: <b>'.$amzOrder['ASIN'].'</b>)',
																];
														}
												}
										}else{
												// check product/variation's product already created in opencart store
												$productEntryDetails = $this->getProductOrVariationProductFromOCStore($amzOrder['ASIN'], $data['account_details']['id']);

												if(!empty($productEntryDetails)){
														$productEntryDetails['quantity'] 			= $orderedQuantity;
														$productEntryDetails['price'] 	 			= $orderedPrice;
														$productEntryDetails['total'] 	 			= $orderedQuantity * $orderedPrice;
														$productEntryDetails['tax'] 	 				= $tax;
														$productEntryDetails['shipping_price']= $shipping_price;
														$amzOrderDetails['orderItems'] 				= $productEntryDetails;
														$amzOrderDetails['error_status']			= false;
												}else{
														// create amazon product to openacrt store
														$amzProdDetails = $this->createAmazonOrderedProductToOC($amzOrder, $data['account_details']);
														if(!empty($amzProdDetails) && isset($amzProdDetails['success'])){
																// get product/variation product entry details
																$productEntryDetails = $this->getProductOrVariationProductFromOCStore($amzOrder['ASIN'], $data['account_details']['id']);

																if(!empty($productEntryDetails) && isset($productEntryDetails['product_id'])){
																		$productEntryDetails['quantity'] 			= $orderedQuantity;
																		$productEntryDetails['price'] 	 			= $orderedPrice;
																		$productEntryDetails['total'] 	 			= $orderedQuantity * $orderedPrice;
																		$productEntryDetails['tax'] 	 				= $tax;
																		$productEntryDetails['shipping_price']= $shipping_price;
																		$amzOrderDetails['orderItems'] 				= $productEntryDetails;
																		$amzOrderDetails['error_status']			= false;
																}
														}else{
																if(isset($amzProdDetails['error_status'])){
																		$amzOrderDetails 	= [
																				'error_status'		=> true,
																				'amazon_order_id'	=> $data['amazon_order_id'],
																				'message'					=> $amzProdDetails['message'],
																		];
																}else{
																		$amzOrderDetails 	= [
																				'error_status'		=> true,
																				'amazon_order_id'	=> $data['amazon_order_id'],
																				'message'					=> 'Warning: invalid product data ('.$amzOrder['ASIN'].') for Amazon Order Id: '.$data['amazon_order_id'].' !',
																		];
																}
														}
												}
										}
								}else{
										$amzOrderDetails 	= [
												'error_status'		=> true,
												'amazon_order_id'	=> $data['amazon_order_id'],
												'message'					=> 'Warning: Invalid product for the Amazon Order Id: '.$data['amazon_order_id'].' !',
										];
								}
								$orderItemList[] = $amzOrderDetails;
						}
				}else{
						$orderItemList[] = [
							'error_status'		=> true,
							'amazon_order_id'	=> $data['amazon_order_id'],
							'message'					=> 'Warning: No product found for the Amazon Order Id: '.$data['amazon_order_id'].' !',
						];
				}

				return $orderItemList;
		}

		public function getProductOrVariationProductFromOCStore($productASIN, $account_id){
				$ordProdArray = array();
				$flag 				= false;
				//check main product map entry
				$mapProdEntry = $this->model_amazon_map_product->getProductMappedEntry(array('filter_amazon_product_id' => $productASIN, 'account_id' => $account_id));

				if(isset($mapProdEntry[0]['amazon_product_id']) && $mapProdEntry[0]['amazon_product_id'] == $productASIN){
						$flag 									= true;
						$ordProdArray 					= $this->makeRawOrderProductData();
						$getProductDetails 			= $this->model_catalog_product->getProduct($mapProdEntry[0]['oc_product_id']);
						$ordProdArray['product_id'] 			= $getProductDetails['product_id'];
						$ordProdArray['name'] 						= $getProductDetails['name'];
						$ordProdArray['model'] 						= $getProductDetails['model'];
						$ordProdArray['quantity'] 				= 0;
						$ordProdArray['price'] 						= 0;
						$ordProdArray['total'] 						= 0;
						$ordProdArray['tax'] 							= 0;
						$ordProdArray['shipping_price'] 	= 0;
				}

				if(!$flag){
						//check product variation map entry
						$mapProdVariationEntry = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $account_id, 'id_value' => $productASIN));
						if(isset($mapProdVariationEntry[0]['id_value']) && $mapProdVariationEntry[0]['id_value'] == $productASIN){
								$mapVarEntry = $mapProdVariationEntry[0];
								$option_data[] = array(
											'product_option_value_id' => $mapVarEntry['product_option_value_id'],
											'product_option_id' 			=> $mapVarEntry['product_option_id'],
											'name' 										=> 'Amazon Variations',
											'value' 									=> $mapVarEntry['value_name'],
											'type' 										=> 'select',
								);
								$ordProdArray 			= $this->makeRawOrderProductData();
								$getProductDetails 	= $this->model_catalog_product->getProduct($mapProdVariationEntry[0]['product_id']);
								$ordProdArray['product_id'] 			= $getProductDetails['product_id'];
								$ordProdArray['name'] 						= $getProductDetails['name'];
								$ordProdArray['model'] 						= $getProductDetails['model'];
								$ordProdArray['quantity'] 				= 0;
								$ordProdArray['price'] 						= 0;
								$ordProdArray['total'] 						= 0;
								$ordProdArray['tax'] 							= 0;
								$ordProdArray['shipping_price'] 	= 0;
								$ordProdArray['option'] 					= $option_data;
						}
				}
				return $ordProdArray;
		}

		public function makeRawOrderProductData(){
				return $orderedProductArray = array(
							'product_id'  		=> '',
							'name'  					=> '',
							'model'  					=> '',
							'quantity'  			=> 0,
							'price'  					=> 0,
							'total'  					=> 0,
							'tax'  						=> 0,
							'shipping_price'	=> 0,
							'option'					=> array(),
				);
		}

		public function createAmazonOrderedProductToOC($amazonASIN, $account_details){
				$resultProduct = array();
				$getFormattedData = $this->getProductRawData($amazonASIN, $account_details);
				if(!empty($getFormattedData) && isset($getFormattedData['item_id'])){
						$getProdResult = $this->model_amazon_map_product->createProductToOC($getFormattedData, $account_details);
						if(isset($getProdResult[0]['error'])){
								$resultProduct = [
																		'error_status' => true,
																		'message' 		 => $getProdResult[0]['message'],
																	];
						}
						if(isset($getProdResult[$amazonASIN['ASIN']])){
							$resultProduct = [
																	'success' 		=> true,
																	'message' 		=> 'Success: Amazon product data imported for Product Id: '.$amazonASIN['ASIN'].' !',
																];
						}
				}
				return $resultProduct;
		}

		public function getProductRawData($amzProData, $account_details){
			$completeWellFormedData = array();
				$languages  			= $this->model_localisation_language->getLanguages();
				$stores     			= $this->model_setting_store->getStores();
				array_push($stores,array('store_id' => 0,'name' => 'Default Store','url' => HTTP_CATALOG, 'ssh' => ''));

				// get config value
				$product_quantity = $this->config->get('wk_amazon_connector_default_quantity');
				$product_price    = '50';
				$product_weight   = $this->config->get('wk_amazon_connector_default_weight');
				$product_category = $this->config->get('wk_amazon_connector_default_category');

				// get default attribute group value
				if(isset($account_details['wk_amazon_connector_attribute_group']) && $account_details['wk_amazon_connector_attribute_group']){
						$product_attribute_group = $account_details['wk_amazon_connector_attribute_group'];
				}else{
						$product_attribute_group = 3;
				}
				$date_added 	= new \DateTime();
				$currentDate 	= $date_added->format('Y-m-d\TH:i:s');

				$item_name = $item_description = '';
				$item_name 				= str_replace('"', '', $amzProData['Title']);
				$item_description = $item_name;
				foreach ($languages as $key => $language) {
						$product_description[$language['language_id']] = array(
								'name'          => htmlspecialchars($item_name, ENT_QUOTES),
								'description'   => htmlspecialchars($item_name, ENT_QUOTES),
								'meta_title'    => htmlspecialchars($item_name, ENT_QUOTES)
							);
				}
				$product_sku  		= $amzProData['SellerSKU'];
				$itemId  					= $amzProData['ASIN'];
				if(isset($amzProData['QuantityOrdered']) && $amzProData['QuantityOrdered']){
						$product_quantity = $amzProData['QuantityOrdered'];
				}

				$compPriceData 		= $this->Amazonconnector->GetCompetitivePricingForASIN($amzProData['ASIN'], $account_details['id']);
				$asinPriceData  	= $this->Amazonconnector->GetMyPriceForASIN($amzProData['ASIN'], $account_details['id']);
				$product_price  	= $this->getAmazonProdPrice($asinPriceData, $compPriceData) * ($this->currency->getValue($this->config->get('config_currency')) / $account_details['wk_amazon_connector_currency_rate']);

				$wholeData = array(
						'ItemID'							=> $itemId,
						'account_id'					=> $account_details['id'],
				    'attribute_group'     => $product_attribute_group,
				    'model'            		=> $itemId,
						'sku'              		=> empty($product_sku) ? htmlspecialchars($item_name, ENT_QUOTES) : $product_sku,
						'quantity'         		=> $product_quantity,
						'stock_status_id'  		=> 7,
						'image'            		=> '',
						'amazon_image'      	=> '',
						'price'            		=> (float)$product_price,
						'tax_class_id'     		=> 0,
						'weight'           		=> $product_weight,
						'weight_class_id'  		=> 2,
						'subtract'         		=> 1,
						'minimum'          		=> 1,
						'sort_order'       		=> 1,
						'status'           		=> 1,
						'shipping'         		=> 1,
						'category_id'					=> $product_category,
						'product_description' => $product_description,
						'product_attribute'	  => array(),
						'product_option'			=> array(),
						'product_condition'	  => array(),
						'product_store'				=> $stores,
						'amazonProductType' 	=> 'ASIN',
						'amazonProductTypeValue' => $itemId,
					);

				  $completeWellFormedData = [
				      'item_type' 					=> 'product',
							'item_id'   					=> $amzProData['ASIN'],
				      'item_data' 					=> json_encode($wholeData),
				      'amazon_product_id' 	=> $amzProData['ASIN'],
				      'account_id' 					=> $account_details['id'],
							'added_date'					=> $currentDate,
				  ];
				return $completeWellFormedData;
		}

		/**
		 * get amazon product price
		 * @param  array $asinPriceData
		 * @param  array $compPriceData
		 * @return int
		 */
		public function getAmazonProdPrice($asinPriceData, $compPriceData)
		{
				$price = null;
				try {
						if (isset($compPriceData['GetCompetitivePricingForASINResult']['Product']['CompetitivePricing']['CompetitivePrices'])) {
								$competitivePrices = $compPriceData['GetCompetitivePricingForASINResult']['Product']['CompetitivePricing']['CompetitivePrices'];
								if (isset($competitivePrices['CompetitivePrice']) && isset($competitivePrices['CompetitivePrice']['Price']) && isset($competitivePrices['CompetitivePrice']['Price']['ListingPrice']) && isset($competitivePrices['CompetitivePrice']['Price']['ListingPrice']['Amount'])) {
										$price = $competitivePrices['CompetitivePrice']['Price']['ListingPrice']['Amount'];
								}
						} else if (empty($price)) {
								$getOfferPrice = $asinPriceData['GetMyPriceForASINResult']['Product'];
								if (isset($getOfferPrice['Offers']['Offer']) && isset($getOfferPrice['Offers']['Offer']['RegularPrice'])) {
										$price = $getOfferPrice['Offers']['Offer']['RegularPrice']['Amount'];
								}
						}

						if (empty($price)) {
								$price = '50';
						}
				} catch(\Exception $e) {
						$this->log->write('OrderedProductCreatingData getAmazonProdPrice : '.$e->getMessage());
				}
				return $price;
		}

		public function createOrderToOC($orderDetails, $account_details = array()){
			$result = array();
			$this->load->model('customer/customer');
			try{
						$tempData = json_decode($orderDetails['item_data'], true);

						if(!empty($tempData)){
								$checkOrderMapEntry = $this->getOcAmazonOrderMap(array('amazon_order_id' => $orderDetails['item_id'], 'account_id' => $account_details['id']));

								if(empty($checkOrderMapEntry)){
										if(isset($tempData['amazon_order_status']) && ($tempData['amazon_order_status'] == 'Shipped' || $tempData['amazon_order_status'] == 'Unshipped' || $tempData['amazon_order_status'] == 'PartiallyShipped')){
												if(isset($tempData['products'][0]['orderItems'])){
														// add customer to opencart store
														$getCustomer = $this->addOrderCustomer($tempData['customer_details'], $account_details['id']);
														if(!empty($getCustomer) && isset($getCustomer['customer_id']) && $getCustomer['customer_id']){
																$tempData['customer_id']				= $getCustomer['customer_id'];
																$tempData['customer_group_id'] 	= $getCustomer['customer_group_id'];
																$tempData['firstname'] 					= $getCustomer['firstname'];
																$tempData['lastname'] 					= $getCustomer['lastname'];
																$tempData['email'] 							= $getCustomer['email'];
																$tempData['telephone'] 					= $getCustomer['telephone'];
																$tempData['account_id'] 				= $account_details['id'];

																$getCustomerAddress = $this->model_customer_customer->getAddress($getCustomer['address_id']);
																if(!empty($getCustomerAddress) && isset($getCustomerAddress['address_id'])){
																		$tempData['shipping_firstname'] 		= $getCustomer['firstname'];
																		$tempData['shipping_lastname'] 			= $getCustomer['lastname'];
																		$tempData['shipping_address_1'] 		= $getCustomerAddress['address_1'];
																		$tempData['shipping_address_2'] 		= $getCustomerAddress['address_2'];
																		$tempData['shipping_city'] 					= $getCustomerAddress['city'];
																		$tempData['shipping_postcode']			= $getCustomerAddress['postcode'];
																		$tempData['shipping_zone']					= $getCustomerAddress['zone'];
																		$tempData['shipping_zone_id']				= $getCustomerAddress['zone_id'];
																		$tempData['shipping_country']				= $getCustomerAddress['country'];
																		$tempData['shipping_country_id']		= $getCustomerAddress['country_id'];
																		$tempData['shipping_address_format']= $getCustomerAddress['address_format'];
																}
																$saveResult = $this->__saveOrderData($tempData, $account_details['id']);

																if(isset($saveResult['status']) && $saveResult['status']){
																		$result[$orderDetails['item_id']] = array(
																											'error' 	=> false,
																											'message' => $saveResult['message']);
																		$this->__removeAmazonTempEntry(array('item_id' => $orderDetails['item_id'], 'account_id' => $account_details['id']));
																}
																if(isset($saveResult['status']) && !$saveResult['status']){
																		$result[$orderDetails['item_id']] = array(
																											'error' 	=> true,
																											'message' => $saveResult['message']);
																}
														}else{
																$result[$orderDetails['item_id']] = array(
																									'error' 	=> true,
																									'message' => sprintf($this->language->get('error_customer_notfound'), $orderDetails['item_id']));
														}
												}else{
														$result[$orderDetails['item_id']] = array(
																			'error' 	=> true,
																			'message' => 'Warning: Order product(s) missing for amazon order Id: '.$orderDetails['item_id'].'!');
														$this->__removeAmazonTempEntry(array('item_id' => $orderDetails['item_id'], 'account_id' => $account_details['id']));
												}
										}else{
												$result[$orderDetails['item_id']] = array(
																				'error' => true,
																				'message' => sprintf($this->language->get('error_order_status'), $orderDetails['item_id']));
										}
								}else{
										$result[$orderDetails['item_id']] = array(
														'error' 					=> true,
														'amazon_order_id' => $orderDetails['item_id'],
														'message' => 'Success: Amazon Order Id <b>'.$orderDetails['item_id'].'</b> already mapped with Opencart Order Id <b>'.$checkOrderMapEntry[0]['oc_order_id'].'</b> ');
										$this->__removeAmazonTempEntry(array('item_id' => $orderDetails['item_id'], 'account_id' => $account_details['id']));
								}
						}else{
								$result[$orderDetails['item_id']] = array(
													'error' 	=> true,
													'message' => 'Warning: Order Data not found for Amazon Order Id: '.$orderDetails['item_id'].'!');
								$this->__removeAmazonTempEntry(array('item_id' => $orderDetails['item_id'], 'account_id' => $account_details['id']));
						}
				} catch(\Exception $e) {
						$this->log->write('Create Order  createOrderToOC : '.$e->getMessage());
						$result[] = array(
												'error' 	=> true,
												'message' => 'Warning: '.$e->getMessage());
				}
				return $result;
		}

		public function __removeAmazonTempEntry($data = array()){
				if(isset($data['item_id']) && isset($data['account_id'])){
						$this->db->query("DELETE FROM ".DB_PREFIX."amazon_tempdata WHERE item_id = '".$this->db->escape($data['item_id'])."' AND account_id = '".(int)$data['account_id']."' AND item_type = 'order' ");
				}
		}

		public function deleteMapOrders($map_id, $account_id){
				$result = $order_data = array();
				$this->load->model('sale/order');
				$getProductEntry = $this->getOcAmazonOrderMap(array('filter_map_id' => $map_id, 'account_id' => $account_id));

				if(!empty($getProductEntry) && isset($getProductEntry[0]['map_id']) && $getProductEntry[0]['map_id'] == $map_id){
						$order_data = $getProductEntry[0];
						$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_order_map WHERE oc_order_id = '" . (int)$order_data['oc_order_id'] . "'");
						$this->model_sale_order->deleteOrder($order_data['oc_order_id']);
						$result = array('status' => true, 'message' => sprintf($this->language->get('text_success_delete'), $order_data['amazon_order_id']). $order_data['oc_order_id']);
				}else{
					$result = array('status' => false, 'message' => sprintf($this->language->get('error_order_delete'), $map_id));
				}
				return $result;
		}

		public function addOrderCustomer($data = array(), $account_id)
		{
				$getCustomerData = array();
				$getCustomerData = $this->model_customer_customer->getCustomerByEmail($data['BuyerEmail']);

				if(empty($getCustomerData)){
					$cust_group = $this->config->get('config_customer_group_display');
						if(!empty($cust_group)){
								$getCustomerGroup = $cust_group;
								if(isset($getCustomerGroup[0]) && $getCustomerGroup[0]){
										$data['customer_group_id'] = $getCustomerGroup[0];
								}else{
									$data['customer_group_id'] = 1;
								}
						}else{
							$data['customer_group_id'] = 1;
						}
						if(isset($data['BuyerName']) && $data['BuyerName']){
								$getCustomerName 	 = explode(' ', $data['BuyerName']);
								$data['firstname'] = $getCustomerName[0];
								$data['lastname']  = isset($getCustomerName[1]) ? $getCustomerName[1] : '';
						}else{
								$data['firstname'] = $data['BuyerEmail'];
								$data['lastname']  = $data['BuyerEmail'];
						}
						$data['telephone'] = '1234567890';

						$this->db->query("INSERT INTO ".DB_PREFIX."customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', language_id = '".(int)$this->config->get('config_language_id')."', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '".$this->db->escape($data['BuyerEmail'])."', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', newsletter = '0', salt = '" . $this->db->escape($salt = token(9)) . "', password = '".$this->db->escape(sha1($salt . sha1($salt . sha1($data['lastname'].'_'.$data['firstname']))))."', status = '1', approved = '1', safe = '0', date_added = NOW()");

						$customer_id = $this->db->getLastId();

						$city_name = $country_name = '';
						if (isset($data['ShippingAddress'])) {
								if(isset($data['ShippingAddress']['CountryCode'])){
									$country = $this->db->query("SELECT * FROM ".DB_PREFIX."country WHERE iso_code_2 = '".$this->db->escape($data['ShippingAddress']['CountryCode'])."' ")->row;

										if(isset($country['country_id'])){
											$country_id 	= $country['country_id'];
											$country_name = $country['name'];
										}else{
											$country_id = 0;
										}
									$city_name = $data['ShippingAddress']['City'];
								}else{
										$country_id = 0;
								}
								$address1 = $address2 = '';
								if(isset($data['ShippingAddress']['AddressLine1'])){
									$address1 = $data['ShippingAddress']['AddressLine1'];
								}
								if(isset($data['ShippingAddress']['AddressLine2'])){
									$address2 = $data['ShippingAddress']['AddressLine2'];
								}
								$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '', address_1 = '" . $this->db->escape($address1) . "', address_2 = '" . $this->db->escape($address2) . "', city = '" . $this->db->escape($data['ShippingAddress']['City']) . "', postcode = '" . $this->db->escape($data['ShippingAddress']['PostalCode']) . "', country_id = '" . (int)$country_id . "', zone_id = '0' ");

								$address_id = $this->db->getLastId();

								$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");

								$getCustomerData = $this->model_customer_customer->getCustomerByEmail($data['BuyerEmail']);
						}
						if($customer_id){
								$this->db->query("INSERT INTO ".DB_PREFIX."amazon_customer_map SET `oc_customer_id` = '".(int)$customer_id."', `customer_group_id` = '".(int)$data['customer_group_id']."', `name` = '".$this->db->escape($data['BuyerName'])."', `email` = '".$this->db->escape($data['BuyerEmail'])."', `city` = '".$this->db->escape($city_name)."', `country` = '".$this->db->escape($country_name)."', `account_id` = '".(int)$account_id."' ");
						}
				}
				return $getCustomerData;
		}


		public function createOrders($order, $orderProducts, $customerDetails, $orderReport)
		{
				$sync_result = array();
				if(!empty($order) && isset($order['AmazonOrderId']) && $order['AmazonOrderId']){
						$this->load->model('catalog/product');
						$this->load->model('amazon_map/product');
						$customerDetails['amazon_order_id'] = $order['AmazonOrderId'];
						$getOrderData = $this->createOrderProduct($orderProducts, $customerDetails, $orderReport);

						if(!empty($getOrderData) && isset($getOrderData[$customerDetails['amazon_order_id']]['status']) && $getOrderData[$customerDetails['amazon_order_id']]['status']){
								$sync_result = $this->__saveOrderData($order, $getOrderData[$customerDetails['amazon_order_id']]['order_array'], $customerDetails['account_id']);
						}else if(isset($getOrderData[$customerDetails['amazon_order_id']]['status']) && !$getOrderData[$customerDetails['amazon_order_id']]['status']){
								$sync_result = $getOrderData[$customerDetails['amazon_order_id']];
						}else{
								$sync_result = array(
																	'status'  => false,
																	'message' => 'Amazon order id : <b> '.$order['AmazonOrderId']." </b> failed to mapped with opencart order!",
																);
						}
				}
				return $sync_result;
		}

		public function createOrderProduct($Products, $customerDetails, $orderReport)
		{
				$product_data = $order_data = $orderResponse = array();
				$this->load->model('catalog/attribute');
				$getAccountEntry = $this->Amazonconnector->getAccountDetails(array('account_id' => $customerDetails['account_id']));

				if(!empty($Products) && $getAccountEntry){
						foreach ($Products as $key => $product) {
								$orderedProductId = false;
								$option_data = array();
								$product = (array)$product;

								if(isset($product['ASIN']) && $product['ASIN']){
									// check product is already imported to opencart store or note
									$getproductEntry = $this->model_amazon_map_product->getProductMappedEntry(array('filter_amazon_product_id' => $product['ASIN'], 'account_id' => $customerDetails['account_id']));

									if(empty($getproductEntry)){
											// check variation entry exists for this ASIN
											$getVaristionASINEntry = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $customerDetails['account_id'], 'id_value' => $product['ASIN']));

											if(empty($getVaristionASINEntry) && isset($orderReport['prd_reportId']) && isset($orderReport['qty_reportId'])){
													// If variation entry not exists, then create product
													$result = $this->model_amazon_map_product->getAmazonProductByAsin(array('product_asin' => $product['ASIN'], 'account_details' => $getAccountEntry));
													$product_result 		= array_values($result);
													$orderProductResult = array_column($product_result,'status');

													if(isset($orderProductResult[0]) && $orderProductResult[0] && !empty($result)){
															if(in_array($product['ASIN'], array_column($product_result,'asin'))){
																$getASINMatch = array_column($product_result, 'asin');
																$getINDEX 		= array_search($product['ASIN'], $getASINMatch);

																		// check product is already imported to opencart store or note
																		$getproductEntryAgain = $this->model_amazon_map_product->getProductMappedEntry(array('filter_amazon_product_id' => $product_result[$getINDEX]['asin'], 'account_id' => $customerDetails['account_id']));

																			if(empty($getproductEntryAgain)){
																					$getVaristionASINEntryAgain = $this->Amazonconnector->filterVariationASINEntry(array('account_id' => $customerDetails['account_id'], 'id_value' => $product_result[$getINDEX]['asin']));

																					if(empty($getVaristionASINEntryAgain) && $orderReport['prd_reportId'] && $orderReport['qty_reportId']){
																						$product_id = false;
																						foreach ($getVaristionASINEntryAgain as $key => $opt_product) {
																								if(isset($opt_product['product_option_value_id']) && $opt_product['product_option_value_id'] && isset($opt_product['main_product_type_value']) && $opt_product['main_product_type_value']){
																											$option_data[] = array(
																																			'product_option_value_id' => $opt_product['product_option_value_id'],
																																			'product_option_id' 			=> $opt_product['product_option_id'],
																																			'name' 										=> 'Amazon Variations',
																																			'value' 									=> $opt_product['value_name'],
																																			'type' 										=> 'select',
																																		);
																											$product_id = $opt_product['product_id'];
																								}
																						}
                                            $getOcProductDetails = $this->model_catalog_product->getProduct($product_id);
                                            if(!empty($getOcProductDetails)){
                                                $order_quantity = $product['QuantityOrdered'];

																									$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$product_data[] = array(
																																			'product_id'=> $getOcProductDetails['product_id'],
																																			'name'			=> $getOcProductDetails['name'],
																																			'model'			=> $getOcProductDetails['model'],
																																			'quantity'	=> $order_quantity,
																																			'price'			=> $unit_price,
																																			'total'			=> $unit_price * $order_quantity,
																																			'tax'				=> $tax,
																																			'shipping_price' => $shipping_price,
																																			'option'		=> $option_data,
																																		);
																							}else{
																										$orderResponse[$customerDetails['amazon_order_id']] = array(
																																				'status' => false,
																																				'message'=> 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																																			);
																									return $orderResponse;
																							}
																					}
																			}else{
																					if(isset($getproductEntryAgain[0]['oc_product_id']) && $getproductEntryAgain[0]['oc_product_id']){
																							$getOcProductDetailsAgain = $this->model_catalog_product->getProduct($getproductEntryAgain[0]['oc_product_id']);
																							if($getOcProductDetailsAgain){

																									$order_quantity = $product['QuantityOrdered'];

																									$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

																									$product_data[] = array(
																																				'product_id'=> $getOcProductDetailsAgain['product_id'],
																																				'name'			=> $getOcProductDetailsAgain['name'],
																																				'model'			=> $getOcProductDetailsAgain['model'],
																																				'quantity'	=> $order_quantity,
																																				'price'			=> $unit_price,
																																				'total'			=> $unit_price * $order_quantity,
																																				'tax'				=> $tax,
																																				'shipping_price' => $shipping_price,
																																				'option'		=> $option_data,
																																			);
																							}
																					}else{
																							$orderResponse[$customerDetails['amazon_order_id']] = array(
																																			'status' 	=> false,
																																			'message' => 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																																		);
																							return $orderResponse;
																					}
																			}
															}else{
																	foreach ($result as $key => $value) {
																			$orderResponse[$customerDetails['amazon_order_id']] = array(
																														'status' 				=> false,
																														'product_asin' 	=> $value['asin'],
																														'message' 			=> $value['message']. ' for Amazon order-Id: '.$customerDetails['amazon_order_id'],
																													);
																	}
																	return $orderResponse;
															}
													}else{
														foreach ($result as $key => $value) {
																$orderResponse[$customerDetails['amazon_order_id']] = array(
																											'status' 				=> false,
																											'product_asin' 	=> $value['asin'],
																											'message' 			=> $value['message']. ' for Amazon order-Id: '.$customerDetails['amazon_order_id'],
																										);
														}
														return $orderResponse;
													}
											}else{
													$product_id = false;
													foreach ($getVaristionASINEntry as $key => $opt_product) {
															if(isset($opt_product['product_option_value_id']) && $opt_product['product_option_value_id'] && isset($opt_product['main_product_type_value']) && $opt_product['main_product_type_value']){
																		$option_data[] = array(
																										'product_option_value_id' => $opt_product['product_option_value_id'],
																										'product_option_id' 			=> $opt_product['product_option_id'],
																										'name' 										=> 'Amazon Variations',
																										'value' 									=> $opt_product['value_name'],
																										'type' 										=> 'select',
																									);
																		$product_id = $opt_product['product_id'];
															}
													}

													$getOcProductDetails = $this->model_catalog_product->getProduct($product_id);
													if(!empty($getOcProductDetails)){
															$order_quantity = $product['QuantityOrdered'];

															$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$product_data[] = array(
																									'product_id'=> $getOcProductDetails['product_id'],
																									'name'			=> $getOcProductDetails['name'],
																									'model'			=> $getOcProductDetails['model'],
																									'quantity'	=> $order_quantity,
																									'price'			=> $unit_price,
																									'total'			=> $unit_price * $order_quantity,
																									'tax'				=> $tax,
																									'shipping_price' => $shipping_price,
																									'option'		=> $option_data,
																								);
													}else{
																$orderResponse[$customerDetails['amazon_order_id']] = array(
																										'status' 				=> false,
																										'product_asin' 	=> $product['ASIN'],
																										'message' 			=> 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																									);
															return $orderResponse;
													}
											}
										}else{
												if(isset($getproductEntry[0]['oc_product_id']) && $getproductEntry[0]['oc_product_id']){
													$getOcProductDetails = $this->model_catalog_product->getProduct($getproductEntry[0]['oc_product_id']);

													if($getOcProductDetails){
															$order_quantity = $product['QuantityOrdered'];

															$unit_price 		= $product['ItemPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$shipping_price = $product['ShippingPrice']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$tax 						= $product['ItemTax']['Amount'] * ($this->currency->getValue($this->config->get('config_currency')) / $getAccountEntry['wk_amazon_connector_currency_rate']);

															$product_data[] = array(
																										'product_id'	=> $getOcProductDetails['product_id'],
																										'name'				=> $getOcProductDetails['name'],
																										'model'				=> $getOcProductDetails['model'],
																										'quantity'		=> $order_quantity,
																										'price'				=> $unit_price,
																										'total'				=> $unit_price * $order_quantity,
																										'tax'					=> $tax,
																										'shipping_price' => $shipping_price,
																										'option'			=> $option_data,
																									);
													}
											}else{
													$orderResponse[$customerDetails['amazon_order_id']] = array(
																								'status' 				=> false,
																								'product_asin' 	=> $product['ASIN'],
																								'message' 			=> 'Warning: Amazon Order\'s product ('.$product['ASIN'].') import error for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																							);
													return $orderResponse;
											}
									}// else of product entry check in opencart
								}else{
											$orderResponse[$customerDetails['amazon_order_id']] = array(
																					'status' => false,
																					'message' => 'Warning: Amazon Order\'s product ASIN number not found for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																				);
											return $orderResponse;
								}// product ASIN check
						} // product foreach loop end

						$getCustomerAddress = $this->model_customer_customer->getAddress($customerDetails['address_id']);
						$getCurrencyId = array();
						if($this->config->get('config_currency')){
							$getCurrencyId = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE code = '" . $this->db->escape($this->config->get('config_currency')) . "'")->row;
						}
						if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
							$forwarded_ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
						} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
							$forwarded_ip = $this->request->server['HTTP_CLIENT_IP'];
						} else {
							$forwarded_ip = '';
						}

						if (isset($this->request->server['HTTP_USER_AGENT'])) {
							$user_agent = $this->request->server['HTTP_USER_AGENT'];
						} else {
							$user_agent = '';
						}
						$order_data = [
									'status'					=> 1,
									'invoice_no'			=> 0,
									'invoice_prefix'	=> $this->config->get('config_invoice_prefix'),
									'store_id'				=> $getAccountEntry['wk_amazon_connector_default_store'],
									'store_name'			=> $this->config->get('config_name'),
									'store_url'				=> $this->config->get('config_store_id') ? $this->config->get('config_url') : ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER),
									'customer_id'			=> $customerDetails['customer_id'],
									'customer_group_id'=> $customerDetails['customer_group_id'],
									'firstname' 			=> $customerDetails['firstname'],
									'lastname' 				=> $customerDetails['lastname'],
									'email' 					=> $customerDetails['email'],
									'telephone' 			=> $customerDetails['telephone'],
									'fax' 						=> '',
									'account_id' 			=> $customerDetails['account_id'],
									'language_id' 		=> $this->config->get('config_language_id'),
									'currency_id' 		=> $getCurrencyId['currency_id'],
									'currency_code' 	=> $getCurrencyId['code'],
									'currency_value'	=> $getCurrencyId['value'],
									'order_status_id' => $this->config->get('wk_amazon_connector_order_status'),
													'shipping_firstname' 	=> $customerDetails['firstname'],
													'shipping_lastname' 	=> $customerDetails['lastname'],
													'shipping_address_1' 	=> $getCustomerAddress['address_1'],
													'shipping_address_2' 	=> $getCustomerAddress['address_2'],
													'shipping_city' 			=> $getCustomerAddress['city'],
													'shipping_postcode'		=> $getCustomerAddress['postcode'],
													'shipping_zone'				=> $getCustomerAddress['zone'],
													'shipping_zone_id'		=> $getCustomerAddress['zone_id'],
													'shipping_country'		=> $getCustomerAddress['country'],
													'shipping_country_id'	=> $getCustomerAddress['country_id'],
													'shipping_address_format'=> $getCustomerAddress['address_format'],
									'shipping_method'	=> '',
									'shipping_code' 	=> '',
									'payment_method' 	=> '',
									'payment_code' 	 	=> '',
									'products' 				=> $product_data,
									'affiliate_id' 		=> 0,
									'commission' 			=> 0,
									'marketing_id' 		=> 0,
									'tracking' 				=> '',
									'user_agent'  		=> $user_agent,
									'forwarded_ip'  	=> $forwarded_ip,
									'ip' 							=> $this->request->server['REMOTE_ADDR'],
						];

						 $orderResponse[$customerDetails['amazon_order_id']] = array(
																			'status' 				=> true,
																			'order_array' 	=> $order_data,
																			'message' 			=> 'success: Amazon Order Id : '.$customerDetails['amazon_order_id'].'!'
																		);
						return $orderResponse;
				}else{
						$orderResponse[$customerDetails['amazon_order_id']] = array(
																		'status' 				=> false,
																		'message' 			=> 'Warning: There is no product found for Amazon Order-Id : '.$customerDetails['amazon_order_id'].'!'
																	);
					 return $orderResponse;
			 }// product array empty check
		}// function end

		public function __saveOrderData($orderArray, $account_id)
		{

				  $sync_result = array();
					if ($orderArray['payment_method'] == 'Other') {
							//for all countries
							$payment_method = 'Other';
					} elseif ($orderArray['payment_method'] == 'COD') {
							//for japan only
							$payment_method = 'cod';
					} elseif ($orderArray['payment_method'] == 'CVS') {
							//for japan only
							$payment_method = 'cvs';
					}
					$orderArray['payment_method'] = $payment_method;
					$orderArray['payment_code'] 	= $payment_method;
					$orderArray['shipping_method']= 'Amazon Shipping';
					$orderArray['shipping_code'] 	= 'wk_amazon';

				$getOrderId = $this->addOrder($orderArray);
		    $this->addOrderHistory($getOrderId, $orderArray['order_status_id']);

		    if($getOrderId){
		    		$this->db->query("INSERT INTO ".DB_PREFIX."amazon_order_map SET `oc_order_id` = '".(int)$getOrderId."', `amazon_order_id` = '".$this->db->escape($orderArray['amazon_order_id'])."', `amazon_order_status` = '".$this->db->escape($orderArray['amazon_order_status'])."', `sync_date` = NOW(), `account_id` = '".(int)$orderArray['account_id']."' ");

		    		$map_id = $this->db->getLastId();

			    	if($map_id){
			    		$sync_result = array(
																	'status'  => true,
																	'message' => 'Success: Amazon order id : <b> '.$orderArray['amazon_order_id']." </b> has been synchronized with opencart's order id : <b> '" .$getOrderId. "' </b>.",
																);
			    	}else{
							$sync_result = array(
																	'status'  => false,
																	'message' => 'Warning: Amazon order id : <b> '.$orderArray['amazon_order_id'].' </b> failed to mapped with opencart order!',
																);
						}
		    }else{
						 $sync_result = array(
															'status'  => false,
															'message' => 'Warning: Amazon order id : <b> '.$orderArray['amazon_order_id'].' </b> failed to mapped with opencart order!',
														);
				}
		    return $sync_result;
		}


		public function addOrder($data)
		{
				$order_total = $order_sub_total = $order_total_shipping = 0;
				if(!empty($data['products'])){
						foreach ($data['products'] as $key => $product) {
								if($key === 'orderItems'){
										$order_sub_total 				+= $product['total'];
										$order_total_shipping 	+= $product['shipping_price'];
								}else{
										if(isset($product['orderItems']['product_id'])){
												$order_sub_total 				+= $product['orderItems']['total'];
												$order_total_shipping 	+= $product['orderItems']['shipping_price'];
										}
								}
						}
						$order_total = $order_sub_total + $order_total_shipping;
				}else{
						return false;
				}

				$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->db->escape($data['store_name']) . "', store_url = '" . $this->db->escape($data['store_url']) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : '') . "', payment_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', payment_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', payment_company = '', payment_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', payment_city = '" . $this->db->escape($data['shipping_city']) . "', payment_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', payment_country = '" . $this->db->escape($data['shipping_country']) . "', payment_country_id = '" . (int)$data['shipping_country_id'] . "', payment_zone = '', payment_zone_id = '0', payment_custom_field = '" . $this->db->escape(isset($data['payment_custom_field']) ? json_encode($data['payment_custom_field']) : '') . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $this->db->escape($data['payment_code']) . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '', shipping_zone_id = '0', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_custom_field = '" . $this->db->escape(isset($data['shipping_custom_field']) ? json_encode($data['shipping_custom_field']) : '') . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', total = '" . (float)$order_total . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', marketing_id = '" . (int)$data['marketing_id'] . "', tracking = '" . $this->db->escape($data['tracking']) . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip = '" . $this->db->escape($data['ip']) . "', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . "', user_agent = '" . $this->db->escape($data['user_agent']) . "', date_added = NOW(), date_modified = NOW()");

				$order_id = $this->db->getLastId();

				// Products
				if (isset($data['products'])) {
						foreach ($data['products'] as $product) {
								if(isset($product['orderItems'])){
										$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$product['orderItems']['product_id'] . "', name = '" . $this->db->escape($product['orderItems']['name']) . "', model = '" . $this->db->escape($product['orderItems']['model']) . "', quantity = '" . (int)$product['orderItems']['quantity'] . "', price = '" . (float)$product['orderItems']['price'] . "', total = '" . (float)$product['orderItems']['total'] . "', tax = '" . (float)$product['orderItems']['tax'] . "', reward = '0'");

										$order_product_id = $this->db->getLastId();

										if(isset($product['orderItems']['option']) && !empty($product['orderItems']['option'])){
												foreach ($product['orderItems']['option'] as $option) {
														$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . $this->db->escape($option['name']) . "', `value` = '" . $this->db->escape($option['value']) . "', `type` = '" . $this->db->escape($option['type']) . "'");
												}
										}
								}
						}
				}

				$totals 	=	[array(
									'code'       => 'sub_total',
									'title'      => 'Sub-Total',
									'value'      => (float)$order_sub_total,
									'sort_order' => $this->config->get('sub_total_sort_order')
								),
								array(
									'code'       => 'shipping',
									'title'      => $data['shipping_method'],
									'value'      => (float)$order_total_shipping,
									'sort_order' => $this->config->get('shipping_sort_order')
								),
								array(
									'code'       => 'total',
									'title'      => 'Total',
									'value'      => (float)$order_total,
									'sort_order' => $this->config->get('total_sort_order')
								)];

				// Totals
				if (isset($totals)) {
					foreach ($totals as $total) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($total['code']) . "', title = '" . $this->db->escape($total['title']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
					}
				}

				return $order_id;
		}

		public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false){
			// Stock subtraction
			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $order_product) {
				$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

				$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");

				foreach ($order_option_query->rows as $option) {
					$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}

			// Update the DB with the new statuses
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
		}


		/************************************** Customer Section ******************************/

		public function getAmazonCustomerList($data = array()){
				$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS customer_name FROM ".DB_PREFIX."amazon_customer_map acm LEFT JOIN ".DB_PREFIX."customer c ON((acm.oc_customer_id = c.customer_id) AND (acm.customer_group_id = c.customer_group_id))  WHERE c.status = '1' AND c.approved = '1' ";

				$implode = array();

				if (!empty($data['filter_name'])) {
					$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}

				if (!empty($data['filter_email'])) {
					$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
				}

				if (!empty($data['filter_customer_group_id'])) {
					$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
				}

				if (!empty($data['filter_account_id'])) {
					$implode[] = "acm.account_id = '" . (int)$data['filter_account_id'] . "'";
				}

				if ($implode) {
					$sql .= " AND " . implode(" AND ", $implode);
				}

				$sort_data = array(
					'customer_name',
					'c.email',
					'acm.city',
					'acm.country',
				);

				if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
					$sql .= " ORDER BY " . $data['sort'];
				} else {
					$sql .= " ORDER BY acm.id";
				}

				if (isset($data['order']) && ($data['order'] == 'DESC')) {
					$sql .= " DESC";
				} else {
					$sql .= " ASC";
				}

				if (isset($data['start']) || isset($data['limit'])) {
						if ($data['start'] < 0) {
							$data['start'] = 0;
						}

						if ($data['limit'] < 1) {
							$data['limit'] = 20;
						}

						$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
				}
				return $this->db->query($sql)->rows;
	  }

	  public function getAllAmazonCustomerList($start = 0, $length = 10, $filterData = null, $orderColumn = "id")
	  {
		  # code...
		  $sql_str = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS customer_name FROM ".DB_PREFIX."amazon_customer_map acm LEFT JOIN ".DB_PREFIX."customer c ON((acm.oc_customer_id = c.customer_id) AND (acm.customer_group_id = c.customer_group_id))  WHERE c.status = '1' AND c.approved = '1' ";
		
		  $total = $this->db->query($sql_str)->num_rows;
		  $where = "";
		  if (!empty($filterData['search'])) {
		  $where .= "(acm.name like '%" . $this->db->escape($filterData['search']) . "%'
					  OR acm.email like '%".$this->db->escape($filterData['search'])."%'
					  OR acm.city like '%".$this->db->escape($filterData['search'])."%'
					  OR acm.country like '%".$this->db->escape($filterData['search'])."%') ";
		  $sql_str .= " AND " . $where;
		  }
  
		  $totalFiltered = $this->db->query($sql_str)->num_rows;
		  $sql_str .= " ORDER by {$orderColumn} DESC";
  
		  if ($length != -1) {
		  $sql_str .= " LIMIT " . $start . ", " . $length;
		  }
  
		  $results=$this->db->query($sql_str)->rows;
  
		  $data = array(
			  'data' => $results,
			  'total' => $total,
			  'totalFiltered' => $totalFiltered
		  );
  
		  return $data;
	  }
		/**
		 * [getTotalAmazonCustomerList to get total number of mapped Amazon orders with opencart orders]
		 * @param  array $data [description]
		 * @return [type]              [description]
		 */
		public function getTotalAmazonCustomerList($data = array()){
				$sql = "SELECT DISTINCT acm.* FROM ".DB_PREFIX."amazon_customer_map acm LEFT JOIN ".DB_PREFIX."customer c ON((acm.oc_customer_id = c.customer_id) AND (acm.customer_group_id = c.customer_group_id))  WHERE c.status = '1' AND c.approved = '1' ";

				$implode = array();

				if (!empty($data['filter_name'])) {
					$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}

				if (!empty($data['filter_email'])) {
					$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
				}

				if (!empty($data['filter_customer_group_id'])) {
					$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
				}

				if (!empty($data['filter_account_id'])) {
					$implode[] = "acm.account_id = '" . (int)$data['filter_account_id'] . "'";
				}

				if ($implode) {
					$sql .= " AND " . implode(" AND ", $implode);
				}

				$result = $this->db->query($sql)->rows;

				return count($result);
	  }
}
