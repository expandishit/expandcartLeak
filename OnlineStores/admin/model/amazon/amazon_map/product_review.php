<?php

class ModelAmazonAmazonMapProductReview extends Model {
  public function getProductReview($data) {
        $sql = "SELECT apr.*, apf.*, apr.id as map_id, pd.name as product_name, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_review apr LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apr.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apr.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apr.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";


    		if(!empty($data['filter_oc_prod_id_review'])){
    			$sql .= " AND apr.oc_product_id = '".(int)$data['filter_oc_prod_id_review']."' AND p.product_id = '".(int)$data['filter_oc_prod_id_review']."' ";
    		}

    		if(!empty($data['filter_oc_prod_name_review'])){
    			$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['filter_oc_prod_name_review']))."%' ";
    		}

    		if(isset($data['account_id']) && $data['account_id']){
          $sql .= " AND apr.account_id = '".(int)$data['account_id']."' ";
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

  public function getAllProductReview($start = 0, $length = 10, $filterData = null, $orderColumn = "product_id") {
      
      $sql_str = "SELECT apr.*, apf.*, apr.id as map_id, pd.name as product_name, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_review apr LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apr.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apr.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apr.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";

      $total = $this->db->query($sql_str)->num_rows;
      $where = "";
      if (!empty($filterData['search'])) {
        $where .= "(LCASE(pd.name) like '%" . $this->db->escape($filterData['search']) . "%') ";
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

  public function getProductReviewTotal($data) {
        $sql = "SELECT apr.*, apf.*, apr.id as map_id, pd.name as product_name, p.model, p.price, p.quantity, p.image FROM ".DB_PREFIX."amazon_product_review apr LEFT JOIN ".DB_PREFIX."amazon_product_fields apf ON (apr.oc_product_id = apf.product_id) LEFT JOIN ".DB_PREFIX."product p ON (apr.oc_product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON(apr.oc_product_id = pd.product_id) WHERE p.status = '1' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' ";


    		if(!empty($data['filter_oc_prod_id_review'])){
    			$sql .= " AND apr.oc_product_id = '".(int)$data['filter_oc_prod_id_review']."' AND p.product_id = '".(int)$data['filter_oc_prod_id_review']."' ";
    		}

    		if(!empty($data['filter_oc_prod_name_review'])){
    			$sql .= " AND LCASE(pd.name) LIKE '".$this->db->escape(strtolower($data['filter_oc_prod_name_review']))."%' ";
    		}

    		if(isset($data['account_id']) && $data['account_id']){
          $sql .= " AND apr.account_id = '".(int)$data['account_id']."' ";
        }

        return count($this->db->query($sql)->rows);
  }

  public function getFeedSubmissionResult($feed_id, $account_id, $product_id) {
      $result = $this->Amazonconnector->getFeedSubmissionResult($feed_id, $account_id);
      if(isset($result['success']) && $result['success']) {

            $data               =$result['data']['Message']['ProcessingReport'];
            $data['status']     = 1;
            $data['product_id']  = $product_id;
            if(isset($data['ProcessingSummary']['MessagesProcessed']) && $data['ProcessingSummary']['MessagesProcessed'] >0) {
                if($this->mapProduct($feed_id,$product_id,$account_id)){
                  $data['move_map'] = true;
                }
            }
        return $data;
      } else {
         $result['product_id'] = $product_id;
        return $result;
      }
  }
  public function mapProduct($feed_id,$product_id,$account_id) {

      if($this->checkProduct($product_id,$account_id)) {
          $data = $this->getProductReview(['filter_oc_prod_id_review'=>$product_id]);
          if(isset($data[0]['oc_product_id'])) {

              $this->db->query("INSERT INTO " . DB_PREFIX . "amazon_product_map SET oc_product_id = '" . (int)$product_id . "', amazon_product_id = '" . $data[0]['main_product_type_value'] . "',  oc_category_id = '" . (int)$data[0]['oc_category_id'] . "' , account_id = '".(int)$account_id."', `sync_source` = 'Opencart Item', added_date = NOW() ");

            $this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_review WHERE oc_product_id='".$product_id."' AND account_id = '".(int)$account_id."'");
              return true;
          } else {
             return false;
          }


      }
  }
  public function checkProduct($product_id,$account_id) {
    $sql = $this->db->query("SELECT oc_product_id FROM ".DB_PREFIX."amazon_product_map WHERE oc_product_id='".$product_id."' AND account_id = '".(int)$account_id."'")->row;
    if(isset($sql['oc_product_id'])) {
      return false;
    } else {
       return true;
    }

  }
  public function deleteProduct($account_id, $product_ids) {
      foreach ($product_ids as $product_id) {
        $this->db->query("DELETE FROM ".DB_PREFIX."amazon_product_review WHERE oc_product_id='".$product_id."' AND account_id = '".(int)$account_id."'");
      }
  }

}

?>
