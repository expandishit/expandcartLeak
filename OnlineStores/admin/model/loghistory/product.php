<?php
class ModelLoghistoryProduct extends Model {
	public function getTotalProducts($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'product' ";

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " ORDER BY date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
		}
		
		$query = $this->db->query($sql);

        return $query->row['total'];
	}	
	


    public function ajaxResponse($data, $request)
    {

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,pd.name,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (lh.reference_id = pd.product_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'product' ";

        $implode[] = " pd.language_id = ". $this->config->get('config_language_id');

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( pd.name LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $total = $this->getTotalProducts($data);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered, 'total' => $total];
    }

    public function getHistoryInfo($log_id)
    {
        $logData = [];

        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "log_history WHERE log_history_id = '" . (int)$log_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        if(count($query->row) > 0){
            $info = $query->row;
            $logData['old_value'] = ($info['old_value'] != NULL) ? json_decode($info['old_value'],true) : array();
            $logData['new_value'] = ($info['new_value'] != NULL) ? json_decode($info['new_value'],true) : array();
            $this->load->model('catalog/category');
            $this->load->model('catalog/manufacturer');
            $this->load->model('localisation/stock_status');
            $this->load->model('localisation/tax_class');
            $this->load->model('localisation/length_class');
            $this->load->model('localisation/weight_class');
            $this->load->model('catalog/option');
            $this->load->model('catalog/attribute');
            $this->load->model('sale/customer_group');
            // get old data
            foreach ($logData['old_value']['categories'] as $category){
                $logData['old_value']['categoriesData'][] = $this->model_catalog_category->getCategory($category);
            }

            $logData['old_value']['manufacturer'] = $this->model_catalog_manufacturer->getManufacturer($logData['old_value']['product_info']['manufacturer_id']);
            $logData['old_value']['stock_status'] = $this->model_localisation_stock_status->getStockStatus($logData['old_value']['product_info']['stock_status_id']);
            $logData['old_value']['tax_class'] = $this->model_localisation_tax_class->getTaxClass($logData['old_value']['product_info']['tax_class_id']);
            $logData['old_value']['length_class'] = $this->model_localisation_length_class->getLengthClass($logData['old_value']['product_info']['length_class_id']);
            $logData['old_value']['weight_class'] = $this->model_localisation_weight_class->getWeightClass($logData['old_value']['product_info']['weight_class_id']);
            foreach ($logData['old_value']['product_discounts'] as $key=> $discount){
                $logData['old_value']['product_discounts'][$key]['name'] = $this->model_sale_customer_group->getCustomerGroup($discount['customer_group_id'])['name'];
            }

            // get new data
            foreach ($logData['new_value']['product_category'] as $category){
                $logData['new_value']['categoriesData'][] = $this->model_catalog_category->getCategory($category);
            }

            $logData['new_value']['manufacturer'] = $this->model_catalog_manufacturer->getManufacturer($logData['new_value']['manufacturer_id']);
            $logData['new_value']['stock_status'] = $this->model_localisation_stock_status->getStockStatus($logData['new_value']['stock_status_id']);
            $logData['new_value']['tax_class'] = $this->model_localisation_tax_class->getTaxClass($logData['new_value']['tax_class_id']);
            $logData['new_value']['length_class'] = $this->model_localisation_length_class->getLengthClass($logData['new_value']['length_class_id']);
            $logData['new_value']['weight_class'] = $this->model_localisation_weight_class->getWeightClass($logData['new_value']['weight_class_id']);

            foreach ($logData['new_value']['product_option'] as $option){
                foreach ($option['product_option_value'] as $key=>$option_value){
                    $logData['new_value']['product_option'][$option['option_id']]['product_option_value'][$option_value['option_value_id']]['name'] = $this->model_catalog_option->getOptionValue($option_value['option_value_id'])['name'];
                }
            }

            foreach ($logData['new_value']['product_attribute'] as $attribute){
                $attributeData = $this->model_catalog_attribute->getAttribute($attribute['attribute_id']);
                    $logData['new_value']['product_attribute'][$attribute['attribute_id']]['name'] = $attributeData['name'];
                    $logData['new_value']['product_attribute'][$attribute['attribute_id']]['GroupName'] = $attributeData['GroupName'];
            }

            foreach ($logData['new_value']['product_special_discount'] as $key=> $discount){
                $logData['new_value']['product_special_discount'][$key]['name'] = $this->model_sale_customer_group->getCustomerGroup($discount['customer_group_id'])['name'];
            }
            $this->load->model('user/user');     
            $userInfo= $this->model_user_user->getUser($info['user_id']);
            $logData['username'] =$userInfo['username'];
            $logData['email'] =$userInfo['email'];	
            $logData['date_added'] =$info['date_added'];
        }

        $this->load->model('localisation/language');
        $logData['languages'] = $this->model_localisation_language->getLanguages();
        return $logData;
    }




}
?>