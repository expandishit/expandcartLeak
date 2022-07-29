<?php

class ModelReportSale extends Model
{
    public function getOrders($data = array())
    {
        $sql = "SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS `orders`, SUM(tmp.products) AS products, SUM(tmp.tax) AS tax, SUM(tmp.total) AS total, SUM(tmp.total) - SUM(tmp.cost) AS profit FROM (SELECT o.order_id, (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, (SELECT SUM(p.cost_price * op.quantity) FROM `" . DB_PREFIX . "order_product` op JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) WHERE op.order_id = o.order_id GROUP BY op.order_id) AS cost, (SELECT SUM(ot.value) FROM `" . DB_PREFIX . "order_total` ot WHERE ot.order_id = o.order_id AND ot.code = 'tax' GROUP BY ot.order_id) AS tax, o.total, o.date_added FROM `" . DB_PREFIX . "order` o";

        if (!empty($data['filter_order_status_id'])) {
            $sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " WHERE o.order_status_id > '0'";
        }

        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        //Exclude archived orders
        $sql .= " AND o.archived = 0";

        $sql .= " GROUP BY o.order_id) tmp";

//		$sql.= " WHERE products LIKE '2%' ";

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY DAY(tmp.date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY WEEK(tmp.date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY MONTH(tmp.date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY YEAR(tmp.date_added)";
                break;
        }

        $sql .= " ORDER BY tmp.date_added DESC";

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

    public function getTotalOrders($data = array())
    {
        /*
        $data['archive'] => on -> not archieved-> only that archieved = 0
                         => off-> all orders whatever archieved value  
        */
        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                switch ($group) {
                    case 'day';
                        $sql .= "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM ashawqy_".$store_code."." . DB_PREFIX . "order";
                        break;
                    default:
                    case 'week':
                        $sql .= "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "order";
                        break;
                    case 'month':
                        $sql .= "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "order";
                        break;
                    case 'year':
                        $sql .= "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM ashawqy_".$store_code."."  . DB_PREFIX . "order";
                        break;
                }

                if ( !empty($data['filter_order_status_id']) ) {
                    $sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } 
                else{
                    $sql .= " WHERE order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if($data['archive'] == "on"){
                    $sql .= " AND archived = 0";
                }

                $sql .= " UNION "; // to unioin sqls of multi_stores
            }
            $sql = substr($sql, 0, -6); //for the last "UNION" word after the loop 
        }else{

            switch ($group) {
                case 'day';
                    $sql = "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM `" . DB_PREFIX . "order`";
                    break;
                default:
                case 'week':
                    $sql = "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM `" . DB_PREFIX . "order`";
                    break;
                case 'month':
                    $sql = "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM `" . DB_PREFIX . "order`";
                    break;
                case 'year':
                    $sql = "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM `" . DB_PREFIX . "order`";
                    break;
            }

            if ( !empty($data['filter_order_status_id']) ) {
                $sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } 
            else{
                $sql .= " WHERE order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if($data['country_id'] && is_array($data['country_id'])){
                $sql .= " AND payment_country_id in(".implode(',',$data['country_id']).")";
            }
            
            if($data['customer_id'] && is_array($data['customer_id'])){
                $sql .= " AND customer_id in(".implode(',',$data['customer_id']).")";
            }
            
            if (isset($data['product_id']) && is_array($data['product_id'])) {

                $productIds = implode(',',$data['product_id']);

                $orderProductQuery = 'SELECT order_id FROM order_product WHERE product_id IN (' . $productIds . ')';
    
                $sql .= " AND order_id IN (" . $orderProductQuery . ")";
            }
            if (isset($data['order_assignee_id']) && is_array($data['order_assignee_id'])) {

                $orderAssigneeIds = implode(',',$data['order_assignee_id']);
               
                $orderAssigneeQuery = 'SELECT order_id FROM order_assignee WHERE user_id IN (' .$this->db->escape($orderAssigneeIds) . ')';
    
                $sql .= " AND order_id IN (" . $orderAssigneeQuery. ")";
            }
           

            if (isset($data['range_min']) || isset($data['range_max'])) {
                if (((int)$data['range_min'] > 0 || (int)$data['range_max'] > 0) && $data['range_min'] != $data['range_max']) {
                    $sql .= ' AND ((total >= ' . $data['range_min'] . ') AND (total <= ' . $data['range_max'] . '))';
                }elseif ((int)$data['range_min'] == (int)$data['range_max']) {
                    $sql .= ' AND ((total = ' . $data['range_max'] . '))';
                }
            }

            //Exclude archived orders
            if($data['archive'] == "on"){
                $sql .= " AND archived = 0";
            }

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

    public function getTaxes($data = array())
    {
        $sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

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

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                break;
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

    public function getTotalTaxes($data = array())
    {
        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(order_total_id) AS total FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT COUNT(order_total_id) AS total FROM ashawqy_".$store_code.".". DB_PREFIX . "order_total ot LEFT JOIN ashawqy_".$store_code.".". DB_PREFIX . "order o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    $sql .= " AND order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                //Exclude archived orders
                $sql .= " AND o.archived = 0";

                switch ($group) {
                    case 'day';
                        $sql .= " GROUP BY DAY(o.date_added), ot.title";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY WEEK(o.date_added), ot.title";
                        break;
                    case 'month':
                        $sql .= " GROUP BY MONTH(o.date_added), ot.title";
                        break;
                    case 'year':
                        $sql .= " GROUP BY YEAR(o.date_added), ot.title";
                        break;
                }

                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10); //for the last "UNION ALL" word after the loop 
            $sql .= ") tmp ";
        }else{

            $sql = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                $sql .= " AND order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            //Exclude archived orders
            $sql .= " AND o.archived = 0";

            switch ($group) {
                case 'day';
                    $sql .= " GROUP BY DAY(o.date_added), ot.title";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY WEEK(o.date_added), ot.title";
                    break;
                case 'month':
                    $sql .= " GROUP BY MONTH(o.date_added), ot.title";
                    break;
                case 'year':
                    $sql .= " GROUP BY YEAR(o.date_added), ot.title";
                    break;
            }

            $sql .= ") tmp";
        }


        $query = $this->db->query($sql);

        return $query->row['total'];

    }

    public function getShipping($data = array())
    {
        $sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " AND o.order_status_id > '0'";
        }

        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }

        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }

