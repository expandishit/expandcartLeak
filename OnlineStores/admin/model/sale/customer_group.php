<?php
class ModelSaleCustomerGroup extends Model {
		public function dtHandler($start=0, $length=10, $search = null, $orderColumn="filename", $orderType="ASC") {

		$current_language = (int)$this->config->get('config_language_id');
        $query = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '{$current_language}'";
        //$query = ;
        $total = $totalFiltered = $this->db->query($query)->num_rows;
        $where = "";

        if (!empty($search)) {
            $where .= "AND cgd.name like '%" . $this->db->escape($search) . "%'";
            $query .= " WHERE " . $where;
            $totalFiltered = $this->db->query($query)->num_rows;
        }

        $query .= " ORDER by {$orderColumn} {$orderType}";

        if($length != -1) {
            $query .= " LIMIT " . $start . ", " . $length;
        }

        $results = $this->db->query($query)->rows;

        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered
        );
        return $data;
    }

	public function addCustomerGroup($data) {

	    $queryString = $fields = [];
	    $queryString[] = "INSERT INTO " . DB_PREFIX . "customer_group SET";

        $fields[] = "approval = '" . (int)$data['approval'] . "'";
        $fields[] = "company_id_display = '" . (int)$data['company_id_display'] . "'";
        $fields[] = "company_id_required = '" . (int)$data['company_id_required'] . "'";
        $fields[] = "tax_id_display = '" . (int)$data['tax_id_display'] . "'";
        $fields[] = "tax_id_required = '" . (int)$data['tax_id_required'] . "'";
        $fields[] = "sort_order = '" . (int)$data['sort_order'] . "'";
        $fields[] = "email_activation = '" . (int)$data['email_activation'] . "'";
        $fields[] = "sms_activation = '" . (int)$data['sms_activation'] . "'";
		$fields[] = "customer_verified = '" . (int)$data['customer_verified'] . "'";
		$fields[] = "permissions = '" . (isset($data['permissions']) ? serialize($data['permissions']) : '') . "'";
		

        $queryString[] = implode(',', $fields);

		$this->db->query(implode(' ', $queryString));
	
		$customer_group_id = $this->db->getLastId();
		
		foreach ($data['customer_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$customer_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}	
	}
	
	/**
	*	A simple function to grab the customer group using its ID
	*
	*	@param int $id
	*	@return array $customer_group
	*/
	public function getCustomerGroupDescriptionById($id)
	{
		$current_language = $this->config->get('config_language_id');
		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '{$id}' AND language_id = '{$current_language}' LIMIT 1";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function editCustomerGroup($customer_group_id, $data) {

        $queryString = $fields = [];
        $queryString[] = "UPDATE " . DB_PREFIX . "customer_group SET";

        // Convert On and Offs to 1s and 0s respectively.
        /*foreach ($data as $index => $item)
        {
        	if ( $item == 'on' )
        	{
        		$data[$index][$item] = 1;
        	}
        	else
        	{
        		$data[$index][$item] = 0;	
        	}
        }*/
        $data['permissions'] = array_map(function($value) {
            return $this->db->escape($value);
        }, $data['permissions']);

        $fields[] = "approval = '" . $this->db->escape($data['approval']) . "'";
        $fields[] = "company_id_display = '" . $this->db->escape($data['company_id_display']) . "'";
        $fields[] = "company_id_required = '" . $this->db->escape($data['company_id_required']) . "'";
        $fields[] = "tax_id_display = '" . $this->db->escape($data['tax_id_display']) . "'";
        $fields[] = "tax_id_required = '" . $this->db->escape($data['tax_id_required']) . "'";
        $fields[] = "sort_order = '" . $this->db->escape($data['sort_order']) . "'";
        $fields[] = "email_activation = '" . $this->db->escape($data['email_activation']) . "'";
        $fields[] = "sms_activation = '" . $this->db->escape($data['sms_activation']) . "'";
		$fields[] = "customer_verified = '" . $this->db->escape($data['customer_verified']) . "'";
		$fields[] = "permissions = '" . (isset($data['permissions']) ? serialize($data['permissions']) : '') . "'";

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE customer_group_id="' . $this->db->escape($customer_group_id) . '"';

        $this->db->query(implode(' ', $queryString));

		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$this->db->escape($customer_group_id) . "'");

		foreach ($data['customer_group_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_group_description SET customer_group_id = '" . (int)$this->db->escape($customer_group_id) . "', language_id = '" . (int)$this->db->escape($language_id) . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}
		return True;
	}
	
	public function deleteCustomerGroup($customer_group_id) {
		$result = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group");

		if ( $result->num_rows <= 1 )
		{
			return False;
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_group WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		return True;
	}
	
	public function getCustomerGroup($customer_group_id) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cg.customer_group_id = '" . (int)$customer_group_id . "' AND cgd.language_id = '{$lang_id}'");
		
		return $query->row;
	}
	
	public function getCustomerGroups($data = array()) {
        $lang_id = $this->config->get('config_language_id') ? (int) $this->config->get('config_language_id') : 1;

		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '{$lang_id}'";
		
		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY cgd.name";	
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
	
	public function getCustomerGroupDescriptions($customer_group_id) {
		$customer_group_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = '" . (int)$customer_group_id . "'");
				
		foreach ($query->rows as $result) {
			$customer_group_data[$result['language_id']] = array(
				'name'        => $result['name'],
				'description' => $result['description']
			);
		}
		
		return $customer_group_data;
	}
		
	public function getTotalCustomerGroups() {
	    $current_language = (int)$this->config->get('config_language_id');

        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_group cg ".
        " LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '{$current_language}'"
        );
		
		return $query->row['total'];
	}
}
?>