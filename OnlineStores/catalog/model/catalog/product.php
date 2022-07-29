<?php
class ModelCatalogProduct extends Model {
	public function updateViewed($product_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
	}

	public function getProduct($product_id, $language_id = null) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		
		$language_id = $language_id ?? $this->config->get('config_language_id');

		$query = $this->db->query("SELECT 
			DISTINCT *, 
			pd.name AS name, 
			p.image, m.name AS manufacturer, 
			m.image AS manufacturerimg, 
			p.general_use, 
			(SELECT SUM(op.quantity) AS qty_in_orders FROM order_product op WHERE op.product_id = p.product_id) AS qty_in_orders, 
			(SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((IFNULL(pd2.date_start, '0000-00-00') = '0000-00-00' OR pd2.date_start < NOW()) AND (IFNULL(pd2.date_end, '0000-00-00') = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
			(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND  ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, 
			(SELECT SUM(points) FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id .  "' ) AS reward, 
			(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, 
			(SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, 
			(SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, 
			(SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, 
			(SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, 
			p.sort_order 

			FROM " . DB_PREFIX . "product p 
			LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
			LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
			LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) 

			WHERE p.product_id = '" . (int)$product_id . "' 
			AND p.archived = 0 AND pd.language_id = " . (int)$language_id . "
			AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed'],
                'manufacturerimg'  => $query->row['manufacturerimg'],
				'sls_bstr'  	   => $query->row['sls_bstr'] ?? '',
				'qty_in_orders'    => $query->row['qty_in_orders'] ?? 0 ,
				'unlimited'		   => $query->row['unlimited'],
				'transaction_type' => $query->row['transaction_type'],
				'general_use'      => (float)$query->row['general_use'] ?? 0
			);
		} else {
			return false;
		}
	}

	public function getProducts($data = array(), $language_id = null) {

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {

			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$language_id = $language_id ?? $this->config->get('config_language_id');

		//(SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status,
		$sql = "SELECT p.product_id, p.quantity, pov.option_value_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX .
            "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM "
            . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '"
            . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((IFNULL(pd2.date_start, '0000-00-00') = '0000-00-00' OR pd2.date_start < NOW()) AND (IFNULL(pd2.date_end, '0000-00-00') = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM "
            . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '"
            . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT ss.name FROM "
            . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = " . (int)$language_id . ") AS stock_status";

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

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "ms_product as md on md.product_id=p.product_id LEFT JOIN " . DB_PREFIX . "ms_seller as ms on ms.seller_id=md.seller_id";
        }

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (pov.product_id=p.product_id)";

		if ($data['filter_name']) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p2c.product_id = p.product_id)";
            $sql .= " LEFT JOIN " . DB_PREFIX . "category_description cat ON (p2c.category_id = cat.category_id)";
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE  p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        } else {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE  p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        }

		if (isset($data['filter_option']) && !empty($data['filter_option'])) {
			$sql .= " AND pov.option_value_id IN(" . implode(', ', $data['filter_option']) . ")";
		}

        if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
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

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {

            $sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%' OR cat.name LIKE '%"
                        . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description']) || !empty($data['filter_name'])) {
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

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " AND ms.zone_id=" . $data['city_id'];
        }

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}


		$sql .= " AND p.archived = 0 GROUP BY p.product_id";
 
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
            $product_data[$result['product_id']] = $this->getProduct($result['product_id'], $language_id);
		}

		return $product_data;
	}

	public function getProductSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((IFNULL(ps.date_start, '0000-00-00') = '0000-00-00' OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, '0000-00-00') = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);

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

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getLatestProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $customer_group_id . '.' . (int)$limit);
		if (!$product_data) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}
		return $product_data;
	}

	public function getPopularProducts($limit) {
		$product_data = array();

		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) where p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.viewed, p.date_added DESC LIMIT " . (int)$limit);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getBestSellerProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();

			$query = $this->db->query("SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getProductAttributes($product_id) {

		if(!empty($product_id)) {

            $simple_attributes = $this->db->query("
			SELECT pa.attribute_id as advanced_attribute_id , aa.type, aa.glyphicon, aad.name, agd.`name` as GroupName
			from product_attribute pa join `attribute` aa on pa.attribute_id = aa.attribute_id
			join attribute_description aad on aad.`attribute_id` = aa.`attribute_id`
			join `attribute_group_description` agd on agd.`attribute_group_id` = aa.`attribute_group_id`
			where product_id=" . $product_id . "
			and pa.attribute_id <> 0 and advanced_attribute_id=0
			and aad.language_id=". $this->config->get("config_language_id") ."
			and agd.`language_id`=" .  $this->config->get("config_language_id") ." GROUP BY pa.attribute_id ")->rows;

		$advanced_attributes = $this->db->query("
			SELECT pa.advanced_attribute_id, aa.type, aa.glyphicon, aad.name, agd.`name` as GroupName
			from product_attribute pa join advanced_attribute aa on pa.advanced_attribute_id = aa.advanced_attribute_id
			join advanced_attribute_description aad on aad.`advanced_attribute_id` = aa.`advanced_attribute_id`
			join `attribute_group_description` agd on agd.`attribute_group_id` = aa.`attribute_group_id`
			where product_id=" . $product_id . " 
			and pa.advanced_attribute_id <> 0 and attribute_id=0 
			and aad.language_id=". $this->config->get("config_language_id") ." 
			and agd.`language_id`=" .  $this->config->get("config_language_id"))->rows;

            $advanced_attributes = array_merge($simple_attributes,$advanced_attributes);

		foreach ($advanced_attributes as $key=>$advanced_attribute) {

			$product_attribute_description_data = []; 
			$advanced_attribute_selected_values = [];
			$advanced_attribute_all_values = [];

	        if($advanced_attribute['type'] === 'text'){
                $product_attribute_description_query_simple  = $this->db->query("
		    		SELECT * 
		    		FROM " . DB_PREFIX . "product_attribute 
		    		WHERE product_id = '" . (int)$product_id . "' AND advanced_attribute_id=0
		    		AND attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "'"
                );
                foreach ($product_attribute_description_query_simple->rows as $product_attribute_description) {
                    $product_attribute_description_data[$product_attribute_description['language_id']] = ['text' => $product_attribute_description['text'] ];
                }

		    	$product_attribute_description_query = $this->db->query("
		    		SELECT * 
		    		FROM " . DB_PREFIX . "advanced_product_attribute_text 
		    		WHERE product_id = '" . (int)$product_id . "' 
		    		AND advanced_attribute_id = '" . (int)$advanced_attribute['advanced_attribute_id'] . "'");

	            foreach ($product_attribute_description_query->rows as $product_attribute_description) {
	                $product_attribute_description_data[$product_attribute_description['language_id']] = ['text' => $product_attribute_description['text'] ];
	            }
            }
            elseif(in_array($advanced_attribute['type'], ['single_select', 'multi_select']) ){
                $advanced_attribute_selected_values = array_column($this->db->query("
                    SELECT advanced_attribute_value_id
                    FROM `advanced_product_attribute_choose` pavc
                    WHERE pavc.advanced_attribute_id = ".(int)$advanced_attribute['advanced_attribute_id']."
                    AND pavc.product_id  = ".(int)$product_id)->rows
                , 'advanced_attribute_value_id');
				
				$advanced_attribute_all_values = $this->getAttributeValuesCurrentLanguage($advanced_attribute['advanced_attribute_id']);
                
            }
            $advanced_attributes[$key]['product_attribute_description'] = $product_attribute_description_data?:[];
            $advanced_attributes[$key]['values'] = $advanced_attribute_all_values?: [];
            $advanced_attributes[$key]['selected_values'] = $advanced_attribute_selected_values?:[];
		}
		return $advanced_attributes;
	}else{
            return [];
        }
		
	}	
	public function getProductOptions($product_id) {
		$product_option_data = array();
		$this->load->model('module/product_option_image_pro');
		$images = $this->model_module_product_option_image_pro->getProductOptionImages($product_id);

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();

				$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

				foreach ($product_option_value_query->rows as $product_option_value) {
						$option_value_images = array();
						if (isset($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']])) {
							foreach ($images[$product_option['product_option_id']][$product_option_value['product_option_value_id']] as $image) {
								$option_value_images[] = $image;
							}
						}
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'images'                  => $option_value_images,
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}

				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option_value_data,
					'required'          => $product_option['required']
				);
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);
			}
      	}

		return $product_option_data;
	}

	public function getProductDiscounts($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

		return $query->rows;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductRelated($product_id) {
		$product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		foreach ($query->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}

		return $product_data;
	}

	public function getProductLayoutId($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return  $this->config->get('config_layout_product');
		}
	}

	public function getCategories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
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

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "ms_product as md on md.product_id=p.product_id LEFT JOIN " . DB_PREFIX . "ms_seller as ms on ms.seller_id=md.seller_id";
        }

		$sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			if (!empty($data['filter_sub_category'])) {
				$sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
			} else {
				$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
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

				if (!empty($data['filter_description']) || !empty($data['filter_name'])) {
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

        if (isset($data['city_id']) && $data['city_id'] > 0) {
            $sql .= " AND ms.zone_id=" . $data['city_id'];
        }

		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductSpecials() {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$query = $this->db->query("SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))");

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

    public function checkMSModule($search_by_city = true)
    {
    	if(\Extension::isInstalled('multiseller')) {
    		if($search_by_city && (isset($search_by_city) && $search_by_city != 1))
    			return false;
			return true;
		}
        /*$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if ($query->row) {
            $search_by_city = $this->config->get('msconf_search_by_city');

            if (isset($search_by_city) && $search_by_city == 1) {
                return true;
            }
        }*/

        return false;
    }

    public function get_zones()
    {
        $query = $this->db->query("
			SELECT * FROM `ms_seller` inner join `zone` on `ms_seller`.zone_id=`zone`.zone_id group by zone.name
		");

        if ($query->num_rows > 0) {
            return $query->rows;
        }

        return null;
    }


    public function getProductsV2($data)
    {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        $db_prefix = DB_PREFIX;
        $lang = (int)$this->config->get('config_language_id');
        $store_id = $this->config->get('config_store_id');



        /*
         * Here we `SELECT` the needed Columns
         *
            Not Used But Found :
                pro.sort_order, pro.sku, pro.upc,  pro.ean,  pro.jan,  pro.isbn, pro.mpn, pro.location,  pro.points,
                pro.date_available, pro.weight, pro.weight_class_id, pro.length, pro.width, pro.height, pro.length_class_id, pro.subtract, pro.minimum,
                pro.status, pro.date_added, pro.date_modified, pro.viewed,
                pro_desc.meta_description, pro_desc.meta_keyword, pro_desc.tag,
                (SELECT unit FROM {$db_prefix}weight_class_description weight_desc WHERE pro.weight_class_id = weight_desc.weight_class_id
                    AND weight_desc.language_id ={$lang}) AS weight_class,
                (SELECT unit FROM {$db_prefix}length_class_description length_desc WHERE pro.length_class_id = length_desc.length_class_id
                    AND length_desc.language_id ={$lang}) AS length_class,
         */

        $sql = "SELECT pro.product_id, pro.quantity, pro_desc.name, pro.image, pro.model as product_code, pro.general_use,
                     pro.tax_class_id, pro_desc.description,  ";
		if ( $data['sort'] == 'product_manual_sort' && \Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
			$sql .= "pro.manual_sort,p2cs.manual_sort as category_manual_sort, ";
		}
		$sql  .="LEFT(pro_desc.description, 25) AS short_description,
                     pro.manufacturer_id as brand_id, man.name AS brand, man.image AS brand_image,
                ROUND ((SELECT AVG(rating) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id
                    AND rev.status = '1')) AS rating,
                IFNULL( (SELECT price FROM {$db_prefix}product_discount pro_discount WHERE pro_discount.product_id = pro.product_id 
                     AND pro_discount.customer_group_id ={$customer_group_id} AND pro_discount.quantity = '1' 
                     AND (pro_discount.date_start = '0000-00-00' OR pro_discount.date_start IS NULL OR pro_discount.date_start <= NOW()) 
                     AND (pro_discount.date_end = '0000-00-00' OR pro_discount.date_end IS NULL OR pro_discount.date_end >= NOW())
                     ORDER BY pro_discount.priority ASC, pro_discount.price ASC LIMIT 1) , pro.price ) AS price,
                (SELECT price FROM {$db_prefix}product_special pro_special 
                    WHERE pro_special.product_id = pro.product_id AND pro_special.customer_group_id ={$customer_group_id}
                    AND (pro_special.date_start = '0000-00-00' OR pro_special.date_start <= NOW() OR pro_special.date_start IS NULL ) 
                    AND (pro_special.date_end = '0000-00-00' OR pro_special.date_end >= NOW() OR pro_special.date_end IS NULL )
                    ORDER BY pro_special.priority ASC, pro_special.price ASC LIMIT 1) AS special,
                (SELECT st_status.name FROM {$db_prefix}stock_status st_status
                    WHERE st_status.stock_status_id = pro.stock_status_id AND st_status.language_id = {$lang}) AS stock_status,
                (SELECT points FROM {$db_prefix}product_reward pro_reward WHERE pro_reward.product_id = pro.product_id 
                    AND customer_group_id ={$customer_group_id} LIMIT 1) AS reward,
                IFNULL ((SELECT COUNT(*) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id AND rev.status = '1' 
                    GROUP BY rev.product_id), 0) AS reviews
                FROM product pro ";


        /*
         *
         *
         * Here we select the `Join` the needed Tables
         *
         *
         */

        $sql .= " LEFT JOIN {$db_prefix}manufacturer as man ON (pro.manufacturer_id = man.manufacturer_id) ";
        $sql .= " LEFT JOIN {$db_prefix}product_description pro_desc ON (pro.product_id = pro_desc.product_id)";
        $sql .= " LEFT JOIN {$db_prefix}product_to_store pro_store ON (pro.product_id = pro_store.product_id)";

        if (isset($data['categories_ids']) || isset($data['filterText'])) {
            $sql .= " LEFT JOIN {$db_prefix}product_to_category pro_cat ON (pro_cat.product_id = pro.product_id)";
            if (isset($data['filterText'])) {
                $sql .= " LEFT JOIN {$db_prefix}category_description cat ON (pro_cat.category_id = cat.category_id)";
              //  $sql .= " LEFT JOIN {$db_prefix}product_to_store pro_store ON (pro.product_id = pro_store.product_id)";
            }
        }

        if (isset($data['seller_id'])) {
            $sql .= " LEFT JOIN {$db_prefix}ms_product seller_pro ON (seller_pro.product_id = pro.product_id)";

        }
		if(isset($data['seller_id'])&& (\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)) {
            $sql .= " LEFT JOIN {$db_prefix}trips_product trips_pro ON (trips_pro.product_id = pro.product_id)";
        }

		if (isset($data['categories_ids']) && \Extension::isInstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_categories_sorting p2cs ON (p2cs.product_id = pro.product_id)";
        }

        /*
         *
         *
         * Here we start the `WHERE` Queries ( filters )
         *
         *
         */

        $sql .= " WHERE  pro.status = '1' AND pro.date_available <= NOW() AND pro_store.store_id ={$store_id} AND pro_desc.language_id=$lang";

        if (isset($data['categories_ids'])) {
            $categories_ids = implode(" , ", $data['categories_ids']);
            $sql .= " AND pro_cat.category_id IN ({$categories_ids})";
        }

        if (isset($data['brands_ids'])) {
            $brands_ids = implode(", ", $data['brands_ids']);
            $sql .= " AND pro.manufacturer_id IN ({$brands_ids})";
        }

        if (isset($data['filterText'])) {
            $sql .= " AND ( ";
            $implode = [];
            $filter_name = $data['filterText'];
    
            if($data['filter_text_each_word']){
	            $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $filter_name)));

	            foreach ($words as $word) {
	                $word = $this->db->escape($word);
	                $implode[] = " pro_desc.name LIKE '%{$word}%' OR cat.name LIKE '%{$word}%' ";
	            }

	            if ($implode) {
	                $sql .= implode(" AND ", $implode);
	            }            	
            }
            else{
	            $sql .= " pro_desc.name LIKE '%{$filter_name}%'";
            }

            $sql .= " OR pro_desc.description LIKE '%{$filter_name}%'";
            $sql .= " OR pro_desc.tag LIKE '%{$filter_name}%'";
            $sql .= " OR LCASE(pro.model) ='%$filter_name%'";
            $sql .= " OR LCASE(pro.sku) ='%$filter_name%'";
            $sql .= " ) ";
        }

        if (isset($data['sellers_ids'])) {
            $sellers_ids = implode(" , ", $data['sellers_ids']);
            $sql .= " AND seller_pro.seller_id IN ({$sellers_ids})";
        }

        if ($data['product_id']) {
            $sql .= " AND pro.product_id = {$data['product_id']} ";
        }
		if ($data['seller_id']) {
            $sql .= " AND seller_pro.seller_id = {$data['seller_id']} ";
        }
		if ((isset($data['seller_id'])) && (isset($data['trips']) && $data['trips'] =="next")) {
			
            $sql .= " AND trips_pro.from_date = CURDATE()";
        }
		if ((isset($data['seller_id'])) &&(isset($data['trips']) && $data['trips'] =="upcoming")) {
			
            $sql .= " AND trips_pro.from_date > CURDATE()";
        }
		if ((isset($data['seller_id'])) &&(isset($data['trips']) && $data['trips'] =="past")) {
			
            $sql .= " AND trips_pro.from_date < CURDATE()";
        }

        if ((isset($data['deals']) && $data['deals'] == 1) || $data['starting_price'] || $data['ending_price']) {
            $sql .= " HAVING ";

            $havingQuery = [];

            if (isset($data['deals']) && $data['deals'] == 1) {
                $havingQuery[] = " special IS NOT NULL ";
            }
            if ($data['starting_price']) {
                $havingQuery[] = " price >= {$data['starting_price']} ";
            }
            if ($data['ending_price']) {
                $havingQuery[] = " price <= {$data['ending_price']} ";
            }

            $sql .= implode(" AND ", $havingQuery);
        }


        /*
         *
         *
         * Here Sorting - Offset - Limit
         *
         *
         */
        $sort_data = [
            'name' => 'pro_desc.name',
            'model' => 'pro.model',
            'price' => 'pro.price',
            'rating' => 'rating',
            'default' => 'pro.sort_order',
        ];

		if($data['sort'] == 'product_manual_sort' && \Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1')
		{
			if (isset($data['categories_ids'])) {
				$sql .= " ORDER BY category_manual_sort";
			} else {
				$sql .= " ORDER BY pro.manual_sort";
			}	
		}
       elseif (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) 
	    {
		    $sql .= " ORDER BY LCASE({$sort_data[$data['sort']]}) ";
        } 
		else 
		{
            $sql .= " ORDER BY pro.sort_order ";
        }

        if (isset($data['order']) && (strtolower($data['order']) == 'desc')) {
            $sql .= " DESC, LCASE(pro_desc.name) DESC ";
        } else {
            $sql .= " ASC, LCASE(pro_desc.name) ASC";
        }

        if (isset($data['limit'])) {
            $limit = (int)$data['limit'];
            $sql .= " LIMIT {$limit} ";
        }

        if (isset($data['start'])) {
            $start = (int)$data['start'];
            $sql .= " OFFSET {$start} ";
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getProductOptionsV2($product_id, $tax_class_id)
    {
        $db_prefix = DB_PREFIX;
        $lang = (int)$this->config->get('config_language_id');
        $options = [];
        $this->load->model('tool/image');

        $product_option_query = $this->db
            ->query("SELECT * FROM {$db_prefix}product_option LEFT JOIN {$db_prefix}`option` op
                        ON (product_option.option_id = op.option_id) LEFT JOIN {$db_prefix}option_description 
                        ON (op.option_id = option_description.option_id) WHERE product_option.product_id = {$product_id} 
                        AND option_description.language_id ={$lang} ORDER BY op.sort_order");


        foreach ($product_option_query->rows as $product_option) {

            if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox'
                || $product_option['type'] == 'image') {

                $product_option_value_query = $this->db
                    ->query("SELECT * FROM {$db_prefix}product_option_value pov LEFT JOIN {$db_prefix}option_value ov 
                            ON (pov.option_value_id = ov.option_value_id) LEFT JOIN {$db_prefix}option_value_description ovd 
                            ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = {$product_id} 
                            AND pov.product_option_id = {$product_option['product_option_id']} AND ovd.language_id = {$lang} 
                            ORDER BY ov.sort_order");

                $product_options_value = [];

                foreach ($product_option_value_query->rows as $key => $option) {
                    $values = [];
                    if (!$option['subtract'] || ($option['quantity'] > 0)) {
                        if (($this->customer->isCustomerAllowedToViewPrice()) && (float)$option['price']) {

                            $values['price'] = $this->currency->format($this->tax->calculate((float)$option['price'],
                                $tax_class_id, $this->config->get('config_tax')), '', '', false);
                        } else {
                            $values['price'] = null;
                        }
                    }

                    $values['currency'] = $this->currency->getCode();
                    $values['image'] = $this->model_tool_image->resize($option['image'], 50, 50);
                    $values['product_option_value_id'] = $option['product_option_value_id'];
                    $values['option_value_id'] = $option['option_value_id'];
                    $values['name'] = $option['name'];
                    $values['price_prefix'] = $option['price_prefix'];

                    $product_options_value[] = $values;
                }

                $options[] = [
                    'product_option_id' => $product_option['product_option_id'],
                    'product_option_value' => '0',
                    'option_id' => $product_option['option_id'],
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_options_value,
                    'required' => $product_option['required']

                ];

            } elseif ($product_option['type'] == 'text_dis' || $product_option['type'] == 'text' || $product_option['type'] == 'textarea_dis' || $product_option['type'] == 'textarea' || $product_option['type'] == 'file_dis' || $product_option['type'] == 'date' || $product_option['type'] == 'datetime' || $product_option['type'] == 'time') {
                $options[] = array(
                    'product_option_id' => $product_option['product_option_id'],
                    'option_id' => '0',
                    'name' => $product_option['name'],
                    'type' => $product_option['type'],
                    'option_value' => $product_option['option_value'],
                    'required' => $product_option['required']
                );
            }
        }
        return $options;
    }

    public function new_filter($data)
    {
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }
        $db_prefix = DB_PREFIX;
        $lang = (int)$this->config->get('config_language_id');
        $store_id = $this->config->get('config_store_id');


        $sql = "SELECT DISTINCT pro.product_id, pro.quantity, pro_desc.name, pro.image, pro.model as product_code, pro.general_use,
		pro.tax_class_id, pro_desc.description,";  
		if ( $data['sort'] == 'product_manual_sort' && \Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
			$sql .= "pro.manual_sort,p2cs.manual_sort as category_manual_sort,";
		}
		$sql .="LEFT(pro_desc.description, 25) AS short_description,
		pro.manufacturer_id as brand_id, man.name AS brand, man.image AS brand_image,
		ROUND ((SELECT AVG(rating) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id
		AND rev.status = '1')) AS rating,
		IFNULL( (SELECT price FROM {$db_prefix}product_discount pro_discount WHERE pro_discount.product_id = pro.product_id
		AND pro_discount.customer_group_id ={$customer_group_id} AND pro_discount.quantity = '1'
		AND (pro_discount.date_start = '0000-00-00' OR pro_discount.date_start IS NULL OR pro_discount.date_start <= NOW())
		AND (pro_discount.date_end = '0000-00-00' OR pro_discount.date_end IS NULL OR pro_discount.date_end >= NOW())
		ORDER BY pro_discount.priority ASC, pro_discount.price ASC LIMIT 1) , pro.price ) AS price,
		(SELECT price FROM {$db_prefix}product_special pro_special
		WHERE pro_special.product_id = pro.product_id AND pro_special.customer_group_id ={$customer_group_id}
		AND (pro_special.date_start = '0000-00-00' OR pro_special.date_start <= NOW() OR pro_special.date_start IS NULL )
		AND (pro_special.date_end = '0000-00-00' OR pro_special.date_end >= NOW() OR pro_special.date_end IS NULL )
		ORDER BY pro_special.priority ASC, pro_special.price ASC LIMIT 1) AS special,
		(SELECT st_status.name FROM {$db_prefix}stock_status st_status
		WHERE st_status.stock_status_id = pro.stock_status_id AND st_status.language_id = {$lang}) AS stock_status,
		(SELECT points FROM {$db_prefix}product_reward pro_reward WHERE pro_reward.product_id = pro.product_id
		AND customer_group_id ={$customer_group_id} LIMIT 1) AS reward,
		IFNULL ((SELECT COUNT(*) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id AND rev.status = '1'
		GROUP BY rev.product_id), 0) AS reviews
		FROM product pro ";

		/**
		 * Removed from new filter query
		 * category_id column
		 * (SELECT SUM(op.quantity) AS qty_in_orders FROM order_product op WHERE op.product_id = pro.product_id) AS qty_in_orders 
		 */
        /*
        *
        *
        * Here we select the `Join` the needed Tables
        *
        *
        */
        $sql .= " LEFT JOIN {$db_prefix}manufacturer as man ON (pro.manufacturer_id = man.manufacturer_id) ";
        if(!isset($data['filterText'])){
        	// if filterText parameter was not posted then take store language in consideration
        	$sql .= " LEFT JOIN {$db_prefix}product_description pro_desc ON (pro.product_id = pro_desc.product_id AND pro_desc.language_id = {$lang})";
        }
        else{
        	$sql .= " LEFT JOIN {$db_prefix}product_description pro_desc ON (pro.product_id = pro_desc.product_id)";
        }


        $sql .= " LEFT JOIN {$db_prefix}product_to_store pro_store ON (pro.product_id = pro_store.product_id)";

		if (isset($data['categories_ids']) || isset($data['filterText']) || isset($data['offer_categories']) || isset($data['popular'])) {
            $sql .= " LEFT JOIN {$db_prefix}product_to_category pro_cat ON (pro_cat.product_id = pro.product_id)";
            if (isset($data['filterText'])) {
                $sql .= " LEFT JOIN {$db_prefix}category_description cat ON (pro_cat.category_id = cat.category_id)";
            }
        }

        if (isset($data['seller_id'])) {
            $sql .= " LEFT JOIN {$db_prefix}ms_product seller_pro ON (seller_pro.product_id = pro.product_id)";

		}
		if(($data['trips']==1)
		&&(\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1))   {
            $sql .= " LEFT JOIN {$db_prefix}trips_product trips_pro ON (trips_pro.product_id = pro.product_id)";
			if(isset($data['filterbyArea'])){
			$sql .= " LEFT JOIN {$db_prefix}geo_area_locale geoarea ON (trips_pro.area_id = geoarea.area_id)";
			}
        }
        if (isset($data['filter_option']) && !empty($data['filter_option'])) {
		$sql .= " LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (pov.product_id=pro.product_id)";
		}

		if (isset($data['categories_ids']) && \Extension::isInstalled('product_sort') && $this->config->get('product_sort_app_status') == '1') {
			$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_categories_sorting p2cs ON (p2cs.product_id = pro.product_id) AND p2cs.category_id IN (". $this->db->escape(implode(',', $data['categories_ids'])) .")";
        }

        /*
        *
        *
        * Here we start the `WHERE` Queries ( filters )
        *
        *
        */

		$sql .= " WHERE  pro.status = '1' AND pro.date_available <= NOW() AND pro_store.store_id ={$store_id}";
				if (isset($data['filter_option']) && !empty($data['filter_option'])) {
			$sql .= " AND pov.option_value_id IN(" . implode(', ', $data['filter_option']) . ")";
			if(isset($data['filter_option_quantity']) && $data['filter_option_quantity'])
				$sql .= " AND pov.quantity > 0 ";
		}

        if (isset($data['categories_ids'])) {
            $categories_ids = implode(" , ", $data['categories_ids']);
            $sql .= " AND pro_cat.category_id IN ({$categories_ids})";
        }

        if (isset($data['brands_ids'])) {
            $brands_ids = implode(", ", $data['brands_ids']);
            $sql .= " AND pro.manufacturer_id IN ({$brands_ids})";
        }

		if (isset($data['filterText']) ) {

			if (!empty($data['filterText'])) {
				$sql .= " AND (";

				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filterText'])));

				foreach ($words as $word) {
					$implode[] = "pro_desc.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " (" . implode(" AND ", $implode) . ") ";
				}

				$sql .= " OR pro_desc.description LIKE '%" . $this->db->escape($data['filterText']) . "%'";
				$sql .= " OR pro_desc.tag LIKE '%" . $this->db->escape($data['filterText']) . "%'";
				$sql .= " OR LCASE(pro.model) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.sku) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.upc) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.ean) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.jan) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.isbn) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.mpn) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= ")";

			}

		}
		if( (\Extension::isInstalled('multiseller'))
		&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1) && $data['trips']==1) 
		{
			$sql .= " AND trips_pro.from_date >= CURDATE()";
			if(isset($data['filterbyArea']))
			{
            $sql .= " AND ( ";
            $implode = [];
            $filter_name = $data['filterbyArea'];
            $words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $filter_name)));

            foreach ($words as $word) {
                $word = $this->db->escape($word);
                $implode[] = " geoarea.name LIKE '%{$word}%'";
            }

            if ($implode) {
                $sql .= implode(" AND ", $implode);
            }

            $sql .= " ) ";
            }
			if(isset($data['from_date']) && isset($data['to_date']))
			{
				$sql .= " AND trips_pro.from_date >= '".$data['from_date']."' AND trips_pro.to_date <= '".$data['to_date']."' ";
			}
			elseif(isset($data['from_date']))
			{
				$sql .= " AND trips_pro.from_date >= '".$data['from_date']."' ";
			}
			elseif(isset($data['to_date']))
			{
				$sql .= " AND  trips_pro.to_date <= '".$data['to_date']."' ";
			}
       }
	  
		
		$sql .= " GROUP BY pro.product_id";

        if ((isset($data['deals']) && $data['deals'] == 1) || $data['starting_price'] || $data['ending_price'] || $data['popular']) {
            $sql .= " HAVING ";

            $havingQuery = [];

            if (isset($data['deals']) && $data['deals'] == 1) {
                $havingQuery[] = " special IS NOT NULL ";
            }
            if ($data['starting_price']) {
                $havingQuery[] = " price >= {$data['starting_price']} ";
            }
            if ($data['ending_price']) {
                $havingQuery[] = " price <= {$data['ending_price']} ";
            }
			if ($data['popular']) {
                $havingQuery[] = "COUNT(pro_cat.category_id) > 1";
            }

            $sql .= implode(" AND ", $havingQuery);
        }

        /*
        *
        *
        * Here Sorting - Offset - Limit
        *
        *
        */
        $sort_data = [
			'date' => 'pro.date_added',
			'date_available'=>'pro.date_available',
            'name' => 'pro_desc.name',
            'model' => 'pro.model',
            'price' => 'pro.price',
            'rating' => 'rating',
			'quantity'=>'pro.quantity',
            'default' => 'pro.sort_order',
        ];
        // $sort_data  you can sort results by any of these indexes just send it in "sort" parameter and the results will be sorted using it, such as name, price, quantity, .... etc
        if (isset($data['sort']) && is_array($data['sort']) && count($data['sort'])>0){
        	$sql .= " ORDER BY";
        	if (!isset($data['order']))
				$data['order'] = array();
			for ($i = 0 ; $i<count($data['sort']) ; $i++){
				$isSortFieldExists = false;
				if (array_key_exists($data['sort'][$i], $sort_data)) {
					$isSortFieldExists = true;
					$sql .= " LCASE({$sort_data[$data['sort'][$i]]}) ";
				}
				if (isset($data['order'][$i])){
					if ((strtolower($data['order'][$i]) == 'desc')) {
						$sql .= " DESC ,";
					}else if ((strtolower($data['order'][$i]) == 'asc')){
						$sql .= " ASC ,";
					}else{
						throw new Exception('error value for order field');
					}
				}else{
					if (array_key_exists($data['sort'][$i], $sort_data)){
						// in case there is a sort  column is set by
						// not have order value then set it default one
						if ($isSortFieldExists)
							$sql .= " ASC ,";
					}
				}
			}
			$sql = rtrim($sql , ',');
		}
        else
		{   
			if( $data['sort'] == 'product_manual_sort' && \Extension::isinstalled('product_sort') && $this->config->get('product_sort_app_status') == '1')
			{
				if (isset($data['categories_ids'])) {
					$sql .= " ORDER BY category_manual_sort";
				} else {
					$sql .= " ORDER BY pro.manual_sort";
				}		
			}
			elseif (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) 
			{
				$sql .= " ORDER BY LCASE({$sort_data[$data['sort']]}) ";
			} 
			else 
			{
				$sql .= " ORDER BY pro.sort_order ";
			}
			if (isset($data['order']) && (strtolower($data['order']) == 'desc')) 
			{
				$sql .= " DESC, LCASE(pro_desc.name) DESC ";
			} else {
				$sql .= " ASC, LCASE(pro_desc.name) ASC";
			}
		}

        if (isset($data['limit'])) {
			$sqlWithoutLimit = $sql;
			// this query is to get the total filterd count before excuting the limit
			$totalFilteredResults = count($this->db->query($sqlWithoutLimit)->rows);
			$results['totalFiltered'] = $totalFilteredResults;
            $limit = (int)$data['limit'];
            $sql .= " LIMIT {$limit} ";
        }

        if (isset($data['start'])) {
            $start = (int)$data['start'];
            $sql .= " OFFSET {$start} ";
        }
        $query = $this->db->query($sql);
        $results['products'] = $query->rows;
        return $results;
    }

