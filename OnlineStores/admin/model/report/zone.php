<?php
class ModelReportZone extends Model {

    public function getPurchasedDataTable($data, $request, $columns)
    {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = 'select name,payment_zone_id,
                           sum(total_orders) as total_orders,
                           sum(quantity) as quantity,
                           sum(total) as total,
                           sum(cost) as cost ,
                           sum(profit) AS profit 
                from(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "(
        select z.*,
           payment_zone_id,
           sum(orders) as total_orders,
           sum(prds) as quantity,
           sum(totals) as total,
           sum(cost) as cost ,
           sum(profit) AS profit 
            from (
                            select o.payment_zone_id, o.order_id, count(o.order_id) as orders, 
                            (
                                    select count(op.order_product_id) from ashawqy_".$store_code."."  . DB_PREFIX . "order_product op
                                        where op.order_id = o.order_id
                            ) as prds,
                                (
                                   SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product op
                                                    where op.order_id = o.order_id
                                ) AS totals,
                                (
                                   SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product opr
                                   left join ashawqy_".$store_code."."  . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                                                    where opr.order_id = o.order_id
                                ) AS cost,
                                (
                                   SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product opr
                                   left join ashawqy_".$store_code."."  . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                                                    where opr.order_id = o.order_id
                                 ) AS profit
                                    from ashawqy_".$store_code."."  . DB_PREFIX . "order o 
							WHERE ";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= "  o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else if ($data['filter_all_products'] == "1"){
                    $sql .= "  o.order_status_id = '0'";
                }
                else{
                    $sql .= "  o.order_status_id > 0 ";
                }
                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                // Some filter for the query to avoid invalid orders from added to reports
                //  An invalid order contains no payment_method, shipping_method and order status id is 0
               $sql .= "AND o.payment_code != '' OR shipping_code != ''";

                $sql .= " group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) ";

                $sql .= " ) UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") lastall GROUP BY payment_zone_id ";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( lastall.name LIKE '" . $request['search']['value'] . "%' )";

            }


        }else{
            $sql = "select z.*,
       payment_zone_id,
	   sum(orders) as total_orders,
	   sum(prds) as quantity,
	   sum(totals) as total,
	   sum(cost) as cost ,
	   sum(profit) AS profit 
	    from (
				select o.payment_zone_id, o.order_id, count(o.order_id) as orders, (
						select count(op.order_product_id) from " . DB_PREFIX . "order_product op
							where op.order_id = o.order_id) as prds,
							(
                               SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from " . DB_PREFIX . "order_product op
                                                where op.order_id = o.order_id
                               ) AS totals,
                               (
           SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS cost,
           (
           SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS profit
                            from `" . DB_PREFIX . "order` o 
							WHERE ";


            if (!empty($data['filter_order_status_id'])) {
                $sql .= "  o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else if ($data['filter_all_products'] == "1"){
                $sql .= "  o.order_status_id = '0'";
            }
            else{
                $sql .= "  o.order_status_id > 0 ";
            }
            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= "AND ";
                $sql .= "DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            // Some filter for the query to avoid invalid orders from added to reports
            //  An invalid order contains no payment_method, shipping_method and order status id is 0
            $sql .= "AND (o.payment_code != '' OR shipping_code != '')";

            $sql .= " group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) ";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( z.name LIKE '" . $request['search']['value'] . "%' )";

            }

            $sql .= "GROUP BY final.payment_zone_id";

        }

        // Get total records without limit and offset
        $total_records = $this->db->query($sql)->num_rows;

        // after that get records with limit and offset
        $sql .= " LIMIT " . $request['start'] . " ," . $request['length'] . " ; ";
       
        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered, 'total_records' => $total_records];
    }

