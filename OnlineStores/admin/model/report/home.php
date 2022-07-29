<?php
use ExpandCart\Foundation\Analytics\Live;

class ModelReportHome extends Model
{
    public function getSalesRevenueTotal($range)
    {
        $mysqlConditions = "";
        if($this->config->get("config_status_based_revenue")){
            $mysqlConditions = " AND `order_status_id` = ". $this->config->get('config_complete_status_id');
        }

        $query = "SELECT SQL_BIG_RESULT IFNULL(SUM(total), 0) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND ";
        switch ($range) {
            case 'today':
                $query .= "date_added LIKE '" . date("Y-m-d %", strtotime("today")) . "'";
                //$query .= "DATE(date_added) = DATE(NOW())";
                break;
            case 'yesterday':
                $query .= "date_added LIKE '" . date("Y-m-d %", strtotime("yesterday")) . "'";
                //$query .= "DATE(date_added) = DATE(SUBDATE(NOW(), 1))";
                break;
            case '7days':
                $query .= "(date_added BETWEEN CURRENT_TIMESTAMP - INTERVAL '7' DAY AND CURRENT_TIMESTAMP)";
                break;
            case '30days':
                $query .= "(date_added BETWEEN CURRENT_TIMESTAMP - INTERVAL '30' DAY AND CURRENT_TIMESTAMP)";
                break;
            case 'thismonth':
                $query .= "date_added LIKE '" . date("Y-m-%", strtotime("this month")) . "'";
                //$query .= "MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())";
                break;
            case 'lastmonth':
                $query .= "date_added LIKE '" . date("Y-m-%", strtotime("first day of last month")) . "'";
                //$query .= "MONTH(date_added) = MONTH(NOW() - INTERVAL 1 MONTH) AND YEAR(date_added) = YEAR(NOW() - INTERVAL 1 MONTH)";
                break;
        }

        $query .= " ".$mysqlConditions." ";

        $result = $this->db->query($query);
        return $result->row['total'];
    }

    public function getOrdersTotal($range)
    {
        $mysqlConditions = "";
        if($this->config->get("config_status_based_revenue")){
            $mysqlConditions = " AND `order_status_id` = ". $this->config->get('config_complete_status_id');
        }

        $query = "SELECT SQL_BIG_RESULT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND ";
        switch ($range) {
            case 'today':
                $query .= "date_added LIKE '" . date("Y-m-d %", strtotime("today")) . "'";
                //$query .= "DATE(date_added) = DATE(NOW())";
                break;
            case 'yesterday':
                $query .= "date_added LIKE '" . date("Y-m-d %", strtotime("yesterday")) . "'";
                //$query .= "DATE(date_added) = DATE(SUBDATE(NOW(), 1))";
                break;
            case '7days':
                $query .= "(date_added BETWEEN CURRENT_TIMESTAMP - INTERVAL '7' DAY AND CURRENT_TIMESTAMP)";
                break;
            case '30days':
                $query .= "(date_added BETWEEN CURRENT_TIMESTAMP - INTERVAL '30' DAY AND CURRENT_TIMESTAMP)";
                break;
            case 'thismonth':
                $query .= "date_added LIKE '" . date("Y-m-%", strtotime("this month")) . "'";
                //$query .= "MONTH(date_added) = MONTH(NOW()) AND YEAR(date_added) = YEAR(NOW())";
                break;
            case 'lastmonth':
                $query .= "date_added LIKE '" . date("Y-m-%", strtotime("last month")) . "'";
                //$query .= "MONTH(date_added) = MONTH(NOW() - INTERVAL 1 MONTH) AND YEAR(date_added) = YEAR(NOW() - INTERVAL 1 MONTH)";
                break;
        }
        $query .= " ".$mysqlConditions." ";
        $result = $this->db->query($query);
        return $result->row['total'];
    }

