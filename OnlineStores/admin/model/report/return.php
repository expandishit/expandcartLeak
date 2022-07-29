<?php
class ModelReportReturn extends Model {
	public function getReturns($data = array()) {

        if (isset($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM ashawqy_".$store_code."." . DB_PREFIX . "return r";

                if (!empty($data['filter_return_status_id'])) {
                    $sql .= " WHERE r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
                } else {
                    $sql .= " WHERE r.return_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(r.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(r.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                switch($group) {
                    case 'day';
                        $sql .= " GROUP BY DAY(r.date_added)";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY WEEK(r.date_added)";
                        break;
                    case 'month':
                        $sql .= " GROUP BY MONTH(r.date_added)";
                        break;
                    case 'year':
                        $sql .= " GROUP BY YEAR(r.date_added)";
                        break;
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM `" . DB_PREFIX . "return` r";

            if (!empty($data['filter_return_status_id'])) {
                $sql .= " WHERE r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
            } else {
                $sql .= " WHERE r.return_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(r.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(r.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            switch($group) {
                case 'day';
                    $sql .= " GROUP BY DAY(r.date_added)";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY WEEK(r.date_added)";
                    break;
                case 'month':
                    $sql .= " GROUP BY MONTH(r.date_added)";
                    break;
                case 'year':
                    $sql .= " GROUP BY YEAR(r.date_added)";
                    break;
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
		
		return $query->rows;
	}	
	
	public function getTotalReturns($data = array()) {
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                switch($group) {
                    case 'day';
                        $sql .= "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "return";
                        break;
                    default:
                    case 'week':
                        $sql .= "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "return";
                        break;
                    case 'month':
                        $sql .= "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "return";
                        break;
                    case 'year':
                        $sql .= "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "return";
                        break;
                }

                if (!empty($data['filter_return_status_id'])) {
                    $sql .= " WHERE return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
                } else {
                    $sql .= " WHERE return_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            switch($group) {
                case 'day';
                    $sql = "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM `". DB_PREFIX . "return`";
                    break;
                default:
                case 'week':
                    $sql = "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM `". DB_PREFIX . "return`";
                    break;
                case 'month':
                    $sql = "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM `". DB_PREFIX . "return`";
                    break;
                case 'year':
                    $sql = "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM `". DB_PREFIX . "return`";
                    break;
            }

            if (!empty($data['filter_return_status_id'])) {
                $sql .= " WHERE return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
            } else {
                $sql .= " WHERE return_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }
        }


		$query = $this->db->query($sql);

        $total = 0;
        if(count($query->rows) > 0){
            foreach ($query->rows as $row){
                $total += $row['total'];
            }
        }

        return $total;
	}



    public function getReturnsDataTable($data = array(), $request, $columns)
    {

        if (isset($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql = "SELECT * FROM (SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM ".$store_code."." . DB_PREFIX . "return r";

                if (!empty($data['filter_return_status_id'])) {
                    $sql .= " WHERE r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
                } else {
                    $sql .= " WHERE r.return_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(r.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(r.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                switch($group) {
                    case 'day';
                        $sql .= " GROUP BY DAY(r.date_added)";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY WEEK(r.date_added)";
                        break;
                    case 'month':
                        $sql .= " GROUP BY MONTH(r.date_added)";
                        break;
                    case 'year':
                        $sql .= " GROUP BY YEAR(r.date_added)";
                        break;
                }

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE date_start LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR returns LIKE '%" . $request['search']['value'] . "%' ";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM `" . DB_PREFIX . "return` r";

            if (!empty($data['filter_return_status_id'])) {
                $sql .= " WHERE r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
            } else {
                $sql .= " WHERE r.return_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(r.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(r.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            switch($group) {
                case 'day';
                    $sql .= " GROUP BY DAY(r.date_added)";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY WEEK(r.date_added)";
                    break;
                case 'month':
                    $sql .= " GROUP BY MONTH(r.date_added)";
                    break;
                case 'year':
                    $sql .= " GROUP BY YEAR(r.date_added)";
                    break;
            }

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE date_start LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR returns LIKE '%" . $request['search']['value'] . "%' ";
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