    /**
     * Get product seller id
     */
    public function getProductSellerId($product_id) {
		if ($this->checkMSModule(false)) {
				$query = $this->db->query("SELECT msp.seller_id, mss.nickname FROM " . DB_PREFIX . "ms_product msp LEFT JOIN " . DB_PREFIX . "ms_seller mss ON msp.seller_id = mss.seller_id  WHERE product_id = ".$product_id." LIMIT 1");
			return $query->row;
		}
        return 0;
    }

	public function product_classification_filter($data)
	{
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}
		$db_prefix = DB_PREFIX;
		$lang = (int)$this->config->get('config_language_id');
		$store_id = $this->config->get('config_store_id');


		$sql = "SELECT DISTINCT pro.product_id, pro.quantity, pro_desc.name, pro.image, pro.model as product_code,
pro.tax_class_id, pro_desc.description,  LEFT(pro_desc.description, 25) AS short_description,
ROUND ((SELECT AVG(rating) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id
AND rev.status = '1')) AS rating,
IFNULL( (SELECT price FROM {$db_prefix}product_discount pro_discount WHERE pro_discount.product_id = pro.product_id
AND pro_discount.customer_group_id ={$customer_group_id} AND pro_discount.quantity = '1'
AND (pro_discount.date_start = '0000-00-00' OR pro_discount.date_start IS NULL OR pro_discount.date_start <= NOW())
AND (pro_discount.date_end = '0000-00-00' OR pro_discount.date_end IS NULL OR pro_discount.date_end >= NOW())
ORDER BY pro_discount.priority ASC, pro_discount.price ASC LIMIT 1) , pro.price ) AS price,
(SELECT price FROM {$db_prefix}product_special pro_special
WHERE pro_special.product_id = pro.product_id AND pro_special.customer_group_id ={$customer_group_id}
AND (pro_special.date_start = '0000-00-00' OR pro_special.date_start <= NOW() OR pro_special.date_start IS NULL )
AND (pro_special.date_end = '0000-00-00' OR pro_special.date_end >= NOW() OR pro_special.date_end IS NULL )
ORDER BY pro_special.priority ASC, pro_special.price ASC LIMIT 1) AS special,
(SELECT st_status.name FROM {$db_prefix}stock_status st_status
WHERE st_status.stock_status_id = pro.stock_status_id AND st_status.language_id = {$lang}) AS stock_status,
(SELECT points FROM {$db_prefix}product_reward pro_reward WHERE pro_reward.product_id = pro.product_id
AND customer_group_id ={$customer_group_id} LIMIT 1) AS reward,
IFNULL ((SELECT COUNT(*) FROM {$db_prefix}review rev WHERE rev.product_id = pro.product_id AND rev.status = '1'
GROUP BY rev.product_id), 0) AS reviews FROM " . DB_PREFIX . "pc_product_brand_mapping pbm ";




