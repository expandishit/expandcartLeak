<?php
class ModelSaleCustomer extends Model {

	public function dtHandler($start=0, $length=10, $filterData, $orderColumn="customer_id", $orderType="ASC") {
		//Never get more than 500 rows
		if($length > 500){
			$length = 500;
		}
		//Get total number of customers
		$query = "SELECT count(*) as total FROM " . DB_PREFIX . "customer";
		$total = $this->db->query($query)->row['total'];
		
		//Get customers' information
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM " . DB_PREFIX . "customer";
        if (isset($filterData['search']) && !empty($filterData['search'])) {
        	// basic search by keyword
			$search = $this->db->escape($filterData['search']);
			$query .= " WHERE (customer.firstname like '%{$search}%' OR customer.lastname like '%{$search}%' OR customer.email like '%{$search}%' OR customer.telephone like '%{$search}%' OR customer.customer_id like '%{$search}%')";
		}
		else{
			// advanced search filters
        	if ($filterData['approved'] == "0" ) {
        		$query .= " WHERE customer.approved = 0 ";
        	}else if($filterData['approved'] == "1") {
        		$query .= " WHERE customer.approved = 1 ";
			}else{
				$query .= " WHERE customer.approved >= 0 ";
			}
        	if (isset($filterData['email']) && !empty($filterData['email'])) {
				$query .= " AND customer.email LIKE '%" . $this->db->escape($filterData['email']) . "%'";
        	}
        	if (isset($filterData['name']) && !empty($filterData['name'])) {
				$query .= " AND (customer.firstname LIKE '%" . $this->db->escape($filterData['name']) . "%' OR customer.lastname LIKE '%" . $this->db->escape($filterData['name'])."%')";
        	}
        	if (isset($filterData['phone']) && !empty($filterData['phone'])) {
				$query .= " AND customer.telephone LIKE '%" . $this->db->escape($filterData['phone']) . "%'";
        	}
			if (isset($filterData['customer_group_id']) && count($filterData['customer_group_id']) > 0) {
				$groupsIds = implode(', ', $filterData['customer_group_id']);
	            $query .= ' AND (customer.customer_group_id IN (' . $groupsIds . '))';
	        }
        	if ($filterData['status'] != "") {
				$query .= " AND customer.status = " .(int)$filterData['status'];
        	}
	        // dates
            if (isset($filterData['date_added'])) {
	            $startDate = null;
	            $endDate = null;
	            if (isset($filterData['date_added']['start']) && isset($filterData['date_added']['end'])) {
	                $startDate = strtotime($filterData['date_added']['start']);
					$endDate = strtotime($filterData['date_added']['end']);
	            }

	            if (($startDate && $endDate) && $endDate > $startDate) {

	                $formattedStartDate = date('Y-m-d', $startDate);
	                $formattedEndDate = date('Y-m-d', $endDate);

	                $query .= ' AND (date(date_added) BETWEEN "' . $formattedStartDate . '" AND "' . $formattedEndDate . '")';
	            } elseif(($startDate && $endDate) && $endDate == $startDate) {
	                $formattedStartDate = date('Y-m-d', $startDate);

	                $query .= ' AND (date(date_added) ="' . $formattedStartDate . '")';
	            }
        	}
		}

		if ($orderColumn){
			$query .= " ORDER by {$orderColumn} {$orderType}";
		}

		//Limit max to 500 records
		if($length && $length != -1) {
			$query .= " LIMIT " . $start . ", " . $length;
		}
		else{
			$query .= " LIMIT " . (int) $start . ", 500";
		}
        $results = $this->db->query($query)->rows;
		//Get total number of filtered customers
        $totalFiltered = $this->db->query('SELECT FOUND_ROWS() AS count')->row;
        
        $this->load->language('sale/customer');
        $this->load->model('sale/customer_group');
        foreach ($results as $key => $result)
        {
        	$results[$key]['name'] = trim($result['firstname']) . ' ' . trim($result['lastname']);
        	$results[$key]['status_text'] = $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled');
            if ($result['approved'] == '1') {
    			$results[$key]['approved_text'] = $this->language->get('text_yes');
            } elseif ($result['approved'] == '2') {
                $results[$key]['approved_text'] = $this->language->get('email_activation');
            } elseif ($result['approved'] == '3') {
                $results[$key]['approved_text'] = $this->language->get('sms_activation');
            } else {
                $results[$key]['approved_text'] = $this->language->get('text_no');
            }

			$cg = $this->model_sale_customer_group->getCustomerGroupDescriptionById((int)$result['customer_group_id']);
			$results[$key]['customer_group'] = $cg['name'];
			$results[$key]['customer_group_description'] = $cg['description'];
        }
        $data = array (
            'data' => $results,
            'total' => $total,
            'totalFiltered' => $totalFiltered['count']
        );
        return $data;
    }
	public function addCustomer($data) {
      	$this->db->query("INSERT INTO " . DB_PREFIX .
			"customer SET firstname = '" . $this->db->escape($data['firstname']) .
			"', lastname = '" . $this->db->escape($data['lastname']) .
			"', email = '" . $this->db->escape($data['email']) .
			"', company = '" . $this->db->escape($data['company']) .
			"', dob = '" . $this->db->escape($data['dob']) .
			"', gender = '" . $this->db->escape($data['gender']) .
			"', telephone = '" . $this->db->escape($data['telephone']) .
			"', fax = '" . $this->db->escape($data['fax']) .
			"', newsletter = '" . (int)$data['newsletter'] .
			"', customer_group_id = '" . (int)$data['customer_group_id'] .
			"', salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) .
			"', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) .
			"', status = '" . (int)$data['status'] . "', date_added = NOW() , approved = 1 , create_by_admin = 1"
		);
      	
      	$customer_id = $this->db->getLastId();
      	
      	if (isset($data['address'])) {
      		foreach ($data['address'] as $address) {	
      			$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', company_id = '" . $this->db->escape($address['company_id']) . "', tax_id = '" . $this->db->escape($address['tax_id']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', telephone = '" . $this->db->escape($address['telephone']) . "' ,zone_id = '" . (int)$address['zone_id'] . "'");
				
				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();
					
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . $address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
				}
			}
		}

		//fire delete product trigger for zapier if installed
        $this->load->model('module/zapier');
        if ($this->model_module_zapier->isInstalled()) {
            $customer = $this->getCustomer($customer_id);
            if ($customer)
				$this->model_module_zapier->newCustomerTrigger($customer);
        }

        $store_statistics = new StoreStatistics($this->user);
        $store_statistics->store_statistcs_push('customers', 'create', [
            'customer_id' => $customer_id,
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname']
        ]);

        return $customer_id;
	}
	
	public function editCustomer($customer_id, $data) {
        $fields = $wherries = [];
        $data['gender'] = ($data['gender'] == '1' || $data['gender'] == 'm') ? 'm' : 'f';
        
        $fields[] = "`firstname`='" . $this->db->escape($data['firstname']) . "'";
        
        // when new login applied, merchant can not edit these fields
        if (!defined('LOGIN_MODE') || LOGIN_MODE === 'default' || !$this->identity->isStoreOnWhiteList()) {
            $fields[] = "`lastname`='" . $this->db->escape($data['lastname']) . "'";
            $fields[] = "`email`='" . $this->db->escape($data['email']) . "'";
            $fields[] = "`telephone`='" . $this->db->escape($data['telephone']) . "'";
            
            if ($data['password']) {
                $fields[] = "`salt`='" .  $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "'";
                $fields[] = "`password`='" .  $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "'";
            }
        }
        
        $fields[] = "`fax`='" . $this->db->escape($data['fax']) . "'";
        $fields[] = "`company`='" . $this->db->escape($data['company']) . "'";
        $fields[] = "`newsletter`='" . (int)$data['newsletter'] . "'";
        $fields[] = "`customer_group_id`='" . (int)$data['customer_group_id'] . "'";
        $fields[] = "`status`='" . (int)$data['status'] . "'";
        $fields[] = "`dob`=" . (!!$this->db->escape($data['dob']) ? ("'" . $this->db->escape($data['dob']) . "'") : "null");
        $fields[] = "`gender`='" . $this->db->escape($data['gender']) . "'";

        $wherries[] = "`customer_id` ='" . (int)$customer_id . "'";
        
        $queryString = sprintf("UPDATE %s SET %s WHERE %s", (DB_PREFIX . "customer"), implode(',', $fields), implode('AND ', $wherries));
        
        $this->db->query($queryString);
        
		// $this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . /*"', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) .*/ "', company = '" . $this->db->escape($data['company']) . "', newsletter = '" . (int)$data['newsletter'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', status = '" . (int)$data['status'] ."', dob = " . (!!$this->db->escape($data['dob']) ? ("'" . $this->db->escape($data['dob']) . "'") : "null") . ", gender = '" .$data['gender'] . "' WHERE customer_id = '" . (int)$customer_id . "'");
	
      	// if ($data['password']) {
        // 	$this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
      	// }
      	
//      	$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
//
//      	if (isset($data['address'])) {
//      		foreach ($data['address'] as $address) {
//				$this->db->query("INSERT INTO " . DB_PREFIX . "address SET address_id = '" . (int)$address['address_id'] . "', customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', company_id = '" . $this->db->escape($address['company_id']) . "', tax_id = '" . $this->db->escape($address['tax_id']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', telephone = '" . $this->db->escape($address['telephone']) . "' ,country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");
//
//				if (isset($address['default'])) {
//					$address_id = $this->db->getLastId();
//
//					$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
//				}
//			}
//		}
	}

	public function changeStatus($customer_id, $status) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET status = '" . (int)$status . "' WHERE customer_id = '" . (int)$customer_id . "'");
        return true;
	}

	public function addAddress($customer_id, $address) {
		// if($address['address_id']) {
        //     $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address['address_id'] . "'");
		// }
        
        if($address['address_id']) {
            $query = "UPDATE " . DB_PREFIX . "address SET ";
        } else {
            $query = "INSERT INTO " . DB_PREFIX . "address SET ";
        }
        
        $query .= "customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', company_id = '" . $this->db->escape($address['company_id']) . "', tax_id = '" . $this->db->escape($address['tax_id']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', location = '" . $this->db->escape($address['location']) . "', area_id = '" . $this->db->escape($address['area_id']) ."', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', telephone = '" . $this->db->escape($address['telephone']) . "' ,country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'";
        
        if($address['address_id']) {
            $query .= " WHERE address_id = '" . (int)$address['address_id'] . "'";
        }
        
        // $this->db->query("INSERT INTO " . DB_PREFIX . "address SET address_id = '" . (int)$address['address_id'] . "', customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', company_id = '" . $this->db->escape($address['company_id']) . "', tax_id = '" . $this->db->escape($address['tax_id']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', telephone = '" . $this->db->escape($address['telephone']) . "' ,country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");
        $this->db->query($query);
        
        $address_id = $address['address_id'] ? $address['address_id'] : $this->db->getLastId();
        
        $this->db->query("UPDATE customer SET address_id='$address_id' WHERE customer_id='" . (int)$customer_id . "' AND address_id='0'");
        return $address_id;
	}

    public function deleteAddress($address_id, $customer_id = 0) {
        if($address_id) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "'");
            
            //ON DELETE SET DEFAULT (0)            
            if($customer_id)
            	$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET address_id = 0 WHERE customer_id =" . (int)$customer_id. " AND address_id = " . (int) $address_id);
        }

        return true;
    }

    public function setDefaultAddress($customer_id, $address_id) {
        $this->db->query("UPDATE customer SET address_id='" . (int)$address_id . "' WHERE customer_id='" . (int)$customer_id . "'");
	}

    public function getDefaultAddressId($customer_id) {
        $result = $this->db->query("SELECT address_id FROM customer WHERE customer_id='" . (int)$customer_id . "'");
        return $result->row['address_id'];
    }

	public function editToken($customer_id, $token) {
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET token = '" . $this->db->escape($token) . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function deleteCustomer($customer_id) {

		if($this->config->get('wk_amazon_connector_status')){ 
			$this->Amazonconnector->deleteCustomerEntry($customer_id); 
		}

        $queryMultiseller = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'multiseller'");

        if($queryMultiseller->num_rows) {
            $this->MsLoader->MsSeller->deleteSeller($customer_id);
        }

		$this->db->query("DELETE FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_authentication WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
	}
	
	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$this->db->escape($customer_id) . "'");
	
		return $query->row;
	}
	
	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
	
		return $query->row;
	}

	public function getCustomerByName($data = array()){

		$sql = "SELECT c.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND (c.firstname LIKE '" . $this->db->escape($data['filter_name']) . "%' OR c.lastname LIKE '" . $this->db->escape($data['filter_name']) . "%')";
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

	public function getCustomers($data = array()) {
		$fields = ['*', "CONCAT(c.firstname, ' ', c.lastname) AS name"];
		if(!isset($data['skip_group']) || !$data['skip_group']){
			$fields[] = 'cgd.name AS customer_group';
			$group_join = " LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cgd.customer_group_id = c.customer_group_id AND cgd.language_id = " . (int) $this->config->get('config_language_id')." )";
		}
		
		$sql = "SELECT ".implode(' , ', $fields)."  FROM " . DB_PREFIX . "customer c ".($group_join ?? '')." ";
		
		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}	
				
		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}	
			
		if (!empty($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}	
				
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}	
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}	
				
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (isset($data['filter_customer_ids']) && !is_null($data['filter_customer_ids']) && is_array($data['filter_customer_ids'])) {
			if (count($data['filter_customer_ids']) >0)
				$implode[] = "c.customer_id in ( " . implode(',' , $data['filter_customer_ids']) . ")";
		}
		
		if (!empty($data['search']))
		{
			$implode[] = "
			(CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['search']) . "%'
			OR c.email LIKE '%" . $this->db->escape($data['search']) . "%'
			OR c.telephone LIKE '%" . $this->db->escape($data['search']) . "%')";
		}

		if ($implode) {
			$sql .= ' WHERE ' . implode(" AND ", $implode);
		}
 
		$sort_data = array(
			'name',
			'c.email',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
		);
		if(!isset($data['skip_group']) || !$data['skip_group'])
			$sort_data[] = 'customer_group';
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
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
	
	public function approve($customer_id) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET approved = '1' WHERE customer_id = '" . (int)$customer_id . "'");

			$customer_activation = $this->db->query("SELECT * FROM  `" . DB_PREFIX . "customer_activation` ca WHERE ca.customer_id = '" . (int)$customer_id . "'");
			if ($customer_activation){
				$this->db->query("UPDATE " . DB_PREFIX . "customer_activation SET activation_status = '1' WHERE customer_id = '" . (int)$customer_id . "'");
			}
            
            if (empty($customer_info['email'])) return;

			$this->language->load('mail/customer');
			
			$this->load->model('setting/store');
						
			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);
			
			if ($store_info) {
				$store_name = $store_info['name'];
				$store_url = $store_info['url'] . 'index.php?route=account/login';
			} else {
				$store_name = $this->config->get('config_name');
				$store_url = HTTP_CATALOG . 'index.php?route=account/login';
			}
            
            if (is_array($store_name)) {
                if(isset($store_name[$this->config->get('config_language')])) {
                    $store_name = $store_name[$this->config->get('config_language')];
                } else {
                    $store_name = current($store_name);
                }
            }
            	
			$message  = sprintf($this->language->get('text_approve_welcome'), $store_name) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= $store_url . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $store_name;
	
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
			$mail->setTo($customer_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_approve_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
             if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                 include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

			 	$this->registry->customerLanguageId = $customer_info["language_id"];

                 $cet = new CustomEmailTemplates($this->registry);

                 $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

                 if ($cet_result) {
                     if ($cet_result['subject']) {
                         $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                     }

                     if ($cet_result['html']) {
                         $mail->setNewHtml($cet_result['html']);
                     }

                     if ($cet_result['text']) {
                         $mail->setNewText($cet_result['text']);
                     }

                     if ($cet_result['bcc_html']) {
                         $mail->setBccHtml($cet_result['bcc_html']);
                     }

//                     if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
//                         $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);
//
//                         $mail->addAttachment($path_to_invoice_pdf);
//                     }

                     if (isset($this->request->post['fattachments'])) {
                         if ($this->request->post['fattachments']) {
                             foreach ($this->request->post['fattachments'] as $attachment) {
                                 foreach ($attachment as $file) {
                                     $mail->addAttachment($file);
                                 }
                             }
                         }
                     }

                     $mail->setBccEmails($cet_result['bcc_emails']);
                 }
             }
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
            unset($mail);
            unset($message);
		}		
	}
		
	public function getAddress($address_id) {
		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address 
										   
										   WHERE address_id = '" . (int)$address_id . "'");

		if ($address_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");
			
			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}

            $lngid = $this->config->get('config_language_id');
			
			$zone_query = $this->db->query("SELECT z.code, zl.name FROM `" . DB_PREFIX . "zone` z inner join zones_locale zl on z.zone_id=zl.zone_id WHERE z.zone_id = '" . (int)$address_query->row['zone_id'] . "' and zl.lang_id = '{$lngid}'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$area_query = $this->db->query("SELECT ar.code, arl.name FROM `" . DB_PREFIX . "geo_area` ar inner join geo_area_locale arl on ar.area_id=arl.area_id WHERE ar.area_id = '" . (int)$address_query->row['area_id'] . "' and arl.lang_id = '{$lngid}'");

			if ($area_query->num_rows) {
				$area = $area_query->row['name'];
				$area_code = $area_query->row['code'];
			} else {
				$area = '';
				$area_code = '';
			}
			return array(
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'company_id'     => $address_query->row['company_id'],
				'tax_id'         => $address_query->row['tax_id'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'area_id'        => $address_query->row['area_id'],
				'telephone'		 => $address_query->row['telephone'],
				'location'		 => $address_query->row['location'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'area'           => $area,
				'area_code'      => $area_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
			);
		}
	}
		
	public function getAddresses($customer_id) {
		$address_data = array();
		
		$query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
	
		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);
		
			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}		
		
		return $address_data;
	}	
				
	public function getTotalCustomers($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer";
		
		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}
		
		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}
				
		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}	
		
		if (!empty($data['filter_ip'])) {
			$implode[] = "customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}	
						
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}			
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (isset($data['create_by_admin']) && !is_null($data['create_by_admin'])) {
			$implode[] = "create_by_admin = '" . (int)$data['create_by_admin'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

        if (!empty($data['filter_date_added_greater_equal'])) {
            $implode[] = "DATE(date_added) >= DATE('" . $this->db->escape($data['filter_date_added_greater_equal']) . "')";
        }
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
		
	public function getTotalCustomersAwaitingApproval() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE status = '0' OR approved = '0'");

		return $query->row['total'];
	}
	
	public function getTotalAddressesByCustomerId($customer_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->row['total'];
	}

	public function getCustomersCountsByGroup() {
		$query = $this->db->query("SELECT COUNT(customer_id) AS total , customer_group_id FROM " . DB_PREFIX . " customer group by customer_group_id");

		return $query->rows;
	}

	public function getTotalAddressesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalAddressesByZoneId($zone_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalCustomersByCustomerGroupId($customer_group_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		
		return $query->row['total'];
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		
		return $query->row['total'];
	}
	
	public function addHistory($customer_id, $comment) {
      	$this->db->query("INSERT INTO " . DB_PREFIX . "customer_history SET customer_id = '" . (int)$customer_id . "', comment = '" . $this->db->escape(strip_tags($comment)) . "', date_added = NOW()");
	}	
	
	public function getHistories($customer_id, $start = 0, $limit = 10) { 
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}	
		
		$query = $this->db->query("SELECT comment, date_added FROM " . DB_PREFIX . "customer_history WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
	
		return $query->rows;
	}	

	public function getTotalHistories($customer_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_history WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	/**
	 * add new transaction
	 *
	 * @param int $customer_id
	 * @param string $description
	 * @param string $amount
	 * @param int $order_id
	 */
	public function addTransaction($customer_id, $description = '', $amount = '', $order_id = 0): void
	{
		$customer_info = $this->getCustomer($customer_id);
		
		if ($customer_info) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . $customer_id . "', order_id = '" . $order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");

			$this->language->load('mail/customer');
			
			/*
			 * here we check if customer has multi stores we get store name else we get store name from config based on language
			 */
                        
			if ($customer_info['store_id']) {
				$this->load->model('setting/store');
		
				$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
                                        $store_name = (is_array($store_name)) ? $store_name[$this->config->get('config_language')] : $store_name;
                                        
				}	
			} else {
				$store_name = $this->config->get('config_name');
                                $store_name = (is_array($store_name)) ? $store_name[$this->config->get('config_language')] : $store_name;
			}
			
			$amout_format  = $this->currency->format($amount, $this->config->get('config_currency'));
			$totlat_format = $this->currency->format($this->getTransactionTotal($customer_id));

			$message  = sprintf($this->language->get('text_transaction_received'), $amout_format) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $totlat_format);
								
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );            
            
                       
			$mail->setTo($customer_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));
            if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

				$this->registry->customerLanguageId = $customer_info["language_id"];

                $cet = new CustomEmailTemplates($this->registry);

                $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));
                
                if ($cet_result) {
                    if ($cet_result['subject']) {
                        $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                    }

                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }

                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }

                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }

                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                        $mail->addAttachment($path_to_invoice_pdf);
                    }

                    if (isset($this->request->post['fattachments'])) {
                        if ($this->request->post['fattachments']) {
                            foreach ($this->request->post['fattachments'] as $attachment) {
                                foreach ($attachment as $file) {
                                    $mail->addAttachment($file);
                                }
                            }
                        }
                    }

                    $mail->setBccEmails($cet_result['bcc_emails']);
                }
            }
            $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
		}
	}
	
	public function deleteTransaction($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getTransactions($customer_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}	
				
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
	
		return $query->rows;
	}

	public function getTotalTransactions($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total  FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}

	/**
	 * get total as a sum of all Transactions
	 *
	 * @param $customer_id
	 * @return mixed
	 */
	public function getTransactionTotal(int $customer_id)
	{
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row['total'];
	}	
				
	public function addReward($customer_id, $description = '', $points = '', $order_id = 0) {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $this->load->model('promotions/reward_points_observer');
            $description = (empty($description)) ? "Added by Admin" : $description;
            $this->model_promotions_reward_points_observer->beforeAddReward($customer_id, $description, $points, $order_id);
            /** Override this method to new method addReward */
            return true;
        }

		$customer_info = $this->getCustomer($customer_id);
			
		if ($customer_info) { 
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");

			$this->language->load('mail/customer');
			
			if ($order_id) {
				$this->load->model('sale/order');
		
				$order_info = $this->model_sale_order->getOrder($order_id);
				
				if ($order_info) {
					$store_name = $order_info['store_name'];
				} else {
					$store_name = $this->config->get('config_name');
				}	
			} else {
				$store_name = $this->config->get('config_name');
			}		
				
			$message  = sprintf($this->language->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->language->get('text_reward_total'), $this->getRewardTotal($customer_id));
				
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
            $mail->setReplyTo(
                $this->config->get('config_mail_reply_to'),
                $this->config->get('config_name') ? $this->config->get('config_name')[0] : 'ExpandCart',
                $this->config->get('config_email')
            );
			$mail->setTo($customer_info['email']);
			$mail->setFrom((!empty($this->config->get('config_smtp_username')) ? $this->config->get('config_smtp_username') :  $this->config->get('config_email')));
			$mail->setSender($store_name);
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_reward_subject'), $store_name), ENT_QUOTES, 'UTF-8'));
            if ($this->config->get('custom_email_templates_status') && !isset($cet)) {
                include_once(DIR_SYSTEM . 'library/custom_email_templates.php');

				$this->registry->customerLanguageId = $customer_info["language_id"];

                $cet = new CustomEmailTemplates($this->registry);

                $cet_result = $cet->getEmailTemplate(array('type' => 'admin', 'class' => __CLASS__, 'function' => __FUNCTION__, 'vars' => get_defined_vars()));

                if ($cet_result) {
                    if ($cet_result['subject']) {
                        $mail->setSubject(html_entity_decode($cet_result['subject'], ENT_QUOTES, 'UTF-8'));
                    }

                    if ($cet_result['html']) {
                        $mail->setNewHtml($cet_result['html']);
                    }

                    if ($cet_result['text']) {
                        $mail->setNewText($cet_result['text']);
                    }

                    if ($cet_result['bcc_html']) {
                        $mail->setBccHtml($cet_result['bcc_html']);
                    }

                    if ((isset($this->request->post['attach_invoicepdf']) && $this->request->post['attach_invoicepdf'] == 1) || $cet_result['invoice'] == 1) {
                        $path_to_invoice_pdf = $cet->invoice($order_info, $cet_result['history_id']);

                        $mail->addAttachment($path_to_invoice_pdf);
                    }

                    if (isset($this->request->post['fattachments'])) {
                        if ($this->request->post['fattachments']) {
                            foreach ($this->request->post['fattachments'] as $attachment) {
                                foreach ($attachment as $file) {
                                    $mail->addAttachment($file);
                                }
                            }
                        }
                    }

                    $mail->setBccEmails($cet_result['bcc_emails']);
                }
            }
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            if ($this->config->get('custom_email_templates_status')) {
                $mail->sendBccEmails();
            }
		}
	}

	public function deleteReward($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getRewards($customer_id, $start = 0, $limit = 10) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
	
		return $query->rows;
	}
	
	public function getTotalRewards($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}
			
	public function getRewardTotal($customer_id) {
        $queryRewardPointInstalled = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = 'reward_points_pro'");

        if($queryRewardPointInstalled->num_rows) {
            $query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE status = 1 AND customer_id = '" . (int)$customer_id . "'");

            return $query->row['total'];
        }
        else {
            $query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");

            return $query->row['total'];
        }
	}		
	
	public function getTotalCustomerRewardsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row['total'];
	}
	
	public function getIpsByCustomerId($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}	
	
	public function getTotalCustomersByIp($ip) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($ip) . "'");

		return $query->row['total'];
	}
	
	public function addBanIp($ip) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ban_ip` SET `ip` = '" . $this->db->escape($ip) . "'");
	}
		
	public function removeBanIp($ip) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ban_ip` WHERE `ip` = '" . $this->db->escape($ip) . "'");
	}
			
	public function getTotalBanIpsByIp($ip) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_ban_ip` WHERE `ip` = '" . $this->db->escape($ip) . "'");
				 
		return $query->row['total'];
	}

	public function getCombinedHistory()
    {
        $queryString = [];
        $queryString[] = 'SELECT description, date_added, "transaction" AS type FROM `customer_transaction`';
//        $queryString[] = 'ORDER BY date_added DESC LIMIT 5';
        $queryString[] = 'UNION';
        $queryString[] = 'SELECT description, date_added, "reward" AS type FROM `customer_reward`';
        $queryString[] = 'ORDER BY date_added DESC LIMIT 5';

        $data = $this->db->query(implode(' ', $queryString));

        if ($data->num_rows) {
            return $data->rows;
        }

        return false;
    }

    public function getTimelineTransactions($customer_id) {
        $query = $this->db->query("SELECT *, 'transaction' AS type FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC");

        return $query->rows;
	}

	/**
	 * @param $customer_id
	 * @return mixed
	 */
	public function getTimelineRewards($customer_id) {

		$status = '';
		if (\Extension::isInstalled('reward_points_pro')){
			$status = ' and status=1';
		}

        $query = $this->db->query("SELECT *, 'reward' AS type FROM " . DB_PREFIX . "customer_reward WHERE customer_id = " . (int)$customer_id . $status . " ORDER BY date_added DESC");

        return $query->rows;
	}
}
?>
