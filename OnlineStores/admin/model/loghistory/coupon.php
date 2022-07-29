<?php
class ModelLoghistoryCoupon extends Model {
	public function getTotalCoupons($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'coupon' ";

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

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,c.name,c.code,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "coupon` c ON (lh.reference_id = c.coupon_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'coupon' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( c.name LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.code LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $total = $this->getTotalCoupons($data);

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

            $couponCategory = isset($logData['old_value']['coupon_category']) ? $logData['old_value']['coupon_category'] : array();
            $couponManufacturer = isset($logData['old_value']['coupon_manufacturer']) ? $logData['old_value']['coupon_manufacturer'] : array();
            $couponProducts = isset($logData['old_value']['coupon_product']) ? $logData['old_value']['coupon_product'] : array();
            $couponCategoryExcluded = isset($logData['old_value']['coupon_category_excluded']) ? $logData['old_value']['coupon_category_excluded'] : array();
            $couponManufacturerExcluded = isset($logData['old_value']['coupon_manufacturer_excluded']) ? $logData['old_value']['coupon_manufacturer_excluded'] : array();
            $couponProductsExcluded = isset($logData['old_value']['coupon_product_excluded']) ? $logData['old_value']['coupon_product_excluded'] : array();

            $this->load->model('catalog/category');
            $this->load->model('catalog/manufacturer');
            $this->load->model('catalog/product');

            $logData['old_coupon_category'] = array();
            $logData['old_coupon_manufacturer'] = array();
            $logData['old_coupon_products'] = array();
            $logData['old_coupon_category_excluded'] = array();
            $logData['old_coupon_manufacturer_excluded'] = array();
            $logData['old_coupon_products_excluded'] = array();

            foreach ($couponCategory as $category) {
                $category_info = $this->model_catalog_category->getCategory($category);
                if ($category_info) {
                    $logData['old_coupon_category'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name']];
                }
            }


            foreach ($couponManufacturer as $manufacturers) {
                $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($manufacturers);

                if ($manufacturers_info) {
                    $logData['old_coupon_manufacturer'][] = ['manufacturer_id'=>$manufacturers_info['manufacturer_id'],'name'=>$manufacturers_info['name']];
                }
            }

            foreach ($couponProducts as $product) {
                $product_info = $this->model_catalog_product->getProduct($product);

                if ($product_info) {
                    $logData['old_coupon_products'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name']];
                }
            }

            foreach ($couponCategoryExcluded as $category) {
                $category_info = $this->model_catalog_category->getCategory($category);
                if ($category_info) {
                    $logData['old_coupon_category_excluded'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name']];
                }
            }

            foreach ($couponManufacturerExcluded as $manufacturers) {
                $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($manufacturers);

                if ($manufacturers_info) {
                    $logData['old_coupon_manufacturer_excluded'][] = ['manufacturer_id'=>$manufacturers_info['manufacturer_id'],'name'=>$manufacturers_info['name']];
                }
            }

            foreach ($couponProductsExcluded as $product) {
                $product_info = $this->model_catalog_product->getProduct($product);

                if ($product_info) {
                    $logData['old_coupon_products_excluded'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name']];
                }
            }


            $newCouponCategory = isset($logData['new_value']['coupon_category']) ? $logData['new_value']['coupon_category'] : array();
            $newCouponManufacturer = isset($logData['new_value']['coupon_manufacturer']) ? $logData['new_value']['coupon_manufacturer'] : array();
            $newCouponProducts = isset($logData['new_value']['coupon_product']) ? $logData['new_value']['coupon_product'] : array();
            $newCouponCategoryExcluded = isset($logData['new_value']['coupon_category_excluded']) ? $logData['new_value']['coupon_category_excluded'] : array();
            $newCouponManufacturerExcluded = isset($logData['new_value']['coupon_manufacturer_excluded']) ? $logData['new_value']['coupon_manufacturer_excluded'] : array();
            $newCouponProductsExcluded = isset($logData['new_value']['coupon_product_excluded']) ? $logData['new_value']['coupon_product_excluded'] : array();

            $logData['new_coupon_category'] = array();
            $logData['new_coupon_manufacturer'] = array();
            $logData['new_coupon_products'] = array();
            $logData['new_coupon_category_excluded'] = array();
            $logData['new_coupon_manufacturer_excluded'] = array();
            $logData['new_coupon_products_excluded'] = array();

            foreach ($newCouponCategory as $category) {
                $category_info = $this->model_catalog_category->getCategory($category);
                if ($category_info) {
                    $logData['new_coupon_category'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name']];
                }
            }


            foreach ($newCouponManufacturer as $manufacturers) {
                $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($manufacturers);

                if ($manufacturers_info) {
                    $logData['new_coupon_manufacturer'][] = ['manufacturer_id'=>$manufacturers_info['manufacturer_id'],'name'=>$manufacturers_info['name']];
                }
            }

            foreach ($newCouponProducts as $product) {
                $product_info = $this->model_catalog_product->getProduct($product);

                if ($product_info) {
                    $logData['new_coupon_products'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name']];
                }
            }

            foreach ($newCouponCategoryExcluded as $category) {
                $category_info = $this->model_catalog_category->getCategory($category);
                if ($category_info) {
                    $logData['new_coupon_category_excluded'][] = ['category_id'=>$category_info['category_id'],'name'=>$category_info['name']];
                }
            }

            foreach ($newCouponManufacturerExcluded as $manufacturers) {
                $manufacturers_info = $this->model_catalog_manufacturer->getManufacturer($manufacturers);

                if ($manufacturers_info) {
                    $logData['new_coupon_manufacturer_excluded'][] = ['manufacturer_id'=>$manufacturers_info['manufacturer_id'],'name'=>$manufacturers_info['name']];
                }
            }

            foreach ($newCouponProductsExcluded as $product) {
                $product_info = $this->model_catalog_product->getProduct($product);

                if ($product_info) {
                    $logData['new_coupon_products_excluded'][] = ['product_id'=>$product_info['product_id'],'name'=>$product_info['name']];
                }
            }
            $this->load->model('user/user');     
            $userInfo= $this->model_user_user->getUser($info['user_id']);
            $logData['username'] =$userInfo['username'];
            $logData['email'] =$userInfo['email'];	
            $logData['date_added'] =$info['date_added'];
        }
        return $logData;
    }


    public function getCouponProducts($coupon_id,$excluded)
    {
        $coupon_product_data = $queryString = [];

        $queryString[] = "SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'";

        $queryString[] = 'AND product_excluded = '.$excluded;

        $query = $this->db->query(implode(' ', $queryString));

        $coupon_product_data[] = $result['product_id'];

        return $coupon_product_data;
    }

    public function getCouponCategories($coupon_id,$excluded)
    {
        $coupon_category_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_category';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';

        $queryString[] = 'AND category_excluded = '.$excluded;

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            $coupon_category_data[] = $result['category_id'];
        }

        return $coupon_category_data;
    }

    public function getCouponManufacturer($coupon_id,$excluded)
    {
        $coupon_manufacturer_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_manufacturer';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';

        $queryString[] = 'AND manufacturer_excluded = '.$excluded;

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            $coupon_manufacturer_data[] = $result['manufacturer_id'];
        }
        return $coupon_manufacturer_data;
    }


}
?>