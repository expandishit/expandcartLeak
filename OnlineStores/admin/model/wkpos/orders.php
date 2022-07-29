<?php
class ModelWkposOrders extends Model {
/**
 * Fetches a list of orders made through POS
 * @param  array  $data contains the filter data
 * @return array       returns the orders' data
 */
	public function getOrders($data = array()) {
		$sql = "SELECT o.order_id, wo.txn_id, wo.user_name as user, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o RIGHT JOIN " . DB_PREFIX . "wkpos_user_orders wo ON (wo.order_id = o.order_id)";

		if (isset($data['filter_order_status']) && $data['filter_order_status']) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_txn_id'])) {
			$sql .= " AND wo.txn_id = '" . (int)$data['filter_txn_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_user'])) {
			$sql .= " AND wo.user_name LIKE '%" . $this->db->escape($data['filter_user']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}

		$sort_data = array(
			'o.order_id',
			'wo.txn_id',
			'customer',
			'user',
			'status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
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

	public function getOrdersToFilter($data = [], $filterData = [], $abandoned = false)
	{
		$query = $fields = $statusQuery = [];

        $language_id = $this->config->get('config_language_id') ?: 1;

        $statusQuery[] = 'SELECT os.name FROM ' . DB_PREFIX . 'order_status os';
        $statusQuery[] = 'WHERE os.order_status_id = o.order_status_id AND os.language_id = "' . $language_id . '"';

        $fields = [
        	'o.order_id',
        	'wo.txn_id',
        	'wo.user_name as user',
        	'o.order_status_id',
            'CONCAT(o.firstname, " ", o.lastname) AS customer',
			'o.total',
			'o.currency_code', 
			'o.currency_value',
			'o.date_added',
			'o.date_modified',
			'o.customer_id',
			'o.gift_product',
			'o.email',
			'(SELECT `approved` FROM customer as cu where cu.customer_id=o.customer_id) as customer_status',
			'(%s) AS order_status',
		];

        $fields = sprintf(
        	implode(',', $fields),
			implode(' ', $statusQuery)
		);

        $query[] = 'SELECT ' . $fields . ' FROM `' . DB_PREFIX . 'order` o RIGHT JOIN ' . DB_PREFIX . 'wkpos_user_orders wo ON (wo.order_id = o.order_id)';

        if ($abandoned == false) {
        	$query[] = 'WHERE o.order_status_id > "0"';
		} else {
        	$query[] = 'WHERE o.order_status_id = "0"';
		}

        $total = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        if (isset($filterData['search'])) {

            $filterData['search'] = $this->db->escape($filterData['search']);

            $query[] = 'AND (';

            if (trim($filterData['search'][0]) == '#') {
                $order_id = preg_replace('/\#/', '', $filterData['search']);
                $query[] = "o.order_id LIKE '%{$order_id}%'";
            } else {
                $query[] = "o.order_id LIKE '%{$filterData['search']}%'";
                $query[] = 'OR CONCAT(o.firstname, " ", o.lastname) LIKE "%' . $filterData['search'] . '%"';
                $query[] = 'OR CONCAT(o.payment_address_1,",",o.payment_city,",",o.payment_country,",",o.payment_zone) LIKE "%' . $filterData['search'] . '%"';
                $query[] = 'OR email LIKE "%' . $filterData['search'] . '%"';
                $query[] = "OR o.payment_telephone LIKE '%{$filterData['search']}%'";
            }
            $query[] = ')';
        }

        if (isset($filterData['order_status_id']) && count($filterData['order_status_id']) > 0) {

            $statusesIds = implode(', ', $this->filterArrayOfIds($filterData['order_status_id']));

            $query[] = 'AND (o.order_status_id IN (' . $statusesIds . '))';
        }

        if (isset($filterData['customer_id']) && count($filterData['customer_id']) > 0) {

            $customersIds = implode(', ', $this->filterArrayOfIds($filterData['customer_id']));

            $query[] = 'AND (customer_id IN (' . $customersIds . '))';
        }

        if (isset($filterData['user_id']) && count($filterData['user_id']) > 0) {

            $customersIds = implode(', ', $this->filterArrayOfIds($filterData['user_id']));

            $query[] = 'AND (wo.user_id IN (' . $customersIds . '))';
        }

        if (isset($filterData['product_id']) && count($filterData['product_id']) > 0) {

            $productIds = implode(', ', $this->filterArrayOfIds($filterData['product_id']));

            $orderProductQuery = 'SELECT order_id FROM order_product WHERE product_id IN (' . $productIds . ')';

            $query[] = 'AND (order_id IN (' . $orderProductQuery . '))';
        }

        if (isset($filterData['ranges']) && count($filterData['ranges']) > 0) {

            $ranges = $filterData['ranges'];

            if (isset($ranges['total'])) {
                $price = $ranges['total'];

                if (((int)$price['min'] > 0 || (int)$price['max'] > 0) && $price['min'] != $price['max']) {
                    $query[] = 'AND ((total >= ' . $price['min'] . ') AND (total <= ' . $price['max'] . '))';
                }
            }

            unset($ranges);
        }

        if (isset($filterData['date_added'])) {

            $startDate = null;
            $endDate = null;
            if (isset($filterData['date_added']['start']) && isset($filterData['date_added']['start'])) {
                $startDate = strtotime($filterData['date_added']['start']);
                $endDate = strtotime($filterData['date_added']['end']);
            }

            if (($startDate && $endDate) && $endDate > $startDate) {

                $formattedStartDate = date('Y-m-d', $startDate);
                $formattedEndDate = date('Y-m-d', $endDate);

                $query[] = 'AND (date_added BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
            } else if (($startDate && $endDate) && $endDate == $startDate) {
                $formattedStartDate = date('Y-m-d', $startDate);

                $query[] = 'AND (date_added="' . $formattedStartDate . '")';
            }
        }

        $totalFiltered = $this->db->query(
            'SELECT COUNT(*) AS totalData FROM ('.
            str_replace($fields, '0 as fakeColumn', implode(' ', $query))
            .') AS t'
        )->row['totalData'];

        $sort_data = array(
            'order_id',
            'customer',
            'status',
            'date_added',
            'date_modified',
            'total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $query[] = "ORDER BY " . $data['sort'];
        } else {
            $query[] = "ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'desc')) {
            $query[] = "DESC";
        } else {
            $query[] = "ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] > 0) {
                $query[] = "LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }
        }

        $query = $this->db->query(implode(' ', $query));

        return [
        	'data' => $query->rows,
			'total' => $total,
			'totalFiltered' => $totalFiltered
		];
	}

/**
 * returns the number of orders made through POS
 * @param  array  $data contains the filter data
 * @return integer       returns the total number of orders
 */
	public function getTotalOrders($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o RIGHT JOIN " . DB_PREFIX . "wkpos_user_orders wo ON (o.order_id = wo.order_id)";

		if (isset($data['filter_order_status']) && $data['filter_order_status']) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_txn_id'])) {
			$sql .= " AND wo.txn_id = '" . (int)$data['filter_txn_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_user'])) {
			$sql .= " AND wo.user_name LIKE '%" . $this->db->escape($data['filter_user']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}


    /**
     * Filter array of ids, this method is targeting filtering only ids.
     *
     * @param array $inputs
     *
     * @return array
     */
    public function filterArrayOfIds($inputs)
    {
        return array_filter($inputs, function ($input) {
            return $this->filterInteger($input);
        });
    }

    /**
     * Filter int input.
     *
     * @param int $input
     *
     * @return array
     */
    public function filterInteger($input)
    {
        return filter_var($input, FILTER_VALIDATE_INT);
    }
}
