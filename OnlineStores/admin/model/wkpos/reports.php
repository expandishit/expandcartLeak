<?php
class ModelWkposReports extends Model {
    public function getProducts($data = array()) {

      $selections = [
          'DISTINCT wp.product_id',
          'p.quantity',
          '(SELECT sum(wp.quantity) FROM wkpos_products wp where p.product_id = wp.product_id) as pos_quantity',
          'pd.name',
          'p.model',
          '(SELECT SUM(quantity) AS sold_total FROM ' . DB_PREFIX . 'order_product op LEFT JOIN ' . DB_PREFIX . 'wkpos_user_orders wuo ON (op.order_id = wuo.order_id) WHERE op.product_id = wp.product_id) AS sold',
          '(
            SELECT SUM(IFNULL(op.total, 0) + (IFNULL(op.total, 0) * IFNULL(op.tax, 0) / 100) ) FROM `' . DB_PREFIX . 'order_product` op 
            JOIN `' . DB_PREFIX . 'product` p ON (op.product_id = p.product_id) 
            where op.product_id = wp.product_id
            ) AS total',
          '(
                SELECT SUM((p.cost_price * op.quantity)) FROM `' . DB_PREFIX . 'order_product` op 
                JOIN `' . DB_PREFIX . 'product` p ON (op.product_id = p.product_id) 
                where op.product_id = wp.product_id
             ) AS cost',
          '(
                SELECT (SUM(IFNULL(op.total, 0)) - (SUM(IFNULL(op.quantity, 0)) * p.cost_price) ) FROM `' . DB_PREFIX . 'order_product` op 
                JOIN `' . DB_PREFIX . 'product` p ON (op.product_id = p.product_id) 
                where op.product_id = wp.product_id
             ) AS profit'

      ];
      if (!empty($data['outlet_id'])) {
          array_push($selections, 'wo.name AS outlet');
      }
      if (!empty($data['supplier_id'])) {
            array_push($selections, 'CONCAT(ws.firstname,\' \',ws.lastname) AS supplier');
      }
      $selections = implode(',', $selections);

      $sub_query = "";
      $sql = "SELECT 
                     ".$selections."
                     FROM " . DB_PREFIX . "wkpos_products wp 
                     LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = wp.product_id) 
                     LEFT JOIN " . DB_PREFIX . "product_description pd ON (wp.product_id = pd.product_id)";

      if (!empty($data['outlet_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wp.outlet_id = wo.outlet_id) ";
      }
      if (!empty($data['supplier_id'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "wkpos_supplier_product wsp ON (wp.product_id = wsp.product_id) 
                      LEFT JOIN " . DB_PREFIX . "wkpos_supplier ws ON (wsp.supplier_id = ws.supplier_id)  ";
      }

      $sql .= " WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " AND wp.status = '1'";

      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id = " . (int)$data['outlet_id'] . "";
      }
      if (!empty($data['supplier_id'])) {
        $sql .= " AND wsp.supplier_id = " . (int)$data['supplier_id'] . "";
      }
      if (!empty($data['product_id'])) {
            $sql .= " AND wp.product_id = " . (int)$data['product_id'] . "";
      }

      //$sql .= " GROUP BY wp.product_id";

      if (!empty($data['limit']) && (int)$data['limit'] > 1) {
        $limit = (int)$data['limit'];
      } else {
        $limit = 20;
      }
      if (isset($data['start']) && (int)$data['start'] > 0) {
        $start = $data['start'];
      } else {
        $start = 0;
      }

      $sql .= " ORDER BY pd.name ASC LIMIT " . (int)$start . ", " . (int)$limit . "";

      return $this->db->query($sql)->rows;
    }

    public function getTotalProducts($data = array()) {
      $sub_query = "(SELECT SUM(quantity) AS sold_total FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "wkpos_user_orders wuo ON (op.order_id = wuo.order_id) WHERE op.product_id = wp.product_id) AS sold";
      $sql = "SELECT DISTINCT wp.product_id, wp.quantity,".$sub_query.", pd.name, p.model, wo.name AS outlet, CONCAT(ws.firstname,' ',ws.lastname) AS supplier FROM " . DB_PREFIX . "wkpos_products wp LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wp.outlet_id = wo.outlet_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier_product wsp ON (wp.product_id = wsp.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier ws ON (wsp.supplier_id = ws.supplier_id) WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " AND wp.status = '1'";
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id = " . (int)$data['outlet_id'] . "";
      }
      if (!empty($data['supplier_id'])) {
        $sql .= " AND wsp.supplier_id = " . (int)$data['supplier_id'] . "";
      }
      return count($this->db->query($sql)->rows);
    }

    public function getSales($data = array(), $is_user = false) {

        if($is_user && !$data['user_id'])
            return [];

        $sql = "SELECT * FROM (
                                    SELECT  MIN(tmp.date_added) AS date_start, 
                                            MAX(tmp.date_added) AS date_end, 
                                            COUNT(tmp.order_id) AS `orders`, 
                                            SUM(tmp.total) AS total, 
                                            SUM(tmp.cost) AS cost,
                                            tmp.expense AS expenses
                                            FROM (
                                                    SELECT        
                                                          o.order_id, 
                                                          o.total, 
                                                          o.date_added, 
                                                            (
                                                                SELECT SUM(p.cost_price * op.quantity) FROM `" . DB_PREFIX . "order_product` op 
                                                                JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) 
                                                                WHERE op.order_id = o.order_id 
                                                                GROUP BY op.order_id
                                                            ) AS cost,
                                                            ( SELECT SUM( we.amount) FROM `wkpos_expenses` we  JOIN `wkpos_outlet` wo ON (we.outlet_id = wo.outlet_id)
                                                              WHERE WEEK(we.date_added) = WEEK(o.date_added)
                                                              GROUP BY WEEK(we.date_added) 
                                                             ) as expense
                                                            FROM `" . DB_PREFIX . "wkpos_user_orders` wuo 
                                                            LEFT JOIN `" . DB_PREFIX . "order` o ON (wuo.order_id = o.order_id) 
                                                            LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id) 
                                                            LEFT JOIN `" . DB_PREFIX . POS_USERS_TABLE."` wu ON (wuo.user_id = wu.user_id) 
                                                            LEFT JOIN `" . DB_PREFIX . "wkpos_outlet` wo ON(wu.outlet_id = wo.outlet_id) 
                                                            WHERE os.order_status_id > 0 
                                                            AND os.language_id = " . (int)$this->config->get('config_language_id') . " ";

        if (!empty($data['user_id'])) {
            $sql .= " AND wuo.user_id=".(int)$data['user_id'];
        }
        if (!empty($data['outlet_id'])) {
            $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
        }
        if (!empty($data['payment'])) {
            $sql .= " AND o.payment_code='".$data['payment']."'";
        }
        if (!empty($data['customer_id'])) {
            $sql .= " AND o.customer_id=".(int)$data['customer_id'];
        }
        if (!empty($data['order_mode']) && $data['order_mode'] == 'offline') {
            $sql .= " AND wuo.txn_id !='0'";
        }
        if (!empty($data['order_mode']) && $data['order_mode'] == 'online') {
            $sql .= " AND wuo.txn_id ='0'";
        }
        if (!empty($data['date_from']) && !empty($data['date_to'])) {
            $sql .= " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
        }

        $sql .= " GROUP BY o.order_id) tmp";

        if (!empty($data['group'])) {
            $group = $data['group'];
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

        return $this->db->query($sql)->rows;
    }

    public function getOrders($data = array(), $is_user = false) {

      if($is_user && !$data['user_id'])
          return [];

      $sql = "SELECT DISTINCT o.order_id, 
                              wuo.*, 
                              o.total, 
                              wo.name AS outlet, 
                              o.payment_method, 
                              o.currency_code, 
                              DATE(o.date_added) AS date_added, 
                              os.name AS order_status, 
                              wo.name AS outlet, 
                              CONCAT(o.firstname, ' ', o.lastname) AS customer, 
                              o.email, 
                                (
                                    SELECT SUM(p.cost_price * op.quantity) FROM `" . DB_PREFIX . "order_product` op 
                                    JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) 
                                    WHERE op.order_id = o.order_id 
                                    GROUP BY op.order_id
                                ) AS cost
                                FROM `" . DB_PREFIX . "wkpos_user_orders` wuo 
                                LEFT JOIN `" . DB_PREFIX . "order` o ON (wuo.order_id = o.order_id) 
                                LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id) 
                                LEFT JOIN `" . DB_PREFIX . POS_USERS_TABLE."` wu ON (wuo.user_id = wu.user_id) 
                                LEFT JOIN `" . DB_PREFIX . "wkpos_outlet` wo ON(wu.outlet_id = wo.outlet_id) 
                                WHERE os.order_status_id > 0 
                                AND os.language_id = " . (int)$this->config->get('config_language_id') . " ";

      if (!empty($data['user_id'])) {
        $sql .= " AND wuo.user_id=".(int)$data['user_id'];
      }
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
      }
      if (!empty($data['payment'])) {
        $sql .= " AND o.payment_code='".$data['payment']."'";
      }
      if (!empty($data['customer_id'])) {
        $sql .= " AND o.customer_id=".(int)$data['customer_id'];
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'offline') {
        $sql .= " AND wuo.txn_id !='0'";
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'online') {
        $sql .= " AND wuo.txn_id ='0'";
      }
      if (!empty($data['date_from']) && !empty($data['date_to'])) {
         $sql .= " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
      }
      if (!empty($data['limit']) && (int)$data['limit'] > 1) {
        $limit = (int)$data['limit'];
      } else {
        $limit = 20;
      }
      if (isset($data['start']) && (int)$data['start'] > 0) {
        $start = $data['start'];
      } else {
        $start = 0;
      }
      $sql .= " ORDER BY o.order_id DESC LIMIT " . (int)$start . ", " . (int)$limit . "";

      return $this->db->query($sql)->rows;
    }

    public function getTotalOrders($data = array(), $is_user = false) {

        if($is_user && !$data['user_id'])
            return 0;

      $sql = "SELECT DISTINCT o.order_id, wuo.*, o.total, wo.name AS outlet, o.payment_method, o.currency_code, DATE(o.date_added) AS date_added, os.name AS order_status, wo.name AS outlet, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email FROM `" . DB_PREFIX . "wkpos_user_orders` wuo LEFT JOIN `" . DB_PREFIX . "order` o ON (wuo.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id) LEFT JOIN `" . DB_PREFIX . "wkpos_user` wu ON (wuo.user_id = wu.user_id) LEFT JOIN `" . DB_PREFIX . "wkpos_outlet` wo ON(wu.outlet_id = wo.outlet_id) WHERE os.order_status_id > 0 AND os.language_id = " . (int)$this->config->get('config_language_id') . " ";
      if (!empty($data['user_id'])) {
        $sql .= " AND wu.user_id=".(int)$data['user_id'];
      }
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
      }
      if (!empty($data['payment'])) {
        $sql .= " AND o.payment_code='".$data['payment']."'";
      }
      if (!empty($data['customer_id'])) {
        $sql .= " AND o.customer_id=".(int)$data['customer_id'];
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'offline') {
        $sql .= " AND wuo.txn_id !='0'";
      }
      if (!empty($data['date_from']) && !empty($data['date_to'])) {
         $sql .= " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
      }

      return count($this->db->query($sql)->rows);
    }

    public function getUsers($data = array()) {

        $sql_dates = '';
        if (!empty($data['date_from']) && !empty($data['date_to'])) {
            $sql_dates = " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
        }

        $sql = "SELECT wu.user_id, wu.username, CONCAT(wu.firstname,' ', wu.lastname) as name, wu.email, wo.name AS outlet,
                        (
                            SELECT SUM(o.total) FROM `" . DB_PREFIX . "order` o 
                            JOIN `" . DB_PREFIX . "wkpos_user_orders` wuo ON (wuo.order_id = o.order_id) 
                            WHERE wuo.user_id = wu.user_id AND payment_code='cash'".$sql_dates."
                        ) AS total_cash,
                        (
                            SELECT SUM(o.total) FROM `" . DB_PREFIX . "order` o 
                            JOIN `" . DB_PREFIX . "wkpos_user_orders` wuo ON (wuo.order_id = o.order_id) 
                            WHERE wuo.user_id = wu.user_id AND payment_code='card'".$sql_dates."
                        ) AS total_card,
                        (
                            SELECT SUM(we.amount) FROM `" . DB_PREFIX . "wkpos_expenses` we 
                            WHERE we.user_id = wu.user_id ".$sql_dates."
                        ) AS expenses
                        
                       FROM `" . DB_PREFIX . POS_USERS_TABLE . "` wu
                       LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wu.outlet_id = wo.outlet_id) 
                       WHERE 1=1 ";
        if (!empty($data['user_id'])) {
            $sql .= " AND wu.user_id=".(int)$data['user_id'];
        }
        if (!empty($data['outlet_id'])) {
            $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
        }

        if (!empty($data['limit']) && (int)$data['limit'] > 1) {
            $limit = (int)$data['limit'];
        } else {
            $limit = 20;
        }
        if (isset($data['start']) && (int)$data['start'] > 0) {
            $start = $data['start'];
        } else {
            $start = 0;
        }
        $sql .= " ORDER BY wu.username DESC LIMIT " . (int)$start . ", " . (int)$limit . "";

        return $this->db->query($sql)->rows;
    }

    public function getExpenses($data = array()) {

        $sql = "SELECT * FROM " . DB_PREFIX . "wkpos_expenses " . " we WHERE 1=1 ";
        if (!empty($data['user_id'])) {
            $sql .= " AND we.user_id=".(int)$data['user_id'];
        }
        if (!empty($data['outlet_id'])) {
            $sql .= " AND we.outlet_id=".(int)$data['outlet_id'];
        }

        if (!empty($data['date_from']) && !empty($data['date_to'])) {
            $sql .= " AND DATE(we.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
        }

        if (!empty($data['limit']) && (int)$data['limit'] > 1) {
            $limit = (int)$data['limit'];
        } else {
            $limit = 20;
        }
        if (isset($data['start']) && (int)$data['start'] > 0) {
            $start = $data['start'];
        } else {
            $start = 0;
        }
        $sql .= " ORDER BY we.wkpos_expense_id ASC LIMIT " . (int)$start . ", " . (int)$limit . "";

        return $this->db->query($sql)->rows;
    }

    public function getTotalExpenses($data = array()) {

        $sql = "SELECT * FROM " . DB_PREFIX . "wkpos_expenses " . " we WHERE 1=1 ";

        if (!empty($data['user_id'])) {
            $sql .= " AND we.user_id=".(int)$data['user_id'];
        }
        if (!empty($data['outlet_id'])) {
            $sql .= " AND we.outlet_id=".(int)$data['outlet_id'];
        }

        return count($this->db->query($sql)->rows);
    }
    public function getTotalUsers($data = array()) {
        $sql = "SELECT wu.*, wo.name AS outlet
                       FROM `" . DB_PREFIX . POS_USERS_TABLE . "` wu
                       LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wu.outlet_id = wo.outlet_id) 
                       WHERE 1=1 ";
        if (!empty($data['user_id'])) {
            $sql .= " AND wu.user_id=".(int)$data['user_id'];
        }
        if (!empty($data['outlet_id'])) {
            $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
        }


        return count($this->db->query($sql)->rows);
    }

    public function getOrderCustomers($data = array()) {
      $sql = "SELECT DISTINCT c.customer_id, CONCAT(c.firstname,' ', c.lastname) AS name, c.email FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "order o ON (o.customer_id = c.customer_id) ";
      if (!empty($data['filter_customer'])) {
        $sql .= "WHERE CONCAT(c.firstname,' ', c.lastname) LIKE '%". $this->db->escape($data['filter_customer']) ."%' OR c.email LIKE '%". $this->db->escape($data['filter_customer']) ."%'";
      }
      return $this->db->query($sql)->rows;
    }
}
