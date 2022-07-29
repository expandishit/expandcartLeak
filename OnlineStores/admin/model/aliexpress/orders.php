<?php

class ModelAliexpressOrders extends Model
{

    public function getOrderTotal($order_id, $productIds) {
        $sql = "SELECT SUM(total) as total FROM ".DB_PREFIX."order_product op WHERE op.product_id IN (".$productIds.") AND op.order_id = '".(int)$order_id."' ";
        $result = $this->db->query($sql)->row;
        if($result) {
            return $result['total'];
        } else {
            return false;
        }
    }
    /**
     * [getWarehouseId get warehouse id]
     * @param  [type] $id [warehouse id]
     * @return [type]     [array]
     */
    public function getWarehouseId($id){

        $warehouse_id=$this->db->query("SELECT warehouse_id FROM " . DB_PREFIX . "warehouse WHERE user_id = '".$id."'");

        if (!empty($warehouse_id->row)) {

            $this->session->data['warehouse_id']=$warehouse_id->row['warehouse_id'];

        }else{

             return 0;
        }

    }

    public function getWarehouseOrders($filterData = array()) {
        $sql = "SELECT DISTINCT(wos.order_id), wos.cost, wos.id as warehouse_order_id, w.warehouse_id, w.warehouse_code, w.title, o.date_added, o.payment_firstname, o.payment_lastname, o.shipping_firstname, o.shipping_lastname,os.name as order_status, (SELECT SUM(total) FROM ".DB_PREFIX."warehouse_order wo WHERE wo.order_id = o.order_id AND wo.warehouse_id = w.warehouse_id) as product_total FROM ".DB_PREFIX."warehouse_order_shipping wos LEFT JOIN ".DB_PREFIX."warehouse w ON (w.warehouse_id=wos.warehouse_id) LEFT JOIN `".DB_PREFIX."order` o ON (o.order_id=wos.order_id) LEFT JOIN ".DB_PREFIX."order_status os ON (os.order_status_id=o.order_status_id) WHERE os.language_id = '".$this->config->get('config_language_id')."' AND w.warehouse_id != 0 ";

        if($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $sql .= " AND w.user_id = '".(int)$this->user->getId()."' ";
        }

        if(isset($filterData['filter_order_id']) && $filterData['filter_order_id']) {
            $sql .= " AND o.order_id = '".(int)$filterData['filter_order_id']."' ";
        }

        if(isset($filterData['filter_warehouse']) && $filterData['filter_warehouse']) {
            $sql .= " AND w.title like '".$this->db->escape($filterData['filter_warehouse'])."%' ";
        }

        if(isset($filterData['filter_warehouse_code']) && $filterData['filter_warehouse_code']) {
            $sql .= " AND w.warehouse_code like '".$this->db->escape($filterData['filter_warehouse_code'])."%' ";
        }

        if(isset($filterData['filter_order_date']) && $filterData['filter_order_date']) {
            $sql .= " AND o.date_added like '".$this->db->escape($filterData['filter_order_date'])."%' ";
        }

        // if(isset($filterData['filter_total_price']) && $filterData['filter_total_price']) {
        //  if(isset($filterData['filter_total_price_operation_type']) && $filterData['filter_total_price_operation_type']) {
        //      $sql .= " AND o.total ".$filterData['filter_total_price_operation_type']." ".(double)$filterData['filter_total_price']." ";
        //  } else {
        //      $sql .= " AND o.total = ".(double)$filterData['filter_total_price']." ";
        //  }
        // }

        if(isset($filterData['filter_order_status']) && $filterData['filter_order_status']) {
            $sql .= " AND o.order_status_id like '".(int)$filterData['filter_order_status']."' ";
        }

        if (isset($filterData['sort']) && $filterData['sort']) {
            $sql .= " ORDER BY " . $filterData['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($filterData['order']) && ($filterData['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($filterData['start']) || isset($filterData['limit'])) {
            if ($filterData['start'] < 0) {
                $filterData['start'] = 0;
            }
            if ($filterData['limit'] < 1) {
                $filterData['limit'] = 20;
            }
            $sql .= " LIMIT " . (int)$filterData['start'] . "," . (int)$filterData['limit'];
        }

        $result = $this->db->query($sql)->rows;

        if($result) {
            return $result;
        } else {
            return false;
        }

    }

    /**
    * [getTotalWarehouseOrders get warehouse orders]
    * @param  array  $data         [filter array]
    * @param  [type] $warehouse_id [warehouse id]
    * @return [type]               [array]
    */
    public function getWarehouseOrdersTotal($filterData = array()){

        $sql = "SELECT DISTINCT(wos.order_id), wos.cost, wos.id as warehouse_order_id, w.warehouse_id, w.warehouse_code, w.title, o.date_added, o.payment_firstname, o.payment_lastname, o.shipping_firstname, o.shipping_lastname,os.name as order_status, (SELECT SUM(total) FROM ".DB_PREFIX."warehouse_order wo WHERE wo.order_id = o.order_id AND wo.warehouse_id = w.warehouse_id) as product_total FROM ".DB_PREFIX."warehouse_order_shipping wos LEFT JOIN ".DB_PREFIX."warehouse w ON (w.warehouse_id=wos.warehouse_id) LEFT JOIN `".DB_PREFIX."order` o ON (o.order_id=wos.order_id) LEFT JOIN ".DB_PREFIX."order_status os ON (os.order_status_id=o.order_status_id) WHERE os.language_id = '".$this->config->get('config_language_id')."' ";

        if($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $sql .= " AND w.user_id = '".$this->user->getId()."' ";
        }

        $sort_data = array(
            'o.order_id',
            'o.total',
            'o.order_status_id',
            'o.date_added',
        );

        if (isset($filterData['sort']) && in_array($filterData['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $filterData['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($filterData['order']) && ($filterData['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        $result = $this->db->query($sql)->rows;

        if($result) {
            return count($result);
        } else {
            return false;
        }

    }

    /**
     * [getOrderStatus get order status]
     * @return [type] [array]
     */
    public function getOrderStatus() {
        $query = $this->db->query("SELECT order_status_id,name FROM `" . DB_PREFIX . "order_status` WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    /**
     * [getTotalOrder get total order of a warehouse]
     * @param  [type] $warehouse_id [warehouse id]
     * @return [type]               [integer]
     */
    public function getTotalOrder($warehouse_id) {

        $query = $this->db->query("SELECT count(*) AS total FROM `" . DB_PREFIX . "warehouse_order` WHERE warehouse_id = '" . $warehouse_id . "'");

        return $query->row['total'];
    }

    /**
     * [getOrder get order details]
     * @param  [type] $order_id [order id]
     * @return [type]           [array]
     */
    public function getOrder($order_id) {
        $order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "' ");

        if ($order_query->num_rows) {
            $reward = 0;

            $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

            foreach ($order_product_query->rows as $product) {
                $reward += $product['reward'];
            }

            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

            if ($country_query->num_rows) {
                $payment_iso_code_2 = $country_query->row['iso_code_2'];
                $payment_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $payment_iso_code_2 = '';
                $payment_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $payment_zone_code = $zone_query->row['code'];
            } else {
                $payment_zone_code = '';
            }

            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

            if ($country_query->num_rows) {
                $shipping_iso_code_2 = $country_query->row['iso_code_2'];
                $shipping_iso_code_3 = $country_query->row['iso_code_3'];
            } else {
                $shipping_iso_code_2 = '';
                $shipping_iso_code_3 = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

            if ($zone_query->num_rows) {
                $shipping_zone_code = $zone_query->row['code'];
            } else {
                $shipping_zone_code = '';
            }

            if ($order_query->row['affiliate_id']) {
                $affiliate_id = $order_query->row['affiliate_id'];
            } else {
                $affiliate_id = 0;
            }

            // $this->load->model('marketing/affiliate');

            // $affiliate_info = $this->model_marketing_affiliate->getAffiliate($affiliate_id);

            // if ($affiliate_info) {
            //     $affiliate_firstname = $affiliate_info['firstname'];
            //     $affiliate_lastname = $affiliate_info['lastname'];
            // } else {
            //     $affiliate_firstname = '';
            //     $affiliate_lastname = '';
            // }
            $affiliate_firstname = '';
            $affiliate_lastname = '';

            $this->load->model('localisation/language');

            $language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

            if ($language_info) {
                $language_code = $language_info['code'];
                $language_directory = $language_info['directory'];
            } else {
                $language_code = '';
                $language_directory = '';
            }

            return array(
                'order_id'                => $order_query->row['order_id'],
                'invoice_no'              => $order_query->row['invoice_no'],
                'invoice_prefix'          => $order_query->row['invoice_prefix'],
                'store_id'                => $order_query->row['store_id'],
                'store_name'              => $order_query->row['store_name'],
                'store_url'               => $order_query->row['store_url'],
                'customer_id'             => $order_query->row['customer_id'],
                'customer'                => $order_query->row['customer'],
                'customer_group_id'       => $order_query->row['customer_group_id'],
                'firstname'               => $order_query->row['firstname'],
                'lastname'                => $order_query->row['lastname'],
                'email'                   => $order_query->row['email'],
                'telephone'               => $order_query->row['telephone'],
                'fax'                     => $order_query->row['fax'],
                // 'custom_field'            => unserialize($order_query->row['custom_field']),
                'payment_firstname'       => $order_query->row['payment_firstname'],
                'payment_lastname'        => $order_query->row['payment_lastname'],
                'payment_company'         => $order_query->row['payment_company'],
                'payment_address_1'       => $order_query->row['payment_address_1'],
                'payment_address_2'       => $order_query->row['payment_address_2'],
                'payment_postcode'        => $order_query->row['payment_postcode'],
                'payment_city'            => $order_query->row['payment_city'],
                'payment_zone_id'         => $order_query->row['payment_zone_id'],
                'payment_zone'            => $order_query->row['payment_zone'],
                'payment_zone_code'       => $payment_zone_code,
                'payment_country_id'      => $order_query->row['payment_country_id'],
                'payment_country'         => $order_query->row['payment_country'],
                'payment_iso_code_2'      => $payment_iso_code_2,
                'payment_iso_code_3'      => $payment_iso_code_3,
                'payment_address_format'  => $order_query->row['payment_address_format'],
                // 'payment_custom_field'    => unserialize($order_query->row['payment_custom_field']),
                'payment_method'          => $order_query->row['payment_method'],
                'payment_code'            => $order_query->row['payment_code'],
                'shipping_firstname'      => $order_query->row['shipping_firstname'],
                'shipping_lastname'       => $order_query->row['shipping_lastname'],
                'shipping_company'        => $order_query->row['shipping_company'],
                'shipping_address_1'      => $order_query->row['shipping_address_1'],
                'shipping_address_2'      => $order_query->row['shipping_address_2'],
                'shipping_postcode'       => $order_query->row['shipping_postcode'],
                'shipping_city'           => $order_query->row['shipping_city'],
                'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
                'shipping_zone'           => $order_query->row['shipping_zone'],
                'shipping_zone_code'      => $shipping_zone_code,
                'shipping_country_id'     => $order_query->row['shipping_country_id'],
                'shipping_country'        => $order_query->row['shipping_country'],
                'shipping_iso_code_2'     => $shipping_iso_code_2,
                'shipping_iso_code_3'     => $shipping_iso_code_3,
                'shipping_address_format' => $order_query->row['shipping_address_format'],
                // 'shipping_custom_field'   => unserialize($order_query->row['shipping_custom_field']),
                'shipping_method'         => $order_query->row['shipping_method'],
                'shipping_code'           => $order_query->row['shipping_code'],
                'comment'                 => $order_query->row['comment'],
                'total'                   => $order_query->row['total'],
                'reward'                  => $reward,
                'order_status_id'         => $order_query->row['order_status_id'],
                'affiliate_id'            => $order_query->row['affiliate_id'],
                'affiliate_firstname'     => $affiliate_firstname,
                'affiliate_lastname'      => $affiliate_lastname,
                'commission'              => $order_query->row['commission'],
                'language_id'             => $order_query->row['language_id'],
                'language_code'           => $language_code,
                'language_directory'      => $language_directory,
                'currency_id'             => $order_query->row['currency_id'],
                'currency_code'           => $order_query->row['currency_code'],
                'currency_value'          => $order_query->row['currency_value'],
                'ip'                      => $order_query->row['ip'],
                'forwarded_ip'            => $order_query->row['forwarded_ip'],
                'user_agent'              => $order_query->row['user_agent'],
                'accept_language'         => $order_query->row['accept_language'],
                'date_added'              => $order_query->row['date_added'],
                'date_modified'           => $order_query->row['date_modified']
            );
        } else {
            return;
        }
    }

    /**
     * [getOrderProducts get order products]
     * @param  [type] $order_id [order id]
     * @return [type]           [array]
     */
    public function getOrderProducts($order_id, $warehouse_id) {
        $result = array();
        $sql = "SELECT wo.product_id, wo.total, p.product_id, p.model, pd.name, op.quantity, op.price, op.order_product_id FROM ".DB_PREFIX."warehouse_order wo LEFT JOIN ".DB_PREFIX."product p ON (p.product_id=wo.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id=pd.product_id) LEFT JOIN ".DB_PREFIX."order_product op ON (op.product_id=p.product_id && op.order_id=wo.order_id) WHERE wo.order_id = '".(int)$order_id."' AND wo.warehouse_id = '".(int)$warehouse_id."' ";

        $result['products'] = $this->db->query($sql)->rows;
        $sql = "SELECT * FROM ".DB_PREFIX."warehouse_order_shipping wos WHERE wos.order_id = '".(int)$order_id."' AND wos.warehouse_id = '".(int)$warehouse_id."' ";
        $result['shipping'] = $this->db->query($sql)->row;

        // if($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
        //  $sql .= " AND w.user_id = '".$this->user->getId()."' ";
        // }

        // if($resultSub) {
        //  $result = $this->db->query("SELECT p.product_id, p.model, pd.name,op.* FROM ".DB_PREFIX."product p LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id=pd.product_id) LEFT JOIN ".DB_PREFIX."order_product op ON (op.product_id=p.product_id) WHERE p.product_id IN (".implode(unserialize($resultSub['product_id']),',').") AND op.order_id = '".(int)$order_id."' ")->rows;
        // }

        if($result) {
            return $result;
        } else {
            return false;
        }

    }

    /**
     * [getOrderOptions get order options detail]
     * @param  [type] $order_id         [order id]
     * @param  [type] $order_product_id [order_product id]
     * @return [type]                   [array]
     */
    public function getOrderOptions($order_id, $order_product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

        return $query->rows;
    }


    /**
     * [getPosition get warehouse position]
     * @param  [type] $user_id [warehouse manager id]
     * @return [type]          [array]
     */
    public function getPosition($user_id){

       $query=$this->db->query("SELECT * FROM " . DB_PREFIX . "warehouse WHERE user_id = '".$this->db->escape($user_id)."'");

        return $query->row;

    }

    /**
     * [getOrderVouchers get order vouchers]
     * @param  [type] $order_id [order id]
     * @return [type]           [array]
     */
    public function getOrderVouchers($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

        return $query->rows;
    }

    /**
     * [getOrderTotals get total order]
     * @param  [type] $order_id [order id]
     * @return [type]           [array]
     */
    public function getOrderTotals($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

        return $query->rows;
    }

    /**
     * [getwarehouseorderById get warehouse by order ]
     * @param  [type] $order_id     [order id]
     * @param  [type] $warehouse_id [warehouse id]
     * @return [type]               [array]
     */
    public function getwarehouseorderById($order_id,$warehouse_id){

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "warehouse_order WHERE order_id = '" . (int)$order_id . "' AND warehouse_id='".$warehouse_id."'");

        return $query->row;

    }


}
