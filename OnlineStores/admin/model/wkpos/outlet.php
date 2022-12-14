<?php
class ModelWkposOutlet extends Model {
	/**
	 * Adds an outlet for POS
	 * @param int $data returns the outlet id
	 */
	public function addOutlet($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_outlet SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', status = '" . $this->db->escape($data['status']) . "'");

		return $this->db->getLastId();
	}

	/**
	 * edits an outlet
	 * @param  int $outlet_id contains the outlet id
	 * @param  array $data          contains the form data for outlet
	 * @return null                none
	 */
	public function editOutlet($outlet_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_outlet SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', status = '" . $this->db->escape($data['status']) . "' WHERE outlet_id = '" . (int)$outlet_id . "'");
	}

	/**
	 * Deletes an outlet
	 * @param  int $outlet_id contains the outlet id
	 * @return null                none
	 */
	public function deleteOutlet($outlet_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$outlet_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "wkpos_products WHERE outlet_id = '" . (int)$outlet_id . "'");
		//$this->db->query("UPDATE " . DB_PREFIX . "wkpos_user SET status = '0' WHERE outlet_id = '" . (int)$outlet_id . "'");
	}

	/**
	 * Fetches the content of outlet
	 * @param  int $outlet_id contains the outlet id
	 * @return array                returns the content of an outlet
	 */
	public function getOutlet($outlet_id) {
		$outlet = array();
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$outlet_id . "'");
		if ($query->num_rows) {
			$outlet = array(
				'name'       => $query->row['name'],
				'address'    => $query->row['address'],
				'country_id' => $query->row['country_id'],
				'zone_id'    => $query->row['zone_id'],
				'status'     => $query->row['status']
			);
		}

		return $outlet;
	}

	public function getOutletsDt($data = [], $filterData = []) {
		$query = $fields = [];

	    $fields = [
	      '*'
	    ];

	    $fields = sprintf(
	          implode(',', $fields)
	    );

	    $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'wkpos_outlet`';

	    $query[] = 'WHERE outlet_id > "0"';

	    $total = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';

	      if (trim($filterData['search'][0]) == '#') {
	          $outlet_id = preg_replace('/\#/', '', $filterData['search']);
	          $query[] = "outlet_id LIKE '%{$outlet_id}%'";
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
			$query[] = " ORDER BY name";
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
	 * Fetches all outlets
	 * @param  array  $data contains the filter data
	 * @return array       return the outlets
	 */
	public function getOutlets($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "wkpos_outlet";

		if(isset($data['enabled'])){
            $sql .= " WHERE status=1 ";
        }

		$sort_data = array(
			'name',
			'status'
		);

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
	 * Fetches the total number of outlets
	 * @return int contains the total number of outlets
	 */
	public function getTotalOutlets() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "wkpos_outlet");

		return $query->row['total'];
	}

	// assigns all products to given outlet
	public function assignAll($outlet) {
		 $products = $this->db->query("SELECT product_id, quantity, status FROM " . DB_PREFIX . "product")->rows;

		 foreach ($products as $product) {
			 $assigned_quantity = $this->db->query("SELECT SUM(quantity) as assigned FROM " . DB_PREFIX . "wkpos_products WHERE product_id = '" . (int)$product['product_id'] . "' AND outlet_id != '" . (int)$outlet . "'")->row;

			 $quantity = $product['quantity'] - $assigned_quantity['assigned'];

			 $pos_product = $this->db->query("SELECT * FROM " . DB_PREFIX . "wkpos_products WHERE product_id = '" . $product['product_id'] . "' AND outlet_id = '" . (int)$outlet . "'")->row;

			 if (isset($pos_product['wkpos_products_id'])) {
				 $this->db->query("UPDATE " . DB_PREFIX . "wkpos_products SET status = '" . $product['status'] . "', quantity = '" . $quantity . "' WHERE wkpos_products_id = '" . $pos_product['wkpos_products_id'] . "' AND outlet_id = '" . (int)$outlet . "'");
			 } else {
				 $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_products SET status = '" . $product['status'] . "', quantity = '" . $quantity . "', product_id = '" . $product['product_id'] . "', outlet_id = '" . (int)$outlet . "'");
			 }
		 }
	}

    // assigns product to outlet
    public function assignProduct($product_id, $outlet) {
        $pos_product = $this->db->query("SELECT * FROM " . DB_PREFIX . "wkpos_products WHERE product_id = '" . $product_id . "' AND outlet_id = '" . (int)$outlet . "'")->row;

        if (!isset($pos_product['wkpos_products_id'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_products SET status = 1, quantity = 1, product_id = '" . $product_id . "', outlet_id = '" . (int)$outlet . "'");
        }
    }
	// public function addPermission($outlet_id, $type, $route) {
	// 	$outlet_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$outlet_id . "'");

	// 	if ($outlet_query->num_rows) {
	// 		$data = json_decode($outlet_query->row['permission'], true);

	// 		$data[$type][] = $route;

	// 		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_outlet SET permission = '" . $this->db->escape(json_encode($data)) . "' WHERE outlet_id = '" . (int)$outlet_id . "'");
	// 	}
	// }

	// public function removePermission($outlet_id, $type, $route) {
	// 	$outlet_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$outlet_id . "'");

	// 	if ($outlet_query->num_rows) {
	// 		$data = json_decode($outlet_query->row['permission'], true);

	// 		$data[$type] = array_diff($data[$type], array($route));

	// 		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_outlet SET permission = '" . $this->db->escape(json_encode($data)) . "' WHERE outlet_id = '" . (int)$outlet_id . "'");
	// 	}
	// }
}
