<?php
class ModelReportProduct extends Model {
	public function getProductsViewed($data = array()) {
		$sql = "SELECT pd.name, p.model, p.viewed FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > 0 ORDER BY p.viewed DESC";
					
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
	
	public function getTotalProductsViewed($data = "") {
        if(is_array($data) && isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(product_id) AS total FROM(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT product_id FROM ashawqy_".$store_code."." . DB_PREFIX . "product WHERE viewed > 0";
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";

        }else{
            $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE viewed > 0";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];

    }
	
	public function getTotalProductViews($data = "") {
        if(is_array($data) && isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= " SELECT SUM(viewed) AS total FROM ashawqy_".$store_code."." . DB_PREFIX . "product";
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = " SELECT SUM(viewed) AS total FROM " . DB_PREFIX . "product";
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
			
	public function reset() {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = '0'");
	}

    public function getTopTenPurchased($data = array()) {
        $sql = "SELECT pd.name, p.model, SUM(IFNULL(op.quantity, 0)) AS quantity, SUM(IFNULL(op.total, 0) + IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) AS total, SUM(IFNULL(op.total, 0)) - SUM(IFNULL(op.quantity, 0)) * p.cost_price AS profit FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id)";

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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

        $sql .= " GROUP BY p.product_id";

        if ($data['sort_col'] == "quantity") {
            $sql .= " ORDER BY quantity";
        }
        elseif ($data['sort_col'] == "total") {
            $sql .= " ORDER BY total";
        }
        elseif ($data['sort_col'] == "profit") {
            $sql .= " ORDER BY profit";
        }
        else {
            $sql .= " ORDER BY total";
        }

        if ($data['sort_direction'] == "ASC")
            $sql .= " ASC";
        else
            $sql .= " DESC";

        $sql .= " LIMIT 10";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getPurchased($data = array()) {
		$sql = "SELECT pd.name, p.model, SUM(IFNULL(op.quantity, 0)) AS quantity, SUM(IFNULL(op.total, 0) + IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) AS total, SUM(IFNULL(op.total, 0)) - SUM(IFNULL(op.quantity, 0)) * p.cost_price AS profit FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id)";

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
            if ($data['filter_all_products'] != "1")
                $sql .= " AND o.order_status_id > '0'";
        }

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}
		
		$sql .= " GROUP BY p.product_id";

        if ($data['sort_col'] == "quantity") {
            $sql .= " ORDER BY quantity";
        }
        elseif ($data['sort_col'] == "total") {
            $sql .= " ORDER BY total";
        }
        elseif ($data['sort_col'] == "profit") {
            $sql .= " ORDER BY profit";
        }
        else {
            $sql .= " ORDER BY total";
        }

        if ($data['sort_direction'] == "ASC")
            $sql .= " ASC";
        else
            $sql .= " DESC";
					
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

    public function getTotalPurchased($data) {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.product_id) AS total FROM(';            
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT p.product_id FROM ashawqy_".$store_code."." . DB_PREFIX . "product p LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order_product op ON (op.product_id = p.product_id) LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o ON (op.order_id = o.order_id) LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id)";
                if (!empty($data['filter_product_category_id']) && $data['filter_product_category_id'] != 0) {
                        $sql .= " LEFT JOIN ashawqy_$store_code." . DB_PREFIX . "product_to_category pc ON (p.product_id = pc.product_id)";
                        $whereCondition = "AND category_id = " . (int) $data['filter_product_category_id'];
                    }

                $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' $whereCondition";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    if ($data['filter_all_products'] != "1")
                        $sql .= " AND o.order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id)";
            
            if (!empty($data['filter_product_category_id']) && $data['filter_product_category_id'] != 0) {
                $sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (p.product_id = pc.product_id)";
                $whereCondition = "AND category_id = ". (int) $data['filter_product_category_id'];
            }
            if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1)
            {
             $sql.= "Right JOIN " . DB_PREFIX . " trips_product trips_pro ON (trips_pro.product_id = p.product_id)"; 
            }

            $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' $whereCondition";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                if ($data['filter_all_products'] != "1")
                    $sql .= " AND o.order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }
        }


        $query = $this->db->query($sql);

        return $query->row['total'];
    }



    public function getProductsViewedDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT pd.name, p.model, p.viewed FROM ashawqy_".$store_code."."  . DB_PREFIX . "product p LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > 0 ORDER BY p.viewed DESC) final";

                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR model LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR viewed LIKE '" .  $request['search']['value'] . "%' )";

                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);
        }else{
            $sql = "SELECT * FROM (SELECT pd.name, p.model, p.viewed FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > 0 ORDER BY p.viewed DESC) final";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR model LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR viewed LIKE '" .  $request['search']['value'] . "%' )";

            }
        }



