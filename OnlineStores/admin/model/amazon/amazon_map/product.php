<?php

class ModelAmazonAmazonMapProduct extends Model {

	private $product_manage 	= array();

	/**
	 * [updateAccountReportEntry to get/update the product report listing id and inverntory id]
	 * @param  boolean $account_id [description]
	 * @return [type]              [description]
	 */
	public function updateAccountReportEntry($account_id = false){
		$result = $data = array();
		try{
			$getProductReportId =  $this->Amazonconnector->requestReport('_GET_MERCHANT_LISTINGS_DATA_', $account_id);
			$getProdQtyReportId =  $this->Amazonconnector->requestReport('_GET_AFN_INVENTORY_DATA_', $account_id);

					if($getProductReportId && $getProdQtyReportId){
						do {
		                sleep(3);
		                $productReportRequestList 		= $this->Amazonconnector->getReportRequestList($getProductReportId, $account_id);
		                $productQtyReportRequestList 	= $this->Amazonconnector->getReportRequestList($getProdQtyReportId, $account_id);
		                $status = $this->getReportStatus($productReportRequestList, $productQtyReportRequestList);
		            } while (!$status);

					}

					if (isset($productReportRequestList['GeneratedReportId']) && $productReportRequestList['GeneratedReportId'] && isset($productQtyReportRequestList['GeneratedReportId']) && $productQtyReportRequestList['GeneratedReportId'] ) {
							$data['list_report_id'] 			= $productReportRequestList['GeneratedReportId'];
							$data['inventory_report_id'] 	= $productQtyReportRequestList['GeneratedReportId'];
					}else if(isset($productReportRequestList['GeneratedReportId'])){
							$data['list_report_id'] 			= $productReportRequestList['GeneratedReportId'];
							$data['inventory_report_id'] 	= '0';
					}else{
							$data['list_report_id'] 			= '0';
							$data['inventory_report_id'] 	= '0';
					}

					if (isset($data['list_report_id']) && isset($data['inventory_report_id']) && $data['list_report_id']) {
							$this->db->query("UPDATE ".DB_PREFIX."amazon_accounts SET `wk_amazon_connector_listing_report_id` = '".$this->db->escape($data['list_report_id'])."', `wk_amazon_connector_inventory_report_id` = '".$this->db->escape($data['inventory_report_id'])."' WHERE id = '".(int)$account_id."' ");

							$result = array('status' => true, 'message' => sprintf($this->language->get('success_report_added'), $data['list_report_id']), 'report_id' => $data['list_report_id']);
					}else{
							$result = array('status' => false, 'message' => $this->language->get('error_report_list_id'));
					}
		} catch (\Exception $e) {
        $result = array('status' => false,'message' => $e->getMessage());
    }
		return $result;
	}

	/**
	 * [getReportStatus to check listing/inventory id]
	 * @param  [type] $productRequestList [description]
	 * @param  [type] $QtyRequestList     [description]
	 * @return [type]                     [description]
	 */
	private function getReportStatus($productRequestList, $QtyRequestList) {
      $status = isset($productRequestList['success']) && $productRequestList['success'] && $productRequestList['GeneratedReportId'] && isset($QtyRequestList['success']) && $QtyRequestList['success'] && $QtyRequestList['GeneratedReportId'];
      if (!$status) {
          $status =  isset($productRequestList['success']) && $productRequestList['success'] && $productRequestList['GeneratedReportId']
                  || isset($QtyRequestList['success']) && $QtyRequestList['success'] && $QtyRequestList['GeneratedReportId'];
      }
      return $status;
  }

