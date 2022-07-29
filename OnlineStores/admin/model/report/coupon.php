<?php
class ModelReportCoupon extends Model {
	public function getCoupons($data = array()) {
		$sql = "SELECT ch.coupon_id, c.name, c.code, COUNT(DISTINCT ch.order_id) AS `orders`, SUM(ch.amount) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id)"; 

		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(c.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(c.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " GROUP BY ch.coupon_id ORDER BY total DESC";
		
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
	
	public function getTotalCoupons($data = array()) {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.coupon_id) AS total FROM( ';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT coupon_id FROM ashawqy_".$store_code."."  . DB_PREFIX . "coupon_history";

                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
                }
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT coupon_id) AS total FROM `" . DB_PREFIX . "coupon_history`";

            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
            }
        }


		$query = $this->db->query($sql);

        return $query->row['total'];

    }

    public function getCouponsDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT ch.coupon_id, c.name, c.code, COUNT(DISTINCT ch.order_id) AS `orders`, SUM(ch.amount) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "coupon_history ch JOIN ashawqy_".$store_code."."  . DB_PREFIX . "coupon c ON (ch.coupon_id = c.coupon_id)";
				$sql .= 'LEFT JOIN `order` ON `order`.order_id = ch.order_id ';
				
                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(ch.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(ch.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                //Affiliate promo App
                if (isset($data['filter_affiliate_id']) && $data['filter_affiliate_id']) {
                    $implode[] = "c.from_affiliate = '" . (int)$data['filter_affiliate_id'] . "'";
                }
                //////////////////////
                ///
                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode)." AND `order`.order_status_id !=0";
                }

                $sql .= " GROUP BY ch.coupon_id ORDER BY total DESC";

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR code LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR orders LIKE '" . (integer) $request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT ch.coupon_id, c.name, c.code, COUNT(DISTINCT ch.order_id) AS `orders`, SUM(ch.amount) AS total FROM `" . DB_PREFIX . "coupon_history` ch JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id)";
			$sql .= 'LEFT JOIN `order` ON `order`.order_id = ch.order_id ';
            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(ch.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(ch.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            //Affiliate promo App
            if (isset($data['filter_affiliate_id']) && $data['filter_affiliate_id']) {
                $implode[] = "c.from_affiliate = '" . (int)$data['filter_affiliate_id'] . "'";
            }
            //////////////////////

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode)." AND `order`.order_status_id !=0";
            }

            $sql .= " GROUP BY ch.coupon_id ORDER BY total DESC";

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR code LIKE '" . $request['search']['value'] . "%' ";
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