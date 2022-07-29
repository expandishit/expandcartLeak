<?php
class ModelReportCustomer extends Model {
	public function getOrders($data = array()) { 
		$sql = "SELECT tmp.customer_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.order_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.total) AS total FROM (SELECT o.order_id, c.customer_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email, cgd.name AS customer_group, c.status, (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, o.total FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "customer` c ON (o.customer_id = c.customer_id) LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE o.customer_id > 0 AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
				
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		//Exclude archived orders
		$sql .= " AND o.archived = 0";

		$sql .= ") tmp GROUP BY tmp.customer_id ORDER BY total DESC";
				
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

	public function getTotalOrders($data = array()) {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.customer_id) AS total FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT o.customer_id FROM ashawqy_".$store_code."." . DB_PREFIX . "order o WHERE o.customer_id > '0'";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    $sql .= " AND o.order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if (!empty($data['user_agent'])) {
                    if($data['user_agent'] == 'web_platform'){
                        $sql .= " AND o.user_agent  LIKE '%Windows%' OR '%Macintosh%'  OR '%Linux %' ";
                    }else if($data['user_agent'] == 'mobile_platform'){
                        $sql .= " AND o.user_agent  LIKE '%iPhone%' OR '%Android%' ";
                    }
                }
                //Exclude archived orders
                $sql .= " AND o.archived = 0 UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";

        }else{
            $sql = "SELECT COUNT(DISTINCT o.customer_id) AS total FROM `" . DB_PREFIX . "order` o WHERE o.customer_id > '0'";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                $sql .= " AND o.order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if (!empty($data['user_agent'])) {
                if($data['user_agent'] == 'web_platform'){
                    $sql .= " AND (o.user_agent  LIKE '%Windows%' OR o.user_agent  LIKE '%Macintosh%'  OR o.user_agent  LIKE '%Linux %') ";
                }else if($data['user_agent'] == 'mobile_platform'){
                    $sql .= " AND ( o.user_agent  LIKE '%iPhone%' OR  o.user_agent  LIKE '%Android%' )";
                }
            }
            //Exclude archived orders
            $sql .= " AND o.archived = 0";
        }


		$query = $this->db->query($sql);

        return $query->row['total'];

    }
	
	public function getRewardPoints($data = array()) { 
		$sql = "SELECT cr.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(cr.points) AS points, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM " . DB_PREFIX . "customer_reward cr LEFT JOIN `" . DB_PREFIX . "customer` c ON (cr.customer_id = c.customer_id) LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (cr.order_id = o.order_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		//Exclude archived orders
		$sql .= " AND o.archived = 0";

		$sql .= " GROUP BY cr.customer_id ORDER BY points DESC";
				
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

	public function getTotalRewardPoints() {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.customer_id) AS total FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT  customer_id FROM ashawqy_".$store_code."." . DB_PREFIX . "customer_reward";

                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
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
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT customer_id) AS total FROM `" . DB_PREFIX . "customer_reward`";

            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
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
        }

						
		$query = $this->db->query($sql);
        return $query->row['total'];

    }
	
	public function getCredit($data = array()) { 
		$sql = "SELECT ct.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(ct.amount) AS total FROM " . DB_PREFIX . "customer_transaction ct LEFT JOIN `" . DB_PREFIX . "customer` c ON (ct.customer_id = c.customer_id) LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_date_start'])) {
			$sql .= "DATE(ct.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= "DATE(ct.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
				
		$sql .= " GROUP BY ct.customer_id ORDER BY total DESC";
				
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

	public function getTotalCredit() {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.customer_id) AS total FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT customer_id FROM ashawqy_".$store_code."." . DB_PREFIX . "customer_transaction";

                $implode = array();

                if (!empty($data['filter_date_start'])) {
                    $implode[] = "DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $implode[] = "DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if ($implode) {
                    $sql .= " WHERE " . implode(" AND ", $implode);
                }
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT customer_id) AS total FROM `" . DB_PREFIX . "customer_transaction`";

            $implode = array();

            if (!empty($data['filter_date_start'])) {
                $implode[] = "DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $implode[] = "DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if ($implode) {
                $sql .= " WHERE " . implode(" AND ", $implode);
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

        return $query->row['total'];
	}



    public function getOrdersDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT tmp.customer_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.order_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.total) AS total FROM (SELECT o.order_id, c.customer_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email, cgd.name AS customer_group, c.status, (SELECT SUM(op.quantity) FROM ashawqy_".$store_code."." . DB_PREFIX . "order_product op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, o.total FROM ashawqy_".$store_code."." . DB_PREFIX . "order o LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "customer c ON (o.customer_id = c.customer_id) LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE o.customer_id > 0 AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    $sql .= " AND o.order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if (!empty($data['user_agent'])) {
                    if($data['user_agent'] == 'web_platform'){
                        $sql .= " AND (o.user_agent  LIKE '%Windows%' OR o.user_agent  LIKE '%Macintosh%'  OR o.user_agent  LIKE '%Linux %') ";
                    }else if($data['user_agent'] == 'mobile_platform'){
                        $sql .= " AND ( o.user_agent  LIKE '%iPhone%' OR  o.user_agent  LIKE '%Android%' )";
                    }
                }
                //Exclude archived orders
                $sql .= " AND o.archived = 0";

                $sql .= ") tmp GROUP BY tmp.customer_id ORDER BY total DESC) final";


                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR customer_group LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR status LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR orders LIKE '" .(integer) $request['search']['value'] . "%'";
                    $sql .= " OR products LIKE '" .(integer) $request['search']['value'] . "%'";
                    $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT tmp.customer_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.order_id) AS orders,
             SUM(tmp.products) AS products, SUM(tmp.total) AS total FROM (SELECT o.order_id, c.customer_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, 
             o.email, cgd.name AS customer_group, c.status,(SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op 
             WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, o.total FROM `" . DB_PREFIX . "order` o";
            
             $sql.="  LEFT JOIN `" . DB_PREFIX . "customer` c ON (o.customer_id = c.customer_id) ";
             $sql.="  LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) ";

               if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1)
               {
                $sql.= "LEFT JOIN " . DB_PREFIX . " order_product order_pro ON (order_pro.order_id = o.order_id)";
                $sql.= "RIGHT JOIN " . DB_PREFIX . " trips_product trips_pro ON (trips_pro.product_id = order_pro.product_id)"; 
               }
            $sql.="  WHERE o.customer_id > 0 AND cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
              
           
            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                $sql .= " AND o.order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if (!empty($data['user_agent'])) {
                if($data['user_agent'] == 'web_platform'){
                    $sql .= " AND (o.user_agent  LIKE '%Windows%' OR o.user_agent  LIKE '%Macintosh%'  OR o.user_agent  LIKE '%Linux %') ";
                }else if($data['user_agent'] == 'mobile_platform'){
                    $sql .= " AND ( o.user_agent  LIKE '%iPhone%' OR  o.user_agent  LIKE '%Android%' )";
                }
            }
         
            //Exclude archived orders
            $sql .= " AND o.archived = 0";

            $sql .= ") tmp GROUP BY tmp.customer_id ORDER BY total DESC) final";



            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR customer_group LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR status LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR orders LIKE '" .(integer) $request['search']['value'] . "%'";
                $sql .= " OR products LIKE '" .(integer) $request['search']['value'] . "%'";
                $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
            }
        }

        if($data['top_customers_purchasing'] == 1){
            $orderBy = 'total';
            $sortBy = 'DESC';
        }else{
            $orderBy = $columns[$request['order'][0]['column']];
            $sortBy = $request['order'][0]['dir'];            
        }     
        $sql .= " ORDER BY " . $orderBy . "   " . $sortBy . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }
  
    public function getRewardPointsDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT cr.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(cr.points) AS points, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "customer_reward cr LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "customer c ON (cr.customer_id = c.customer_id) LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "order o ON (cr.order_id = o.order_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                //Exclude archived orders
                $sql .= " AND o.archived = 0";

                $sql .= " GROUP BY cr.customer_id ORDER BY points DESC";

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR customer_group LIKE '" .  $request['search']['value'] . "%' ";
                    $sql .= " OR status LIKE '" .  $request['search']['value'] . "%'";
                    $sql .= " OR points LIKE '" . (integer) $request['search']['value'] . "%'";
                    $sql .= " OR orders LIKE '" . (integer) $request['search']['value'] . "%'";
                    $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT cr.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(cr.points) AS points, COUNT(o.order_id) AS orders, SUM(o.total) AS total FROM " . DB_PREFIX . "customer_reward cr LEFT JOIN `" . DB_PREFIX . "customer` c ON (cr.customer_id = c.customer_id) LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (cr.order_id = o.order_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(cr.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(cr.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            //Exclude archived orders
            $sql .= " AND o.archived = 0";

            $sql .= " GROUP BY cr.customer_id ORDER BY points DESC";

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR customer_group LIKE '" .  $request['search']['value'] . "%' ";
                $sql .= " OR status LIKE '" .  $request['search']['value'] . "%'";
                $sql .= " OR points LIKE '" . (integer) $request['search']['value'] . "%'";
                $sql .= " OR orders LIKE '" . (integer) $request['search']['value'] . "%'";
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

    public function getCreditDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT ct.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(ct.amount) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "customer_transaction ct LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "customer c ON (ct.customer_id = c.customer_id) LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

                $sql .= " GROUP BY ct.customer_id ORDER BY total DESC) final";

                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR customer_group LIKE '" .  $request['search']['value'] . "%' ";
                    $sql .= " OR status LIKE '" .  $request['search']['value'] . "%'";
                    $sql .= " OR total LIKE '%" . (integer) $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT ct.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(ct.amount) AS total FROM " . DB_PREFIX . "customer_transaction ct LEFT JOIN `" . DB_PREFIX . "customer` c ON (ct.customer_id = c.customer_id) LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
//
//        if (!empty($data['filter_date_start'])) {
//            $sql .= "DATE(ct.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
//        }
//
//        if (!empty($data['filter_date_end'])) {
//            $sql .= "DATE(ct.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
//        }

            $sql .= " GROUP BY ct.customer_id ORDER BY total DESC) final";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( customer LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR email LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR customer_group LIKE '" .  $request['search']['value'] . "%' ";
                $sql .= " OR status LIKE '" .  $request['search']['value'] . "%'";
                $sql .= " OR total LIKE '%" . (integer) $request['search']['value'] . "%' )";
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