	// ********** To get the amazon store products data through report **********
	public function getFinalProductReport($getAccountEntry){
			$proReportData = $this->Amazonconnector->getReportFinal($getAccountEntry['wk_amazon_connector_listing_report_id'], $getAccountEntry);

			if($proReportData){
					$finalReportArray = $newQtyReport = $items = [];

					$qtyReportData = $this->Amazonconnector->getReportFinal($getAccountEntry['wk_amazon_connector_inventory_report_id'], $getAccountEntry);

					foreach ($proReportData as $proReportValue) {
							$productInventoryFound = false;
							if($qtyReportData){
									foreach ($qtyReportData as $qtyReportValue) {
											if ($proReportValue['asin1'] == $qtyReportValue['asin']) {
													foreach ($qtyReportValue as $qtyKey => $qtyValue) {
															$newQtyReport[trim($qtyKey)] = $qtyValue;
													}
													$productInventoryFound = true;
													$proReportValue['qty_avail'] = $newQtyReport['Quantity Available'];
													break;
											}
											if (!$productInventoryFound) {
													$proReportValue['qty_avail'] = $proReportValue['quantity'];
											}
									}
							}else{
									$proReportValue['qty_avail'] = $proReportValue['quantity'];
							}

							$finalReportArray[] = $proReportValue;
					}
					if (!empty($finalReportArray)) {
							$items = $this->saveFormattedReportData($finalReportArray, $getAccountEntry);
					}
					return $items;
			}
	}

// ********** To arrange the amazon products data in formatted form  **********
	public function saveFormattedReportData($reportData = array(), $accountDetails){
			$completeWellFormedData = [];$items = [];
			$this->load->model('localisation/language');
			$this->load->model('setting/store');
			$languages  			= $this->model_localisation_language->getLanguages();
			$stores     			= $this->model_setting_store->getStores();
			array_push($stores,array('store_id' => 0,'name' => 'Default Store','url' => HTTP_CATALOG, 'ssh' => ''));

			// get config value
			$product_quantity = $this->config->get('wk_amazon_connector_default_quantity');
			$product_price    = '50';
			$product_weight   = $this->config->get('wk_amazon_connector_default_weight');
			$product_category = $this->config->get('wk_amazon_connector_default_category');
			// get default attribute group value
			if(isset($accountDetails['wk_amazon_connector_attribute_group']) && $accountDetails['wk_amazon_connector_attribute_group']){
					$product_attribute_group = $accountDetails['wk_amazon_connector_attribute_group'];
			}else{
					$product_attribute_group = 3;
			}

			$dt = new \DateTime();
			$currentDate = $dt->format('Y-m-d\TH:i:s');

			$tempAvlImported 						= $this->getProductTempData(array('account_id' 		=> $accountDetails['id']));
			try{
					foreach($reportData as $amzProData) {
							$product_description = [];
							if (in_array($amzProData['asin1'], array_column($tempAvlImported, 'amazon_prod_id'))) {
									continue;
							}

							$item_name = $item_description = '';
							$item_name 				= str_replace('"', '', $amzProData['item-name']);
							$item_description = str_replace('"', '', $amzProData['item-description']);
							foreach ($languages as $key => $language) {
									$product_description[$language['language_id']] = array(
											'name'          => htmlspecialchars($item_name, ENT_QUOTES),
											'description'   => htmlspecialchars($item_description, ENT_QUOTES),
											'meta_title'    => htmlspecialchars($item_name, ENT_QUOTES));
							}
							$product_price      = $amzProData['price'] * ($this->currency->getValue($this->config->get('config_currency')) / $accountDetails['wk_amazon_connector_currency_rate']);

							$wholeData = array(
									'ItemID'							=> $amzProData['asin1'],
									'account_id'					=> $accountDetails['id'],
				          'attribute_group'     => $product_attribute_group,
				          'model'            		=> $amzProData['asin1'],
									'sku'              		=> empty($amzProData['seller-sku']) ? $amzProData['item-name'] : $amzProData['seller-sku'],
									'quantity'         		=> $amzProData['qty_avail'],
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
									'amazonProductTypeValue' => $amzProData['asin1'],
								);

                $completeWellFormedData[$amzProData['asin1']] = [
                    'item_type' 					=> 'product',
                    'item_id'   					=> $amzProData['asin1'],
                    'item_data' 					=> json_encode($wholeData),
                    'amazon_product_id' 	=> $amzProData['product-id'],
                    'account_id' 					=> $accountDetails['id'],
										'added_date'					=> $currentDate,
                ];
                $items[$amzProData['asin1']] = $amzProData['asin1'];
					}
					$this->InsertDataInBulk($completeWellFormedData);
			} catch(\Exception $e) {
					$this->log->write('ManageProductRawData saveDataInWellFormat : '.$e->getMessage());
			}
			return $items;
	}

// ********** To get the formatted amazon products data from amazon_tempdata table  **********
	public function getProductTempData($data = array()){
			$sql = "SELECT td.item_id as amazon_prod_id, td.* FROM ".DB_PREFIX."amazon_tempdata td LEFT JOIN ".DB_PREFIX."amazon_accounts aa ON (td.account_id = aa.id) WHERE td.item_type = 'product' ";

			if(!empty($data['item_id'])){
				$sql .= " AND td.item_id = '".$this->db->escape($data['item_id'])."' ";
			}

			if(!empty($data['account_id'])){
				$sql .= " AND td.account_id = '".(int)$data['account_id']."' AND aa.id = '".(int)$data['account_id']."' ";
			}

			$sql .= " GROUP BY amazon_prod_id ";

			return $this->db->query($sql)->rows;
	}
	//
	// public function getProductMainEntry($data = array()){
	// 		$sql = "SELECT pm.*, pm.amazon_product_id as amazon_prod_id FROM ".DB_PREFIX."amazon_product_map pm LEFT JOIN ".DB_PREFIX."amazon_accounts aa ON (pm.account_id = aa.id) LEFT JOIN ".DB_PREFIX."product p ON (pm.oc_product_id = p.product_id) WHERE 1 ";
	//
	// 		if(!empty($data['account_id'])){
	// 			$sql .= " AND pm.account_id = '".(int)$data['account_id']."' AND aa.id = '".(int)$data['account_id']."' ";
	// 		}
	// 		return $this->db->query($sql)->rows;
	// }
	//
	// public function getProductOptionsEntry($data = array()){
	// 		$sql = "SELECT pvm.*, pvm.id_value as amazon_prod_id FROM ".DB_PREFIX."amazon_product_variation_map pvm LEFT JOIN ".DB_PREFIX."product_option_value pov ON ((pvm.product_option_value_id = pov.product_option_value_id) AND (pvm.option_value_id = pov.option_value_id) AND (pvm.product_id = pov.product_id)) LEFT JOIN ".DB_PREFIX."product p ON (pvm.product_id = p.product_id) WHERE pvm.main_product_type_value IN (SELECT amazon_product_id FROM ".DB_PREFIX."amazon_product_map apm WHERE apm.account_id = '".(int)$data['account_id']."' ) ";
	//
	// 		return $this->db->query($sql)->rows;
	// }

// ********** To get Single/All formatted amazon product(s) data from amazon_tempdata table  **********
	public function getImportedProduct($accountId, $count_type = 'single', $product_asin = 0){
			$sql = "";
			if($product_asin){
				$sql .= " AND item_id = '".$this->db->escape($product_asin)."' ";
			}
			if($count_type == 'all'){
					$getRecord = $this->db->query("SELECT COUNT(*) as total FROM ".DB_PREFIX."amazon_tempdata WHERE item_type = 'product' AND account_id = '".(int)$accountId."' ORDER BY id ASC ")->row;
			}else{
					$getRecord = $this->db->query("SELECT * FROM ".DB_PREFIX."amazon_tempdata WHERE item_type = 'product' $sql AND account_id = '".(int)$accountId."' ORDER BY id ASC LIMIT 1 ")->row;
			}
			return $getRecord;
	}

// ********** To create the amazon product(s) in opencart store from amazon_tempdata table  **********
	public function createProductToOC($productDetails, $account_details = array()){
		$this->load->model('catalog/attribute');
		$result = [];

		$product_attribute  = $product_dimensions = $product_array = $childArray = $containChildData = array();
		try{
			$tempData = json_decode($productDetails['item_data'], true);

			$importType = $this->config->get('wk_amazon_connector_variation');

			if(!empty($tempData)){
				$tempData['account_id'] = $account_details['id'];
				$getProductDetails  = $this->Amazonconnector->getMatchedProduct($productDetails['item_id'], $account_details['id']);

				if(isset($getProductDetails['GetMatchingProductResult']['@attributes']['status']) && $getProductDetails['GetMatchingProductResult']['@attributes']['status'] != 'Success'){
					
						$this->__removeAmazonTempEntry(array('item_id' => $productDetails['item_id'], 'account_id' => $account_details['id']));
						if(isset($getProductDetails['GetMatchingProductResult']['Error'])){
							$result[] = array(
								'error'    => true,
								'asin'			=> $productDetails['item_id'],
								'message'   => 'Warning: '. $getProductDetails['GetMatchingProductResult']['Error']['Message'],
							);
						}else{
							$result[] = array(
								'error'    => true,
								'asin'			=> $productDetails['item_id'],
								'message'   => "Error: Amazon product Id: ".$productDetails['item_id']." failed for mapped with opencart as product status is not Success from amazon store!",
							);
						}

					return $result;
				}
			

				// ***********   create product attribute (specification)   ***********
				$product_attribute = $this->saveProductAttributes($getProductDetails, $tempData['attribute_group'], $account_details['id']);
				if(!empty($product_attribute) && isset($product_attribute['product_attribute'])){
						$tempData['product_attribute'] = $product_attribute['product_attribute'];
				}
				if(!empty($product_attribute) && isset($product_attribute['product_dimensions'])){
						$product_dimensions = $product_attribute['product_dimensions'];
				}
				// ***********  create product attribute (specification)   ***********

				// ***********  save image to opencart   ***********
				if(isset($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL'])){
					$defaultImage = $this->__saveproductImage($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL']);
				}else{
					$defaultImage = array('image' => '', 'amazon_image' => '');
				}
				$tempData['image']        = $defaultImage['image'];
				$tempData['amazon_image'] = $defaultImage['amazon_image'];
				// ***********   save image to opencart   ***********

				$containChildData = $tempData;
				//simple product import
				
									if($importType == 2){
											if (!empty($getProductDetails['GetMatchingProductResult']['Product']['Relationships'])) {
													$result[] = array(
																		'error' 	=> true,
																		'asin'		=> $productDetails['item_id'],
																		'message' => "Error: Amazon product Id: ".$productDetails['item_id']." will not imported because this is a variation product!");
													$this->__removeAmazonTempEntry(array('item_id' => $productDetails['item_id'], 'account_id' => $account_details['id']));
												 return $result;
											}
									}else{
												$languages  = $this->model_localisation_language->getLanguages();
												//simple + variation products import
												if(isset($getProductDetails['GetMatchingProductResult']['Product']['Relationships']['VariationParent'])) {
														$parentAsinId = $this->Amazonconnector->checkParentAsinValue($getProductDetails['GetMatchingProductResult']['Product']['Relationships']['VariationParent']);
														$tempData['parent_id'] = $parentAsinId;
												}else{
														$parentAsinId = $productDetails['item_id'];
												}

												$tempData['amazonProductType'] 				= 'ASIN';
												$tempData['amazonProductTypeValue'] 	= $parentAsinId;

												// ******* to get the parent container details call again getMatchedProduct function *******
				                $parentAsinData   = $this->Amazonconnector->getMatchedProduct($parentAsinId, $account_details['id']);
												// ******* to get the parent container details call again getMatchedProduct function *******

												$getTotalQuantity = $getMinimumOptionPrice = 0;
				                if (isset($parentAsinData['GetMatchingProductResult']['Product']['Relationships']['ns2VariationChild']) && $parentAsinData['GetMatchingProductResult']['Product']['Relationships']['ns2VariationChild']) {
														foreach ($languages as $key => $language) {
															$tempData['product_description'][$language['language_id']] = array(
																'name'          => str_replace('"', '', $parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']),
																'description'   => str_replace('"', '', isset($data['product_data']['item-description']) ? $data['product_data']['item-description'] : ''),
																'meta_title'    => str_replace('"', '', $parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2Title']));
														}
													$tempData['ItemID'] 	= 	$tempData['model']	 = 	$tempData['sku'] = $parentAsinData['GetMatchingProductResult']['Product']['Identifiers']['MarketplaceASIN']['ASIN'];

													// *********  save parent container image to opencart and set main product image *********
													if(isset($parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL'])){
															$product_array_image 			= $this->__saveproductImage($parentAsinData['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']['ns2SmallImage']['ns2URL']);
															$tempData['image'] 				= $product_array_image['image'];
															$tempData['amazon_image'] = $product_array_image['amazon_image'];
													}else{
															$tempData['image'] 				= $product_array['amazon_image'] = '';
													}
													// *********  save parent container image to opencart and set main product image *********
								
			                    foreach ($parentAsinData as $key => $parentVal) {
															if($key == 'GetMatchingProductResult'){
			                          $product_images = $option_details = $product_option = array();

			                            if(isset($parentVal['Product']['Relationships']['ns2VariationChild']['Identifiers'])){
																		$parentVal['Product']['Relationships']['ns2VariationChild'] = [$parentVal['Product']['Relationships']['ns2VariationChild']];
																	}
																	if(isset($parentVal['Product']['Relationships']['ns2VariationChild'])){
																			$childArray = $parentVal['Product']['Relationships']['ns2VariationChild'];

																			$getChildIndex = array_search($productDetails['item_id'], array_column(array_column(array_column($childArray, 'Identifiers'), 'MarketplaceASIN'), 'ASIN'));

			                                if (isset($childArray[$getChildIndex]['Identifiers']['MarketplaceASIN']['ASIN']) && $childArray[$getChildIndex]['Identifiers']['MarketplaceASIN']['ASIN'] == $productDetails['item_id']) {

			                                    $childAsin = '';
																					// *********  create product option in opencart  ***********
			                                    if (isset($childArray[$getChildIndex]['Identifiers'])) {
			                                        $savedVariation = $this->createVariationOpencart($childArray[$getChildIndex], $account_details['id']);
			                                        $childAsin      = $childArray[$getChildIndex]['Identifiers']['MarketplaceASIN']['ASIN'];
			                                    }
																					// *********  create product option in opencart  ***********

			                                    if(isset($savedVariation['id']) && $savedVariation['id']){
																							if(isset($containChildData['ItemID']) && $containChildData['ItemID'] == $childAsin){
																									$opt_name       = $containChildData['product_description'][$this->config->get('config_language_id')]['name'];
																									$opt_sku        = $containChildData['sku'];
																									$opt_id_type    = $savedVariation['id_type'];
																									$opt_id_value   = $savedVariation['id_value'];
																									$opt_price 			= $containChildData['price'] ;
																									$opt_quantity 	= $containChildData['quantity'];
																									if($containChildData['image'] != '' && $containChildData['amazon_image'] != ''){
																											$opt_image_name = array('image' => $containChildData['image'], 'amazon_image' => $containChildData['amazon_image']);
																									}else{
																											$opt_image_name = array('image' => '', 'amazon_image' => '');
																									}
																									//total product quantity
																									$getTotalQuantity = $getTotalQuantity + $opt_quantity;

																									//get minimum option price for product
																									if($getMinimumOptionPrice == 0){
																										$getMinimumOptionPrice = $opt_price;
																									}else if($opt_price < $getMinimumOptionPrice){
																										$getMinimumOptionPrice = $opt_price;
																									}
																									if(isset($opt_image_name['image']) && $opt_image_name['image']){
																											$product_images[$opt_image_name['image']] = array('sort_order' => $key, 'image' => $opt_image_name['image']);
																									}
															                    $option_details = array(
															                        'name'          => str_replace('"', '', $opt_name),
															                        'sku'           => $opt_sku,
																											'id_type'				=> $opt_id_type,
																											'id_value'			=> $opt_id_value,
															                        'price'         => $opt_price,
															                        'quantity'      => $opt_quantity,
															                        'image'         => $opt_image_name['image'],
															                        'amazon_image'  => $opt_image_name['amazon_image'],
															                    );
															                    $product_option[] = array_merge($savedVariation, $option_details);

															                    $this->db->query("UPDATE ".DB_PREFIX."option_value SET `image` = '".$opt_image_name['image']."' WHERE option_value_id = '".(int)$savedVariation['variation_value_id']."' AND option_id = '".(int)$savedVariation['variation_id']."' ");
																							}
											                    }
											                }
											            }

			                            $product_options = $product_option_value = array();
			                            if(!empty($product_option)){
			                                $option_id = false;
			                                foreach ($product_option as $key_opt => $p_option) {
			                                        $option_id = $p_option['variation_id'];
			                                        $product_option_value[] = array(
			                                            'option_value_id'	=> $p_option['variation_value_id'],
			                                            'quantity'				=> $p_option['quantity'],
																									'price'						=> (float)$p_option['price'],
			                                            'subtract'				=> 1,
			                                            'price_prefix' 		=> '+',
																									'sku'           	=> $p_option['sku'],
																									'id_type'					=> $p_option['id_type'],
																									'id_value' 				=> $p_option['id_value'],
																								);
			                                }
			                                $product_options[] = array(
			                                    'name'				=> 'Amazon Variations',
			                                    'type'				=> 'checkbox',
			                                    'option_id'		=> $option_id,
			                                    'required'		=> 1,
			                                    'product_option_value' => $product_option_value,);
			                            }
															$tempData['price']					= 0;
															$tempData['quantity']				= $getTotalQuantity;
			                        $tempData['product_image']	= $product_images;
			                        $tempData['product_option']	= $product_options;

			                    }// if condition
												}// loop for variation of child
										}
									}// else closing for simple + variation product condition
									$product_array = array_merge($tempData, $product_dimensions);

									$getMappedEntry = $this->getProductMappedEntry(array('filter_amazon_product_id' => $product_array['ItemID'], 'account_id' => $account_details['id']));

									if(!empty($getMappedEntry) && isset($getMappedEntry[0])){
											$product_array['product_id'] = $getMappedEntry[0]['oc_product_id'];
											$result = $this->__editAmazonProduct($product_array);
																$this->__removeAmazonTempEntry(array('item_id' => $productDetails['item_id'], 'account_id' => $account_details['id']));
									}else{
											$result = $this->__saveAmazonProduct($product_array);
																$this->__removeAmazonTempEntry(array('item_id' => $productDetails['item_id'], 'account_id' => $account_details['id']));
									}
					}else{
							$result[] = array(
												'error' 	=> true,
												'message' => 'Data not found');
							$this->__removeAmazonTempEntry(array('item_id' => $productDetails['item_id'], 'account_id' => $account_details['id']));
					}
			} catch(\Exception $e) {
					$this->log->write('Create Product : '.$e->getMessage());
					$result[] = array(
											'error' 	=> true,
											'message' => $e->getMessage());
			}
			return $result;
	}

// ********** To delete the amazon product(s) entry from amazon_tempdata table (on success/Error)  **********
	public function __removeAmazonTempEntry($data = array()){
			if(isset($data['item_id']) && isset($data['account_id'])){
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_tempdata WHERE item_id = '".$this->db->escape($data['item_id'])."' AND account_id = '".(int)$data['account_id']."' ");
			}
	}

// ********** To save the amazon product(s) specification entries to opencart tables  **********
	public function saveProductAttributes($getProductDetails, $product_attribute_group, $account_id){
			$product_dimensions = $product_attribute = array();
			if(isset($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes']) && !empty($getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes'])){
					$product_attributes = $getProductDetails['GetMatchingProductResult']['Product']['AttributeSets']['ns2ItemAttributes'];
					$mappedAttributes   = $this->createAttributes($product_attributes, $product_attribute_group, $account_id);

					if(isset($mappedAttributes['product_dimensions']['ItemDimensions']) && $mappedAttributes['product_dimensions']['ItemDimensions']){
							$product_dimensions = array(
																			'length'    => $mappedAttributes['product_dimensions']['ItemDimensions']['lenght'],
																			'width'     => $mappedAttributes['product_dimensions']['ItemDimensions']['width'],
																			'height'    => $mappedAttributes['product_dimensions']['ItemDimensions']['height'],
																			'length_class_id'   => 1,
																			);
							$product_weight     = $mappedAttributes['product_dimensions']['ItemDimensions']['height'];
					}else{
						$product_dimensions = array('length'    => 0,'width'     => 0,'height'    => 0,'length_class_id'   => 0,);
					}
					if(isset($mappedAttributes['attributes_data']) && $mappedAttributes['attributes_data']){
							$product_attribute = $mappedAttributes['attributes_data'];
					}
			}else{
					$product_dimensions = array('length'    => 0,'width'     => 0,'height'    => 0,'length_class_id'   => 0,);
			}
			return array('product_dimensions' => $product_dimensions, 'product_attribute' => $product_attribute);
	}

	// ********** To divide the amazon product(s) array into bunched from amazon_tempdata table **********
	public function InsertDataInBulk($completeWellFormedData = []) {
			try {
					if (!empty($completeWellFormedData)) {
							$numberOfRecord = 500;
							$indexNumber = 0;$allCount = count($completeWellFormedData);
							if (count($completeWellFormedData) > $numberOfRecord) {
									while(count($completeWellFormedData) > $indexNumber) {
											$slicedArray = [];
											if (count($completeWellFormedData) > ($indexNumber+$numberOfRecord)) {
													$slicedArray = array_slice($completeWellFormedData, $indexNumber, $numberOfRecord);

													$this->insertMultiple('amazon_tempdata', $slicedArray);
													$indexNumber = $indexNumber + $numberOfRecord;
											} else {
													$remainingIndexes = $allCount -  $indexNumber;
													$slicedArray = array_slice($completeWellFormedData, $indexNumber, $remainingIndexes);
													$this->insertMultiple('amazon_tempdata', $slicedArray);
													$indexNumber = $indexNumber + $remainingIndexes;
													break;
											}
									}
							}  else {
									$this->insertMultiple('amazon_tempdata', $completeWellFormedData);
							}
					}
			} catch(\Exception $e) {
					$this->log->write('ManageProductRawData InsertDataInBulk : '.$e->getMessage());
			}
	}

// ********** To arrange the amazon product(s) columns and their row values **********
	public function insertMultiple($table, array $data)
  {
			$insertArray = [];
      $row = reset($data);
      // validate data array
      $cols = array_keys($row);

      foreach ($data as $row) {
          $line = [];
					foreach ($cols as $field) {
							$line[] = $row[$field];
					}
					$insertArray[] = $line;
      }
      unset($row);
      return $this->insertArray($table, $cols, $insertArray);
  }

// ********** To insert multiple rows of amazon product(s) into amazon_tempdata table **********
	public function insertArray($table, array $columns, array $data)
  {
      $values       = [];
      $bind         = [];
      foreach ($data as $row) {
          $values[] = $this->_prepareInsertData($row, $bind);
      }
      $query = $this->_getInsertSqlQuery($table, $columns, $values);

      // execute the statement and return the number of affected rows
      $this->db->query("INSERT INTO ".DB_PREFIX."$table$query");
			$result = $this->db->query("SELECT ROW_COUNT() as total ")->row;

      return $result['total'];
  }
// ********** To make the sting query to insert the multiple rows of amazon product(s) into amazon_tempdata table **********
	protected function _prepareInsertData($row, &$bind)
  {
      $row = (array)$row;
      $line = [];
      foreach ($row as $value) {
							$line[] = "'$value'";
              $bind[] = $value;
      }
      $line = implode(', ', $line);
      return sprintf('(%s)', $line);
  }
// ********** To make the sting query to insert the multiple rows of amazon product(s) into amazon_tempdata table **********
	protected function _getInsertSqlQuery($tableName, array $columns, array $values)
	{
			 $columns   = implode(',', $columns);
			 $values    = implode(', ', $values);
			 $insertSql = sprintf(' (%s) VALUES %s', $columns, $values);
			 return $insertSql;
	}

	/**
	* getProductMappedEntry used to get the mapped product with filter conditions
	*/
	public function getAllProductMappedEntry($start = 0, $length = 10, $filterData = null, $orderColumn = "map_id") {
		$sql_str = "SELECT apm.*, apf.*, apm.id as map_id, pd.name as product_name, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apm.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";


		$total = $this->db->query($sql_str)->num_rows;
		$where = "";
		if (!empty($filterData['search'])) {
		  $where .= "( apm.oc_product_id  like '%" . $this->db->escape($filterData['search']) . "%'
					  OR LCASE(pd.name) like '%" . $this->db->escape($filterData['search']) . "%
					  OR apm.amazon_product_id like '%" . $this->db->escape($filterData['search']) . "%
					  OR p.price like '%" . $this->db->escape($filterData['search']) . "%
					  OR p.quantity like '%" . $this->db->escape($filterData['search']) . "%') ";
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
	* getProductMappedEntry used to get the mapped product with filter conditions
	*/
	public function getProductMappedEntry($data = array()) {
		$sql = "SELECT apm.*, apf.*, apm.id as map_id, pd.name as product_name, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apm.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

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

	/**
	* getTotalProductMappedEntry used to get the total number of mapped products with filter conditions
	*/
	public function getTotalProductMappedEntry($data = array()) {
		$sql = "SELECT COUNT(DISTINCT apm.id) as total FROM ".DB_PREFIX."amazon_product_map apm LEFT JOIN ".DB_PREFIX."product p ON (apm.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apm.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

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
		$result = $this->db->query($sql)->row;

		return $result['total'];
	}

	public function deleteMapProducts($map_id, $account_id){
			$result = $product_data = array();
			$this->load->model('catalog/product');
			$getProductEntry = $this->getProductMappedEntry(array('filter_map_id' => $map_id, 'account_id' => $account_id));

			if(!empty($getProductEntry) && isset($getProductEntry[0]['map_id']) && $getProductEntry[0]['map_id'] == $map_id){
					$product_data = $getProductEntry[0];

					$getOrderProduct = $this->orderProductCheck($product_data);
					if(empty($getOrderProduct)){
							$product_id = $product_data['oc_product_id'];
							if($product_data['sync_source'] == 'Amazon Item'){
									$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_fields WHERE product_id = '" . (int)$product_id . "'");
									$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_variation_map WHERE product_id = '" . (int)$product_id . "'");
									$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_map WHERE oc_product_id = '" . (int)$product_id . "'");
									$this->model_catalog_product->deleteProduct($product_id);
							}else{
									$DeleteProductArray = array();
									$this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_map WHERE oc_product_id = '" . (int)$product_id . "'");

									if((isset($product_data['main_product_type']) && $product_data['main_product_type']) && (isset($product_data['main_product_type_value']) && $product_data['main_product_type_value'])){

											if($getCombinations = $this->Amazonconnector->_getProductVariation($product_id, $type = 'amazon_product_variation_value')){
													foreach ($getCombinations as $option_id => $combination_array) {
															foreach ($combination_array['option_value'] as $key => $combination_value) {
																//delete amazon product
																$DeleteProductArray[] = array(
																														'sku' => $combination_value['sku'],
																												);
															}
													}
											}else{
													//delete amazon product
													$DeleteProductArray[] = array(
																										'sku' => (!empty($product_data['amazon_product_sku']) ? $product_data['amazon_product_sku'] : 'oc_prod_'.$product_id),
																								);
											}
											$this->Amazonconnector->product['ActionType']  = 'DeleteProduct';
											$this->Amazonconnector->product['ProductData'] = $DeleteProductArray;

											$product_updated = $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_DATA_', $account_id);
									}
							}
							$result = array('status' => true, 'message' => sprintf($this->language->get('text_success_delete'), $product_data['amazon_product_id']). $product_id);
					}else if(isset($getOrderProduct[0]['order_product_id']) && $getOrderProduct[0]['order_product_id']){
							$result = array('status' => false, 'message' => sprintf($this->language->get('error_found_order1'), $getOrderProduct[0]['name']).sprintf($this->language->get('error_found_order2'), $getOrderProduct[0]['amazon_order_id'].' which is mapped with Opencart Order-Id: '. $getOrderProduct[0]['order_id']));
					}
			}
			return $result;
	}

	public function orderProductCheck($data = array()){
		$sql = "SELECT op.*, aom.amazon_order_id FROM ".DB_PREFIX."order_product op LEFT JOIN ".DB_PREFIX."order o ON(op.order_id = o.order_id) LEFT JOIN ".DB_PREFIX."amazon_order_map aom ON(o.order_id = aom.oc_order_id) WHERE o.language_id = '".(int)$this->config->get('config_language_id')."' ";

			if(!empty($data['account_id'])){
				$sql .= " AND aom.account_id = '".(int)$data['account_id']."' ";
			}

			if(!empty($data['oc_product_id'])){
				$sql .= " AND op.product_id = '".(int)$data['oc_product_id']."' ";
			}

			if(!empty($data['oc_order_id'])){
				$sql .= " AND aom.oc_order_id = '".(int)$data['oc_order_id']."' AND o.order_id = '".(int)$data['oc_order_id']."' ";
			}
			$results = $this->db->query($sql)->rows;
			return $results;
	}

  /**
  * createAttributes used to manage the amazon product specifications, and saved to opencart store as attributes.
  */
  public function createAttributes($amazon_attributes, $attribute_group_id, $account_id)
  {
      $attribute_data = $oc_attributes = $product_dimensions = array();
      $remove_attributes = array('Title', 'SmallImage', 'PackageQuantity');

      $languages = $this->model_localisation_language->getLanguages();
      foreach ($amazon_attributes as $key => $p_attribute) {
          $attribute_name     = substr($key,3);
          $oc_attribute_value = array();
          $oc_attributes      = array(
                                  'attribute_group_id'    => $attribute_group_id,
                                  'sort_order'            => 0,
                                  );
          if(!in_array($attribute_name, $remove_attributes)){
              if($attribute_name == 'ItemDimensions' && is_array($p_attribute)){
                  $itemDimStr = '';
                  // (L*W*H)-> inch to cm
                  if(isset($p_attribute['ns2Length'])){
                      $product_dimensions['ItemDimensions']['lenght'] = $p_attribute['ns2Length'] * 2.54;
                      $itemDimStr .= number_format(($p_attribute['ns2Length'] * 2.54), 1, '.', '') .' * ';
                  }else{
                      $product_dimensions['ItemDimensions']['lenght'] = 0;
                  }
                  if(isset($p_attribute['ns2Width'])){
                      $product_dimensions['ItemDimensions']['width'] = $p_attribute['ns2Width'] * 2.54;
                      $itemDimStr .= number_format(($p_attribute['ns2Width'] * 2.54), 1, '.', '') .' * ';
                  }else{
                      $product_dimensions['ItemDimensions']['width'] = 0;
                  }
                  if(isset($p_attribute['ns2Height'])){
                      $product_dimensions['ItemDimensions']['height'] = $p_attribute['ns2Height'] * 2.54;
                      $itemDimStr .= number_format(($p_attribute['ns2Height'] * 2.54), 1, '.', '') .' cm';
                  }else{
                      $product_dimensions['ItemDimensions']['height'] = 0;
                  }
                  foreach ($languages as $key1 => $language) {
                     $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $itemDimStr);
                     $oc_attribute_value[$language['language_id']] = array('text' => $itemDimStr);
                  }
                  // (weight)-> pound to gram
                  if(isset($p_attribute['ns2Weight'])){
                      $product_dimensions['ItemDimensions']['weight'] = $p_attribute['ns2Weight'] * 453.592;
                  }
              }else if($attribute_name == 'PackageDimensions' && is_array($p_attribute)){
                  $packDimStr = '';
                  // (L*W*H)-> inch to cm
                  if(isset($p_attribute['ns2Length'])){
                      $product_dimensions['PackageDimensions']['lenght'] = $p_attribute['ns2Length'] * 2.54;
                      $packDimStr .= number_format(($p_attribute['ns2Length'] * 2.54), 1, '.', '')  .' * ';
                  }else{
                      $product_dimensions['PackageDimensions']['lenght'] = 0;
                  }
                  if(isset($p_attribute['ns2Width'])){
                      $product_dimensions['PackageDimensions']['width'] = $p_attribute['ns2Width'] * 2.54;
                      $packDimStr .= number_format(($p_attribute['ns2Width'] * 2.54), 1, '.', '') .' * ';
                  }else{
                      $product_dimensions['PackageDimensions']['width'] = 0;
                  }
                  if(isset($p_attribute['ns2Height'])){
                      $product_dimensions['PackageDimensions']['height'] = $p_attribute['ns2Height'] * 2.54;
                      $packDimStr .= number_format(($p_attribute['ns2Height'] * 2.54), 1, '.', '') .' cm';
                  }else{
                      $product_dimensions['PackageDimensions']['height'] = 0;
                  }
                  foreach ($languages as $key1 => $language) {
                     $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $packDimStr);
                     $oc_attribute_value[$language['language_id']] = array('text' => $packDimStr);
                  }
                  // (weight)-> pound to gram
                  if(isset($p_attribute['ns2Weight'])){
                      $product_dimensions['PackageDimensions']['weight'] = $p_attribute['ns2Weight'] * 453.592;
                  }
              }else if(!is_array($p_attribute) && isset($p_attribute)){
                 foreach ($languages as $key1 => $language) {
                     $oc_attributes['attribute_description'][$language['language_id']] = array('name' => $attribute_name, 'value' => $p_attribute);
                     $oc_attribute_value[$language['language_id']] = array('text' => $p_attribute);
                 }
              }
              if(!empty($oc_attributes) && isset($oc_attributes['attribute_description'])){
                  $filter_data = array(
                      'account_group_id'  => $attribute_group_id,
                      'account_id'        => $account_id,
                      'attr_code_map'     => 'attr_'.strtolower($attribute_name).'_'.$account_id.'_'.$attribute_group_id,
                  );
									$result = $this->checkAttributeMapEntry($filter_data);

                  if(!$result){
                      if($getAttributeId  = $this->model_catalog_attribute->addAttribute($oc_attributes)){
                          if($getAttributeMapId  = $this->saveAttributeMapEntry(array('oc_attribute_id' => $getAttributeId, 'account_group_id' => $attribute_group_id, 'attr_code_map' => 'attr_'.strtolower($attribute_name).'_'.$account_id.'_'.$attribute_group_id, 'account_id' => $account_id))){

                              array_push($attribute_data, array('attribute_id' => $getAttributeId, 'product_attribute_description' => $oc_attribute_value, 'attribute_map_id' => $getAttributeMapId));
                          }
                      }
                  }else{
                      array_push($attribute_data, array('attribute_id' => $result['attribute_id'], 'product_attribute_description' => $oc_attribute_value, 'attribute_map_id' => $result['id']));
                  }
              }
          }
      }

      return array('attributes_data' => $attribute_data, 'product_dimensions' => $product_dimensions);
  }

 /**
  * checkAttributeMapEntry used to check the amazon product's attributes entry already saved or not to opencart store attributes based on account_id, account_group_id and attr_code_map
  */
  public function checkAttributeMapEntry($data = array()){
      $sql = "SELECT * FROM ".DB_PREFIX."amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute a ON ((aam.oc_attribute_id = a.attribute_id) AND (aam.account_group_id = a.attribute_group_id)) LEFT JOIN ".DB_PREFIX."attribute_description ad ON(a.attribute_id = ad.attribute_id) WHERE ad.language_id = '".(int)$this->config->get('config_language_id')."' ";

      if(!empty($data['account_group_id'])){
				$sql .= " AND aam.account_group_id ='".(int)$data['account_group_id']."' AND a.attribute_group_id = '".(int)$data['account_group_id']."' ";
			}

      if(!empty($data['oc_attribute_id'])){
				$sql .= " AND aam.oc_attribute_id ='".(int)$data['oc_attribute_id']."' AND a.attribute_id = '".(int)$data['oc_attribute_id']."' ";
			}

      if(!empty($data['account_id'])){
				$sql .= " AND aam.account_id ='".(int)$data['account_id']."' ";
			}

			if(!empty($data['attr_code_map'])){
				$sql .= " AND aam.attr_code_map = '".$this->db->escape($data['attr_code_map'])."' ";
			}

      return $result = $this->db->query($sql)->row;
  }
  /**
  * saveAttributeMapEntry used to save the amazon product's attributes entry to opencart store
  */
  public function saveAttributeMapEntry($data = array()){
      $result = false;
      if(isset($data['oc_attribute_id']) && $data['oc_attribute_id']){
          $this->db->query("INSERT INTO ".DB_PREFIX."amazon_attribute_map SET `oc_attribute_id` = '".(int)$data['oc_attribute_id']."', `account_group_id` = '".(int)$data['account_group_id']."', `attr_code_map` = '".$this->db->escape($data['attr_code_map'])."', `account_id` = '".(int)$data['account_id']."' ");

          $result = $this->db->getLastId();
      }
      return $result;
  }

	/**
	* createVariationOpencart function used to manage the amazon product's combinations/variations/options and make array structure like in opencart product's options.
	*/
	public function createVariationOpencart($variation_data = array(), $account_id){
      $languages = $this->model_localisation_language->getLanguages();
      $savedVariation = array();
      try{
          $allVariations = $this->getVariationWithValues($variation_data);

          if(!empty($allVariations) && isset($allVariations['option_value'])) {
              $getVariation = $this->__getVariationEntry(array('variation_name' => $allVariations['option_value']), $account_id);

							if(empty($getVariation)){
									$savedVariation = $this->__save_Variation($allVariations, $account_id);
							}else{
                  $savedVariation = $getVariation;
              }
							$savedVariation['id_type']	= $allVariations['id_type'];
							$savedVariation['id_value']	= $allVariations['id_value'];
					}

      } catch (\Exception $e) {
          $this->log->write('Create variationOpencart : '.$e->getMessage());
      }

      return $savedVariation;
  }

	/**
	 * __save_Variation function used to save the amazon product's combinations/variations/options in opencart store as options.
	 */
	 public function __save_Variation($option = array(), $account_id){
		$amazon_OptVar = array();
		$this->load->model('localisation/language');
		if(!empty($option) && ($getGlobal_Option = $this->Amazonconnector->__getOcAmazonGlobalOption())){
				if (isset($option['option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$getGlobal_Option['option_id'] . "', image = '" . $this->db->escape(html_entity_decode('', ENT_QUOTES, 'UTF-8')) . "', sort_order = '0'");

						$option_value_id = $this->db->getLastId();

						$languages = $this->model_localisation_language->getLanguages();

						foreach ($languages as $langauge_code => $language) {
								$this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = '" . (int)$option_value_id . "', language_id = '".(int)$language['language_id']."', option_id = '" . (int)$getGlobal_Option['option_id'] . "', name = '" . $this->db->escape($option['option_value']) . "'");
						}

						try {
		            $this->db->query("INSERT INTO ".DB_PREFIX."amazon_variation_map SET `variation_id` = '".(int)$getGlobal_Option['option_id']."', `variation_value_id` = '".(int)$option_value_id."',  `value_name` = '".$this->db->escape($option['option_value'])."', `label` = '".serialize($option['option_name'])."', `account_id` = '".(int)$account_id."' ");

								$amazon_OptVar_Id = $this->db->getLastId();
	              if($amazon_OptVar_Id){
	                  $amazon_OptVar = array('id' => $amazon_OptVar_Id, 'variation_id' => $getGlobal_Option['option_id'], 'variation_value_id' => $option_value_id, 'value_name' => $this->db->escape($option['option_value']), 'label' => serialize($option['option_name']), 'id_type' => $option['id_type'], 'id_value' => $option['id_value'] );
	              }
	          } catch (\Exception $e) {
	              $this->log->write('Save Variation to Opencart Store : '.$e->getMessage());
	          }
			 }
      return $amazon_OptVar;
		}
		return $amazon_OptVar;
	}

	/**
  * getVariationWithValues Function used to make variations like $key => $value pair.
  */
  public function getVariationWithValues($variation_data)
  {
      $variations_arr = $opt_value = array();
      $make_option_value  = '';
      foreach ($variation_data as $var_key => $variation) {
          if($var_key == ''  || $variation == ''){
              continue;
          }
          if($var_key != 'Identifiers'){
              $opt_value['option_name'][]     = str_replace('ns2', '', $var_key);

              if(isset($opt_value['option_value']) && $opt_value['option_value'] != ''){
								$opt_value['option_value'] = $make_option_value .' # '. $variation;
							}else{
								$opt_value['option_value']  = $make_option_value = $variation;
							}
              $variations_arr = $opt_value;
          }
					if($var_key == 'Identifiers'){
							if(isset($variation['MarketplaceASIN']['ASIN'])){
									$opt_value['id_type'] = 'ASIN';
									$opt_value['id_value'] = $variation['MarketplaceASIN']['ASIN'];
							}else{
								$getIdType = array_slice($variation['MarketplaceASIN'], 1, 1);
									foreach ($getIdType as $key => $value) {
										$opt_value['id_type'] = strtoupper($key);
										$opt_value['id_value'] = $value;
									}
							}
							$variations_arr = $opt_value;
					}
      }
			if(!isset($variations_arr['option_name']) || $variations_arr['option_value']){
					// $variations_arr['option_name'] = array('N/A');
					// $variations_arr['option_value'] = 'N/A';
			}
      return $variations_arr;
  }

	/**
	* __getVariationEntry Function used to get the amazon saved variations with filters.
	*/
	public function __getVariationEntry($variation_data = array(), $account_id){
		$result = array();
		if($variation_data){
			$getGlobalOption = $this->Amazonconnector->__getOcAmazonGlobalOption();

            $sql = "SELECT avm.* FROM ".DB_PREFIX."amazon_variation_map avm LEFT JOIN ".DB_PREFIX."option_value ov ON((avm.variation_id = ov.option_id) AND (avm.variation_value_id = ov.option_value_id)) LEFT JOIN ".DB_PREFIX."option op ON(avm.variation_id = op.option_id) WHERE avm.account_id = '".(int)$account_id."' ";

            if(!empty($getGlobal_Option['option_id']) && !isset($variation_data['variation_id'])){
                $sql .= " AND avm.variation_id = '".(int)$getGlobalOption['option_id']."' AND ov.option_id = '".(int)$getGlobalOption['option_id']."' AND op.option_id = '".(int)$getGlobalOption['option_id']."' ";
            }else if(!empty($variation_data['variation_id'])){
                 $sql .= " AND avm.variation_id = '".(int)$variation_data['variation_id']."' AND ov.option_id = '".(int)$variation_data['variation_id']."' AND op.option_id = '".(int)$variation_data['variation_id']."' ";
            }

            if(!empty($variation_data['variation_name'])){
                $sql .= " AND avm.value_name = '".$this->db->escape($variation_data['variation_name'])."' ";
            }

            if(!empty($variation_data['id'])){
                $sql .= " AND avm.id = '".$this->db->escape($variation_data['id'])."' ";
            }
            $result = $this->db->query($sql)->row;
		}
		return $result;
	}

	/**
	* getProductOptions Function used to get the amazon imported product's options.
	*/
	public function getProductOptions($data = array())
	{
		$sql = "SELECT pov.*, avm.*, apm.oc_product_id, apm.amazon_product_id, apvm.id_type, apvm.id_value, apvm.sku FROM ".DB_PREFIX."product_option_value pov LEFT JOIN ".DB_PREFIX."product_option po ON ((pov.product_option_id = po.product_option_id) AND (pov.option_id = po.option_id) AND (pov.product_id = po.product_id)) LEFT JOIN ".DB_PREFIX."option_value ov ON ((pov.option_value_id = ov.option_value_id) AND (pov.option_id = ov.option_id)) LEFT JOIN ".DB_PREFIX."option_value_description ovd ON ((ov.option_id = ovd.option_id) AND (ov.option_value_id = ovd.option_value_id)) LEFT JOIN ".DB_PREFIX."amazon_variation_map avm ON ((pov.option_id = avm.variation_id) AND (pov.option_value_id = avm.variation_value_id)) LEFT JOIN ".DB_PREFIX."amazon_product_variation_map apvm ON ((pov.product_option_value_id = apvm.product_option_value_id) AND (pov.option_value_id = apvm.option_value_id) AND (pov.product_id = apvm.product_id)) LEFT JOIN ".DB_PREFIX."amazon_product_map apm ON(pov.product_id = apm.oc_product_id) WHERE ovd.language_id = '".(int)$this->config->get('config_language_id')."' ";

		if(!empty($data['oc_product_id'])){
			$sql .= " AND pov.product_id = '".(int)$data['oc_product_id']."' AND apm.oc_product_id = '".(int)$data['oc_product_id']."' ";
		}

		if(!empty($data['amazon_product_id'])){
			$sql .= " AND apm.amazon_product_id = '".$this->db->escape($data['amazon_product_id'])."' ";
		}

		if(!empty($data['account_id'])){
			$sql .= " AND apm.account_id = '".(int)$data['account_id']."' ";
		}

		if(!empty($data['option_id'])){
			$sql .= " AND pov.option_id = '".(int)$data['option_id']."' AND po.option_id = '".(int)$data['option_id']."' AND ov.option_id = '".(int)$data['option_id']."' AND ovd.option_id = '".(int)$data['option_id']."' AND avm.variation_id = '".(int)$data['option_id']."' ";
		}

		return $this->db->query($sql)->rows;
	}

  public function __saveproductImage($imageURL = false){
		$result = $allowed = array();
		$imageURLNew = urldecode($imageURL);
		
		// if($imageURLNew && $this->checkImageExist($imageURLNew)){
		if($imageURLNew){
			$imageURLUpdate = str_replace('75', '500', $imageURLNew);
			$path = 'image/data/amazon_connector/';
        if (!\Filesystem::isDirExists($path)) {
            \Filesystem::createDir($path);
		}
			$explodeImagePath   = explode('/', $imageURLUpdate);
      $imageName          = end($explodeImagePath);
      $sperateNameExt     = explode('.', $imageName);

      $imgName = '';
      for ($i=0; $i < (count($sperateNameExt) -1) ; $i++) {
          $imgName        .= $sperateNameExt[$i].'.';
      }
      $checkExtention	     = strtolower(end($sperateNameExt));
			$extension_allowed   = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			$filetypes           = explode("\n", $extension_allowed);

      foreach ($filetypes as $filetype) {
			$allowed[]       = trim($filetype);
		}
			if(in_array($checkExtention, $allowed) && ($checkExtention !== 'php')){

				$filePath = html_entity_decode($path.trim($imgName, '.').'.'.$checkExtention);

				$imageContents = \Filesystem::read($imageURLUpdate);
				\Filesystem::setPath($filePath)->put($imageContents);

				$img = \Filesystem::setPath($filePath);

				try {
		            $file = $img->get();
		            $info = $img->getMetadata();

		            if ($info['type'] == 'dir') {
		                return \Filesystem::getUrl('image/no_image.jpg');
		            }

		            $resize = $file->createFromString($img->read())->resize(500, 500)->save($filePath);
		            $imageSource = vsprintf('data:%s;base64, %s', [$info['mimetype'], base64_encode($resize)]);
		        } catch (\Exception $e) {
		            $imageSource = \Filesystem::getUrl('image/no_image.jpg');
		        }

        		// $image = new Image($path.trim($imgName, '.').'.'.$checkExtention);
				// $image->resize(500, 500);
				// $image->save($path.trim($imgName, '.').'.'.$checkExtention);
				return $result   = array('image' => 'image/catalog/amazon_connector/'.trim($imgName, '.').'.'.$checkExtention, 'amazon_image' => $imageURLNew);
			}
		}
	}

	function checkImageExist($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_NOBODY, 1);
	    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    if (curl_exec($ch) !== FALSE) {
	        return true;
	    } else {
	        return false;
	    }
	}

	public function __saveAmazonProduct($data = array()){
		$addResult = array();
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

		$product_id = $this->db->getLastId();
		if($this->config->get('wk_amazon_connector_default_category')) {

			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '".(int)$this->config->get('wk_amazon_connector_default_category')."' ");
		}
		/**
		 * [ price rule changes]
		 * @var [starts]
		 */

		if(!$this->config->get('wk_amazon_connector_price_rules')){
			$price_map_rules = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
      		$param['price'] = $price_map_rules->row['price'];
			$param['product_id'] = $product_id;
			$this->index_import_map($param);
		}
		if($this->config->get('wk_amazon_connector_import_quantity_rule')){
			$price_map_rules = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
     		 $param['quantity'] = $price_map_rules->row['quantity'];
			$param['product_id'] = $product_id;
			$this->load->controller('price_rules_amazon/import_map/quantity_rule',$param);
		}

		 /**
 		 * [price rule changes]
 		 * @var [ends]
 		 */
		  
		if($product_id){
			$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_map SET oc_product_id = '" . (int)$product_id . "', amazon_product_id = '" . $data['ItemID'] . "', `amazon_product_sku` = '".$this->db->escape($data['sku'])."', oc_category_id = '" . (int)$data['category_id'] . "', `amazon_image` = '".$this->db->escape($data['amazon_image'])."',  account_id = '".(int)$data['account_id']."', `sync_source` = 'Amazon Item', added_date = NOW() ");

			$map_product_id = $this->db->getLastId();

			$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_fields SET product_id = '" . (int)$product_id . "', main_product_type = '".$data['amazonProductType']."', `main_product_type_value` = '".$this->db->escape($data['amazonProductTypeValue'])."' ");
		}

		if (isset($data['image'])) {
				$product_img = array();
				if(isset($data['product_image']) && !empty($data['product_image'])){
						$product_img = reset($data['product_image']);
				}
				if(strpos($data['image'], 'no-img-sm') && isset($product_img['image'])){
						$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($product_img['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
				}else{
						$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
				}
		}

		foreach ($data['product_description'] as $language_id => $value) {
            str_replace('"', '', $value['name']);
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape(str_replace('"', '', $value['name'])) . "', description = '" . $this->db->escape($value['description']) . "', meta_keyword = '" . $this->db->escape(str_replace('"', '', $value['meta_title'])) . "'");
		}

		if (isset($data['product_store'])) {
			$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $data['account_id']));
				foreach ($data['product_store'] as $store) {
            if($store['store_id'] == $accountDetails['wk_amazon_connector_default_store']){
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store['store_id'] . "'");
            }
				}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '".(int)$product_option_id."', product_id = '" .(int)$product_id. "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+'");

							$product_option_value_id = $this->db->getLastId();

							if(isset($product_option_value_id)){
									$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$product_id."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$product_option_value['option_value_id']."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."', `account_id` = '".(int)$data['account_id']."' ");
									$addResult[$product_option_value['id_value']]		=		array(
																					'error'    	=> false,
																					'asin'			=> $product_option_value['id_value'],
																					'message'   => "Success: Amazon product's variation Id: ".$product_option_value['id_value']." successfully mapped with the option of Opencart product Id: ".$product_id,
																				);
							}
						}
					}
				}
			}
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$data['category_id']."' ");

