<?php
class ModelReportAffiliate extends Model {
	public function getCommission($data = array()) { 
		$sql = "SELECT at.affiliate_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM " . DB_PREFIX . "affiliate_transaction at LEFT JOIN `" . DB_PREFIX . "affiliate` a ON (at.affiliate_id = a.affiliate_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (at.order_id = o.order_id)";
		
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " GROUP BY at.affiliate_id ORDER BY commission DESC";
				
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

	public function getTotalCommission($data = "") {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){

            $sql = 'SELECT COUNT(DISTINCT final.affiliate_id) AS total FROM(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT affiliate_id FROM ashawqy_".$store_code."." . DB_PREFIX . "affiliate_transaction at";

                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
                }
                $sql .= " UNION ALL ";

            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT affiliate_id) AS total FROM `" . DB_PREFIX . "affiliate_transaction` at";

            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
            }
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
		
		return $query->row['total'];
	}
	
	public function getProducts($data = array()) { 
		$sql = "SELECT at.product_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM " . DB_PREFIX . "affiliate_transaction at LEFT JOIN `" . DB_PREFIX . "affiliate` a ON (at.affiliate_id = a.affiliate_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (at.order_id = o.order_id) LEFT JOIN " . DB_PREFIX . "product";
		
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " GROUP BY at.affiliate_id ORDER BY commission DESC";
				
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

	public function getTotalProducts() {
		$sql = "SELECT COUNT(DISTINCT product_id) AS total FROM `" . DB_PREFIX . "affiliate_transaction`";
		
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
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
		
		return $query->row['total'];
	}


    public function getCommissionDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT at.affiliate_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM ashawqy_".$store_code."." . DB_PREFIX . "affiliate_transaction at LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "affiliate a ON (at.affiliate_id = a.affiliate_id) LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o ON (at.order_id = o.order_id)";

                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
                }

                $sql .= " GROUP BY at.affiliate_id ORDER BY commission DESC";

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( affiliate LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR status LIKE '" .  $request['search']['value'] . "%' ";
                    $sql .= " OR commission LIKE '" . (integer) $request['search']['value'] . "%' ";
                    $sql .= " OR orders LIKE '" . (integer) $request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT at.affiliate_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM " . DB_PREFIX . "affiliate_transaction at LEFT JOIN `" . DB_PREFIX . "affiliate` a ON (at.affiliate_id = a.affiliate_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (at.order_id = o.order_id)";

            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(at.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(at.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
            }

            $sql .= " GROUP BY at.affiliate_id ORDER BY commission DESC";

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( affiliate LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR status LIKE '" .  $request['search']['value'] . "%' ";
                $sql .= " OR commission LIKE '" . (integer) $request['search']['value'] . "%' ";
                $sql .= " OR orders LIKE '" . (integer) $request['search']['value'] . "%' ";
                $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
            }

        }


//        $sql .= " ORDER BY tmp.date_added DESC";
        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }


}
?>