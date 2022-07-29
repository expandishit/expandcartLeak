<?php
class ModelReportBrand extends Model {

    public function getTotalPurchased($data) {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(DISTINCT final.manufacturer_id) AS total FROM(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT ma.manufacturer_id FROM ashawqy_".$store_code."." . DB_PREFIX . "manufacturer ma
                 LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "product p ON (ma.manufacturer_id = p.manufacturer_id)
                 LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order_product op ON (op.product_id = p.product_id)
                  LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o ON (op.order_id = o.order_id)";

				$sql .= " WHERE ";
                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                }

                if (!empty($data['filter_date_start'])) {
					if (!empty($data['filter_order_status_id'])) $sql .= " AND ";
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
					if (!empty($data['filter_order_status_id']) || !empty($data['filter_date_start'])) $sql .= " AND ";
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }
                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") final";
        }else{
            $sql = "SELECT COUNT(DISTINCT ma.manufacturer_id) AS total FROM `" . DB_PREFIX . "manufacturer` ma 
                    LEFT JOIN `" . DB_PREFIX . "product` p ON (ma.manufacturer_id = p.manufacturer_id)
                    LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.product_id = p.product_id)
                    LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

			$sql .= "WHERE ";
            if (!empty($data['filter_order_status_id'])) {
				$sql .= " o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            }

            if (!empty($data['filter_date_start'])) {
				if (!empty($data['filter_order_status_id'])) $sql .= " AND ";
                $sql .= " DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
				if (!empty($data['filter_order_status_id']) || !empty($data['filter_date_start'])) $sql .= " AND ";
                $sql .= " DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }
        }


        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getPurchasedDataTable($data, $request, $columns)
    {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = 'SELECT name,manufacturer_id,SUM(current_quantity) AS current_quantity,SUM(quantity) AS quantity, SUM(total) AS total, SUM(cost) AS cost,SUM(profit) AS profit FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "
            (SELECT ma.name,ma.manufacturer_id,
            SUM(IFNULL(p.quantity, 0)) AS current_quantity, 
            SUM(IFNULL(op.quantity, 0)) AS quantity, 
            SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
            SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
            (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit

            FROM ashawqy_".$store_code."."  . DB_PREFIX . "manufacturer ma
            
            LEFT JOIN  ashawqy_".$store_code."."  . DB_PREFIX . "product p 
            ON (ma.manufacturer_id = p.manufacturer_id)

            LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "order_product op 
            ON (op.product_id = p.product_id)  

            LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "order o 
            ON (op.order_id = o.order_id) ";


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

                $sql .= " GROUP BY ma.manufacturer_id";

                $sql .= ") UNION ALL ";
            }
            $sql = substr($sql, 0, -10);

            $sql .= ") final GROUP BY manufacturer_id";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( final.name LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR final.total LIKE '" .  $request['search']['value'] . "%' ";
                $sql .= " OR final.profit LIKE '" .  $request['search']['value'] . "%' )";
            }

        }else{
            $sql = "SELECT * FROM (
            SELECT ma.name,ma.manufacturer_id,
            SUM(IFNULL(p.quantity, 0)) AS current_quantity, 
            SUM(IFNULL(op.quantity, 0)) AS quantity, 
            SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
            SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
            (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit 

            FROM `" . DB_PREFIX . "manufacturer` ma
            
            LEFT JOIN  `" . DB_PREFIX . "product` p 
            ON (ma.manufacturer_id = p.manufacturer_id)

            LEFT JOIN `" . DB_PREFIX . "order_product` op 
            ON (op.product_id = p.product_id) 

            LEFT JOIN `" . DB_PREFIX . "order` o 
            ON (op.order_id = o.order_id) ";

			$sql .= "WHERE ";
            if (!empty($data['filter_order_status_id'])) {
                $sql .= " o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else if ($data['filter_all_products'] == "1"){
                $sql .= " o.order_status_id = '0'";
            }
            else{
                $sql .= " order_status_id > 0 ";
            }
            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            $sql .= " GROUP BY ma.manufacturer_id) final";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( final.name LIKE '" . $request['search']['value'] . "%' ";
                $sql .= " OR final.total LIKE '" .  $request['search']['value'] . "%' ";
                $sql .= " OR final.profit LIKE '" .  $request['search']['value'] . "%' )";

            }

        }


//        $sql .= " ORDER BY tmp.date_added DESC";
        $sql .= " ORDER BY manufacturer_id desc, " . $columns[$request['order'][0]['column']] . " " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . " ; ";

        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }

    public function getAllBrandPurchased()
    {

        $sql = "SELECT * FROM (
        SELECT ma.name,ma.manufacturer_id,
        SUM(IFNULL(p.quantity, 0)) AS current_quantity, 
        SUM(IFNULL(op.quantity, 0)) AS quantity, 
        SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) AS total, 
        SUM(IFNULL(p.quantity, 0)) * p.cost_price  AS cost, 
        (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) AS profit 

        FROM `" . DB_PREFIX . "manufacturer` ma
        
        LEFT JOIN  `" . DB_PREFIX . "product` p 
        ON (ma.manufacturer_id = p.manufacturer_id)

        LEFT JOIN `" . DB_PREFIX . "order_product` op 
        ON (op.product_id = p.product_id) 

        LEFT JOIN `" . DB_PREFIX . "order` o 
        ON (op.order_id = o.order_id) 
        AND order_status_id >= 0
         GROUP BY ma.manufacturer_id) final
          ORDER BY manufacturer_id DESC";


        $query = $this->db->query($sql);


        $data = $query->rows;

        return $data;
    }

}
?>
