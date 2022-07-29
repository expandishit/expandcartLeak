<?php
class ModelModuleWarehouses extends Model {
	/**
	 * Adds an warehouse
	 * @param int $data returns the warehouse id
	 */
	public function addWarehouse($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "warehouses SET seller_id = '" . (int)$data['seller_id'] . "', name = '" . $this->db->escape($data['name']) . "',  rates = '" . $this->db->escape($data['rates']) . "',  duration = '" . $this->db->escape($data['duration']) . "', status = '" . $this->db->escape($data['status']) . "'");

		return $this->db->getLastId();
	}

	/**
	 * edits an warehouse
	 * @param  int $warehouse_id contains the warehouse id
	 * @param  array $data          contains the form data for warehouse
	 * @return null                none
	 */
	public function editWarehouse($id, $data) 
	{
		$this->db->query("UPDATE " . DB_PREFIX . "warehouses SET seller_id = '" . (int)$data['seller_id'] . "', name = '" . $this->db->escape($data['name']) . "',  rates = '" . $this->db->escape($data['rates']) . "',  duration = '" . $this->db->escape($data['duration']) . "', status = '" . $this->db->escape($data['status']) . "' WHERE id = '" . (int)$id . "'");
	}

	/**
	 * Deletes an warehouse
	 * @param  int $warehouse_id contains the warehouse id
	 * @return null                none
	 */
	public function deleteWarehouse($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "warehouses WHERE id = '" . (int)$id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_warehouse WHERE warehouse_id = '" . (int)$id . "'");
	}

	/**
	 * Fetches the content of warehouse
	 * @param  int $warehouse_id contains the warehouse id
	 * @return array                returns the content of an warehouse
	 */
	public function getWarehouse($id) {
		$warehouse = array();
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "warehouses WHERE id = '" . (int)$id . "'");
		if ($query->num_rows) {
			$warehouse = $query->row;
		}

		return $warehouse;
	}

	public function getWarehousesDt($data = [], $filterData = []) {
		$query = $fields = [];

	    $fields = [
	      '*'
	    ];

	    $fields = sprintf(
	          implode(',', $fields)
	    );

	    $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'warehouses`';

	    $query[] = 'WHERE id > "0"';

	    $total = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';

	      if (trim($filterData['search'][0]) == '#') {
	          $id = preg_replace('/\#/', '', $filterData['search']);
	          $query[] = "id LIKE '%{$id}%'";
	      } else {
	          $query[] = "name LIKE '%{$filterData['search']}%'";
	          $query[] = 'OR address LIKE "%' . $filterData['search'] . '%"';
	      }
	      $query[] = ')';
	    }

	    $totalFiltered = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
        )->row['totalData'];

        $sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$query[] = " ORDER BY " . $data['sort'];
		} else {
			$query[] = " ORDER BY id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query[] = " DESC";
		} else {
			$query[] = " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query(implode(' ', $query));

	    return [
	      'data' => $query->rows,
	      'total' => $total,
	      'totalFiltered' => $totalFiltered
	    ];
	}

	/**
	 * Fetches all warehouses
	 * @param  array  $data contains the filter data
	 * @return array       return the warehouses
	 */
	public function getWarehouses($data = array()) {
        $selections = '*';
        if (isset($data['selections']) && is_array($data['selections'])) {
            $selections = implode(', ', $data['selections']);
        }
		$sql = "SELECT $selections FROM " . DB_PREFIX . "warehouses WHERE 1 ";

		$sort_data = array(
			'name',
			'status'
		);

        if (isset($data['filter_name'])) {
            $sql .= " AND name LIKE '%".$data['filter_name']."%'";
        }

        if (isset($data['active'])) {
            $sql .= " AND status = 1 ";
        }

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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
	 * Fetches the total number of warehouses
	 * @return int contains the total number of warehouses
	 */
	public function getTotalwarehouses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "warehouses");

		return $query->row['total'];
	}
	
	/**
	 * Fetches the products based on the filters
	 * @param  array  $data contains different filters
	 * @return array       product data
	 */
	public function getProducts($data = array()) {
		$sql = "SELECT p.*, pd.*, sum(wp.quantity) as pos_quantity, wp.status as pos_status, wb.barcode FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_warehouse wp ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_barcode wb ON (p.product_id = wb.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_assign']) && !is_null($data['filter_assign'])) {
			$sql .= " AND wp.quantity = '" . (int)$data['filter_assign'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_pos_status']) && !is_null($data['filter_pos_status'])) {
			$sql .= " AND wp.status = '" . (int)$data['filter_pos_status'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'wp.status',
			'wp.quantity',
			'p.sort_order',
			'p.product_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_warehouse wp ON (p.product_id = wp.product_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_assign']) && !is_null($data['filter_assign'])) {
			$sql .= " AND wp.quantity = '" . (int)$data['filter_assign'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_pos_status']) && !is_null($data['filter_pos_status'])) {
			$sql .= " AND wp.status = '" . (int)$data['filter_pos_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getWarehouseProductData($product_id, $warehouse) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . $product_id . "' AND warehouse_id = '" . $warehouse . "'";

		$query = $this->db->query($sql);

		return array(
			'quantity' => isset($query->row['quantity']) ? $query->row['quantity'] : 0,
			'status'   => isset($query->row['status']) ? $query->row['status'] : 0
			);
	}

	public function changeStatus($product_id, $status, $warehouse) {
		$war_product = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . $product_id . "' AND warehouse_id = '" . (int)$warehouse . "'")->row;

		if (isset($war_product['product_id'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET status = '" . $status . "' WHERE product_id = '" . $war_product['product_id'] . "' AND warehouse_id = '" . (int)$warehouse . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET  product_id = '" . $product_id . "', warehouse_id = '" . (int)$warehouse . "', status = '" . $status . "'");
		}
	}


	public function getSettings()
    {
        return $this->config->get('warehouses');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    /**
	 * Get product assigned quantity to warehouse
	 * @param  int $product_id contains the id of product
	 * @param  int $warehouse   contains the warehouse id
	 * @return int assigned
	 */
    public function getAssignedQuantity($product_id, $warehouse_id) {
		$sql = "SELECT SUM(quantity) as assigned FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . (int)$product_id . "' AND warehouse_id != '" . (int)$warehouse_id . "'";
		$query = $this->db->query($sql)->row;
		//print_r($query);
		if (isset($query['assigned'])) {
			return $query['assigned'];
		} else {
			return 0;
		}
	}

	/**
	 * Assigns all products to warehouse
	 * @param  int $warehouse_id contains the id of warehouse
	 * @return null             none
	 */
	public function assignAll($warehouse_id) {
		 $products = $this->db->query("SELECT product_id, quantity, status FROM " . DB_PREFIX . "product")->rows;

		 foreach ($products as $product) {
			 $assigned_quantity = $this->getAssignedQuantity($product['product_id'], $warehouse_id);

			 if ($product['quantity'] >= $assigned_quantity['assigned']) {
				 $wr_product = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . $product['product_id'] . "' AND warehouse_id = '" . (int)$warehouse_id . "'")->row;

				 

				 if (!isset($wr_product['id'])) {
				 	$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET status = '1', quantity = '1', product_id = '" . $product['product_id'] . "', warehouse_id = '" . (int)$warehouse_id . "'");
				 }else{
				 	if($wr_product['quantity'] == 0)
				 		$quantity = 1;
				 	else
				 		$quantity = $wr_product['quantity'];

				 	$product['quantity'] = $quantity;

				 	$this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET status = '1', quantity = '" . $quantity . "' WHERE id = '" . $wr_product['id'] . "' AND warehouse_id = '" . (int)$warehouse_id . "'");
				 }

				 /*if (isset($wr_product['id'])) {
				 	$quantity = $product['quantity'] - $assigned_quantity['assigned'];

					$this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET status = '" . $product['status'] . "', quantity = '" . $quantity . "' WHERE id = '" . $wr_product['id'] . "' AND warehouse_id = '" . (int)$warehouse_id . "'");
				 } else {
					 $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET status = '" . $product['status'] . "', quantity = '1', product_id = '" . $product['product_id'] . "', warehouse_id = '" . (int)$warehouse_id . "'");
				 }*/
			}
		 }
	}

	/**
	 * UnAssigns all products from warehouse
	 * @param  int $warehouse_id contains the id of warehouse
	 * @return null             none
	 */
	public function unAssignAll($warehouse_id) {
		 $this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET status = '0', quantity = '0'  WHERE warehouse_id = '" . (int)$warehouse_id . "'");
	}

	/**
	 * Assigns the quantity of product to Warehouse
	 * @param  int $product_id contains the id of product
	 * @param  int $quantity   contains the quantity of product to be assigned
	 * @param  int $warehouse   contains the warehouse id
	 * @return null             none
	 */
	public function assignQuantity($product_id, $quantity, $warehouse_id) {
		$wr_product = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . $product_id . "' AND warehouse_id = '" . (int)$warehouse_id . "'")->row;

		if (isset($wr_product['id'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET quantity = '" . $quantity . "' WHERE id = '" . $wr_product['id'] . "' AND warehouse_id = '" . (int)$warehouse_id . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET quantity = '" . $quantity . "', product_id = '" . $product_id . "', warehouse_id = '" . (int)$warehouse_id . "'");
		}
	}

	/**
    *   Install the required values for the application.
    *
    *   @return boolean whether successful or not.
    */
    public function install($store_id = 0)
    {
        try 
        {
            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "warehouses` 
                    (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `seller_id` int(11) DEFAULT 0,
                      `name` varchar(191) NOT NULL,
                      `rates` text NOT NULL,
                      `duration` text NULL,
                      `status` int(1) DEFAULT 1,
                      PRIMARY KEY (`id`)
                    ) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->db->query($sql);

            //Assing default rates for geo zones
            $zones_rates[0] = '9999:1';
            $this->load->model('localisation/geo_zone');
			$geo_zones = $this->model_localisation_geo_zone->getGeoZones();
			foreach ($geo_zones as $geo_zone) {
				$zones_rates[$geo_zone['geo_zone_id']] = '9999:1';
			}
			$defaultRates = json_encode($zones_rates);
			//////////////////////////////////////

            $sql = "INSERT INTO `warehouses` VALUES (1, 0, 'Default', '".$defaultRates."','', 1)";
            
            $this->db->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_to_warehouse` 
                    (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `product_id` int(11) NOT NULL,
                      `warehouse_id` int(11) NOT NULL,
                      `quantity` int(11) DEFAULT 0,
                      `status` int(1) DEFAULT 1,
                      PRIMARY KEY (`id`),
                      KEY `product_id` (`product_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";            
            $this->db->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_to_warehouse` 
                    (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `order_id` int(11) NOT NULL,
                      `data` text DEFAULT NULL,
                      `init_data` text DEFAULT NULL,
                      `warehouses_list` text DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      KEY `order_id` (`order_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";            
            $this->db->query($sql);

            return true;

        } 
        catch (Exception $e) 
        {
            return false;
        }
    }

    /**
    *   Remove the values from the database.
    *
    *   @return boolean whether successful or not.
    */
    public function uninstall($store_id = 0)
    {
        try
        {
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "warehouses`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_to_warehouse`;");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_to_warehouse`;");
            return true;
        } 
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'warehouses', $inputs
        );

        return true;
    }

    public function getGroupProducts($address, $order_id) {
        if(!$order_id) return;
        $this->language->load_json('shipping/warehouse_shipping');

        ////Get order warehouse data
        $order_wr_data = $this->db->query("SELECT data FROM " . DB_PREFIX . "order_to_warehouse WHERE order_id = ". $order_id);
        if(!$order_wr_data->num_rows){
            return;
        }
        $order_wr_data = json_decode($order_wr_data->row['data'], true);
        ////////////////////////////////////

        //////// Cart Products Loop
        $pr_group = [];
        $wrs_prds = [];

        $this->load->model('sale/order');
        $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);

        foreach ($products as $product) {

            foreach ($order_wr_data['products'] as $prid => $wrid) {
                if($product['product_id'] == $prid){
                    //Attache product warehouse to product object
                    $product['warehouse'] = $wrid;
                    $pr_group[] = $product;
                    //$wrs_prds[$wrid]['products'][] = $product;
                    $wrs_prds[$product['product_id']] = $wrid;
                }
            }
        }
        //////// End Cart Products Loop

        /// Set warehouses_products session to use in controller/module/quickcheckout.php get_cart_view() method
        $warehouses_products = [
            'products'  => $pr_group,
            'warehouses_products'  => $wrs_prds,
            'wrs_costs' => $order_wr_data['wrs_costs'],
            'wrs_name'  => $order_wr_data['wrs_name'],
            'wrs_duration'  => $order_wr_data['wrs_duration']
        ];

        return $warehouses_products;
        ///////////////////////////////////////////////////////////
    }

    /**
     * change order's product warehouse | new changes v.21.05.2020
     * @return mixed
     */
    public function changeProductWarehouse($data){
        $order_wr_data = $this->db->query("SELECT data,init_data FROM " . DB_PREFIX . "order_to_warehouse WHERE order_id = ". $data['wr_orid']);

        if(!$order_wr_data->num_rows){
            return 0;
        }

        //Save first order data
        $init_data = false;
        if(!$order_wr_data->row['init_data']){
            $init_data = $order_wr_data->row['data'];
        }

        $current_data = json_decode($order_wr_data->row['data'], true);
        if(isset($current_data['products'][$data['wr_prid']])){
            $current_data['products'][$data['wr_prid']] = $data['wr_id'];

            $init_data_col = '';
            if($init_data)
                $init_data_col = ", init_data = '".$init_data."'";

            $warehouse_ids = '';
            if(count($current_data['products'])){
                $uniq_ids = array_unique(array_values($current_data['products']));
                $warehouse_ids = implode(',',$uniq_ids);
            }

            $this->db->query("UPDATE " . DB_PREFIX . "order_to_warehouse SET 
                                                                            data = '".json_encode($current_data)."'".$init_data_col.",
                                                                            warehouses_list = '[".$warehouse_ids."]'
                                                                            WHERE order_id = ". $data['wr_orid']);
            return 1;
        }
        return 0;
    }

    /**
     * subtract warehouses product quantities | new changes v.21.05.2020
     * @return mixed
     */
    public function subtractQuantities($data, $orid)
    {
        if(is_array($data) && count($data)){
            ////Get product's warehouse
            $order_wr_data = $this->db->query("SELECT data FROM " . DB_PREFIX . "order_to_warehouse WHERE order_id = ". $orid);
            if(!$order_wr_data->num_rows){
                return;
            }
            $order_wr_data = json_decode($order_wr_data->row['data'], true);
            $productsWarehouses = $order_wr_data['products'];
            ////////////////////////////////////

            foreach ($data as $prid => $qty){
                $this->db->query("UPDATE " . DB_PREFIX . "product_to_warehouse SET quantity = quantity - ".(int)$qty." WHERE product_id = ". (int)$prid . " AND warehouse_id = ".$productsWarehouses[$prid]);
            }
        }
        return;
    }
}