		/*
        *
        *
        * Here we select the `Join` the needed Tables
        *
        *
        */
		$sql .= " LEFT JOIN " . DB_PREFIX . "product pro ON (pro.product_id = pbm.product_id)";
		$sql .= " LEFT JOIN {$db_prefix}product_description pro_desc ON (pro.product_id = pro_desc.product_id)";
		$sql .= " LEFT JOIN {$db_prefix}product_to_store pro_store ON (pro.product_id = pro_store.product_id)";


		if (isset($data['seller_id'])) {
			$sql .= " LEFT JOIN {$db_prefix}ms_product seller_pro ON (seller_pro.product_id = pro.product_id)";

		}

		/*
        *
        *
        * Here we start the `WHERE` Queries ( filters )
        *
        *
        */

		$sql .= " WHERE  pro.status = '1' AND pro.date_available <= NOW() AND pro_store.store_id ={$store_id} AND pro_desc.language_id=$lang";


		if (isset($data['filterText']) ) {

			if (!empty($data['filterText'])) {
				$sql .= " AND (";

				$implode = array();

				$words = explode(' ', trim(preg_replace('/\s\s+/', ' ', $data['filterText'])));

				foreach ($words as $word) {
					$implode[] = "pro_desc.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " (" . implode(" AND ", $implode) . ") ";
				}

				$sql .= " OR pro_desc.description LIKE '%" . $this->db->escape($data['filterText']) . "%'";
				$sql .= " OR pro_desc.tag LIKE '%" . $this->db->escape($data['filterText']) . "%'";
				$sql .= " OR LCASE(pro.model) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.sku) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.upc) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.ean) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.jan) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.isbn) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= " OR LCASE(pro.mpn) = '" . $this->db->escape(utf8_strtolower($data['filterText'])) . "'";
				$sql .= ")";

			}

		}