    public function getPurchasedAvgDataTable($data, $request, $columns)
    {

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = 'select name,payment_zone_id,
                           AVG(total_orders) as total_orders,
                           AVG(quantity) as quantity,
                           AVG(total) as total,
                           AVG(cost) as cost ,
                           AVG(profit) AS profit 
                from(';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "(
        select z.*,
           payment_zone_id,
           sum(orders) as total_orders,
           sum(prds) as quantity,
           sum(totals) as total,
           sum(cost) as cost ,
           sum(profit) AS profit 
            from (
                            select o.payment_zone_id, o.order_id, count(o.order_id) as orders, 
                            (
                                    select count(op.order_product_id) from ashawqy_".$store_code."."  . DB_PREFIX . "order_product op
                                        where op.order_id = o.order_id
                            ) as prds,
                                (
                                   SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product op
                                                    where op.order_id = o.order_id
                                ) AS totals,
                                (
                                   SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product opr
                                   left join ashawqy_".$store_code."."  . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                                                    where opr.order_id = o.order_id
                                ) AS cost,
                                (
                                   SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from ashawqy_".$store_code."."  . DB_PREFIX . "order_product opr
                                   left join ashawqy_".$store_code."."  . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                                                    where opr.order_id = o.order_id
                                 ) AS profit
                                    from ashawqy_".$store_code."."  . DB_PREFIX . "order o 
							WHERE ";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= "  o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else if ($data['filter_all_products'] == "1"){
                    $sql .= "  o.order_status_id = '0'";
                }
                else{
                    $sql .= "  o.order_status_id > 0 ";
                }
                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                $sql .= " group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) ";

                $sql .= " ) UNION ALL ";
            }
            $sql = substr($sql, 0, -10);
            $sql .= ") lastall GROUP BY payment_zone_id ";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( lastall.name LIKE '" . $request['search']['value'] . "%' )";

            }
            $sql .= " LIMIT " . $request['start'] . " ," . $request['length'] . " ; ";


        }else{
            $sql = "select z.*,
       payment_zone_id,
	   AVG(orders) as total_orders,
	   AVG(prds) as quantity,
	   AVG(totals) as total,
	   AVG(cost) as cost ,
	   AVG(profit) AS profit 
	    from (
				select o.payment_zone_id, o.order_id, count(o.order_id) as orders, (
						select count(op.order_product_id) from " . DB_PREFIX . "order_product op
							where op.order_id = o.order_id) as prds,
							(
                               SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from " . DB_PREFIX . "order_product op
                                                where op.order_id = o.order_id
                               ) AS totals,
                               (
           SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS cost,
           (
           SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS profit
                            from `" . DB_PREFIX . "order` o 
							WHERE ";


            if (!empty($data['filter_order_status_id'])) {
                $sql .= "  o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else if ($data['filter_all_products'] == "1"){
                $sql .= "  o.order_status_id = '0'";
            }
            else{
                $sql .= "  o.order_status_id > 0 ";
            }
            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            $sql .= " group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) ";

            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( z.name LIKE '" . $request['search']['value'] . "%' )";

            }

            $sql .= "GROUP BY final.payment_zone_id LIMIT " . $request['start'] . " ," . $request['length'] . " ; ";

        }


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }

    public function getZonePurchased()
    {

            $sql = "select z.*,
       payment_zone_id,
	   sum(orders) as total_orders,
	   sum(prds) as quantity,
	   sum(totals) as total,
	   sum(cost) as cost ,
	   sum(profit) AS profit 
	    from (
				select o.payment_zone_id, o.order_id, count(o.order_id) as orders, (
						select count(op.order_product_id) from " . DB_PREFIX . "order_product op
							where op.order_id = o.order_id) as prds,
							(
                               SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from " . DB_PREFIX . "order_product op
                                                where op.order_id = o.order_id
                               ) AS totals,
                               (
           SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS cost,
           (
           SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS profit
                            from `" . DB_PREFIX . "order` o 
							WHERE o.order_status_id >= 0 group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) 
							GROUP BY final.payment_zone_id ";



        // Get total records without limit and offset
        $total_records = $this->db->query($sql)->num_rows;


        $query = $this->db->query($sql);


        $totalData = $query->rows;

        return ['data' => $totalData ,  'total_records' => $total_records];
    }


    public function getZoneAvgPurchased()
    {

        $sql = "select z.*,
       payment_zone_id,
	   AVG(orders) as total_orders,
	   AVG(prds) as quantity,
	   AVG(totals) as total,
	   AVG(cost) as cost ,
	   AVG(profit) AS profit 
	    from (
				select o.payment_zone_id, o.order_id, count(o.order_id) as orders, (
						select count(op.order_product_id) from " . DB_PREFIX . "order_product op
							where op.order_id = o.order_id) as prds,
							(
                               SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) )  from " . DB_PREFIX . "order_product op
                                                where op.order_id = o.order_id
                               ) AS totals,
                               (
           SELECT SUM(IFNULL(p.quantity, 0) * p.cost_price)  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS cost,
           (
           SELECT (SUM(IFNULL(opr.total, 0)) - (SUM(IFNULL(opr.quantity, 0)) * p.cost_price) )  from " . DB_PREFIX . "order_product opr
               left join " . DB_PREFIX . "product p ON (opr.product_id = p.product_id)
                              	where opr.order_id = o.order_id
           ) AS profit
                            from `" . DB_PREFIX . "order` o 
							WHERE
							 o.order_status_id >= 0
							 group by o.order_id ) final left join zone z on (z.zone_id = final.payment_zone_id) 
							 GROUP BY final.payment_zone_id";



        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalRecords' => $totalFiltered];
    }

}
?>
