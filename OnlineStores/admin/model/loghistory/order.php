<?php
class ModelLoghistoryOrder extends Model {
	public function getTotalOrders($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'order' ";
        $implode[] = " action IN('add','update','delete','archive','updateOrderStatus','updateManualShipping') ";

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

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,o.order_id,CONCAT(o.firstname,' ' ,o.lastname) as customerName,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "order` o ON (lh.reference_id = o.order_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'order' ";
        $implode[] = " action IN('add','update','delete','archive','updateOrderStatus','updateManualShipping') ";


        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( o.order_id LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $total = $this->getTotalOrders($data);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered, 'total' => $total];
    }

    public function getHistoryInfo($log_id)
    {
        $logData = [];

        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "log_history WHERE log_history_id = '" . (int)$log_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        if(count($query->row) > 0) {
            $info = $query->row;
            $logData['old_value'] = ($info['old_value'] != NULL) ? json_decode($info['old_value'], true) : array();
            $logData['new_value'] = ($info['new_value'] != NULL) ? json_decode($info['new_value'], true) : array();
            $this->load->model('localisation/order_status');

			$old_order_status_info = $this->model_localisation_order_status->getOrderStatus($logData['old_value']['orderInfo']['order_status_id']);

			if ($old_order_status_info) {
				$logData['old_order_status'] = $old_order_status_info['name'];
                $logData['old_order_status_color'] = $old_order_status_info['bk_color'];
            } 
            $new_order_status_info = $this->model_localisation_order_status->getOrderStatus($logData['new_value']['orderInfo']['order_status_id']);

			if ($new_order_status_info) {
				$logData['new_order_status'] = $new_order_status_info['name'];
                $logData['new_order_status_color'] = $new_order_status_info['bk_color'];
            } 
            $this->load->model('user/user');     
            $userInfo= $this->model_user_user->getUser($info['user_id']);
            $logData['username'] =$userInfo['username'];
            $logData['email'] =$userInfo['email'];	
            $logData['date_added'] =$info['date_added'];

        }
        return $logData;
    }




}
?>