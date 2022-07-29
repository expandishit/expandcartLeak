<?php
class ModelEcdealsProduct extends Model {
	function getCategrories($product_id = 0){
		if($product_id){
			$query = $this->db->query("SELECT category_id FROM ".DB_PREFIX."product_to_category WHERE product_id=".(int)$product_id);

			if($query->num_rows > 0){
				return $query->rows;
			}
		}
		return false;
	}
	/**
	* Get deal data
	* @integer product id
	* @integer category id
	* @boolean(0|1) show expired deal
	* @return Mix
	*
	*/

	public function getDeal($product_id = 0, $category_id = array(), $show_expired_deal = 0){
		$check = true;
		if( $category_id ) {
			$query = $this->db->query("SELECT * FROM ".DB_PREFIX."product_to_category WHERE product_id=".(int)$product_id." AND category_id IN (".implode(",", $category_id).")");

			if($query->num_rows <= 0){
				$check = false;
			}
		}

		if( $check ) {
			if ($this->customer->isLogged()) {
				$customer_group_id = $this->customer->getCustomerGroupId();
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
			$this->load->model('catalog/product');
			if( $product = $this->model_catalog_product->getProduct( $product_id )){
				$product_id = isset($product['product_id'])?(int)$product['product_id']:0;

				if($show_expired_deal) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = ".$product_id." AND ps.customer_group_id = '" . (int)$customer_group_id . "' ORDER BY ps.priority ASC, ps.price ASC");
				} else {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = ".$product_id." AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start = '0000-00-00 00:00:00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end = '0000-00-00 00:00:00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC");

				}
				
				if (!empty($product) && $query->num_rows) {
					$deal = array();
					$active_deal = false;

					if($query->rows) {
						foreach ($query->rows  as $product_special) {
							$current_time = time() + 25200;
							if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] == '0000-00-00 00:00:00' || $product_special['date_start'] < date('Y-m-d H:i:s')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] == '0000-00-00 00:00:00' || $product_special['date_end'] > date('Y-m-d H:i:s', $current_time))) {

								$active_deal = true;
								$deal = $product_special;
								break;
							} else {
								$deal = $product_special;
							}
						}
					}

					if($deal) {
						$special_price = $deal['price'];
						$date_start = $deal['date_start'];
						$date_end = $deal['date_end'];
						$product['active_deal'] = $active_deal;
						$product["special"] = $special_price;
						$product["date_start"] = $date_start;
						$product['date_end'] = $date_end;
					}
					
					return $product;
				}
			}
		}
		
		return false;
	}

	public function getTotalBought($product_id = 0, $order_status_id = 5){
		$bought = 0;
		$order_status_id = is_array($order_status_id)?$order_status_id:array($order_status_id);
		$query = $this->db->query("SELECT sum(quantity) as total FROM " . DB_PREFIX . "order_product op
			LEFT JOIN `".DB_PREFIX."order` AS o ON op.order_id = o.order_id WHERE op.product_id = ".$product_id." AND o.order_status_id IN (".implode(",", $order_status_id).")");
		
		if($query->num_rows){
			return $query->row['total'];
		}
		return 0;
	}

	public function getTotalDeals($data = array()){
		$this->load->model('catalog/product');
		return $this->model_catalog_product->getTotalProductSpecials();
	}
	public function getDeals($data = array()) {
		$this->load->model('catalog/product');
		return $this->model_catalog_product->getProductSpecials();
	}

	
	public function getProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	
		
		$sql = "SELECT p.product_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start = '0000-00-00 00:00:00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end = '0000-00-00 00:00:00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start = '0000-00-00 00:00:00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end = '0000-00-00 00:00:00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special"; 
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";			
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}
		
			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				if(is_array($data['filter_category_id'])){
					$sql .= " AND cp.path_id IN (" . implode(",",$data['filter_category_id']) . ")";	
				}else{
					$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";		
				}
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";	
			} else {
				if(is_array($data['filter_category_id'])){
					$sql .= " AND p2c.category_id IN (" . implode(",",$data['filter_category_id']) . ")";
				}else{
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
							
			}	
		
			if (!empty($data['filter_filter'])) {
				$implode = array();
				
				$filters = explode(',', $data['filter_filter']);
				
				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}
				
				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";				
			}
		}

		if (!empty($data['filter_product_id'])) {
			$data['filter_product_id'] = is_array($data["filter_product_id"])?$data["filter_product_id"]:explode(",",$data["filter_product_id"]);
			$sql .= " AND p.product_id IN (" . implode(',', $data['filter_product_id']) . ")";				
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape($data['filter_tag']) . "%'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";
		}
					
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		$sql .= " GROUP BY p.product_id";
		
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'p.sort_order',
			'p.date_added'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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
		
		$product_data = array();
				
		$query = $this->db->query($sql);
	
		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
		}

		return $product_data;
	}
