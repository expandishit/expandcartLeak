<?php
/**
*
*/
class productExportCron {

	public $language_id = false;

	function __construct($registry)	{
		$this->registry 				= $registry;
    $this->config   				= $registry->get('config');
    $this->db       				= $registry->get('db');
		$this->currency       	= $registry->get('currency');
		$this->Amazonconnector 	= $registry->get('Amazonconnector');
	}

	public function index(){
			if($this->config->get('wk_amazon_connector_status')){
				$getLanguage 				= $this->getLanguageByCode($this->config->get('config_admin_language'));
				if(isset($getLanguage['language_id'])){
						$this->language_id 	= $getLanguage['language_id'];
						$this->config->set('config_language_id', $this->language_id);
				}else{
						$this->language_id 	= 0;
				}

				//Case Export
						// Export Opencart Un-mapped products to Amazon Store
						if($this->config->get('wk_amazon_connector_cron_create_product')){
								$accountDetails = $this->Amazonconnector->getAccountDetails(array());
								if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){
										//get total number of unmapped opencart product
										$getTotalUnMappedProduct = $this->getTotalUnMappedOcProducts();
										if($getTotalUnMappedProduct){
												$totalLoopCount = ceil($getTotalUnMappedProduct/50);

												for ($i=0; $i < $totalLoopCount; $i++) {
														$getOcProducts = $this->getUnMappedOcProducts(array('start' => ($i * 50), 'limit' => 50));
														if(!empty($getOcProducts)){

																// $getProductIds 			= implode(",", array_column($getOcProducts, 'product_id'));
																if (! function_exists('array_column')) {
                                   $getProductIds 			= implode(",", $this->_arrayColumn($getOcProducts, 'product_id'));
                                } else {
																	 $getProductIds 			= implode(",", array_column($getOcProducts, 'product_id'));
																}
																$process_products 	= $this->Amazonconnector->getOcProductWithCombination(array('product_ids' => $getProductIds));
																if (isset($process_products) && !empty($process_products)) {
																		$this->startExportProcess($process_products, $accountDetails);
																}
														}
												}
										}
								}
						}

					//Case Update
						// Update Mapped Product to according to their Sync Source
						if($this->config->get('wk_amazon_connector_cron_update_product')){
							//get all the mapped products
							$getTotalMappedProduct = $this->getTotalProductMappedEntry(array());

							if($getTotalMappedProduct){
									$totalLoopCount = ceil($getTotalMappedProduct/50);
									for ($i=0; $i < $totalLoopCount; $i++) {

											$getMapEntry = $this->getProductMappedEntry(array('start' => ($i * 50), 'limit' => 50));

											$UpdateQuantityArray = $UpdatePriceArray = $UpdateImageArray = array();
											$product_array = array();
											foreach ($getMapEntry as $key => $product_details) {

													if(isset($product_details['account_id'])){
															$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $product_details['account_id']));

															if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){
																	if ((isset($product_details['main_product_type']) && $product_details['main_product_type']) && (isset($product_details['main_product_type_value']) && $product_details['main_product_type_value'])) {

																			if(isset($product_details['image']) && file_exists(DIR_IMAGE.$product_details['image'])){
																				$image = HTTP_CATALOG.'image/'.$product_details['image'];
																			}else{
																				$image = HTTP_CATALOG.'image/placeholder.png';
																			}

																			$getCombinations = $this->Amazonconnector->_getProductVariation($product_details['oc_product_id'], $type = 'amazon_product_variation_value');

																			if(!empty($getCombinations)){
																					foreach ($getCombinations as $option_id => $combination_array) {
																							 $total_combinations = count($combination_array);
																							 foreach ($combination_array['option_value'] as $key => $combination_value) {
																									 $product_data = array();

																										if(isset($combination_value['price_prefix']) && $combination_value['price_prefix'] == '+'){
																												$product_data['price'] = (float)$product_details['price'] + (float)$combination_value['price'];
																										}else{
																												$product_data['price'] = (float)$product_details['price'] - (float)$combination_value['price'];
																										}

																										if(isset($combination_value['quantity']) && $combination_value['quantity']){
																												$product_data['quantity'] = $combination_value['quantity'];
																										}else{
																												$product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
																										}
																										$product_data['sku'] 				 = $combination_value['sku'];
																										$product_data['image'] 			 = $image;

																										//Update qty of amazon product
																										$UpdateQuantityArray[$product_data['sku']] = array(
																																							'sku' => $product_data['sku'],
																																							'qty' => $product_data['quantity'],
																																						);

																										//Update price of amazon product
																										$UpdatePriceArray[$product_data['sku']] = array(
																																					 'sku' 							=> $product_data['sku'],
																																					 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																																					 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																																				 );
																										//Update image of amazon product
	 						 				 															 $UpdateImageArray[] = array(
	 						 																														 'type' 						=> 'Main',
	 						 				 																										 'sku' 							=> $product_data['sku'],
	 						 				 																										 'image_url' 				=> $product_data['image'],
	 						 				 																									 );

	 						 																			 $productImages = $this->getProductRelatedImages($product_details['oc_product_id']);
	 						 																			 if(!empty($productImages)){
	 						 																						for ($i=0; $i < 8; $i++) {
	 						 																								if(isset($productImages[$i]['image']) && file_exists(DIR_IMAGE.$productImages[$i]['image'])){
	 						 																										$UpdateImageArray[] = array(
																																				'type' 			=> 'PT'.($i+1),
																																				'sku' 			=> $product_data['sku'],
																																				'image_url' => HTTP_CATALOG.'image/'.$productImages[$i]['image']
	 						 																										);
	 						 																								}
	 						 																						}
	 						 																			 }
																								}
																					 }
																					 //final data of submit feed data
							 														$product_array[] = array('product_id'  => $product_details['oc_product_id'],
							 																											'account_id' => $accountDetails['id'],
							 																											'name' 			 => $product_details['product_name'],
							 																											'id_value'	 => $product_details['main_product_type_value'],
							 																								);

																			}else{ // if not combination
																					$product_data = array();
																					$product_data['product_id'] 		= $product_details['oc_product_id'];
																					$product_data['account_id'] 		= $accountDetails['id'];
																					$product_data['name'] 					= $product_details['product_name'];
																					$product_data['image'] 					= $image;
																					$product_data['id_value'] 			= $product_details['main_product_type_value'];
																					$product_data['price'] 					= (float)$product_details['price'];
																					$product_data['quantity'] 			= (!empty($product_details['quantity']) ? $product_details['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
																					$product_data['sku'] 						= (!empty($product_details['amazon_product_sku']) ? $product_details['amazon_product_sku'] : $product_details['sku']);

																					//Update qty of amazon product
																					$UpdateQuantityArray[$product_data['sku']] = array(
																																		'sku' => $product_data['sku'],
																																		'qty' => $product_data['quantity'],
																																	);

																					//Update price of amazon product
																					$UpdatePriceArray[$product_data['sku']] = array(
																																 'sku' 							=> $product_data['sku'],
																																 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																																 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																															 );

																				 //Update image of amazon product
						 															$UpdateImageArray[] = array(
																																 'type' 						=> 'Main',
						 																										 'sku' 							=> $product_data['sku'],
						 																										 'image_url' 				=> $product_data['image'],
						 																									 );

																					$productImages = $this->getProductRelatedImages($product_details['oc_product_id']);
																					if(!empty($productImages)){
																							for ($i=0; $i < 8; $i++) {
																									if(isset($productImages[$i]['image']) && file_exists(DIR_IMAGE.$productImages[$i]['image'])){
																											$UpdateImageArray[] = array(
																																'type' 			=> 'PT'.($i+1),
																																'sku' 			=> $product_data['sku'],
																																'image_url' => HTTP_CATALOG.'image/'.$productImages[$i]['image']
																											);
																									}
																							}
																					}
																				  $product_array[] = $product_data;
																			}

																	}
															}
													}
											}

											if(!empty($product_array)){
													$this->Amazonconnector->product['ActionType']  = 'UpdateQuantity';
													$this->Amazonconnector->product['ProductData'] = $UpdateQuantityArray;

													$product_updated = $this->Amazonconnector->submitFeed('_POST_INVENTORY_AVAILABILITY_DATA_', $accountDetails['id']);

													if (isset($product_updated['success']) && $product_updated['success']) {
															$this->Amazonconnector->product['ActionType']  = 'UpdatePrice';
															$this->Amazonconnector->product['ProductData'] = $UpdatePriceArray;
															$this->Amazonconnector->submitFeed('_POST_PRODUCT_PRICING_DATA_', $accountDetails['id']);

															$this->Amazonconnector->product['ActionType'] 	= 'UpdateImage';
															$this->Amazonconnector->product['ProductData'] 	= $UpdateImageArray;
															$this->Amazonconnector->submitFeed('_POST_PRODUCT_IMAGE_DATA_', $accountDetails['id']);

															foreach ($product_array as $key => $oc_product) {
																	$this->db->query("UPDATE ".DB_PREFIX."amazon_product_map SET `feed_id` = '".$this->db->escape($product_updated['feedSubmissionId'])."', `sync_status` = '0' WHERE oc_product_id = '".(int)$oc_product['product_id']."'");
															}
													}
											}

									}
							}
					}
			}
	}

	public function getProductRelatedImages($product_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}


	public function getLanguages(){
		$language_data = array();
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");

			foreach ($query->rows as $result) {
					$language_data[$result['code']] = array(
						'language_id' => $result['language_id'],
						'name'        => $result['name'],
						'code'        => $result['code'],
						'locale'      => $result['locale'],
						'image'       => $result['image'],
						'directory'   => $result['directory'],
						'sort_order'  => $result['sort_order'],
						'status'      => $result['status']
					);
			}
			return $language_data;
	}

	public function getStores(){
		$store_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY url");

		$store_data = $query->rows;

		return $store_data;
	}

	/**
	* getProductMappedEntry used to get the mapped product with filter conditions
	*/
	public function getProductMappedEntry($data = array()) {
		$sql = "SELECT apm.*, apf.*, apm.id as map_id, pd.name as product_name, p.model, p.sku, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apm.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->language_id."' AND apm.sync_status = '1' ";

		if(!empty($data['product_ids'])){
			$sql .= " AND p.product_id IN (".$data['product_ids'].")";
		}

		if(!empty($data['filter_map_id'])){
			$sql .= " AND apm.id = '".(int)$data['filter_map_id']."' ";
		}

		if(!empty($data['filter_oc_product_id'])){
			$sql .= " AND apm.oc_product_id = '".(int)$data['filter_oc_product_id']."' AND p.product_id = '".(int)$data['filter_oc_product_id']."' ";
		}

		if(!empty($data['filter_oc_product_name'])){
			$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['filter_oc_product_name']))."%' ";
		}

		if(!empty($data['filter_amazon_product_id'])){
			$sql .= " AND apm.amazon_product_id = '".$this->db->escape($data['filter_amazon_product_id'])."' ";
		}

		if(isset($data['filter_amazon_product_sku']) && $data['filter_amazon_product_sku']){
      $sql .= " AND apm.amazon_product_sku = '".$this->db->escape($data['filter_amazon_product_sku'])."' ";
    }

		if(isset($data['account_id']) && $data['account_id']){
      $sql .= " AND apm.account_id = '".(int)$data['account_id']."' ";
    }

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['sync_source']) && $data['sync_source'] !== '') {
			if($data['sync_source'] == 'Amazon Item'){
					$sql .= " AND apm.sync_source = 'Amazon Item' ";
			}
			if($data['sync_source'] == 'Opencart Item'){
					$sql .= " AND apm.sync_source = 'Opencart Item' ";
			}
		}

		if (isset($data['export_sync_source']) && $data['export_sync_source'] == 'Opencart Item') {
				$sql .= " AND apm.sync_source = 'Opencart Item' ";
		}
			$sort_data = array(
				'product_name',
				'p.model',
				'p.price',
				'p.quantity',
				'apm.id',
				'apm.oc_product_id',
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY apm.id";
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

	public function getTotalProductMappedEntry($data = array()){
			$sql = "SELECT COUNT(DISTINCT apm.id) as total FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."amazon_accounts aa ON (apm.account_id = aa.id) LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->language_id."' AND apm.sync_status = '1' ";

			if(isset($data['account_id']) && $data['account_id']){
				$sql .= " AND apm.account_id = '".(int)$data['account_id']."' ";
			}

			if (isset($data['sync_source']) && $data['sync_source'] !== '') {
				if($data['sync_source'] == 'Amazon Item'){
						$sql .= " AND apm.sync_source = 'Amazon Item' ";
				}
				if($data['sync_source'] == 'Opencart Item'){
						$sql .= " AND apm.sync_source = 'Opencart Item' ";
				}
			}

			$result = $this->db->query($sql)->row;

			return $result['total'];
	}

	// ********** To export opencart store product at amazon store (with or without variations) **********
	public function startExportProcess($data = array(), $accountDetails = array())
	{
			$final_product_data = $UpdateQuantityArray = $addProductArray = $notSyncIds = $addRelationshipArray = array();

			foreach ($data as $key => $product) {
					if ((isset($product['main_type']) && $product['main_type']) && (isset($product['main_value']) && $product['main_value'])) {

							if(isset($product['combinations']) && !empty($product['combinations'])){
									foreach ($product['combinations'] as $option_id => $option_value_array) {
										$total_combinations = count($option_value_array);
											foreach ($option_value_array as $option_value_id => $option_value_data) {
													$product_data = array();
													if(isset($option_value_data['price_prefix']) && $option_value_data['price_prefix'] == '+'){
															$product_data['price'] = (float)$product['price'] + (float)$option_value_data['price'];
													}else{
															$product_data['price'] = (float)$product['price'] - (float)$option_value_data['price'];
													}
													if(isset($option_value_data['quantity']) && $option_value_data['quantity']){
															$product_data['quantity'] = $option_value_data['quantity'];
													}else{
															$product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
													}
													$product_data['product_id']= $product['product_id'];
													$product_data['name'] 		 = $option_value_data['name'];
													$product_data['id_type'] 	 = $option_value_data['id_type'];
													$product_data['id_value']  = $option_value_data['id_value'];
													$product_data['sku'] 			 = $option_value_data['sku'];
													$product_data['image'] 		 = $product['image'];

													//Add new product on Amazon store
	                        $addProductArray[$product_data['sku']] = array(
	                                                'sku' 										=> $product_data['sku'],
																									'exportProductType' 			=> $product_data['id_type'],
																	                'exportProductTypeValue' 	=> $product_data['id_value'],
	                                                'name' 										=> $product_data['name'],
	                                                'description' 						=> strip_tags(htmlspecialchars_decode($product['description'] ? $product['description'] : $product_data['name'])),
	                                        			);

													//Update qty of amazon product
													$UpdateQuantityArray[$product_data['sku']] = array(
																										'sku' 				=> $product_data['sku'],
																										'qty' 				=> $product_data['quantity'],
																									);

													//Update price of amazon product
		 											$UpdatePriceArray[$product_data['sku']] = array(
		 																						 'sku' 							=> $product_data['sku'],
																								 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																								 'price' 						=> round(($product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate']), 2),
		 																					 );
 												 //Update image of amazon product
 												 $UpdateImageArray[$product_data['sku']] = array(
 																								'sku' 							=> $product_data['sku'],
 																								'image_url' 				=> $product_data['image'],
 																							);
											}
									} // combination foreach loop

								//final data of submit feed data
								$final_product_data[] = array('product_id' 	=> $product['product_id'],
																							'account_id' 	=> $accountDetails['id'],
																							'name' 			 	=> $product['name'],
																							'category_id' => $this->config->get('wk_amazon_connector_default_category'),
																							'id_type'			=> $product['main_type'],
																							'id_value'		=> $product['main_value'],
																							'sku'					=> (!empty($product['sku']) ? $product['sku'] : 'oc_prod_'.$product['product_id']),

								);
							}else{
									$product_data = array();
									$product_data['product_id']		= $product['product_id'];
									$product_data['price'] 				= (float)$product['price'];
									$product_data['quantity'] 		= (!empty($product['quantity']) ? $product['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
									$product_data['name'] 				= $product['name'];
									$product_data['id_type'] 			= $product['main_type'];
									$product_data['id_value'] 		= $product['main_value'];
									$product_data['sku'] 					= (!empty($product['sku']) ? $product['sku'] : 'oc_prod_'.$product['product_id']);
									$product_data['description'] 	= strip_tags(htmlspecialchars_decode($product['description']));
									$product_data['account_id'] 	= $accountDetails['id'];
									$product_data['category_id'] 	= $this->config->get('wk_amazon_connector_default_category');
									$product_data['image'] 				= $product['image'];

									//Add new product on Amazon store
									$addProductArray[$product_data['sku']] = array(
																					'sku' 										=> $product_data['sku'],
																					'exportProductType' 			=> $product_data['id_type'],
																					'exportProductTypeValue' 	=> $product_data['id_value'],
																					'name' 										=> $product_data['name'],
																					'description' 						=> strip_tags(htmlspecialchars_decode($product_data['description'])),
																			);

									//Update qty of amazon product
									$UpdateQuantityArray[$product_data['sku']] = array(
																							'sku' => $product_data['sku'],
																							'qty' => $product_data['quantity'],
																					);

									//Update price of amazon product
								 $UpdatePriceArray[$product_data['sku']] = array(
																					 'sku' 							=> $product_data['sku'],
																					 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																					 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																				 );

								 //Update image of amazon product
								 $UpdateImageArray[$product_data['sku']] = array(
																					 'sku' 							=> $product_data['sku'],
																					 'image_url' 				=> $product_data['image'],
																				 );

								 //final data of submit feed data
									$final_product_data[] = $product_data;
							} // combination if condition
					} else {
							$notSyncIds[$product['product_id']] = array('product_id' => $product['product_id'], 'name' => $product['name'], 'message' => 'Warning: ASIN Number is missing for opencart product : <b>'.$product['name'].'</b> !');
					}
			} // Product foreach loop

			if (!empty($final_product_data)) {
					$this->Amazonconnector->product['ActionType'] 	= 'AddProduct';
					$this->Amazonconnector->product['ProductData'] 	= $addProductArray;
					$product_created = $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_DATA_', $accountDetails['id']);

					if (isset($product_created['success']) && $product_created['success']) {
						$this->Amazonconnector->product['ActionType'] 	= 'UpdateQuantity';
						$this->Amazonconnector->product['ProductData'] 	= $UpdateQuantityArray;
						$this->Amazonconnector->submitFeed($feedType 		= '_POST_INVENTORY_AVAILABILITY_DATA_', $accountDetails['id']);

						$this->Amazonconnector->product['ActionType'] 	= 'UpdatePrice';
						$this->Amazonconnector->product['ProductData'] 	= $UpdatePriceArray;
						$this->Amazonconnector->submitFeed($feedType 		= '_POST_PRODUCT_PRICING_DATA_', $accountDetails['id']);

						$this->Amazonconnector->product['ActionType'] 	= 'UpdateImage';
						$this->Amazonconnector->product['ProductData'] 	= $UpdateImageArray;
						$this->Amazonconnector->submitFeed($feedType 		= '_POST_PRODUCT_IMAGE_DATA_', $accountDetails['id']);

						foreach ($final_product_data as $product_details) {
								$product_details['feed_id'] = $product_created['feedSubmissionId'];
								$this->saveExportMapEntry($product_details);
						}

						$result_data = array('status' 					=> true,
																'feedSubmissionId' 	=> $product_created['feedSubmissionId'],
																'success' 					=> $final_product_data,
																'error' 						=> $notSyncIds,
														);
					} else {
							if (isset($product_created['comment']) && $product_created['comment']) {
									$result_data = array('status' 	=> false,
																				'error' 	=> array($product_created['comment']));
							} else {
									$result_data = array('status' 	=> false,
																			 'error' 		=> array($this->language->get('error_occurs')));
							}
					}
			} else {
					$result_data = array('status' 			=> false,
															'error' 				=> $notSyncIds,);
			}

		return $result_data;
	}

  // ********** To save the map entry after export opencart store product to amazon store **********
	public function saveExportMapEntry($product_details = array()){
			if(isset($product_details['feed_id']) && $product_details['feed_id'] && isset($product_details['account_id'])){
					$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_map SET oc_product_id = '" . (int)$product_details['product_id'] . "', amazon_product_id = '" . $this->db->escape($product_details['id_value']) . "', `amazon_product_sku` = '".$this->db->escape($product_details['sku'])."', oc_category_id = '" . (int)$product_details['category_id'] . "', `amazon_image` = '',  account_id = '".(int)$product_details['account_id']."', `sync_source` = 'Opencart Item', `feed_id` = '".$this->db->escape($product_details['feed_id'])."', `sync_status` = '0', added_date = NOW() ");

					$this->db->query("UPDATE ".DB_PREFIX."amazon_product_variation_map SET account_id = '".(int)$product_details['account_id']."' WHERE `main_product_type_value` = '".$this->db->escape($product_details['id_value'])."' ");
			}
	}

	public function getUnMappedOcProducts($data = array()){
			$sql = "SELECT p.*,pd.*, cd.name as category_name, cd.category_id FROM " . DB_PREFIX . "product p LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map ) AND pd.language_id = '".(int)$this->language_id."' AND p.status = '1' ";

  		$sql .= " GROUP BY p.product_id  ORDER BY p.product_id ASC";

  		if (isset($data['start']) || isset($data['limit'])) {
  			if ($data['start'] < 0) {
  				$data['start'] = 0;
  			}

  			if ($data['limit'] < 1) {
  				$data['limit'] = 20;
  			}

  			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
  		}

  		$query = $this->db->query($sql);

  		return $query->rows;
	}

	public function getTotalUnMappedOcProducts(){
			if($this->language_id){
					$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) WHERE p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map) AND pd.language_id = '".(int)$this->language_id."' AND p.status = '1' ";

					$query = $this->db->query($sql);
					return $query->row['total'];
			}else{
					return false;
			}
	}

	public function getLanguageByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($code) . "'");
		return $query->row;
	}

	public function _arrayColumn($array,$column_name) {
			return array_map(function($element) use($column_name){return $element[$column_name];}, $array);
	}
}

?>
