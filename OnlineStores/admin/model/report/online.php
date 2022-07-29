<?php
class ModelReportOnline extends Model {
	public function getCustomersOnline($data = array()) { 
		$sql = "SELECT co.ip, co.customer_id, co.url, co.referer, co.date_added FROM " . DB_PREFIX . "customer_online co LEFT JOIN " . DB_PREFIX . "customer c ON (co.customer_id = c.customer_id)";

		$implode = array();
				
		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$implode[] = "co.ip LIKE '" . $this->db->escape($data['filter_ip']) . "'";
		}
		
		if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
			$implode[] = "co.customer_id > 0 AND CONCAT(c.firstname, ' ', c.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "'";
		}
				
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " ORDER BY co.date_added DESC";
				
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

	public function getTotalCustomersOnline($data = array()) {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(*) AS total FROM(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT COUNT(*) FROM ashawqy_".$store_code."." . DB_PREFIX . "customer_online co LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "customer c ON (co.customer_id = c.customer_id)";

                $implode = array();

                if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
                    $implode[] = "co.ip LIKE '" . $this->db->escape($data['filter_ip']) . "'";
                }

                if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
                    $implode[] = "co.customer_id > 0 AND CONCAT(c.firstname, ' ', c.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
                }
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_online` co LEFT JOIN " . DB_PREFIX . "customer c ON (co.customer_id = c.customer_id)";

            $implode = array();

            if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
                $implode[] = "co.ip LIKE '" . $this->db->escape($data['filter_ip']) . "'";
            }

            if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
                $implode[] = "co.customer_id > 0 AND CONCAT(c.firstname, ' ', c.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
            }

        }

		$query = $this->db->query($sql);

        return $query->row['total'];
	}


    public function getCustomersOnlineDataTable($data, $request, $columns) {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT co.ip, CONCAT(c.firstname, ' ', c.lastname) AS customer,co.customer_id, co.url, co.referer, co.date_added FROM ashawqy_".$store_code."." . DB_PREFIX . "customer_online co LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "customer c ON (co.customer_id = c.customer_id)";
                $sql .= " ORDER BY co.date_added DESC) final WHERE 1=1";

                if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
                    $sql .= " AND ip LIKE '" . $this->db->escape($data['filter_ip']) . "%'";
                }

                if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
                    $sql .= " AND customer LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
                }
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " OR ( ip LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR customer LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR url LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR referer LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_added LIKE '%" . $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT co.ip, CONCAT(c.firstname, ' ', c.lastname) AS customer,co.customer_id, co.url, co.referer, co.date_added FROM " . DB_PREFIX . "customer_online co LEFT JOIN " . DB_PREFIX . "customer c ON (co.customer_id = c.customer_id)";

            $sql .= " ORDER BY co.date_added DESC) final WHERE 1=1";


            if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
                $sql .= " AND ip LIKE '" . $this->db->escape($data['filter_ip']) . "%'";
            }

            if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
                $sql .= " AND customer LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
            }
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " OR ( ip LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR customer LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR url LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR referer LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_added LIKE '%" . $request['search']['value'] . "%' )";
            }
        }


        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];

    }



}
?>