public function getTotalProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}	

		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total"; 
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";			
			} else {
				$sql .= " FROM " . DB_PREFIX . "product_to_category p2c";
			}
		
			if (!empty($data['filter_filter'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
			}
		} else {
			$sql .= " FROM " . DB_PREFIX . "product p";
		}
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		
		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				if(is_array($data['filter_category_id'])){
					$sql .= " AND cp.path_id IN (" . implode(",",$data['filter_category_id']) . ")";	
				}else{
					$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";		
				}
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";	
			} else {
				if(is_array($data['filter_category_id'])){
					$sql .= " AND p2c.category_id IN (" . implode(",",$data['filter_category_id']) . ")";
				}else{
					$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
				}
							
			}	
		
			if (!empty($data['filter_filter'])) {
				$implode = array();
				
				$filters = explode(',', $data['filter_filter']);
				
				foreach ($filters as $filter_id) {
					$implode[] = (int)$filter_id;
				}
				
				$sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";				
			}
		}

		if (!empty($data['filter_product_id'])) {
			$data['filter_product_id'] = is_array($data["filter_product_id"])?$data["filter_product_id"]:explode(",",$data["filter_product_id"]);
			$sql .= " AND p.product_id IN (" . implode(',', $data['filter_product_id']) . ")";				
		}
		
		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}
		
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";				
		}
		
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}

	public function getProductSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		$join = "";
		$where = "";
		if (isset($data['filter_category_id']) && !empty($data['filter_category_id'])) {
			$join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id)";
			if(is_array($data['filter_category_id'])){
				$where .= " AND p2c.category_id IN (" . implode(",",$data['filter_category_id']) . ")";
			}else{
				$where .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}
		if (isset($data['filter_featured']) && empty($data['filter_featured'])) {
			$where .= " AND p.featured = 0";
		}elseif(isset($data['filter_featured']) && $data['filter_featured'] == 1) {
			$where .= " AND p.featured = 1";
		}
		
		$special_where = "AND ((ps.date_start = '0000-00-00' OR ps.date_start = '0000-00-00 00:00:00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end = '0000-00-00 00:00:00' OR ps.date_end > NOW()))";

		if(isset($data['filter_deal_status']) && $data['filter_deal_status']) {
			switch ($data['filter_deal_status']) {
				case 'today':
					$start_date = date("Y-m-d H:i:s");
					$end_date = date('Y-m-d H:i:s', strtotime("+2 days"));

					$special_where .= "AND (ps.date_end <= '".$end_date."' AND ps.date_end > '".$start_date."')";
					break;
				
				case 'past':
					$special_where = "AND ps.date_end < '".date("Y-m-d H:i:s")."' AND ps.date_end != '0000-00-00' AND ps.date_end != '0000-00-00 00:00:00'";
					break;
				case 'upcomming':
					$special_where = "AND ps.date_start >= '".date("Y-m-d H:i:s")."'";
					break;
			}
			
		}
		
		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps ".$join." LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' ".$where." AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' ".$special_where;

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.date_added',
			'ps.price',
			'ps.date_end',
			'rating',
			'p.sort_order'
		);

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}
		
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";				
		}

		$sql .= " GROUP BY ps.product_id";

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
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
		
		$product_data = array();
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getTotalProductSpecials( $data ) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		$join = "";
		$where = "";
		if (!empty($data['filter_category_id'])) {
			$join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id)";
			if(is_array($data['filter_category_id'])){
				$where .= " AND p2c.category_id IN (" . implode(",",$data['filter_category_id']) . ")";
			}else{
				$where .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
			}
		}
		$special_where = "AND ((ps.date_start = '0000-00-00' OR ps.date_start = '0000-00-00 00:00:00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end = '0000-00-00 00:00:00' OR ps.date_end > NOW()))";

		if(isset($data['filter_deal_status']) && $data['filter_deal_status']) {
			switch ($data['filter_deal_status']) {
				case 'today':
					$start_date = date("Y-m-d");
					$end_date = date('Y-m-d', strtotime("+2 days"));

					$special_where .= "AND (ps.date_end <= '".$end_date."' AND ps.date_end > '".$start_date."')";
					break;
				
				case 'past':
					$special_where = "AND ps.date_end < '".date("Y-m-d H:i:s")."' AND ps.date_end != '0000-00-00' AND ps.date_end != '0000-00-00 00:00:00'";
					break;
				case 'upcomming':
					$special_where = "AND ps.date_start >= '".date("Y-m-d H:i:s")."'";
					break;
			}
			
		}

		$sql = "SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps ".$join." LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' ".$where." AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' ".$special_where;
		
		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";
			
			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
				}
				
				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}
			
			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}
			
			if (!empty($data['filter_tag'])) {
				$sql .= "pd.tag LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . "%'";
			}
		
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}	
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}

			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}		
			
			if (!empty($data['filter_name'])) {
				$sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
			}
			
			$sql .= ")";				
		}
		$query = $this->db->query($sql);

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;	
		}
	}
}
?>