<?php
class ModelLoghistoryZone extends Model {
	public function getTotalZones($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'zone' ";

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

        $sql = "SELECT lh.log_history_id,lh.action,lh.date_added,zl.name,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "zones_locale` zl ON (lh.reference_id = zl.zone_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'zone' ";

        $implode[] = " zl.lang_id = ". $this->config->get('config_language_id');

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( zl.name LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $total = $this->getTotalZones($data);

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

        if(count($query->row) > 0){
            $info = $query->row;
            $logData['old_value'] = ($info['old_value'] != NULL) ? json_decode($info['old_value'],true) : array();
            $logData['new_value'] = ($info['new_value'] != NULL) ? json_decode($info['new_value'],true) : array();
            $this->load->model('localisation/country');
            if($info['old_value'] != NULL){
                $logData['old_value']['country'] = $this->model_localisation_country->getCountry($logData['old_value']['country_id']);
            }
            if($info['new_value'] != NULL){
                $logData['new_value']['country'] = $this->model_localisation_country->getCountry($logData['new_value']['country_id']);
            }
            $this->load->model('user/user');     
            $userInfo= $this->model_user_user->getUser($info['user_id']);
            $logData['username'] =$userInfo['username'];
            $logData['email'] =$userInfo['email'];	
            $logData['date_added'] =$info['date_added'];
        }

        $this->load->model('localisation/language');
        $logData['languages'] = $this->model_localisation_language->getLanguages();
        return $logData;
    }




}
?>