		if(isset($data['brand_id']) AND $data['brand_id'] != null )
		{

			$sql .= " AND pbm.pc_brand_id = ".(int)$data['brand_id'] ." ";
		}

		if(isset($data['model_id']) AND $data['model_id'] != null )
		{

			$sql .= " AND pbm.pc_model_id = ".(int)$data['model_id'] ." ";
		}

		if(isset($data['year_id']) AND $data['year_id'] != null )
		{

			$sql .= " AND pbm.pc_year_id = ".(int)$data['year_id'] ." ";
		}

		if ((isset($data['deals']) && $data['deals'] == 1) || $data['starting_price'] || $data['ending_price']) {
			$sql .= " HAVING ";

			$havingQuery = [];

			if (isset($data['deals']) && $data['deals'] == 1) {
				$havingQuery[] = " special IS NOT NULL ";
			}
			if ($data['starting_price']) {
				$havingQuery[] = " price >= {$data['starting_price']} ";
			}
			if ($data['ending_price']) {
				$havingQuery[] = " price <= {$data['ending_price']} ";
			}

			$sql .= implode(" AND ", $havingQuery);
		}


		/*
        *
        *
        * Here Sorting - Offset - Limit
        *
        *
        */
		$sort_data = [
			'name' => 'pro_desc.name',
			'model' => 'pro.model',
			'price' => 'pro.price',
			'rating' => 'rating',
			'default' => 'pro.sort_order'
		];

