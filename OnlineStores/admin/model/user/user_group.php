<?php
class ModelUserUserGroup extends Model {

    public function dtHandler($start=0, $length=10, $search = null, $orderColumn="name", $orderType="ASC")
    {
        $query = "SELECT * FROM " . DB_PREFIX . "user_group";
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";
        if (!empty($search)) {
            $where .= "(user_group.name like '%" . $this->db->escape($search) . "%')";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        if ($orderColumn){
            $query .= " ORDER by {$orderColumn} {$orderType}";
        }

        if($length && $length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }
        //$data = array_merge($this->db->query($query)->rows, array($totalFiltered));

        $results = $this->db->query($query)->rows;

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        //$data = $this->db->query($query)->rows;
        return $data;
    }

	public function addUserGroup($data) {
		$this->db->query("INSERT INTO " .
            DB_PREFIX . "user_group SET name = '" . $this->db->escape($data['name']) .
            "', permission = '" . (isset($data['permission']) ? serialize($data['permission']) : '') .
            "', created_at = CURRENT_TIMESTAMP".
            ", description = '".$this->db->escape($data['description']) . "'"
        );
		$user_group_id = $this->db->getLastId();
		return $user_group_id;
	}
	
	public function editUserGroup($user_group_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "user_group SET name = '" . $this->db->escape($data['name']) .
            "', permission = '" . (isset($data['permission']) ? serialize($data['permission']) : '') .
            "', description = '".$this->db->escape($data['description']) .
            "' WHERE user_group_id = '" . (int)$user_group_id . "'");
	}
	
	public function deleteUserGroup($user_group_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_group_id . "'");
	}
	
    public function deleteOnlyEmptyUserGroup($ids)
    {
        if (is_array($ids) == false) {
            $ids = [$ids];
        }

        $ids = implode(',', $ids);

        $queryString = 'DELETE FROM `user_group` WHERE NOT EXISTS (%s) and user_group_id IN (' . $ids . ')';

        $subQuery = 'SELECT * FROM `user` WHERE `user`.`user_group_id` = user_group.user_group_id';

        $this->db->query(sprintf($queryString, $subQuery));
    }

    public function ifGroupHasUsers($ids)
    {
        if (is_array($ids) == false) {
            $ids = [$ids];
        }

        $ids = implode(',', $ids);

        $query = 'SELECT 1 as checker FROM `user` WHERE user_group_id IN (' . $ids . ')';

        $results = $this->db->query($query);

        if (isset($results->num_rows) && $results->num_rows > 0) {
            return $results->num_rows;
        }

        return false;
    }

	public function addPermission($user_id, $type, $page) {
		$user_query = $this->db->query("SELECT DISTINCT user_group_id FROM " . DB_PREFIX . "user WHERE user_id = '" . (int)$user_id . "'");
		
		if ($user_query->num_rows) {
			$user_group_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");
		
			if ($user_group_query->num_rows) {
				$data = unserialize($user_group_query->row['permission']);
		
				$data[$type][] = $page;
		
				$this->db->query("UPDATE " . DB_PREFIX . "user_group SET permission = '" . serialize($data) . "' WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");
			}
		}
	}
	
	public function getUserGroup($user_group_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = '" . (int)$user_group_id . "'");
		
		$user_group = array(
			'name'       => $query->row['name'],
            'description'       => $query->row['description'],
            'permission' => unserialize($query->row['permission'])
		);
		
		return $user_group;
	}
	
	public function getUserGroups($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "user_group";
		
		$sql .= " ORDER BY name";	
			
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
	public function addUserGroupOrderStatuses($user_group_id,$data)
	{
		$this->DeleteUserGroupOrderStatuses($user_group_id);
		if (isset($data)) {
			for($i=0; $i<count($data['from_order_status_ids']); $i++){
			$this->db->query("INSERT INTO " . DB_PREFIX . "user_group_order_statuses SET user_group_id = '" . (int)$user_group_id . "',from_order_status_id = '" . $data['from_order_status_ids'][$i] . "', to_order_status_id = '" . $data['to_order_status_ids'][$i] . "'");
			}	
        }
	}
	public function getUserGroupOrderStatuses($user_group_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_group_order_statuses WHERE user_group_id = '" . (int)$user_group_id . "'");
		return $query->rows;
	}
	public function DeleteUserGroupOrderStatuses($user_group_id)
	{
		$getUserGroupOrderStatuses=$this->getUserGroupOrderStatuses($user_group_id);
		if(isset($getUserGroupOrderStatuses))
		{
			foreach($getUserGroupOrderStatuses as $groupStatus )
			{
		   $this->db->query("DELETE FROM " . DB_PREFIX . "user_group_order_statuses WHERE user_group_id = '" . (int)$user_group_id . "'");
			}
		}
	}

	public function getCustomGroupOrderStatuses($user_group_id,$current_order_status)
	{    
		$language_id = $this->config->get('config_language_id') ?: 1;
		$query = $this->db->query("SELECT orderStatus.order_status_id, orderStatus.name, orderStatus.bk_color FROM " . DB_PREFIX . "user_group_order_statuses groupStatus
								  LEFT JOIN " . DB_PREFIX . "order_status orderStatus 
								   ON (groupStatus.to_order_status_id = orderStatus.order_status_id)
								   WHERE groupStatus.user_group_id = '" . (int)$user_group_id . "'
								   AND   groupStatus.from_order_status_id = '" . (int)$current_order_status. "'
								   AND   orderStatus.language_id = '" . $language_id . "'");
		return $query->rows;

	}
	public function getTotalUserGroups() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user_group");
		
		return $query->row['total'];
	}	
}
?>