<?php
class ModelLoghistoryCustomer extends Model {
	public function getTotalCustomers($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'customer' ";
        $implode[] = " action IN('add','update','delete','login') ";

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$sql .= " ORDER BY date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
		}
		
		$query = $this->db->query($sql);

        return $query->row['total'];
	}	
	


    public function ajaxResponse($data, $request)
    {

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,CONCAT(c.firstname,' ' ,c.lastname) as customer_name,c.email as customer_email,c.telephone,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "customer` c ON (lh.reference_id = customer_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'customer' ";

        $implode[] = " lh.action IN('add','update','delete','login') ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( c.firstname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.lastname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.telephone LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $total = $this->getTotalCustomers($data);

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered, 'total' => $total];
    }

    public function getHistoryInfo($log_id)
    {
        $logData = [];

        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "log_history WHERE log_history_id = '" . (int)$log_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        if(count($query->row) > 0){
            $this->load->model('sale/customer_group');
            $info = $query->row;
            $logData['old_value'] = ($info['old_value'] != NULL) ? json_decode($info['old_value'],true) : array();
            $logData['new_value'] = ($info['new_value'] != NULL) ? json_decode($info['new_value'],true) : array();
            $logData['old_value']['customer_group'] = $this->model_sale_customer_group->getCustomerGroupDescriptionById($logData['old_value']['customer_group_id'])['name'];
            $logData['new_value']['customer_group'] = $this->model_sale_customer_group->getCustomerGroupDescriptionById($logData['new_value']['customer_group_id'])['name'];
            $this->load->model('user/user');     
            $userInfo= $this->model_user_user->getUser($info['user_id']);
            $logData['username'] =$userInfo['username'];
            $logData['email'] =$userInfo['email'];	
            $logData['date_added'] =$info['date_added'];

        }
        return $logData;
    }

    public function getTotalCustomersPoints($data = array()) {

        $sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " type = 'customer' ";
        $implode[] = " action = 'updateReward' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }



    public function getCustomersPointsDataTable($data, $request, $columns)
    {

        $sql = "SELECT lh.log_history_id,lh.old_value,lh.new_value,lh.action,lh.date_added,CONCAT(c.firstname,' ' ,c.lastname) as customer_name,c.email as customer_email,c.telephone,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "customer` c ON (lh.reference_id = customer_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'customer' ";

        $implode[] = " lh.action = 'updateReward' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( c.firstname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.lastname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.telephone LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }
    public function getTotalCustomersBalance($data = array()) {

        $sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " type = 'customer' ";
        $implode[] = " action = 'updateBalance' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }



    public function getCustomersBalanceDataTable($data, $request, $columns)
    {

        $sql = "SELECT lh.log_history_id,lh.old_value,lh.new_value,lh.action,lh.date_added,CONCAT(c.firstname,' ' ,c.lastname) as customer_name,c.email as customer_email,c.telephone,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "customer` c ON (lh.reference_id = customer_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'customer' ";

        $implode[] = " lh.action = 'updateBalance' ";

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( c.firstname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.lastname LIKE '" . $request['search']['value'] . "%' ";
            $sql .= " OR c.telephone LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }

}
?>