		if (isset($data['category_id'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['category_id'] . "'");
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '0'");
			}
		}

		$this->cache->delete('product');
		if(empty($addResult)){
			$addResult[$data['ItemID']] = array(
															'error'    	=> false,
															'asin'			=> $data['ItemID'],
															'message'   => "Success: Amazon product Id: ".$data['ItemID']." successfully mapped with Opencart product Id: ".$product_id,
														);
		}
		return $addResult;
	}

	public function _validateRuleRange($price, $min, $max){
		if($price >= $min && $price <= $max) {
			return 1;
		} else {
			return 0;
		}
	}
	 
	public function index_import_map($params){
		$price_change = 0 ;
		$newprice = 0;
		

		$this->load->model('amazon/price_rule_amazon/import_map');
		$this->_amazonRuleMap = $this->model_amazon_price_rule_amazon_import_map;
		$rule_ranges = $this->_amazonRuleMap->getPriceRules('price');
		foreach ($rule_ranges as $key => $rule_range) {
			if(!$this->_amazonRuleMap->getMapProduct($params['product_id'],'price')){
				if($this->_validateRuleRange($params['price'], $rule_range['price_from'], $rule_range['price_to'])){
					if($rule_range['price_opration']) { // take the precentage of the price of product
						$price_change += ($params['price'] * $rule_range['price_value']) / 100 ;
					} else{
						$price_change += $rule_range['price_value'];
					}

					if($rule_range['price_type']) { // take the precentage of the price of product
						$newprice = $params['price'] + $price_change ;
					} else{
						$newprice = $params['price'] - $price_change ;
					}
					$updateEntry = array(
						'product_id'     => $params['product_id'],
						'price'      => $newprice,
						'change_type'     => $rule_range['price_type'],
						'price_change'   => $price_change,
						'source'         => 'amazon',
						'rule_id'        => $rule_range['id'],
						'rule_type'      =>'price',
					);
					$this->_amazonRuleMap->updateRuleMapProduct($updateEntry);
				}
			}
		}
	}

	public function edit_import_map($params){

		$this->load->model('amazon/price_rule_amazon/import_map');
		$this->_amazonRuleMap = $this->model_amazon_price_rule_amazon_import_map;
		if($this->_amazonRuleMap->getMapProduct($params['product_id'],'price')) {

        	$price_rule = $this->_amazonRuleMap->getPriceRule($params['product_id'],'price');

			if($price_rule['change_type']){
				$orgin_price = $params['price'] + $price_rule['change_type'];
			} else {
				$orgin_price = $params['price'] - $price_rule['change_type'];
			}

			$current_price = $this->_amazonRuleMap->getPrice($params['product_id']);

			if($orgin_price != $current_price){
            	$this->index_import_map($params);
			}
		}
	}
	 public function __editAmazonProduct($data = array()){
		$product_id 	= $data['product_id'];
		$updateResult = array();
		$quantity 		= 0;

		if(!$this->config->get('wk_amazon_connector_price_rules')){
			$price_map_rules = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
      		$param['price'] = $price_map_rules->row['price'];
			$param['product_id'] = $product_id;
			$this->edit_import_map($param);
		}
		if($this->config->get('wk_amazon_connector_import_quantity_rule')){
			$price_map_rules = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id =".(int)$product_id."");
			$param['quantity'] = $price_map_rules->row['quantity'];
			$param['product_id'] = $product_id;
			$this->load->controller('price_rules_amazon/import_map/quantity_rule_edit',$param);
		}

		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE product_id = '".(int)$product_id."' ");


		if($product_id){
			$this->db->query("UPDATE " . DB_PREFIX . "amazon_product_map SET `amazon_product_sku` = '".$this->db->escape($data['sku'])."', oc_category_id = '" . (int)$data['category_id'] . "', `amazon_image` = '".$this->db->escape($data['amazon_image'])."'  WHERE oc_product_id = '".(int)$product_id."' AND amazon_product_id = '".$data['ItemID']."' AND account_id = '".(int)$data['account_id']."' ");
		}

		if (isset($data['image'])) {
			$product_img = array();
			if(isset($data['product_image']) && !empty($data['product_image'])){
					$product_img = reset($data['product_image']);
			}
			if(strpos($data['image'], 'no-img-sm') && isset($product_img['image'])){
					$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($product_img['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
			}else{
					$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "' ");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store['store_id'] . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if(isset($data['product_id'])){
				if (isset($data['product_option'])) {
					foreach ($data['product_option'] as $product_option) {
						if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
								if (isset($product_option['product_option_value'])) {
									$getProductOption = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option WHERE product_id = '" . (int)$data['product_id'] . "' AND option_id = '" . (int)$product_option['option_id'] . "'")->row;

										if(empty($getProductOption)){
												$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$data['product_id'] . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

												$product_option_id = $this->db->getLastId();
										}else{
												$product_option_id = $getProductOption['product_option_id'];
										}

										foreach ($product_option['product_option_value'] as $product_option_value) {
												$checkOptionEntry = $this->db->query("SELECT amo.*, pov.product_option_id FROM ".DB_PREFIX."amazon_product_variation_map amo LEFT JOIN ".DB_PREFIX."product_option_value pov ON ((amo.product_id = pov.product_id) AND (amo.product_option_value_id = pov.product_option_value_id) AND (amo.option_value_id = pov.option_value_id)) WHERE pov.option_id = '".(int)$product_option['option_id']."' AND pov.option_value_id = '".(int)$product_option_value['option_value_id']."' AND amo.option_value_id = '".(int)$product_option_value['option_value_id']."' AND pov.product_id = '".(int)$data['product_id']."' AND amo.id_value = '".$this->db->escape($product_option_value['id_value'])."' AND amo.main_product_type_value = '".$this->db->escape($data['amazonProductTypeValue'])."' ")->row;

												if(empty($checkOptionEntry)){
														$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$data['product_id'] . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+'");

														$product_option_value_id = $this->db->getLastId();

															if(isset($product_option_value_id)){
																	$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$data['product_id']."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$product_option_value['option_value_id']."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."', `account_id` = '".(int)$data['account_id']."' ");

															}
												}else{
														$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '0', points_prefix = '+', weight = '0.00000000', weight_prefix = '+' WHERE product_option_value_id = '".(int)$checkOptionEntry['product_option_value_id']."' AND product_option_id = '" . (int)$product_option_id . "' AND product_id = '" . (int)$data['product_id'] . "' AND option_id = '" . (int)$product_option['option_id'] . "' AND option_value_id = '" . (int)$product_option_value['option_value_id'] . "' ");

														$product_option_value_id = $checkOptionEntry['product_option_value_id'];
												}
												$updateResult[$product_option_value['id_value']]		=		array(
																								'error'    => false,
																								'asin'			=> $product_option_value['id_value'],
																								'message'   => "Success: Amazon product's variation Id: ".$product_option_value['id_value']." successfully updated with the option of Opencart product Id: ".$data['product_id'],
																							);
												$getMappedOption = $this->db->query("SELECT pov.*, amo.id_value FROM ".DB_PREFIX."amazon_product_variation_map amo LEFT JOIN ".DB_PREFIX."product_option_value pov ON(amo.product_option_value_id = pov.product_option_value_id) WHERE amo.product_id = '".(int)$data['product_id']."' AND pov.product_id = '".(int)$data['product_id']."' AND pov.option_id = '".(int)$product_option['option_id']."' AND amo.main_product_type_value = '".$this->db->escape($data['amazonProductTypeValue'])."' ")->rows;
												if(!empty($getMappedOption)){
														foreach ($getMappedOption as $key => $optEntry) {
															$quantity = $quantity + $optEntry['quantity'];
														}
														$this->db->query("UPDATE ".DB_PREFIX."product SET quantity = '".(int)$quantity."' WHERE product_id = '".(int)$data['product_id']."' ");
												}
										}
								}
						 }
					 }
				}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$data['category_id']."' ");

		if (isset($data['category_id'])) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['category_id'] . "'");
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$getProductIMAGE = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' AND image = '" . $this->db->escape($product_image['image']) . "'")->row;
				if(!empty($getProductIMAGE)){
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' AND image = '" . $this->db->escape($product_image['image']) . "' ");
				}
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '0'");
			}
		}

		$this->cache->delete('product');
		if(empty($updateResult)){
				$updateResult[$product_id] = array(
																'error'    	=> false,
																'asin'			=> $data['ItemID'],
																'message'   => "Success: Amazon product Id: ".$data['ItemID']." successfully updated with Opencart product Id: ".$product_id,
															);
		}
		return $updateResult;
	}

	/**
	* to get amazon product specification/attribute
	*/
	public function getAmazonAttributes($data = array()) {
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "amazon_attribute_map aam LEFT JOIN ".DB_PREFIX."attribute a ON((a.attribute_id = aam.oc_attribute_id) AND (a.attribute_group_id = aam.account_group_id)) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";
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

		$query = $this->db->query($sql);

		return $query->rows;
	}
