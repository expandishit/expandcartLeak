<?php
class ModelWkposSupplyRequest extends Model {
	/**
	 * Adds an supply request for POS
	 * @param int $data returns the supply request id
	 */
	// public function addSupplyRequest($data) {
	// 	$this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_supplier_request SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', status = '" . $this->db->escape($data['status']) . "'");

	// 	return $this->db->getLastId();
	// }

	/**
	 * edits an supply request
	 * @param  int $request_id contains the supply request id
	 * @param  array $data          contains the form data for supply request
	 * @return null                none
	 */
	// public function editSupplyRequest($request_id, $data) {
	// 	$this->db->query("UPDATE " . DB_PREFIX . "wkpos_supplier_request SET name = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', status = '" . $this->db->escape($data['status']) . "' WHERE request_id = '" . (int)$request_id . "'");
	// }

	/**
	 * Deletes an supply request
	 * @param  int $request_id contains the supply request id
	 * @return null                none
	 */
	public function cancelSupplyRequest($request_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_supplier_request SET cancel = '1' WHERE request_id = '" . (int)$request_id . "'");

		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_request_info SET cancel = '1' WHERE request_id = '" . (int)$request_id . "'");
	}

	/**
	 * Fetches the content of supply request
	 * @param  int $request_id contains the supply request id
	 * @return array                returns the content of an supply request
	 */
	// public function getSupplyRequest($request_id) {
	// 	$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "wkpos_supplier_request WHERE request_id = '" . (int)$request_id . "'");

	// 	$supply_request = array(
	// 		'name'       => $query->row['name'],
	// 		'address'    => $query->row['address'],
	// 		'status'     => $query->row['status']
	// 	);

	// 	return $supply_request;
	// }

	public function getRequestsDt($data = [], $filterData = []) {
		$query = $fields = [];

	    $fields = [
	      '*',
	      'user_name as name '
	    ];

	    $fields = sprintf(
	         implode(',', $fields)
	    );

	    $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'wkpos_supplier_request`';

	    $query[] = 'WHERE request_id > 0';

	    $total = $this->db->query(
	            'SELECT COUNT(*) AS totalData FROM ('.
	            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	            .') AS t'
	        )->row['totalData'];

	    if (isset($filterData['search'])) {

	      $filterData['search'] = $this->db->escape($filterData['search']);

	      $query[] = 'AND (';

	      if (trim($filterData['search'][0]) == '#') {
	          $request_id = preg_replace('/\#/', '', $filterData['search']);
	          $query[] = "user_name LIKE '%{$request_id}%'";
              $query[] = " OR comment LIKE '%{$request_id}%'";
	      } else {
	          $query[] = "request_id LIKE '%{$filterData['search']}%'";
	          $query[] = 'OR user_name LIKE "%' . $filterData['search'] . '%"';
	      }
	      $query[] = ')';
	    }

        if (isset($filterData['typ'])) {
            if($filterData['typ'] == 'c')
                $query[] = "AND cancel = 1";
            else if($filterData['typ'] == 'a')
                $query[] = "AND status = 1 AND cancel = 0";
            else
                $query[] = "AND status = 0 AND cancel = 0";
        }

	    $totalFiltered = $this->db->query(
	        'SELECT COUNT(*) AS totalData FROM ('.
	        str_replace($fields, '0 as fakeColumn', implode(' ', $query))
	        .') AS t'
        )->row['totalData'];

        $sort_data = array(
			'request_id',
			'name',
			'date_added',
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
	 * Fetches all supply requests
	 * @param  array  $data contains the filter data
	 * @return array       return the supply requests
	 */
	public function getSupplyRequests($data = array()) {
		$sql = "SELECT *, user_name as name FROM " . DB_PREFIX . "wkpos_supplier_request";

		$sort_data = array(
			'request_id',
			'name',
			'date_added',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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
	 * Fetches the total number of supply requests
	 * @return int contains the total number of supply requests
	 */
	public function getTotalSupplyRequests() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "wkpos_supplier_request");

		return $query->row['total'];
	}

	public function getRequestInfo($request_id) {
		$sql = "SELECT ri.quantity, ri.status, ri.comment, pd.name, ri.supplier FROM " . DB_PREFIX . "wkpos_request_info ri LEFT JOIN " . DB_PREFIX . "product_description pd ON (ri.product_id = pd.product_id) WHERE ri.request_id = '" . (int)$request_id . "' AND pd.language_id = '" . $this->config->get('config_language_id') . "'";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function updateStatus($request_id) {
		$outlet_info = $this->db->query("SELECT wu.outlet_id FROM " . DB_PREFIX . "wkpos_supplier_request wsr LEFT JOIN " . DB_PREFIX . POS_USERS_TABLE . " wu ON (wsr.user_id = wu.user_id) WHERE wsr.request_id = '" . (int)$request_id . "'")->row;

		$request_info = $this->db->query("SELECT product_id, quantity FROM " . DB_PREFIX . "wkpos_request_info WHERE request_id = '" . (int)$request_id . "'")->rows;

		foreach ($request_info as $request) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = quantity + '" . $request['quantity'] . "' WHERE product_id = '" . $request['product_id'] . "'");
			$this->db->query("UPDATE " . DB_PREFIX . "wkpos_products SET quantity = quantity + '" . $request['quantity'] . "' WHERE product_id = '" . $request['product_id'] . "' AND outlet_id = '" . $outlet_info['outlet_id'] . "'");
		}

		$sql = "UPDATE " . DB_PREFIX . "wkpos_supplier_request SET status = '1', cancel='0' WHERE request_id = '" . (int)$request_id . "'";

		$this->db->query($sql);

		$this->db->query("UPDATE " . DB_PREFIX . "wkpos_request_info SET status = '1', cancel='0' WHERE request_id = '" . (int)$request_id . "'");
	}
}
