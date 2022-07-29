<?php
class ModelAffiliateTransaction extends Model {
    public function getTransactions($data = array()) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";

        $sort_data = array(
            'amount',
            'description',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
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

    public function getHistory($data = array()) {
        $sql = "SELECT " .
            "`" . DB_PREFIX . "affiliate_transaction`.`affiliate_transaction_id` affiliate_transaction_id, " .
            "`" . DB_PREFIX . "affiliate_transaction`.`affiliate_id` affiliate_id, " .
            "`" . DB_PREFIX . "order`.`order_id` order_id, " .
            "`" . DB_PREFIX . "affiliate_transaction`.`description` description, " .
            "`" . DB_PREFIX . "affiliate_transaction`.`amount` amount, " .
            "`" . DB_PREFIX . "affiliate_transaction`.`date_added` date_added, " .
            "`" . DB_PREFIX . "order`.`firstname` firstname, " .
            "`" . DB_PREFIX . "order`.`lastname` lastname, " .
            "`" . DB_PREFIX . "order`.`email` email, " .
            "`" . DB_PREFIX . "order`.`telephone` telephone, " .
            "`" . DB_PREFIX . "order`.`comment` comment, " .
            "`" . DB_PREFIX . "order`.`total` total, " .
            "`" . DB_PREFIX . "order`.`order_status_id` order_status_id, " .
            "`" . DB_PREFIX . "order`.`commission` commission, " .
            "`" . DB_PREFIX . "order`.`date_added` order_date_added, " .
            "`" . DB_PREFIX . "order`.`date_modified` order_date_modified, " .
            "`" . DB_PREFIX . "order_status`.`name` order_status_name, " .
            "`order_product_names`.`order_products` order_products " .
            " FROM `" . DB_PREFIX . "order`" .
            " JOIN `" . DB_PREFIX . "order_status` ON `" . DB_PREFIX . "order`.`order_status_id` = `" . DB_PREFIX . "order_status`.`order_status_id`" .
            " JOIN (SELECT `" . DB_PREFIX . "order_product`.`order_id` ," .
            " group_concat(`" . DB_PREFIX . "order_product`.`quantity`, ' * ', `" . DB_PREFIX . "order_product`.`name` SEPARATOR '<br/>') AS order_products" .
            " FROM `" . DB_PREFIX . "order_product`" .
            " GROUP BY `" . DB_PREFIX . "order_product`.`order_id`) `order_product_names` ON `order_product_names`.`order_id` = `" . DB_PREFIX . "order`.`order_id`" .
            " LEFT JOIN `" . DB_PREFIX . "affiliate_transaction` ON `" . DB_PREFIX . "affiliate_transaction`.`order_id` = `" . DB_PREFIX . "order`.`order_id`";

        if (isset($data['coupon']) && $data['coupon']) {
            $sql .= " LEFT JOIN `" . DB_PREFIX . "coupon_history` ch ON `" . DB_PREFIX . "ch`.`order_id` = `" . DB_PREFIX . "order`.`order_id`";
        }

        $sql .= " WHERE `" . DB_PREFIX . "order`.`affiliate_id` = '" . (int)$this->affiliate->getId() . "'" .
            "   AND `" . DB_PREFIX . "order_status`.`language_id` = " . (int)$this->config->get('config_language_id');

        if (isset($data['coupon']) && $data['coupon']) {
            $sql .= " AND ch.coupon_id = '" . $data['coupon']."'";
        }

        $sort_data = array(
            'amount',
            'description',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY date_added";
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



    public function getTotalTransactions() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");

        return $query->row['total'];
    }

    public function getBalance($coupon_id = 0) {
        $sql = "SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "affiliate_transaction` ";

        $sql .= " WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";

        if($coupon_id){
            $sql .= " AND order_id IN (select order_id from `" . DB_PREFIX . "coupon_history` WHERE coupon_id = '".$coupon_id."')";
        }

        $sql .= " GROUP BY affiliate_id";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getTotalEarning($coupon_id = 0) {
        $sql = "SELECT SUM(commission) AS total FROM `" . DB_PREFIX . "order` JOIN `" . DB_PREFIX . "affiliate_transaction` ON `" . DB_PREFIX . "affiliate_transaction`.`order_id` = `" . DB_PREFIX . "order`.`order_id` ";

        $sql .= " WHERE `" . DB_PREFIX . "order`.`affiliate_id` = '" . (int)$this->affiliate->getId() . "'";

        if($coupon_id){
            $sql .= " AND `" . DB_PREFIX . "order`.`order_id` IN (select order_id from `" . DB_PREFIX . "coupon_history` WHERE coupon_id = '".$coupon_id."')";
        }

        $sql .= " GROUP BY `" . DB_PREFIX . "order`.`affiliate_id`";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getTotalTransactionsIncPending() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");

        return $query->row['total'];
    }

    public function getBalanceIncPending($coupon_id = 0) {
        $sql = "SELECT SUM(commission) AS total FROM `" . DB_PREFIX . "order`  ";

        $sql .= " WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";

        if($coupon_id){
            $sql .= " AND order_id IN (select order_id from `" . DB_PREFIX . "coupon_history` WHERE coupon_id = '".$coupon_id."')";
        }

        $sql .= " GROUP BY affiliate_id";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0,$isSellerAffiliate = 0) {
            if ($amount == 0) return false;

            $this->db->query("INSERT INTO " . DB_PREFIX . "affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . $amount . "', date_added = NOW(),is_seller_affiliate ='" . $isSellerAffiliate . "' ");
        }
        }
?>