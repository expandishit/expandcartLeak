<?php

class ModelAmazonMapProduct extends Model {

   public function updateProductStockAtAmazon($order_info, $order_status_id, $comment){
     $product_details = $product_array = array();
     $product_id      = '';
     $order_id        = $order_info['order_id'];

     $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'")->rows;

     foreach ($order_product_query as $key => $ordered_product) {

       $product_id = $ordered_product['product_id'];

       if(isset($product_id) && $product_id){
 					$getMapEntry = $this->getProductMappedEntry(array('filter_oc_product_id' => $product_id));

 					if(!empty($getMapEntry) && isset($getMapEntry[0]) && $getMapEntry[0]){
 							$product_details = $getMapEntry[0];

							$this->db->query("UPDATE ".DB_PREFIX."amazon_product_map SET `sync_status` = '1' WHERE oc_product_id = '".(int)$product_id."'");

 							if($product_details['sync_source'] == 'Amazon Item' && $this->config->get('wk_amazon_connector_import_update') != 'on'){
 									return true;
 							}

 							if($product_details['sync_source'] == 'Opencart Item' && $this->config->get('wk_amazon_connector_export_update') != 'on'){
 									return true;
 							}

 							$UpdateQuantityArray = array();

 							if(isset($product_details['account_id'])){
 									$accountDetails = $this->Amazonconnector->getAccountDetails(array('account_id' => $product_details['account_id']));

 									if(isset($accountDetails['wk_amazon_connector_marketplace_id']) && $accountDetails['wk_amazon_connector_marketplace_id']){

 											if ((isset($product_details['main_product_type']) && $product_details['main_product_type']) && (isset($product_details['main_product_type_value']) && $product_details['main_product_type_value'])) {

                          $getCombinations = $this->Amazonconnector->_getProductVariation($product_details['oc_product_id'], $type = 'amazon_product_variation_value');

                          if(!empty($getCombinations)){
                            foreach ($getCombinations as $option_id => $combination_array) {
                                 $total_combinations = count($combination_array);
                                 foreach ($combination_array['option_value'] as $key => $combination_value) {
                                     $product_data = array();

                                      if(isset($combination_value['quantity']) && $combination_value['quantity']){
                                          $product_data['quantity'] = $combination_value['quantity'];
                                      }else{
                                          $product_data['quantity'] = ($this->config->get('wk_amazon_connector_default_quantity') / $total_combinations);
                                      }
                                      $product_data['sku'] 				 = $combination_value['sku'];

                                      //Update qty of amazon product
                                      $UpdateQuantityArray[$product_data['sku']] = array(
                                                                'sku' => $product_data['sku'],
                                                                'qty' => $product_data['quantity'],
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
 															$product_data['id_value'] 			= $product_details['main_product_type_value'];
 															$product_data['quantity'] 			= (!empty($product_details['quantity']) ? $product_details['quantity'] : $this->config->get('wk_amazon_connector_default_quantity'));
 															$product_data['sku'] 						= (!empty($product_details['amazon_product_sku']) ? $product_details['amazon_product_sku'] : $product_details['sku']);

 															//Update qty of amazon product
 															$UpdateQuantityArray[$product_data['sku']] = array(
 																												'sku' => $product_data['sku'],
 																												'qty' => $product_data['quantity'],
 																											);
 														$product_array[] = $product_data;
 													}
 											}

 											if(!empty($product_array)){
 													$this->Amazonconnector->product['ActionType']  = 'UpdateQuantity';
 													$this->Amazonconnector->product['ProductData'] = $UpdateQuantityArray;

                          $product_updated =  $this->Amazonconnector->submitFeed('_POST_INVENTORY_AVAILABILITY_DATA_',  $accountDetails['id']);

                          foreach ($product_array as $key => $oc_product) {
                              $this->db->query("UPDATE ".DB_PREFIX."amazon_product_map SET `feed_id` = '".$this->db->escape($product_updated['feedSubmissionId'])."', `sync_status` = '0' WHERE oc_product_id = '".(int)$oc_product['product_id']."'");
                          }

 													if (isset($product_updated['success']) && $product_updated['success']) {
 														return $product_updated;
 													} else {
                            return 0;
                          }
 											}
 									}
 							}
 					}
 			}

     }

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
}