    public function getSalesRevenue($startdate, $enddate, $range) {
        $mysqlConditions = "";
        if ($this->config->get('config_cancelled_order_status_id')) {
            $mysqlConditions = " AND `order_status_id` != ". $this->config->get('config_cancelled_order_status_id');
        }

        if($this->config->get("config_status_based_revenue")){
            $mysqlConditions .= " AND `order_status_id` = ". $this->config->get('config_complete_status_id');
        }
        switch($range) {
            case "hours":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-%d') AS date, HOUR(date_added) AS hour, IFNULL(SUM(total), 0) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY HOUR(date_added) ORDER BY date_added ASC";
                break;
            case "days":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-%d') AS date, IFNULL(SUM(total), 0) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY DATE(date_added) ORDER BY date_added ASC";
                break;
            case "months":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-01') AS date, IFNULL(SUM(total), 0) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY MONTH(date_added) ORDER BY date_added ASC";
                break;
            case "years":
                $query = "SELECT SQL_BIG_RESULT YEAR(date_added) AS year, IFNULL(SUM(total), 0) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY YEAR(date_added) ORDER BY date_added ASC";
                break;
        }
        $result = $this->db->query($query);
        return $result->rows;
    }

    public function getOrders($startdate, $enddate, $range) {

        $mysqlConditions = "";
        if($this->config->get("config_status_based_revenue")){
            $mysqlConditions = " AND `order_status_id` = ". $this->config->get('config_complete_status_id');
        }
        $mysqlConditions .= ' AND archived = 0' ;
        
        switch($range) {
            case "hours":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-%d') AS date, HOUR(date_added) AS hour, COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY HOUR(date_added) ORDER BY date_added ASC";
                break;
            case "days":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-%d') AS date, COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY DATE(date_added) ORDER BY date_added ASC";
                break;
            case "months":
                $query = "SELECT SQL_BIG_RESULT DATE_FORMAT(date_added, '%Y-%m-01') AS date, COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate')  ".$mysqlConditions."  GROUP BY MONTH(date_added) ORDER BY date_added ASC";
                break;
            case "years":
                $query = "SELECT SQL_BIG_RESULT YEAR(date_added) AS year, COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." GROUP BY YEAR(date_added) ORDER BY date_added ASC";
                break;
        }
        $result = $this->db->query($query);
        return $result->rows;
    }

    public function getBestSellerProducts($limit) {
        $customer_group_id = $this->config->get('config_customer_group_id');

        $product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);

