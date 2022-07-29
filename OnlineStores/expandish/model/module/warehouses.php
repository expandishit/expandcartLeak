<?php
class ModelModuleWarehouses extends Model {

	public function getSettings()
    {
        return $this->config->get('warehouses');
    }

    public function is_installed()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

     /**
	 * Fetches seller/admin warehouses
	 * @param  int $seller_id contains the seller id
	 * @return array returns list of warehouses
	 */
	public function getWarehouses($seller_id) {
		$warehouses = 0;
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "warehouses WHERE seller_id = '" . (int)$seller_id . "' OR seller_id = '0'");
		if ($query->num_rows) {
			$warehouses = $query->rows;
		}

		return $warehouses;
	}
    
    /**
	 * Fetches product warehouses
	 * @param  int $product_id
	 * @return array returns list of warehouses
	 */
	public function getProductWarehouses($product_id) {
		$pr_warehouses = [];
		$query = $this->db->query("SELECT warehouse_id FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . (int)$product_id . "'");
		if ($query->num_rows) {
			foreach ($query->rows as $key => $value) {
				$pr_warehouses[] = $value['warehouse_id'];
			}
		}

		return $pr_warehouses;
	}

	/**
	 * Assign Product to warehouses
	 * @param  int $product_id, Array warehouses ids
	 * @return array returns bool
	 */
	public function AssignProdToWarehouses($product_id, $warehouses) {
		$count = count($warehouses);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . (int)$product_id . "'");

		for($i=0; $i<$count; $i++){
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET status = '1', product_id = '" . $product_id . "', warehouse_id = '" . (int)$warehouses[$i] . "'");
		}

		$this->db->query("UPDATE " . DB_PREFIX . "product SET shipping = 1 WHERE product_id = " . (int)$product_id);
	}

	/**
	 * Un-assign Products to warehouses
	 * @param  int $product_id
	 * @return array returns bool
	 */
	public function UnassignProdToWarehouses($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_warehouse WHERE product_id = '" . (int)$product_id . "'");
	}

     /**
	 * Fetches the content of warehouse
	 * @param  int $seller_id contains the seller id
	 * @return array returns the id of an warehouse
	 */
	public function seller_warehouse($seller_id) {
		$warehouse = 0;
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "warehouses WHERE seller_id = '" . (int)$seller_id . "'");
		if ($query->num_rows) {
			$warehouse = $query->row;
		}

		return $warehouse;
	}

	/**
	 * Create Warehouse for seller
	 * @return array returns bool
	 */
	public function create_warehouse($seller_id, $data) {
		$newWs = $this->db->query("INSERT INTO " . DB_PREFIX . "warehouses SET seller_id = '" . (int)$seller_id . "', name = '" . $this->db->escape($data['name']) . "', rates = '" . $this->db->escape($data['rates']) . "', duration = '" . $this->db->escape($data['duration']) . "', status = 1");

		if($newWs){

			$ws_id = $this->db->getLastId();

			$pr_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "ms_product WHERE seller_id = '" . (int)$seller_id . "'");
			if ($pr_query->num_rows) {
				$products = $pr_query->rows;

				foreach ($products as $product) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_warehouse SET status = '1', product_id = '" . $product['product_id'] . "', warehouse_id = '" . (int)$ws_id . "'");
				}
			}
		}
	}

	/**
	 * Update Warehouse of a seller
	 * @return array returns bool
	 */
	public function update_warehouse($seller_id, $wr_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "warehouses SET seller_id = '" . (int)$seller_id . "', name = '" . $this->db->escape($data['name']) . "', rates = '" . $this->db->escape($data['rates']) . "', duration = '" . $this->db->escape($data['duration']) . "' WHERE id = '" . (int)$wr_id . "' AND seller_id = '" . (int)$seller_id . "'");
	}

}