//        $sql .= " ORDER BY tmp.date_added DESC";
        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }

    public function getTopTenPurchasedDataTable($data, $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM 
                (
                    SELECT pd.name, 
                    p.model, 
                    SUM(IFNULL(p.quantity, 0)) AS current_quantity, 
                SUM(IFNULL(op.quantity, 0)) AS quantity, 
                SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
                SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
                (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit 

                    FROM ashawqy_".$store_code."." . DB_PREFIX . "product p 
                    
                    LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order_product op 
                    ON (op.product_id = p.product_id) 

                    LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o 
                    ON (op.order_id = o.order_id) 

                    LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "product_description pd 
                    ON (pd.product_id = p.product_id)
                ";

                $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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

                $sql .= " GROUP BY op.product_id) AS final";

                // if there is a search parameter, $requestData['search']['value'] contains search parameter
                if (!empty($data['search'])) {  
                    $word = $request['search']['value'];

                    $sql .= " WHERE ( final.name LIKE '" . $word . "%' ";
                    $sql .= " OR final.model LIKE '" . $word . "%' ";
                    $sql .= " OR final.quantity LIKE '" . (int) $word . "%' ";
                    $sql .= " OR final.total LIKE '" . (int) $word . "%' )";

                }
                $sql .= " UNION ";
            }

            //To remove the last "not-necessary" union keyword
            $sql = substr($sql, 0, -6);

        }else{
            $sql .= "SELECT * FROM 
                (
                    SELECT pd.name, 
                    op.model, 
                    p.quantity AS current_quantity, 
            SUM(IFNULL(op.quantity, 0)) AS quantity, 
            SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
            SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
            (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit 

                    FROM `" . DB_PREFIX . "product` p 
                
                    LEFT JOIN `" . DB_PREFIX . "order_product` op 
                    ON (op.product_id = p.product_id) 

                    LEFT JOIN `" . DB_PREFIX . "order` o 
                    ON (op.order_id = o.order_id) 

                    LEFT JOIN `" . DB_PREFIX . "product_description` pd 
                    ON (pd.product_id = p.product_id)
                ";

            $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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

            $sql .= " GROUP BY op.product_id) AS final";
            
            // if there is a search parameter, $requestData['search']['value'] contains search parameter
            if (!empty($data['search'])) {
                $word = $request['search']['value'];

                $sql .= " WHERE ( final.name LIKE '" . $word . "%' ";
                $sql .= " OR final.model LIKE '" . $word . "%' ";
                $sql .= " OR final.quantity LIKE '" . (int) $word . "%' ";
                $sql .= " OR final.total LIKE '" . (int) $word . "%' )";
            }

        }

        if(!empty($data['total'])){
            $sql .= " ORDER BY final.quantity desc , final." . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $data['total'] . " ; ";
        }

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }


    public function getPurchasedDataTable($data, $request, $columns)
    {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (
                SELECT op.name, 
                op.model, 
                SUM(IFNULL(p.quantity, 0)) AS current_quantity, 
                SUM(IFNULL(op.quantity, 0)) AS quantity, 
                SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
                SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
                (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit  

                FROM ashawqy_".$store_code."." . DB_PREFIX . "product p 

                LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order_product op 
                ON (op.product_id = p.product_id) 

                LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o 
                ON (op.order_id = o.order_id) 

                LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "product_description pd 
                ON (pd.product_id = p.product_id) ";
                
                if (!empty($data['filter_product_category_id']) && $data['filter_product_category_id'] != 0) {
                    $sql .= "LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "product_to_category pc ON (pc.product_id = p.product_id)";
                    $whereCondition .= "AND category_id = " . (int) $data['filter_product_category_id'];
                }

                $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' $whereCondition";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else if ($data['filter_all_products'] == "1"){
                    $sql .= " AND o.order_status_id = '0'";
                }
                else{
                    $sql .= " AND order_status_id > 0 ";
                }
                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }
                


                $sql .= " GROUP BY p.product_id) final";

                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR model LIKE '" . $request['search']['value'] . "%' ";
                    $sql .= " OR quantity LIKE '" . (integer) $request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '" . (integer) $request['search']['value'] . "%' ";
                    $sql .= " OR profit LIKE '" . (integer) $request['search']['value'] . "%' )";

                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6);

        }else{
            $sql = "SELECT * FROM (
            SELECT op.name, 
            op.model, 
            p.quantity AS current_quantity, 
            p.price AS price, 
            SUM(IFNULL(op.quantity, 0)) AS quantity, 
            SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
            SUM(IFNULL(op.quantity, 0)) * p.cost_price  AS cost,
            (SUM(IFNULL(op.total, 0)) - (SELECT IF(SUM(op.total) > 0, SUM(IFNULL(op.quantity, 0)), 0)) * p.cost_price) AS profit

            FROM `" . DB_PREFIX . "product` p 

            LEFT JOIN `" . DB_PREFIX . "order_product` op 
            ON (op.product_id = p.product_id) 

            LEFT JOIN `" . DB_PREFIX . "order` o 
            ON (op.order_id = o.order_id) 

            LEFT JOIN `" . DB_PREFIX . "product_description` pd 
            ON (pd.product_id = p.product_id) ";

            if (!empty($data['filter_product_category_id']) && $data['filter_product_category_id'] != 0) {
                $sql .= "LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p.product_id)";
                $whereCondition .= "AND category_id = " . (int) $data['filter_product_category_id'];
            }
            if((\Extension::isInstalled('multiseller'))&&(\Extension::isInstalled('trips') &&$this->config->get('trips')['status']==1)&&$data['trips'] == 1)
            {
             $sql.= "Right JOIN " . DB_PREFIX . " trips_product trips_pro ON (trips_pro.product_id = p.product_id)"; 
            }

            $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' $whereCondition";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else if ($data['filter_all_products'] == "1"){
                $sql .= " AND o.order_status_id = '0'";
            }
            else{
                $sql .= " AND order_status_id > 0 ";
            }
            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }
            
           
            $sql .= " GROUP BY p.product_id) final";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( name LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR model LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR quantity LIKE '" . (int) $request['search']['value'] . "%' ";
                $sql .= " OR total LIKE '" . (int) $request['search']['value'] . "%' ";
                $sql .= " OR profit LIKE '" . (int) $request['search']['value'] . "%' )";

            }

        }


//        $sql .= " ORDER BY tmp.date_added DESC";
        $sql .= " ORDER BY final.quantity desc, final." . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . " ; ";


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }


}
?>