        if (!$product_data) {
            $product_data = array();

            $query = $this->db->query("SELECT SQL_BIG_RESULT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND o.archived = 0 AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);
            $returned_product_ids = array_column($query->rows, 'product_id');
            $products = $this->getProductsByIds($returned_product_ids);
            foreach ($products as $product) {
                $product_data[$product['product_id']] = $product;
                $key = array_search($product['product_id'], array_column($query->rows, 'product_id'));
                $product_data[$product['product_id']]['salescount'] = $query->rows[$key]['total'];
            }

            $this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function recentCustomersCount($startdate, $enddate) {
        $query = $this->db->query("SELECT SQL_BIG_RESULT COUNT(*) AS total FROM " . DB_PREFIX . "customer c WHERE DATE(c.date_added) BETWEEN '$startdate' AND '$enddate'");
        return $query->row['total'];
    }

    public function unhandledOrdersCount() {
        $query = $this->db->query('
        select SQL_BIG_RESULT count(*) AS total from (
            SELECT COUNT(*) FROM `order` LEFT JOIN order_history ON `order`.order_id = order_history.order_id WHERE `order`.order_status_id != 0 AND `order`.archived = 0 GROUP by `order_history`.`order_id` HAVING count(`order_history`.`order_history_id`) = 1
        ) as t
        ');
        return $query->row['total'];
    }

    public function averageOrderValue($startdate, $enddate) {
        $mysqlConditions = "";
        if($this->config->get("config_status_based_revenue")){
            $mysqlConditions = " AND `order_status_id` = ". $this->config->get('config_complete_status_id');
        }
        $query = $this->db->query("SELECT SQL_BIG_RESULT IFNULL(AVG(total), 0) AS average FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND archived = 0 AND (DATE(date_added) BETWEEN '$startdate' AND '$enddate') ".$mysqlConditions." ");
        return $query->row['average'];
    }

    public function returningCustomersCount($startdate, $enddate) {
        $query = $this->db->query("SELECT SQL_BIG_RESULT COUNT(*) as total FROM (SELECT COUNT(*) FROM `order` WHERE order_status_id > '0' AND archived = 0 AND DATE(date_added) BETWEEN '$startdate' AND '$enddate' GROUP BY customer_id having count(*) > 1) t1");
        return $query->row['total'];
    }

    public function returnsCount($startdate, $enddate) {
        $query = $this->db->query("SELECT SQL_BIG_RESULT COUNT(*) AS total FROM `" . DB_PREFIX . "return` WHERE DATE(date_added) BETWEEN '$startdate' AND '$enddate'");
        return $query->row['total'];
    }

    public function getProductsByIds($Ids = array()) {
        if(count($Ids) == 0 || !is_array($Ids)) {
            return false;
        }

        $customer_group_id = $this->config->get('config_customer_group_id');

        $product_ids = implode(",", $Ids);
        if(empty($product_ids)) return false;

        $query = $this->db->query("SELECT SQL_BIG_RESULT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, m.image AS manufacturerimg, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = NULL OR pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = NULL OR pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = NULL OR ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = NULL OR ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT date_end FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = NULL OR ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = NULL OR ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special_enddate, (SELECT SUM(points) FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id IN (" . $product_ids . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");


        if ($query->num_rows) {
            $products = array();
            foreach ($query->rows as $product) {
                $products[$product['product_id']] =  array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'meta_description' => $product['meta_description'],
                    'meta_keyword' => $product['meta_keyword'],
                    'tag' => $product['tag'],
                    'model' => $product['model'],
                    'sku' => $product['sku'],
                    'upc' => $product['upc'],
                    'ean' => $product['ean'],
                    'jan' => $product['jan'],
                    'isbn' => $product['isbn'],
                    'mpn' => $product['mpn'],
                    'location' => $product['location'],
                    'quantity' => $product['quantity'],
                    'stock_status' => $product['stock_status'],
                    'stock_status_id' => $product['stock_status_id'],
                    'image' => $product['image'],
                    'manufacturer_id' => $product['manufacturer_id'],
                    'manufacturer' => $product['manufacturer'],
                    'price' => ($product['discount'] ? $product['discount'] : $product['price']),
                    'special' => $product['special'],
                    'special_enddate' => $product['special_enddate'],
                    'reward' => $product['reward'],
                    'points' => $product['points'],
                    'tax_class_id' => $product['tax_class_id'],
                    'date_available' => $product['date_available'],
                    'weight' => $product['weight'],
                    'weight_class_id' => $product['weight_class_id'],
                    'length' => $product['length'],
                    'width' => $product['width'],
                    'height' => $product['height'],
                    'length_class_id' => $product['length_class_id'],
                    'subtract' => $product['subtract'],
                    'rating' => round($product['rating']),
                    'reviews' => $product['reviews'] ? $product['reviews'] : 0,
                    'minimum' => $product['minimum'],
                    'sort_order' => $product['sort_order'],
                    'status' => $product['status'],
                    'date_added' => $product['date_added'],
                    'date_modified' => $product['date_modified'],
                    'viewed' => $product['viewed'],
                    'manufacturerimg' => $product['manufacturerimg']
                );
            }

            $products = array_replace(array_flip($Ids), $products);

            return $products;
        } else {
            return false;
        }
    }

    public function liveVisits() {
        $live = (new Live())->setMethod('getLastVisitsDetails');
        $result = $live->fetch();
        $onlineData = [];
        $desktop = $mobile = 0;
        foreach ($result as $key => $value) {
            if ($value['latitude'] && $value['longitude'] && $value['location']) {

                if ((time() - $value['lastActionTimestamp']) <= (300 * 1) && !isset($onlineData[$value['visitorId']])) {
                    $onlineData[$value['visitorId']] = [
                        'latLng' => [$value['latitude'], $value['longitude']],
                        'name' => $value['location']
                    ];

                    if ($value['deviceType'] == 'Desktop') {
                        $desktop++;
                    } else {
                        $mobile++;
                    }
                }
            }
        }

        $data = [
            'count' => count($onlineData),
            'desktop' => $desktop,
            'mobile' => $mobile,
        ];

        return $data;
    }

}
