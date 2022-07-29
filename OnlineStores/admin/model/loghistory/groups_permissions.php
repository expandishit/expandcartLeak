<?php
class ModelLoghistoryGroupsPermissions extends Model {
	public function getTotalGroupsPermissions ($data = array()) {

		$sql = "SELECT COUNT(DISTINCT log_history_id) AS total   FROM `" . DB_PREFIX . "log_history`";
		$implode = array();
		
		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

        $implode[] = " type = 'groups_permissions' ";

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

        $sql = "SELECT lh.log_history_id,lh.action,lh.old_value,lh.new_value,lh.date_added,group_permiss.name,u.email ,CONCAT(u.firstname,' ' ,u.lastname) as username  FROM `" . DB_PREFIX . "log_history` lh LEFT JOIN `" . DB_PREFIX . "user` u ON (lh.user_id = u.user_id) ";
        $sql .= " LEFT JOIN `" . DB_PREFIX . "user_group` group_permiss ON (lh.reference_id = group_permiss.user_group_id) ";
        $implode = array();

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(lh.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(lh.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        $implode[] = " lh.type = 'groups_permissions' ";
       

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
            $sql .= " AND ( group_permiss.name LIKE '" . $request['search']['value'] . "%' )";
        }


        $sql .= " ORDER BY lh.log_history_id " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);
 
        $logData['totalFilter'] = $query->num_rows;

        $logData['data'] = $query->rows;

        foreach ($logData['data'] as $key=> $info){
            $old_value = json_decode($info['old_value'],true);
            $new_value = json_decode($info['new_value'],true);
            if($info['action'] == 'add'){ $logData['data'][$key]['name'] = $new_value['name']; } 
            else{ $logData['data'][$key]['name'] =$old_value['name'];}
        }

        $logData['total'] = $this->getTotalGroupsPermissions($data);

        return $logData;
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