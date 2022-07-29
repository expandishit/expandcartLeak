<?php
class ModelAffiliateTransaction extends Model {
    public function getTransactions($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";

        $sort_data = array(
            'amount',
            'description',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
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

    public function getHistory($data = array()) {
		$sql = "SELECT " .
                        "`" . DB_PREFIX . "affiliate_transaction`.`affiliate_transaction_id` affiliate_transaction_id, " .
                        "`" . DB_PREFIX . "affiliate_transaction`.`affiliate_id` affiliate_id, " .
                        "`" . DB_PREFIX . "order`.`order_id` order_id, " .
                        "`" . DB_PREFIX . "affiliate_transaction`.`description` description, " .
                        "`" . DB_PREFIX . "affiliate_transaction`.`amount` amount, " .
                        "`" . DB_PREFIX . "affiliate_transaction`.`date_added` date_added, " .
                        "`" . DB_PREFIX . "order`.`firstname` firstname, " .
                        "`" . DB_PREFIX . "order`.`lastname` lastname, " .
                        "`" . DB_PREFIX . "order`.`email` email, " .
                        "`" . DB_PREFIX . "order`.`telephone` telephone, " .
                        "`" . DB_PREFIX . "order`.`comment` comment, " .
                        "`" . DB_PREFIX . "order`.`total` total, " .
                        "`" . DB_PREFIX . "order`.`order_status_id` order_status_id, " .
                        "`" . DB_PREFIX . "order`.`commission` commission, " .
                        "`" . DB_PREFIX . "order`.`date_added` order_date_added, " .
                        "`" . DB_PREFIX . "order`.`date_modified` order_date_modified, " .
                        "`" . DB_PREFIX . "order_status`.`name` order_status_name, " .
                        "`order_product_names`.`order_products` order_products " .
            " FROM `" . DB_PREFIX . "order`" .
              " JOIN `" . DB_PREFIX . "order_status` ON `" . DB_PREFIX . "order`.`order_status_id` = `" . DB_PREFIX . "order_status`.`order_status_id`" .
              " JOIN (SELECT `" . DB_PREFIX . "order_product`.`order_id` ," .
				" group_concat(`" . DB_PREFIX . "order_product`.`quantity`, ' * ', `" . DB_PREFIX . "order_product`.`name` SEPARATOR ', ') AS order_products" .
				" FROM `" . DB_PREFIX . "order_product`" .
				" GROUP BY `" . DB_PREFIX . "order_product`.`order_id`) `order_product_names` ON `order_product_names`.`order_id` = `" . DB_PREFIX . "order`.`order_id`" .
              " LEFT JOIN `" . DB_PREFIX . "affiliate_transaction` ON `" . DB_PREFIX . "affiliate_transaction`.`order_id` = `" . DB_PREFIX . "order`.`order_id`" .
              " WHERE `" . DB_PREFIX . "order`.`affiliate_id` = '" . (int)$this->affiliate->getId() . "'" .
              "   AND `" . DB_PREFIX . "order_status`.`language_id` = " . (int)$this->config->get('config_language_id');
		   
		$sort_data = array(
			'amount',
			'description',
			'date_added'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY date_added";	
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


		
	public function getTotalTransactions() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
			
		return $query->row['total'];
	}	
			
	public function getBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "' GROUP BY affiliate_id");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

    public function getTotalEarning() {
        $query = $this->db->query("SELECT SUM(commission) AS total FROM `" . DB_PREFIX . "order` JOIN `" . DB_PREFIX . "affiliate_transaction` ON `" . DB_PREFIX . "affiliate_transaction`.`order_id` = `" . DB_PREFIX . "order`.`order_id` WHERE `" . DB_PREFIX . "order`.`affiliate_id` = '" . (int)$this->affiliate->getId() . "' GROUP BY `" . DB_PREFIX . "order`.`affiliate_id`");

        if ($query->num_rows) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getTotalTransactionsIncPending() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");

        return $query->row['total'];
    }

    public function getBalanceIncPending() {
        $query = $this->db->query("SELECT SUM(commission) AS total FROM `" . DB_PREFIX . "order` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "' GROUP BY affiliate_id");

        if ($query->num_rows) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }
}
?>