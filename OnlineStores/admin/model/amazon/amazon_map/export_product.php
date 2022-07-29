<?php

class ModelAmazonAmazonMapExportProduct extends Model {

  public function getOcUnmappedProducts($data = array()) {
  		$sql = "SELECT p.*,pd.*, cd.name as category_name, cd.category_id FROM " . DB_PREFIX . "product p LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map )  AND pd.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_review ) AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' ";

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

	public function getAllUnmappedProducts($start = 0, $length = 10, $filterData = null, $orderColumn = "p_product_id"){
		$sql_str = "SELECT p.*, p.product_id as p_product_id,pd.*, cd.name as category_name, cd.category_id FROM " . DB_PREFIX . "product p LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map )  AND pd.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_review ) AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' and cd.language_id = '1'";
		
		$total = $this->db->query($sql_str)->num_rows;
		$where = "";
		if (!empty($filterData['search'])) {
			$where .= "(p_product_id like '%" . $this->db->escape($filterData['search']) . "%'
						OR LCASE(pd.name) like '%".$this->db->escape($filterData['search'])."%'
						OR cd.name like '%".$this->db->escape($filterData['search'])."%'
						OR p.price like '%".$this->db->escape($filterData['search'])."%'
						OR p.quantity like '%".$this->db->escape($filterData['search'])."%') ";
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
  		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN ".DB_PREFIX."product_description pd ON(p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON(p.product_id = p2c.product_id) LEFT JOIN ".DB_PREFIX."category_description cd ON(p2c.category_id = cd.category_id) LEFT JOIN ".DB_PREFIX."amazon_product_map wk_map ON(p.product_id = wk_map.oc_product_id) WHERE p.product_id NOT IN (SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map ) AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p.status = '1' ";

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


// ********** To export opencart store product at amazon store (with or without variations) **********
    public function startExportProcess($data = array(), $accountId)
    {
        $final_product_data = $UpdateQuantityArray = $addProductArray = $notSyncIds = $addRelationshipArray = array();
        $accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $accountId));
        foreach ($data as $key => $product) {

            /**
             * [ price rule changes]
             * @var [starts]
             */


            if ($this->config->get('wk_amazon_connector_price_rules')) {
                $params['price'] = round($product['price'], 2);
                $params['product_id'] = $product['product_id'];
                $this->load->model('amazon/price_rule_amazon/export_map');
                $product['price'] = $this->model_amazon_price_rule_amazon_export_map->_applyPriceRule($params);
            }


            /**
             * [price rule changes]
             * @var [ends]
             */
            // start quantity price for normal product
            if ($this->config->get('wk_amazon_connector_export_quantity_rule')) {
                $params['quantity'] = $product['quantity'];
                $params['product_id'] = $product['product_id'];
                $this->load->model('amazon/price_rule_amazon/export_map');
                $product['quantity'] = $this->model_amazon_price_rule_amazon_export_map->_applyQuantityRule($params);
            }


            if ((isset($product['main_type']) && $product['main_type']) && (isset($product['main_value']) && $product['main_value'])) {

                if (isset($product['combinations']) && !empty($product['combinations'])) {
                    foreach ($product['combinations'] as $option_id => $option_value_array) {
                        $total_combinations = count($option_value_array);
                        foreach ($option_value_array as $option_value_id => $option_value_data) {
                            $product_data = array();
                            // start quantity price
                            if ($this->config->get('wk_amazon_connector_export_quantity_rule') && isset($option_value_data['quantity'])) {
                                $params['quantity'] = $option_value_data['quantity'];
                                $params['product_id'] = $product['product_id'];
                                $this->load->model('amazon/price_rule_amazon/export_map');
                                $option_value_data['quantity'] = $this->model_amazon_price_rule_amazon_export_map->_applyQuantityRule($params);
                            }
                            // end quantity price
                            if (isset($option_value_data['price_prefix']) && $option_value_data['price_prefix'] == '+') {
                                $product_data['price'] = round((float)$product['price'] + (float)$option_value_data['price'], 2);
                            } else {
                                $product_data['price'] = round((float)$product['price'] - (float)$option_value_data['price'], 2);
                            }
                            if (isset($option_value_data['quantity']) && $option_value_data['quantity']) {
                                $product_data['quantity'] = $option_value_data['quantity'];
                            } else {
                                $product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
                            }
                            $product_data['product_id'] = $product['product_id'];
                            $product_data['name'] = $option_value_data['name'];
                            $product_data['id_type'] = $option_value_data['id_type'];
                            $product_data['id_value'] = $option_value_data['id_value'];
                            $product_data['sku'] = $option_value_data['sku'];
                            $product_data['image'] = $product['image'];

                            //Add new product on Amazon store
                            $addProductArray[$product_data['sku']] = array(
                                'sku' => $product_data['sku'],
                                'exportProductType' => $product_data['id_type'],
                                'exportProductTypeValue' => $product_data['id_value'],
                                'name' => $product_data['name'],
                                'description' => strip_tags(htmlspecialchars_decode($product['description'] ? $product['description'] : $product_data['name'])),
                                'brand' => 'super_brand',
                            );

                            //Update qty of amazon product
                            $UpdateQuantityArray[$product_data['sku']] = array(
                                'sku' => $product_data['sku'],
                                'qty' => $product_data['quantity'],
                            );

                            //Update price of amazon product
                            $UpdatePriceArray[$product_data['sku']] = array(
                                'sku' => $product_data['sku'],
                                'currency_symbol' => $accountDetails['wk_amazon_connector_currency_code'],
                                'price' => round(($product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate']), 2),
                            );
                            //Update image of amazon product
                            $UpdateImageArray[] = array(
                                'type' => 'Main',
                                'sku' => $product_data['sku'],
                                'image_url' => $product_data['image'],
                            );

                            $productImages = $this->getProductRelatedImages($product_data['product_id']);
                            if (!empty($productImages)) {
                                for ($i = 0; $i < 8; $i++) {
                                    if (isset($productImages[$i]['image']) && \Filesystem::isExists('image/' . $productImages[$i]['image'])) {
                                        $UpdateImageArray[] = array(
                                            'type' => 'PT' . ($i + 1),
                                            'sku' => $product_data['sku'],
                                            'image_url' => \Filesystem::getUrl('image/' . $productImages[$i]['image'])
                                        );
                                    }
                                }
                            }
                        }
                    } // combination foreach loop

                    //final data of submit feed data
                    $final_product_data[] = array('product_id' => $product['product_id'],
                        'account_id' => $accountId,
                        'name' => $product['name'],
                        'category_id' => $this->config->get('wk_amazon_connector_default_category'),
                        'id_type' => $product['main_type'],
                        'id_value' => $product['main_value'],
                        'sku' => (!empty($product['sku']) ? $product['sku'] : 'oc_prod_' . $product['product_id']),

                    );
                } else {
                    $product_data = array();
                    $product_data['product_id'] = $product['product_id'];
                    $product_data['price'] = round((float)$product['price'], 2);
                    $product_data['quantity'] = (!empty($product['quantity']) ? $product['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
                    $product_data['name'] = $product['name'];
                    $product_data['id_type'] = $product['main_type'];
                    $product_data['id_value'] = $product['main_value'];
                    $product_data['sku'] = (!empty($product['sku']) ? $product['sku'] : 'oc_prod_' . $product['product_id']);
                    $product_data['description'] = strip_tags(htmlspecialchars_decode($product['description']));
                    $product_data['account_id'] = $accountId;
                    $product_data['category_id'] = $this->config->get('wk_amazon_connector_default_category');
                    $product_data['image'] = $product['image'];

                    //Add new product on Amazon store
                    $addProductArray[$product_data['sku']] = array(
                        'sku' => $product_data['sku'],
                        'exportProductType' => $product_data['id_type'],
                        'exportProductTypeValue' => $product_data['id_value'],
                        'name' => $product_data['name'],
                        'description' => str_replace('&nbsp;', '', htmlspecialchars_decode($product_data['description'])),
                        'brand' => 'super_brand',
                    );

                    //Update qty of amazon product
                    $UpdateQuantityArray[$product_data['sku']] = array(
                        'sku' => $product_data['sku'],
                        'qty' => $product_data['quantity'],
                    );

                    //Update price of amazon product
                    $UpdatePriceArray[$product_data['sku']] = array(
                        'sku' => $product_data['sku'],
                        'currency_symbol' => $accountDetails['wk_amazon_connector_currency_code'],
                        'price' => round((float)$product_data['price'] * $accountDetails['wk_amazon_connector_currency_rate'], 2),
                    );

                    //Update image of amazon product
                    $UpdateImageArray[] = array(
                        'type' => 'Main',
                        'sku' => $product_data['sku'],
                        'image_url' => $product_data['image'],
                    );

                    $productImages = $this->getProductRelatedImages($product_data['product_id']);
                    if (!empty($productImages)) {
                        for ($i = 0; $i < 8; $i++) {
                            if (isset($productImages[$i]['image']) && \Filesystem::isExists('image/' . $productImages[$i]['image'])) {
                                $UpdateImageArray[] = array(
                                    'type' => 'PT' . ($i + 1),
                                    'sku' => $product_data['sku'],
                                    'image_url' => \Filesystem::getUrl('image/' . $productImages[$i]['image'])
                                );
                            }
                        }
                    }
                    //final data of submit feed data
                    $final_product_data[] = $product_data;
                } // combination if condition
            } else {
                $notSyncIds[$product['product_id']] = array('product_id' => $product['product_id'], 'name' => $product['name'], 'message' => 'Warning: ASIN Number is missing for ExpandCart product : <b>' . $product['name'] . '</b> !');
            }
        } // Product foreach loop

        if (!empty($final_product_data)) {
            $this->Amazonconnector->product['ActionType'] = 'AddProduct';
            $this->Amazonconnector->product['ProductData'] = $addProductArray;
            $product_created = $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_DATA_', $accountId);

            if (isset($product_created['success']) && $product_created['success']) {
                $this->Amazonconnector->product['ActionType'] = 'UpdateQuantity';
                $this->Amazonconnector->product['ProductData'] = $UpdateQuantityArray;
                $this->Amazonconnector->submitFeed($feedType = '_POST_INVENTORY_AVAILABILITY_DATA_', $accountId);

                $this->Amazonconnector->product['ActionType'] = 'UpdatePrice';
                $this->Amazonconnector->product['ProductData'] = $UpdatePriceArray;
                $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_PRICING_DATA_', $accountId);

                $this->Amazonconnector->product['ActionType'] = 'UpdateImage';
                $this->Amazonconnector->product['ProductData'] = $UpdateImageArray;
                $this->Amazonconnector->submitFeed($feedType = '_POST_PRODUCT_IMAGE_DATA_', $accountId);

                foreach ($final_product_data as $product_details) {
                    $product_details['feed_id'] = $product_created['feedSubmissionId'];
                    $this->saveExportMapEntry($product_details);
                }

                $result_data = array('status' => true,
                    'feedSubmissionId' => $product_created['feedSubmissionId'],
                    'success' => $final_product_data,
                    'error' => $notSyncIds,
                );
            } else {

                if (isset($product_created['comment']) && $product_created['comment']) {
                    $result_data = array('status' => false,
                        'error' => array($product_created['comment']));
                } else {
                    $result_data = array('status' => false,
                        'error' => array($this->language->get('error_occurs')));
                }
            }
        } else {
            $result_data = array('status' => false,
                'error' => $notSyncIds,);
        }

        return $result_data;
    }

	public function getProductRelatedImages($product_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

  // ********** To save the map entry after export opencart store product to amazon store **********
	public function saveExportMapEntry($product_details = array()){
			if(isset($product_details['feed_id']) && $product_details['feed_id'] && isset($product_details['account_id'])){
					$this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_review SET oc_product_id = '" . (int)$product_details['product_id'] . "', oc_category_id = '" . (int)$product_details['category_id'] . "',   account_id = '".(int)$product_details['account_id']."', `feed_id` = '".$this->db->escape($product_details['feed_id'])."', added_date = NOW() ");

					$this->db->query("UPDATE ".DB_PREFIX."amazon_product_variation_map SET account_id = '".(int)$product_details['account_id']."' WHERE `main_product_type_value` = '".$this->db->escape($product_details['id_value'])."' ");
			}
	}
}