        //Exclude archived orders
        $sql .= " AND o.archived = 0";

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        switch ($group) {
            case 'day';
                $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                break;
            case 'month':
                $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                break;
            case 'year':
                $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                break;
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

    public function getTotalShipping($data = array())
    {
        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }


        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE){
            $sql = 'SELECT COUNT(*) AS total FROM (';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT COUNT(*) AS total FROM ashawqy_".$store_code."." . DB_PREFIX . "order_total ot LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    $sql .= " AND order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                //Exclude archived orders
                $sql .= " AND o.archived = 0";

                switch ($group) {
                    case 'day';
                        $sql .= " GROUP BY DAY(o.date_added), ot.title";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY WEEK(o.date_added), ot.title";
                        break;
                    case 'month':
                        $sql .= " GROUP BY MONTH(o.date_added), ot.title";
                        break;
                    case 'year':
                        $sql .= " GROUP BY YEAR(o.date_added), ot.title";
                        break;
                }

                $sql .= " UNION ALL ";
            }
            $sql = substr($sql, 0, -10); //for the last "UNION ALL" word after the 
            $sql .=") tmp";
        }else{
            $sql = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                $sql .= " AND order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            //Exclude archived orders
            $sql .= " AND o.archived = 0";

            switch ($group) {
                case 'day';
                    $sql .= " GROUP BY DAY(o.date_added), ot.title";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY WEEK(o.date_added), ot.title";
                    break;
                case 'month':
                    $sql .= " GROUP BY MONTH(o.date_added), ot.title";
                    break;
                case 'year':
                    $sql .= " GROUP BY YEAR(o.date_added), ot.title";
                    break;
            }

            $sql .= ") tmp";
        }


        $query = $this->db->query($sql);

        return $query->row['total'];

    }


    public function getOrdersJson($data = array(), $request, $columns)
    {
        //
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS `orders`, SUM(tmp.products) AS products, SUM(tmp.tax) AS tax, SUM(tmp.total) AS total, SUM(tmp.total) - SUM(tmp.cost) AS profit FROM (SELECT o.order_id, (SELECT SUM(op.quantity) FROM ashawqy_".$store_code."."  . DB_PREFIX . "order_product op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, (SELECT SUM(p.cost_price * op.quantity) FROM ashawqy_".$store_code."."  . DB_PREFIX . "order_product op JOIN ashawqy_".$store_code."."  . DB_PREFIX . "product p ON (op.product_id = p.product_id) WHERE op.order_id = o.order_id GROUP BY op.order_id) AS cost, (SELECT SUM(ot.value) FROM ashawqy_".$store_code."."  . DB_PREFIX . "order_total ot WHERE ot.order_id = o.order_id AND ot.code = 'tax' GROUP BY ot.order_id) AS tax, o.total, o.date_added FROM ashawqy_".$store_code."."  . DB_PREFIX . "order o";

                if ( !empty($data['filter_order_status_id']) ) {
                    $sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } 
                else{
                    $sql .= " WHERE o.order_status_id > '0'";
                    $sql .= $this->config->get('config_cancelled_order_status_id') ? "  AND o.order_status_id != ".(int)$this->config->get('config_cancelled_order_status_id') : "";

                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                if($data['archive'] == "on"){
                    $sql .= " AND o.archived = 0";
                }

                $sql .= " GROUP BY o.order_id) tmp";


                if (!empty($data['filter_group'])) {
                    $group = $data['filter_group'];
                } else {
                    $group = 'week';
                }

                switch ($group) {
                    case 'day';
                        $sql .= " GROUP BY DAY(tmp.date_added)";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY WEEK(tmp.date_added)";
                        break;
                    case 'month':
                        $sql .= " GROUP BY MONTH(tmp.date_added)";
                        break;
                    case 'year':
                        $sql .= " GROUP BY YEAR(tmp.date_added)";
                        break;
                }

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( orders LIKE '%" . (integer)$request['search']['value'] . "%' ";
                    $sql .= " OR products LIKE '%" . (integer)$request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '%" . (integer)$request['search']['value'] . "%' ";
                    $sql .= " OR tax LIKE '%" . (integer)$request['search']['value'] . "%' ";
                    $sql .= " OR profit LIKE '%" . (integer)$request['search']['value'] . "%' )";
                }


//        $sql .= " ORDER BY tmp.date_added DESC";
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6); //for the last "UNION" word after the loop 
        }else{

            $sql = "SELECT * FROM (
                                    SELECT MIN(tmp.date_added) AS date_start, 
                                    MAX(tmp.date_added) AS date_end, 
                                    COUNT(tmp.order_id) AS `orders`, 
                                    SUM(tmp.products) AS products, 
                                    SUM(tmp.tax) AS tax, 
                                    SUM(tmp.total) AS total, 
                                    SUM(tmp.total) - SUM(tmp.cost) AS profit 
                                    FROM (
                                            SELECT o.order_id, (
                                                                SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op 
                                                                WHERE op.order_id = o.order_id 
                                                                GROUP BY op.order_id
                                                                ) AS products, 
                                                                (
                                                                    SELECT SUM(p.cost_price * op.quantity) FROM `" . DB_PREFIX . "order_product` op 
                                                                    JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) 
                                                                    WHERE op.order_id = o.order_id 
                                                                    GROUP BY op.order_id
                                                                ) AS cost, 
                                                                (
                                                                    SELECT SUM(ot.value) FROM `" . DB_PREFIX . "order_total` ot 
                                                                    WHERE ot.order_id = o.order_id 
                                                                    AND ot.code = 'tax' 
                                                                    GROUP BY ot.order_id
                                                                ) AS tax, 
                                                                o.total, 
                                                                o.date_added FROM `" . DB_PREFIX . "order` o";
            if ( !empty($data['filter_order_status_id']) ) {
                $sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";

            } 
            else{
                $sql .= " WHERE o.order_status_id > '0'";
                $sql .= $this->config->get('config_cancelled_order_status_id') ? "  AND o.order_status_id != ".(int)$this->config->get('config_cancelled_order_status_id') : "";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            if($data['country_id'] && is_array($data['country_id'])){
                $sql .= " AND payment_country_id in(".implode(',',$data['country_id']).")";
            }

            if($data['customer_id'] && is_array($data['customer_id'])){
                $sql .= " AND customer_id in(".implode(',',$data['customer_id']).")";
            }

            if (isset($data['product_id']) && is_array($data['product_id'])) {

                $productIds = implode(',',$data['product_id']);

                $orderProductQuery = 'SELECT order_id FROM order_product WHERE product_id IN (' . $productIds . ')';
    
                $sql .= " AND order_id IN (" . $orderProductQuery . ")";
            }
          
            if (isset($data['order_assignee_id']) && is_array($data['order_assignee_id']) ) {

                $orderAssigneeIds = implode(',',$data['order_assignee_id']);
               
                $orderAssigneeQuery = 'SELECT order_id FROM order_assignee WHERE user_id IN (' .$this->db->escape($orderAssigneeIds) . ')';
    
                $sql .= " AND order_id IN (" . $orderAssigneeQuery. ")";
            }
           

            //Exclude archived orders
            if($data['archive'] == "on"){
                $sql .= " AND o.archived = 0";
            }

            $sql .= " GROUP BY o.order_id) tmp";

            if (!empty($data['filter_group'])) {
                $group = $data['filter_group'];
            } else {
                $group = 'week';
            }

            switch ($group) {
                case 'day';
                    $sql .= " GROUP BY DAY(tmp.date_added)";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY WEEK(tmp.date_added)";
                    break;
                case 'month':
                    $sql .= " GROUP BY MONTH(tmp.date_added)";
                    break;
                case 'year':
                    $sql .= " GROUP BY YEAR(tmp.date_added)";
                    break;
            }

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( orders LIKE '%" . (integer)$request['search']['value'] . "%' ";
                $sql .= " OR products LIKE '%" . (integer)$request['search']['value'] . "%' ";
                $sql .= " OR total LIKE '%" . (integer)$request['search']['value'] . "%' ";
                $sql .= " OR tax LIKE '%" . (integer)$request['search']['value'] . "%' ";
                $sql .= " OR profit LIKE '%" . (integer)$request['search']['value'] . "%' )";
            }

        }
        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";


//        return $sql ;
        $query = $this->db->query($sql);


        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];


    }


    public function getTaxesDataTable($data = array(), $request, $columns)
    {
        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key => $store_code) {
                $sql .= "SELECT * FROM (SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM ashawqy_".$store_code."."  . DB_PREFIX . "order_total ot LEFT JOIN ashawqy_".$store_code."."  . DB_PREFIX . "order o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

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

                if (!empty($data['filter_group'])) {
                    $group = $data['filter_group'];
                } else {
                    $group = 'week';
                }

                switch ($group) {
                    case 'day';
                        $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                        break;
                    case 'month':
                        $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                        break;
                    case 'year':
                        $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                        break;
                }


                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( orders LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_start LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR title LIKE '%" . $request['search']['value'] . "%' )";
                }
                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6); //for the last "UNION" word after the loop 
        }else{
            $sql = "SELECT * FROM (SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

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

            if (!empty($data['filter_group'])) {
                $group = $data['filter_group'];
            } else {
                $group = 'week';
            }

            switch ($group) {
                case 'day';
                    $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                    break;
                case 'month':
                    $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                    break;
                case 'year':
                    $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                    break;
            }


            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( orders LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_start LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR total LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR title LIKE '%" . $request['search']['value'] . "%' )";
            }
        }



        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];

    }


    public function getShippingDataTable($data = array(), $request, $columns)
    {

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'week';
        }

        if(isset($data['multi_store_manager']) && $data['multi_store_manager'] == TRUE) {

            $sql = '';
            foreach ($data['stores_codes'] as $key=>$store_code){
                $sql .= "SELECT * FROM (SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM ashawqy_".$store_code."." . DB_PREFIX . "order_total ot LEFT JOIN ashawqy_".$store_code."." . DB_PREFIX . "order o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

                if (!empty($data['filter_order_status_id'])) {
                    $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
                } else {
                    $sql .= " AND o.order_status_id > '0'";
                }

                if (!empty($data['filter_date_start'])) {
                    $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
                }

                if (!empty($data['filter_date_end'])) {
                    $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
                }

                //Exclude archived orders
                $sql .= " AND o.archived = 0";


                switch ($group) {
                    case 'day';
                        $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                        break;
                    default:
                    case 'week':
                        $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                        break;
                    case 'month':
                        $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                        break;
                    case 'year':
                        $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                        break;
                }

                $sql .= ' ) final';
                if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                    $sql .= " WHERE ( orders LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_start LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR total LIKE '%" . $request['search']['value'] . "%' ";
                    $sql .= " OR title LIKE '%" . $request['search']['value'] . "%' )";
                }

                $sql .= " UNION ";
            }
            $sql = substr($sql, 0, -6); //for the last "UNION" word after the loop 
        }else{
            $sql = "SELECT * FROM (SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

            if (!empty($data['filter_order_status_id'])) {
                $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
            } else {
                $sql .= " AND o.order_status_id > '0'";
            }

            if (!empty($data['filter_date_start'])) {
                $sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
            }

            if (!empty($data['filter_date_end'])) {
                $sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
            }

            //Exclude archived orders
            $sql .= " AND o.archived = 0";

            switch ($group) {
                case 'day';
                    $sql .= " GROUP BY ot.title, DAY(o.date_added)";
                    break;
                default:
                case 'week':
                    $sql .= " GROUP BY ot.title, WEEK(o.date_added)";
                    break;
                case 'month':
                    $sql .= " GROUP BY ot.title, MONTH(o.date_added)";
                    break;
                case 'year':
                    $sql .= " GROUP BY ot.title, YEAR(o.date_added)";
                    break;
            }

            $sql .= ' ) final';
            if (!empty($data['search'])) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
                $sql .= " WHERE ( orders LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_start LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR date_end LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR total LIKE '%" . $request['search']['value'] . "%' ";
                $sql .= " OR title LIKE '%" . $request['search']['value'] . "%' )";
            }
        }

        $sql .= " ORDER BY " . $columns[$request['order'][0]['column']] . "   " . $request['order'][0]['dir'] . "  LIMIT " . $request['start'] . " ," . $request['length'] . "   ";


        $query = $this->db->query($sql);

        $totalFiltered = $query->num_rows;

        $totalData = $query->rows;

        return ['data' => $totalData, 'totalFilter' => $totalFiltered];
    }

    public function getCompactOrders($filter = false)
    {
        $query = [];

        $fields = [
            'o.order_id',
            'o.date_added',
            'o.date_modified',
            '(%s) AS order_status',
            'o.payment_method',
            'CONCAT(o.firstname, " ", o.lastname) AS full_name',
            'o.shipping_address_1',
            'o.shipping_address_2',
            'o.shipping_city',
            'o.shipping_country',
            'o.telephone',
            'op.product_id',
            'op.name',
            'op.quantity',
            'p.barcode',
            'p.sku',
            'op.price',
            'op.total',
            'o.shipping_method',
            '(%s) AS shipping_fees',
        ];

        $shippingSubQuery = sprintf(
            'SELECT `value` FROM `order_total` ot WHERE ot.order_id = o.order_id AND ot.code = "%s"',
            'shipping'
        );

        $statusSubQuery = sprintf(
            'SELECT `name` FROM `order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = %d',
            $this->config->get('config_language_id')
        );

        $fields = vsprintf(implode(',', $fields), [
            $statusSubQuery,
            $shippingSubQuery,
        ]);

        $query[] = 'SELECT %s FROM `order` AS o';
        $query[] = 'INNER JOIN order_product op';
        $query[] = 'ON o.order_id = op.order_id';
        $query[] = 'INNER JOIN product p';
        $query[] = 'ON op.product_id = p.product_id';
        $query[] = 'WHERE 1';

        if (isset($filter['filter_order_status_id']) &&
            preg_match('#^[0-9]+$#', $filter['filter_order_status_id'])
        ) {
            $query[] = sprintf('AND o.order_status_id = %d', $filter['filter_order_status_id']);
        }

        if (isset($filter['date_all'])) {
            $dates = array_map('trim', explode('-', $filter['date_all']));

            $datesValidator = function ($dates) {
                $valid = true;

                if (!strtotime($dates[0])) {
                    $valid = false;
                }

                if (!strtotime($dates[1])) {
                    $valid = false;
                }

                return $valid;
            };

            if ($datesValidator($dates) && $dates[0] === $dates[1]) {
                $query[] = sprintf(
                    'AND DATE(o.date_added) = "%s"', date("Y-m-d", strtotime($dates[0]))
                );
            } else if ($datesValidator($dates) && $dates[0] !== $dates[1]) {
                $query[] = sprintf(
                    'AND (o.date_added >= "%s" AND o.date_added <= "%s")',
                    date("Y-m-d", strtotime($dates[0])),
                    date("Y-m-d", strtotime($dates[1]))
                );
            }
        }

        if (isset($filter['customer_id']) && preg_match('#^[0-9]+$#', $filter['customer_id'])) {
            $customers = is_array($filter['customer_id']) ? $filter['customer_id'] : [$filter['customer_id']];
            $query[] = sprintf('AND o.customer_id IN (%d)', $customers);
        }

        if (isset($filter['filter']['ranges']['total'])) {
            $total = is_array($filter['filter']['ranges']['total']) ? $filter['filter']['ranges']['total'] : null;

            if (isset($total['min']) && isset($total['max']) && $total['min'] <= $total['max']) {
                $query[] = sprintf('AND (o.total BETWEEN "%s" AND "%s")', $total['min'], $total['max']);
            }
        }

        $queryString = vsprintf(implode(' ', $query), [
            $fields,
            $this->config->get('config_language_id')
        ]);

        $data = $this->db->query($queryString);

        if ($data->num_rows > 0) {
            return $data->rows;
        }

        return false;
    }
}

?>