/**
	 * [__saveOpencartProductData assign amazon specification to opencart product]
	 * @param  boolean $product_id [description]
	 * @param  array   $data       [description]
	 * @return [type]              [description]
	 */
	public function __saveOpencartProductData($product_id = false, $data = array(), $type = 'add'){
		if($product_id){
			$this->load->model('localisation/language');
			$languages = $this->model_localisation_language->getLanguages();

			if((isset($data['amazonProductType']) && $data['amazonProductType']) && (isset($data['amazonProductTypeValue']) && $data['amazonProductTypeValue'])){
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_fields WHERE product_id = '".(int)$product_id."' ");

					$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_fields SET `product_id` = '".(int)$product_id."', `main_product_type` = '".strtoupper($data['amazonProductType'])."', `main_product_type_value` = '".trim($data['amazonProductTypeValue'])."' ");
			}

				if(isset($data['amazon_product_specification']) && $data['amazon_product_specification']){
						foreach ($data['amazon_product_specification'] as $product_attribute) {
								if ($product_attribute['attribute_id']) {
										$getAttributeEntry = $this->checkAttributeMapEntry(array('oc_attribute_id' => $product_attribute['attribute_id']));

										if (isset($getAttributeEntry) && $getAttributeEntry) {
												// Removes duplicates
													$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

													foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
															$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

															$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
													}
										}
								}
					 }
				}
				$getAmazonOption = $this->Amazonconnector->__getOcAmazonGlobalOption();
				$getOptionEntry = $this->db->query("SELECT * FROM ".DB_PREFIX."product_option WHERE product_id = '".(int)$product_id."' AND option_id = '".(int)$getAmazonOption['option_id']."' ")->row;
				if(!empty($getOptionEntry)){
                    ecTargetLog( [
                        'backtrace' => debug_backtrace(),
                        'uri' => $_SERVER['REQUEST_URI']
                    ]);

                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '".(int)$getOptionEntry['product_option_id']."' ");
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND product_option_id = '".(int)$getOptionEntry['product_option_id']."' ");
						$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_variation_map WHERE product_id = '".(int)$product_id."' ");
				}

				if (isset($data['amazon_product_variation_value'])) {
						foreach ($data['amazon_product_variation_value'] as $key => $product_option) {
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '1'");

								$product_option_id = $this->db->getLastId();

								if (isset($product_option['option_value']) && !empty($product_option['option_value'])) {
										foreach ($product_option['option_value'] as $key1 => $product_option_value) {
												$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET `product_option_id` = '".(int)$product_option_id. "', `product_id` = '".(int)$product_id."', `option_id` = '".(int)$product_option['option_id']."', `option_value_id` = '".(int)$key1."', `quantity` = '".(int)$product_option_value['quantity']."', `subtract` = '1', `price` = '".(float)$product_option_value['price']."', `price_prefix` = '".$this->db->escape($product_option_value['price_prefix'])."', `points` = '0', `points_prefix` = '+', `weight` = '0', `weight_prefix` = '+'");

												$product_option_value_id = $this->db->getLastId();

												if(isset($product_option_value_id)){
														$this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_variation_map SET `product_id` = '".(int)$product_id."', `product_option_value_id` = '".(int)$product_option_value_id."', `option_value_id` = '".(int)$key1."', `id_type` = '".$this->db->escape($product_option_value['id_type'])."', `id_value` = '".$this->db->escape($product_option_value['id_value'])."', `sku` = '".$this->db->escape($product_option_value['sku'])."', `main_product_type` = '".$this->db->escape($data['amazonProductType'])."', `main_product_type_value` = '".$this->db->escape(isset($data['amazonProductTypeValue']) ? $data['amazonProductTypeValue'] : '')."', `account_id` = '0' ");
												}
										}
								}
						}
				}
		}

		if($type == 'edit'){
				$this->updateRealTimeProduct($product_id);
		}
		return true;
	}

	public function updateRealTimeProduct($product_id = false){
			$product_details = $product_array = array();
			if($product_id){
					$getMapEntry = $this->getProductMappedEntry(array('filter_oc_product_id' => $product_id));

					if(!empty($getMapEntry) && isset($getMapEntry[0]) && $getMapEntry[0]){
							$product_details = $getMapEntry[0];

							/**
							 * [ price rule changes]
							 * @var [starts]
							 */
              if(!isset($this->session->data['isConfigChange'])){
									$getChanges = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "amazon_price_rules_map_product WHERE rule_product_id =".(int)$product_id."");
								  $params['price'] = $product_details['price'];
								  $params['product_id'] = $product_id;
                   // start quantity rule
                // if($this->config->get('wk_amazon_connector_import_quantity_rule') && ($product_details['sync_source'] == 'Amazon Item')){
								// 	$param_quanity['quantity']   = $product_details['quantity'];
								//   $param_quanity['product_id'] = $product_id;
								// 	$this->load->controller('price_rules_amazon/import_map/realtime_update_quantity',$param_quanity);
								// }
                   // end quantity rule
								  if(!$this->config->get('wk_amazon_connector_price_rules') && ($product_details['sync_source'] == 'Amazon Item')){
									   if ($getChanges->num_rows) {
											  if ($getChanges->row['change_type']) {
													 $product_details['price'] -= $getChanges->row['change_price'];
											  } else {
													$product_details['price'] += $getChanges->row['change_price'];
											  }
									   }
										 $params['price'] = $product_details['price'];
										 $this->load->controller('price_rules_amazon/import_map/realtime_update',$params);
								 } else if($this->config->get('wk_amazon_connector_price_rules') && ($product_details['sync_source'] == 'Opencart Item')) {
										 $this->load->model('amazon/price_rule_amazon/export_map');
										 if ($getChanges->num_rows) {
												$this->model_amazon_price_rule_amazon_export_map->_realtimeUpdatePriceRule($params);
										 } else {
											 $product['price'] = $this->model_amazon_price_rule_amazon_export_map->_applyPriceRule($params);
										 }
								 }
							} else {
								unset($this->session->data['isConfigChange']);
							}

							 /**
					 		 * [price rule changes]
					 		 * @var [ends]
					 		 */

							if($product_details['sync_source'] == 'Amazon Item' && $this->config->get('wk_amazon_connector_import_update') != 'on'){
									return true;
							}

							if($product_details['sync_source'] == 'Opencart Item' && $this->config->get('wk_amazon_connector_export_update') != 'on'){
									return true;
							}

							$UpdateQuantityArray = $UpdatePriceArray = $UpdateImageArray = $DeleteProductArray = array();

							if(isset($product_details['account_id'])){
									$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $product_details['account_id']));

									if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

											if ((isset($product_details['main_product_type']) && $product_details['main_product_type']) && (isset($product_details['main_product_type_value']) && $product_details['main_product_type_value'])) {

												if(isset($product_details['image']) && \Filesystem::isExists('image/' . $product_details['image'])){
													$image = \Filesystem::getUrl('image/'.$product_details['image']);
												}else{
													$image = \Filesystem::getUrl('image/placeholder.png');
												}

													if($getCombinations = $this->Amazonconnector->_getProductVariation($product_details['oc_product_id'], $type = 'amazon_product_variation_value')){
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

																				//Update qty of amazon product
																				$UpdateQuantityArray[] = array(
																																	'sku' => $product_data['sku'],
																																	'qty' => $product_data['quantity'],
																																);

																				//Update price of amazon product
																				$UpdatePriceArray[] = array(
																															 'sku' 							=> $product_data['sku'],
																															 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																															 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																														 );
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
															$product_data['sku'] 						= (!empty($product_details['amazon_product_sku']) ? $product_details['amazon_product_sku'] : (!empty($product_details['sku']) ? $product_details['sku'] : 'oc_prod_'.$product_details['oc_product_id']));

															//Update qty of amazon product
															$UpdateQuantityArray[] = array(
																												'sku' => $product_data['sku'],
																												'qty' => $product_data['quantity'],
																											);

															//Update price of amazon product
															$UpdatePriceArray[] = array(
																										 'sku' 							=> $product_data['sku'],
																										 'currency_symbol' 	=> $accountDetails['wk_amazon_connector_currency_code'],
																										 'price' 						=> (float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'],
																									 );

 														 //Update image of amazon product
 															$UpdateImageArray[] = array(
 																										 'sku' 							=> $product_data['sku'],
 																										 'image_url' 				=> $product_data['image'],
 																									 );
														$product_array[] = $product_data;
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
													}
											}
									}
							}
					}
			}
	}

	public function deleteProductMapEntry($data = array()){
			if((isset($data['product_id']) && $data['product_id']) && (isset($data['id_value']) && $data['id_value']) && (isset($data['account_id']) && $data['account_id']) ){
					$this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_map WHERE oc_product_id = '".(int)$data['product_id']."' AND account_id = '".(int)$data['account_id']."' ");
			}
			return true;
	}
	public function autocomplete($data) {

			 $sql = "SELECT pd.name,pd.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)  WHERE p.product_id NOT IN (SELECT product_id FROM ".DB_PREFIX."amazon_product_fields ) AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			if (isset($data['filter_name']) && $data['filter_name']!='') {
					 $sql .= " AND pd.name LIKE '" . $data['filter_name'] . "%'";
			}

			$sql .= ' LIMIT 0, 20';
			 $query = $this->db->query($sql);

			 return $query->rows;
		}
	public function getOcUnmappedProducts($data = array()) {
		$sql = "SELECT p.*,pd.*, cd.name as category_name, cd.category_id FROM " . DB_PREFIX . "amazon_product_fields apf LEFT JOIN ".DB_PREFIX."product p ON(p.product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' ";

		if(!empty($data['filter_oc_prod_id'])){
			$sql .= " AND p.product_id ='".(int)$data['filter_oc_prod_id']."' ";
		}

		if(!empty($data['filter_oc_prod_name'])){
			$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['filter_oc_prod_name']))."%' ";
		}

		if(!empty($data['filter_oc_cat_name'])){
			$sql .= " AND cd.name LIKE '%".$this->db->escape($data['filter_oc_cat_name'])."%' ";
		}

		if (isset($data['filter_oc_price']) && !is_null($data['filter_oc_price'])) {
		$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_oc_price']) . "%'";
		}

		if (isset($data['filter_oc_quantity']) && !is_null($data['filter_oc_quantity'])) {
		$sql .= " AND p.quantity = '" . (int)$data['filter_oc_quantity'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

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

	public function getAllOcUnmappedProducts($start = 0, $length = 10, $filterData = null, $orderColumn = "oc_product_id") {
		$sql_str = "SELECT p.product_id as oc_product_id, p.*,pd.*, cd.name as category_name, cd.category_id FROM " . DB_PREFIX . "amazon_product_fields apf LEFT JOIN ".DB_PREFIX."product p ON(p.product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' ";
		
		$total = $this->db->query($sql_str)->num_rows;
		$where = "";
		if (!empty($filterData['search'])) {
         $where .= "(LCASE(pd.name) like '%" . $this->db->escape($filterData['search']) . "%'
                  OR cd.name like '%".$this->db->escape($filterData['search'])."%'
                  OR p.price like '%".$this->db->escape($filterData['search'])."%'
                  OR p.quantity like '%".$this->db->escape($filterData['search'])."%'
                  ) ";
         $sql_str .= " AND " . $where;
		}
		$sql_str .= " GROUP BY p.product_id";
		
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
		 * [getTotalEbayAccount to get the total number of ebay account]
		 * @param  array  $data [filter data array]
		 * @return [type]       [total number of ebay account records]
		 */
		public function getTotalOcUnmappedProducts($data = array()) {

	  		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "amazon_product_fields apf LEFT JOIN ".DB_PREFIX."product p ON(p.product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE  pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' ";

	  		if(!empty($data['filter_oc_prod_id'])){
	  			$sql .= " AND p.product_id ='".(int)$data['filter_oc_prod_id']."' ";
	  		}

	  		if(!empty($data['filter_oc_prod_name'])){
	  			$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['filter_oc_prod_name']))."%' ";
	  		}

	  		if(!empty($data['filter_oc_cat_name'])){
	  			$sql .= " AND cd.name LIKE '%".$this->db->escape($data['filter_oc_cat_name'])."%' ";
	  		}

	      if (isset($data['filter_oc_price']) && !is_null($data['filter_oc_price'])) {
	        $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_oc_price']) . "%'";
	      }

	      if (isset($data['filter_oc_quantity']) && !is_null($data['filter_oc_quantity'])) {
	        $sql .= " AND p.quantity = '" . (int)$data['filter_oc_quantity'] . "'";
	      }

	  		$query = $this->db->query($sql);

	  		return $query->row['total'];
	  }
		/**
		 * [map_product // Suppose any product already Opencart cart store and amazon also end then if you import product then same product list two times so avoid this issue we map the product]
		 * @return [type] [description]
		 */
		public function map_product($data) {
      $sql="UPDATE ".DB_PREFIX."amazon_product_map SET oc_product_id=".(int)$data['opencart_product_id']." WHERE id=".(int)$data['product_map_id']."";
			$this->db->query($sql);
		  $this->deleteOpencartProduct($data['opencart_map_product_id']);

		}
		public function get_product_record($data) {
			$sql = "SELECT  pd.name as product_name, p.product_id, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."product p  LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map ) ";

			if(!empty($data['opencart_map_product_id'])){
				$sql .= " AND p.product_id != '".$data['opencart_map_product_id']."'";
			}
			if(!empty($data['opencart_product_name'])){
				$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['opencart_product_name']))."%' ";
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
		public function deleteOpencartProduct($product_id) {
			$this->db->query("DELETE FROM ".DB_PREFIX."product WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_description WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_option WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_attribute WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_option_value WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_image WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_to_category WHERE product_id = '".(int)$product_id."' ");
			$this->db->query("DELETE FROM ".DB_PREFIX."product_to_store WHERE product_id = '".(int)$product_id."' ");
		}
		public function csvSave($data) {
			$this->load->language('amazon_map/data_map_csv');
			$error_reporting = array();
			$identifier = array(
				'ASIN',
				'EAN',
				'GTIN',
				'UPC'
			);
      foreach($data['csvValues'] as $product) {
				$getproduct = $this->db->query("SELECT product_id FROM ".DB_PREFIX."product where product_id='".$product['product_id']."'")->row;
        if(!isset($getproduct['product_id'])){
           $error_reporting[$product['product_id']]['error']['product'] = $this->language->get('error_product');
					 continue;
				}
				$check_asin = $this->checkAsinCsv($product);
				if(isset($check_asin['error'])) {
					$error_reporting[$product['product_id']]['error']['product'] = $this->language->get('exist_asin') .$check_asin['product_id'];
				 continue;
				}
				if(in_array(strtoupper($product['product_identification']), $identifier)) {
					 $this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_fields WHERE product_id = '".(int)$product['product_id']."' ");

				  $this->db->query("INSERT INTO ".DB_PREFIX."amazon_product_fields SET `product_id` = '".(int)(int)$product['product_id']."', `main_product_type` = '".strtoupper($product['product_identification'])."', `main_product_type_value` = '".trim($product['product_identification_value'])."' ");
          // if(isset($product['specification_name']) && $product['specification_name']!='') {
          //   $this->manageSpecification($product);
					// }
					// if(isset($product['variation_name']) && $product['variation_name']!='') {
          //   $this->manageVariation($product);
					// }

         $error_reporting[$product['product_id']]['success'] = $this->language->get('text_success');
				} else {
					$error_reporting[$product['product_id']]['error']['identification'] = $this->language->get('error_identification');
				}
			}
			return $error_reporting;
		}
		// public function manageSpecification($data) {
		// 	$error 								= array();
		// 	$specification_names   = explode('||', $data['specification_name']);
		// 	$specification_values  = explode('||', $data['specification_value']);
    //   if((count($specification_names)> 0) && (count($specification_names) == count($specification_values))) {
		// 		 foreach($specification_names as $key=>$specification_name) {
		//
		// 		 }
		//
		// 	} else {
 		// 		$error[$data['product_id']]['error']['specification'] = 'Specification data not formated please checked it.';
		// 	}
		// }
		// public function manageVariation() {
		//
		// }
		public function checkAsin($data) {
			$product_fields = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_fields WHERE main_product_type = '".$data['amazonProductType']."' AND  main_product_type_value=  '".$data['amazonProductTypeValue']."' AND product_id !='".(int)$data['product_id']."'")->rows;
			$map_product = $this->db->query("SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map WHERE amazon_product_id=  '".$data['amazonProductTypeValue']."' AND oc_product_id !='".(int)$data['product_id']."'")->rows;
			$variation_product = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_variation_map WHERE id_value=  '".$data['amazonProductTypeValue']."' AND product_id !='".(int)$data['product_id']."'")->rows;


			if(count($product_fields) > 0) {
					return array(
						'error'      => 'product_field',
						'product_id' => $product_fields[0]['product_id'],
					);

			}
			if (count($map_product) > 0) {
				return array(
					'error'      => 'product_map',
					'product_id' => $map_product[0]['oc_product_id'],
				);
			}
			if (count($variation_product) > 0) {
			 return array(
				 'error'      => 'product_variation',
				 'product_id' => $variation_product[0]['product_id'],
			 );
		 }
			if(isset($data['amazon_product_variation_value'])) {
				foreach($data['amazon_product_variation_value'] as $variation_datas) {
					 foreach ($variation_datas['option_value'] as $variation_data) {

						 $variation_product = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_variation_map WHERE id_value=  '".$variation_data['id_value']."' AND product_id !='".(int)$data['product_id']."'")->rows;
						 if (count($variation_product) > 0) {
							return array(
								'error'      => 'product_variation',
								'product_id' => $variation_product[0]['product_id'],
							);
						}
						$product_fields = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_fields WHERE main_product_type = '".$variation_data['id_type']."' AND  main_product_type_value=  '".$variation_data['id_value']."' AND product_id !='".(int)$data['product_id']."'")->rows;
						$map_product = $this->db->query("SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map WHERE amazon_product_id=  '".$variation_data['id_value']."' AND oc_product_id !='".(int)$data['product_id']."'")->rows;


						if(count($product_fields) > 0) {
								return array(
									'error'      => 'product_field',
									'product_id' => $product_fields[0]['product_id'],
								);

						}
						if (count($map_product) > 0) {
							return array(
								'error'      => 'product_map',
								'product_id' => $map_product[0]['oc_product_id'],
							);
						}
					 }
				}
			}


		}
		public function checkAsinCsv($data) {



					 $variation_product = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_variation_map WHERE id_value=  '".$data['product_identification']."' AND product_id !='".(int)$data['product_id']."'")->rows;
					 if (count($variation_product) > 0) {
						return array(
							'error'      => 'product_variation',
							'product_id' => $variation_product[0]['product_id'],
						);
					}
					$product_fields = $this->db->query("SELECT product_id FROM ".DB_PREFIX."amazon_product_fields WHERE main_product_type = '".$data['product_identification']."' AND  main_product_type_value=  '".$data['product_identification_value']."' AND product_id !='".(int)$data['product_id']."'")->rows;
					$map_product = $this->db->query("SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map WHERE amazon_product_id=  '".$data['product_identification_value']."' AND oc_product_id !='".(int)$data['product_id']."'")->rows;


					if(count($product_fields) > 0) {
							return array(
								'error'      => 'product_field',
								'product_id' => $product_fields[0]['product_id'],
							);

					}
					if (count($map_product) > 0) {
						return array(
							'error'      => 'product_map',
							'product_id' => $map_product[0]['oc_product_id'],
						);
					}

		}

}