		if (isset($data['sort']) && array_key_exists($data['sort'], $sort_data)) {
			$sql .= " ORDER BY LCASE({$sort_data[$data['sort']]}) ";

		} else {
			$sql .= " ORDER BY pro.sort_order ";
		}

		if (isset($data['order']) && (strtolower($data['order']) == 'desc')) {
			$sql .= " DESC, LCASE(pro_desc.name) DESC ";
		} else {
			$sql .= " ASC, LCASE(pro_desc.name) ASC";
		}


		if (isset($data['limit'])) {
			$limit = (int)$data['limit'];
			$sql .= " LIMIT {$limit} ";
		}

		if (isset($data['start'])) {
			$start = (int)$data['start'];
			$sql .= " OFFSET {$start} ";
		}
		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductsByIds($Ids = array()) {
        if(count($Ids) == 0 || !is_array($Ids)) {
            return false;
        }

        $product_ids = implode(",", $Ids);
        if(empty($product_ids)) return false;

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

        $query = $columns = [];
        $columns[] = '*';
        $columns[] = 'TRIM(pd.name) AS name';
        $columns[] = 'p.image';
        $columns[] = 'm.name AS manufacturer';
        $columns[] = 'm.image AS manufacturerimg';
        $columns[] = '(SELECT price FROM product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = "' . (int)$customer_group_id . '" AND pd2.quantity = "1" AND ((IFNULL(pd2.date_start, "0000-00-00") = "0000-00-00" OR pd2.date_start < NOW()) AND (IFNULL(pd2.date_end, "0000-00-00") = "0000-00-00" OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount';
        $columns[] = '(SELECT price FROM product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = "' . (int)$customer_group_id . '" AND ((IFNULL(ps.date_start, "0000-00-00") = "0000-00-00" OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, "0000-00-00") = "0000-00-00" OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special';
        $columns[] = '(SELECT date_end FROM product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = "' . (int)$customer_group_id . '" AND ((IFNULL(ps.date_start, "0000-00-00") = "0000-00-00" OR ps.date_start < NOW()) AND (IFNULL(ps.date_end, "0000-00-00") = "0000-00-00" OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special_enddate';
        $columns[] = '(SELECT points FROM product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = "' . (int)$customer_group_id . '" LIMIT 1) AS reward';
        $columns[] = '(SELECT ss.name FROM stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = "' . (int)$this->config->get('config_language_id') . '") AS stock_status';
        $columns[] = '(SELECT wcd.unit FROM weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = "' . (int)$this->config->get('config_language_id') . '") AS weight_class';
        $columns[] = '(SELECT lcd.unit FROM length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = "' . (int)$this->config->get('config_language_id') . '") AS length_class';
        $columns[] = '(SELECT AVG(rating) AS total FROM review r1 WHERE r1.product_id = p.product_id AND r1.status = "1" GROUP BY r1.product_id) AS rating';
        $columns[] = '(SELECT COUNT(*) AS total FROM review r2 WHERE r2.product_id = p.product_id AND r2.status = "1" GROUP BY r2.product_id) AS reviews';
        $columns[] = "(SELECT CONCAT('[', GROUP_CONCAT(JSON_OBJECT('sort_order', sort_order, 'image', image)), ']') as images from product_image where p.product_id = product_image.product_id) as product_images";
        $columns[] = 'p.sort_order';
        $query[] = 'SELECT DISTINCT %s FROM product p';
        $query[] = 'LEFT JOIN product_description pd ON (p.product_id = pd.product_id)';
        $query[] = 'LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)';
        $query[] = 'LEFT JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)';
        $query[] = 'WHERE 1=1';
        $query[] = 'AND p.product_id IN (' . $product_ids . ')';
        $query[] = 'AND pd.language_id = "' . (int)$this->config->get('config_language_id') . '"';
        $query[] = 'AND p.status = "1"';
        $query[] = 'AND p.date_available <= NOW()';
        $query[] = 'AND p2s.store_id = "' . (int)$this->config->get('config_store_id') . '"';

        $query = $this->db->query(vsprintf(implode(' ', $query), [
            implode(',', $columns)
        ]));


        if ($query->num_rows) {
            $products = array();
            foreach ($query->rows as $product) {

				$products[$product['product_id']] =  array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'meta_description' => $product['meta_description'],
                    'meta_keyword' => $product['meta_keyword'],
                    'tag' => $product['tag'],
                    'model' => $product['model'],
                    'sku' => $product['sku'],
                    'upc' => $product['upc'],
                    'ean' => $product['ean'],
                    'jan' => $product['jan'],
                    'isbn' => $product['isbn'],
                    'mpn' => $product['mpn'],
                    'location' => $product['location'],
                    'quantity' => $product['quantity'],
                    'stock_status' => $product['stock_status'],
                    'stock_status_id' => $product['stock_status_id'],
                    'image' => $product['image'],
                    'manufacturer_id' => $product['manufacturer_id'],
                    'manufacturer' => $product['manufacturer'],
                    'price' => $product['discount'] ? $product['discount'] : $product['price'],
                    'special' => $product['special'],
                    'special_enddate' => $product['special_enddate'],
                    'reward' => $product['reward'],
                    'points' => $product['points'],
                    'tax_class_id' => $product['tax_class_id'],
                    'date_available' => $product['date_available'],
                    'weight' => $product['weight'],
                    'weight_class_id' => $product['weight_class_id'],
                    'length' => $product['length'],
                    'width' => $product['width'],
                    'height' => $product['height'],
                    'length_class_id' => $product['length_class_id'],
                    'subtract' => $product['subtract'],
                    'rating' => round($product['rating']),
                    'reviews' => $product['reviews'] ? $product['reviews'] : 0,
                    'minimum' => $product['minimum'],
                    'sort_order' => $product['sort_order'],
                    'status' => $product['status'],
                    'date_added' => $product['date_added'],
                    'date_modified' => $product['date_modified'],
                    'viewed' => $product['viewed'],
                    'manufacturerimg' => $product['manufacturerimg'],
                    'product_images' => json_decode($product['product_images'], true),
                    'prize_draw' => $prize_draw,
                    'consumed_percentage' => $consumed_percentage
                );
            }

            $products = array_replace(array_flip($Ids), $products);

            return $products;
        } else {
            return false;
        }
	}
	
	public function getProductVariationSku($product_id) {
        $query = $this->db->query("SELECT * FROM product_variations WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
	}
	

	public function getProductVariationOptionValuesDetailed($lang_id, $ov_ids) {
		$query = "SELECT *, ovd.name as option_value_name FROM `" . DB_PREFIX . "option_value` ov LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id=ovd.option_value_id AND ovd.language_id='{$lang_id}')";
		$query .= " LEFT JOIN `" . DB_PREFIX . "option` o ON (ov.option_id=o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id=od.option_id AND ovd.language_id='{$lang_id}')";
		$query .= " WHERE ov.option_value_id IN ({$ov_ids}) AND od.language_id='{$lang_id}'";
		// return $query;
		return $this->db->query($query)->rows;
	}


	public function getProductsOptionsByCategory($category_id) {
		$product_options = $this->db->query("SELECT DISTINCT o.option_id, od.name, o.type FROM `option` o LEFT JOIN option_description od ON (o.option_id = od.option_id) INNER JOIN product_option po ON (po.option_id = o.option_id) INNER JOIN product_to_category ptc ON (ptc.product_id = po.product_id AND ptc.category_id = {$category_id}) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order")->rows;
		$product_options = array_map(function ($po) {
			$po['option_values'] = $this->db->query("SELECT ov.option_value_id, ov.option_id, ovd.name FROM `option_value` ov LEFT JOIN option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . $po['option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order")->rows;
			return $po;
		}, $product_options);

		return $product_options;
	}

	public function addReview($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$data['name'] . "', product_id = '" . (int)$data['product'] . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW(), status=1");
	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 20;
		}		
		
		$query = $this->db->query("SELECT r.review_id, r.author, r.rating, r.text, p.product_id, r.date_added FROM " . DB_PREFIX . "review r LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
			
		return $query->rows;
	}
	public function AddProductImages($product_id,$uploaded_images){
        if (isset($uploaded_images)) {
            if(is_array($uploaded_images)){
                foreach ($uploaded_images as $image) {
                    if($image != null && $image != '' ){
                        $res =  $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $image . "'");
                    }
                }
            }else{
                $res =  $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $uploaded_images . "'");
            }
        }
        return $res;
    }
	public function updateproductMainImage($product_id,$image) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $image . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	public function getProductCategories($product_id)
    {
		$language_id = $this->config->get('config_language_id');
		$query=$this->db->query("SELECT cd.name FROM " . DB_PREFIX . "product_to_category pc 
		LEFT JOIN " . DB_PREFIX . "category_description cd 
		ON (pc.category_id = cd.category_id)
		WHERE pc.product_id ='" . (int)$product_id . "' 
		AND cd.language_id = '" . (int)$language_id . "'");
        return $query->rows;
    }

    public function getRentDisabledDates($from,$to,$product_id,$stock_quantity,$cart_quantity = 0)
    {
        
        // $from and $to are timpestamps
        $disabled_days = [];
        $begin = new DateTime(date('Y-m-d',$from));
        $end = new DateTime(date('Y-m-d',$to));
        $end->setTime(0,0,1);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);
        foreach ($period as $dt) {
            $data = $this->checkProductRentals($product_id,$dt->format("Y-m-d"));
            //two conditions 1-only display the dates in product page 2-submit dates to cart
            if($cart_quantity == 0 && $data['rented_items'] >= $stock_quantity)
                // displaying dates in product page
                $disabled_days[] = $dt->format("Y-m-d");
            elseif( $cart_quantity != 0 && ($data['rented_items'] + $cart_quantity ) > $stock_quantity)
                // submit dates on add to cart pressed
                $disabled_days[] = $dt->format("Y-m-d");
        }
        return $disabled_days;
    }

    public function checkProductRentals($product_id,$date){
        $sql = "SELECT SUM(op.quantity) as rented_items
            FROM `". DB_PREFIX ."order_product_rental` as opr
            JOIN `". DB_PREFIX ."order_product` as op
            On opr.order_product_id = op.order_product_id
            WHERE op.product_id = '" . $product_id . "'
            AND opr.from_date <= '" . $date . "' AND opr.to_date>='" . $date . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getProductBundles($main_product_id) {
        $query = $this->db->query("SELECT pb.bundle_product_id, pb.discount FROM " . DB_PREFIX . "product_bundles pb WHERE pb.main_product_id = " . (int)$main_product_id);
        $rows = $query->rows;
        $returned_product_ids = array_column($rows, 'bundle_product_id');
        $returned_product_discounts = array_column($rows, 'discount','bundle_product_id');
        $products = $this->getProductsByIds($returned_product_ids);
        foreach ($products as $product) {
            $product_data[$product['product_id']] = $product;
            $product_data[$product['product_id']]['bundle_discount'] = $returned_product_discounts[$product['product_id']];
        }
        
        return $product_data;
    }

	public function getAttributeValuesCurrentLanguage($advanced_attribute_id) {
		$attribute_value_data = array();

		$sql = "
			SELECT av.advanced_attribute_value_id as id , avd.name
			FROM " . DB_PREFIX . "advanced_attribute_value av 
			LEFT JOIN " . DB_PREFIX . "advanced_attribute_value_description avd 
			ON (av.advanced_attribute_value_id = avd.advanced_attribute_value_id) 
			WHERE av.advanced_attribute_id = '" . (int)$advanced_attribute_id .
			"' AND avd.language_id = " . (int)$this->config->get('config_language_id') ;

		$attribute_value_query = $this->db->query($sql);

		return $attribute_value_query->rows;
	}

    public function search($searchText)
    {
        $result = [];
        $sqlSelections = [
            'p.product_id',
            'p.image',
            'pd.name',
            'pd.description', 
            'p.price',
            'p.quantity',
        ];

        $selections = implode(', ', $sqlSelections);

        $sql = "SELECT ".$selections;
        $sql .= " FROM " . DB_PREFIX . "product p";

        if (\Extension::isInstalled('multiseller')) {
            $sql .= ' LEFT JOIN ms_product msp ON p.product_id = msp.product_id';
            $sql .= ' LEFT JOIN ms_seller mss ON msp.seller_id = mss.seller_id';
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                  LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE 
                  p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if ($searchText) {
            $sql .= " AND (";

            $implode[] = "(pd.name LIKE '%" . $this->db->escape($searchText) . "%' 
                            OR pd.name LIKE '%".$this->db->escape($searchText)."%' 
                            OR pd.name LIKE '%".$this->db->escape($searchText)."%')";
            
            $sql .= " " . implode(" AND ", $implode) . "";
            

            $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";
            $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($searchText)) . "'";

            if (\Extension::isInstalled('multiseller')) {
                $sql .= ' AND (msp.product_id IS NULL OR mss.seller_id IS NULL OR mss.products_state = 1)';
            }

            $sql .= " ) GROUP BY pd.product_id LIMIT 10";
            
            $result = $this->db->query($sql)->rows ?? [];
        }

        return $result;
    }	

	public function getRelatedProducts($product_id,$extra_data=[])
    {
        $this->load->model('tool/image');
        $with_options=$extra_data['with_options'];
        $related_results = $this->getProductRelated($product_id);
        $related_products = array();
        foreach ($related_results as $related_result) {
            if ($related_result['image']) {
                $related_image = $this->model_tool_image->resize($related_result['image'], 250, 250);
            } else {
                $related_image = $this->model_tool_image->resize('no_image.jpg', 250, 250);
            }

            if ($this->customer->isCustomerAllowedToViewPrice()) {
                $related_price = $this->currency->format($this->tax->calculate($related_result['price'],
                    $related_result['tax_class_id'], $this->config->get('config_tax')));

                $float_related_price =  $this->currency->format($this->tax->calculate(
                    $related_result['price'],
                    $related_result['tax_class_id'],
                    $this->config->get('config_tax')
                ),'','',false);
            } else {
                $related_price = $float_related_price = false;
            }

            if ((float)$related_result['special']) {
                $related_special = $this->currency->format($this->tax->calculate($related_result['special'],
                    $related_result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                $float_related_special = $this->tax->calculate(
                    $related_result['special'],
                    $related_result['tax_class_id'],
                    $this->config->get('config_tax')
                );
            } else {
                $related_special = $float_related_special = false;
            }
            $related_result['description'] = $this->_replaceUnsecureUrlWithSecureUrlFromHtmlTags($related_result['description']);
            $seller_id = $this->getProductSellerId($related_result['product_id'])['seller_id'];
			if($with_options){
				$options=$this->getProductsOptions($related_result['product_id']);
				}
            $related_products[] = array(
                'product_id' => $related_result['product_id'],
                'image' => $related_image,
                'name' => $related_result['name'],
                'price' => $related_price,
                'float_price' => $float_related_price,
                'special' => $related_special,
                'float_special' => $float_related_special,
                'currency' => $this->currency->getCode(),
                'short_description' => (mb_substr(html_entity_decode($related_result['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
                'seller_id' => $seller_id,
                'is_multiseller' => empty($seller_id) ? false : true,
				'product_options'=>  $options ? $options : [],
            );
             
        }
        return $related_products;
    }

	public function getRecommendedProducts($extra_data=[])
	{
        $this->load->model('tool/image');
        $with_options=$extra_data['with_options'];
        $recommended_results = $this->getBestSellerProducts(10);
        $recommended_product = array();
        foreach ($recommended_results as $recommended_result) {
            if ($recommended_result['image']) {
                $recommended_image = $this->model_tool_image->resize($recommended_result['image'], 250, 250);
            } else {
                $recommended_image = $this->model_tool_image->resize('no_image.jpg', 250, 250);
            }

            if ($this->customer->isCustomerAllowedToViewPrice()) {
                $recommended_price = $this->currency->format($this->tax->calculate($recommended_result['price'],
                    $recommended_result['tax_class_id'], $this->config->get('config_tax')));

                $float_recommended_price =  $this->currency->format($this->tax->calculate(
                    $recommended_result['price'],
                    $recommended_result['tax_class_id'],
                    $this->config->get('config_tax')
                ),'','',false);
            } else {
                $recommended_price = $float_recommended_price = false;
            }

            if ((float)$recommended_result['special']) {
                $recommended_special = $this->currency->format($this->tax->calculate($recommended_result['special'],
                    $recommended_result['tax_class_id'], $this->config->get('config_tax')), '', '', false);

                $float_recommended_special = $this->tax->calculate(
                    $recommended_result['special'],
                    $recommended_result['tax_class_id'],
                    $this->config->get('config_tax')
                );
            } else {
                $recommended_special = $float_recommended_special = false;
            }
            $recommended_result['description'] = $this->_replaceUnsecureUrlWithSecureUrlFromHtmlTags($recommended_result['description']);
            $seller_id = $this->getProductSellerId($recommended_result['product_id'])['seller_id'];
			if($with_options){
				$options=$this->getProductsOptions($recommended_result['product_id']);
				}
            $recommended_products[] = array(
                'product_id' => $recommended_result['product_id'],
                'image' => $recommended_image,
                'name' => $recommended_result['name'],
                'price' => $recommended_price,
                'float_price' => $float_recommended_price,
                'special' => $recommended_special,
                'float_special' => $float_recommended_special,
                'currency' => $this->currency->getCode(),
                'short_description' => (mb_substr(html_entity_decode($recommended_result['description'], ENT_QUOTES, 'UTF-8'), 0, 25)),
                'seller_id' => $seller_id,
                'is_multiseller' => empty($seller_id) ? false : true,
				'product_options'=>  $options ? $options : [],
            );
            
        }
        return $recommended_products;
    }
	public function getProductsOptions($product_id)
	{	
        foreach ($this->getProductOptions($product_id) as $option) {
            if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
                $option_value_data = array();
    
                foreach ($option['option_value'] as $k  =>  $option_value) {
                   $product_option_value_id =  $option_value['product_option_value_id'];
                    if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                        if ($this->customer->isCustomerAllowedToViewPrice() && (float)$option_value['price']) {
                            $price = $this->currency->format($this->tax->calculate(
                                $option_value['price'],
                                $product_info['tax_class_id'],
                                $this->config->get('config_tax')
                            ));
    
                            $float_price = $this->currency->format($this->tax->calculate(
                                $option_value['price'],
                                $product_info['tax_class_id'],
                                $this->config->get('config_tax')
                            ),'','',false);
                        } else {
                            $price = $float_price = false;
                        }
    
                        $this->load->model('tool/image');

                        if(isset($option_value['images'][0]['image']) && !empty($option_value['images'][0]['image']))
                            $product_option_image = $option_value['images'][0]['image'];
                        elseif ($option_value['image'])
                            $product_option_image = $option_value['image'];
                        else
                            $product_option_image ="no_image.jpg";
                        $option_value_data[] = array(
                            'product_option_value_id' => $option_value['product_option_value_id'],
                            'option_value_id'         => $option_value['option_value_id'],
                            'name'                    => $option_value['name'],
                            'image_thumb'             => $this->model_tool_image->resize($product_option_image, 50, 50),
                            'image'                   => \Filesystem::getUrl('image/'.$product_option_image),
                            'price'                   => $price,
                            'float_price'             => $float_price,
                            'currency'                => $this->currency->getCode(),
                            'price_prefix'            => $option_value['price_prefix']
                        );
    
                    }
                }
                usort($option_value_data,function($first,$second){
                    return $first['product_option_value_id'] > $second['product_option_value_id'];
                });
    
                $options[] = array(
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value' => '0',
                    'option_id'         => $option['option_id'],
                    'name'              => $option['name'],
                    'type'              => $option['type'],
                    'option_value'      => $option_value_data,
                    'required'          => $option['required']
                );
            } elseif ($option['type'] == 'text_dis' || $option['type'] == 'text' || $option['type'] == 'textarea_dis' || $option['type'] == 'textarea' || $option['type'] == 'file_dis' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
                $options[] = array(
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value' => '0',
                    'option_id'         => $option['option_id'],
                    'name'              => $option['name'],
                    'type'              => $option['type'],
                    'option_value'      => $option['option_value'],
                    'required'          => $option['required']
                );
            }
        }
        return $options;
    }
	public function _replaceUnsecureUrlWithSecureUrlFromHtmlTags($html){
        return str_replace('src="//', 'src="https://', $html);
    }
}

?>
