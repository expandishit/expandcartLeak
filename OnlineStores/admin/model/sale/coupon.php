<?php

class ModelSaleCoupon extends Model
{
    public function addCoupon($data)
    {
        $log = new Log("Coupons.log");

        $from_affiliate = '';
        if (\Extension::isinstalled('affiliate_promo')) {
            //Affiliate promo App
            if($data['from_affiliate']) $from_affiliate = ", from_affiliate = '" . (int)$data['from_affiliate'] . "'";
        }
        //////////////////

        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "coupon SET
            name = '" . $this->db->escape($data['name']) . "',
            code = '" . $this->db->escape($data['code']) . "',
            discount = '" . (float)$data['discount'] . "',
            type = '" . $this->db->escape($data['type']) . "',
            total = '" . (float)$data['total'] . "',
            logged = '" . (int)$data['logged'] . "',
            shipping = '" . (int)$data['shipping'] . "',
            maximum_limit = '" . (float)$data['maximum_limit'] . "',
            minimum_to_apply = '" . (float)$data['minimum_to_apply'] . "',
            date_start = '" . $this->db->escape($data['date_start']) . "',
            date_end = '" . $this->db->escape($data['date_end']) . "',
            uses_total = '" . (int)$data['uses_total'] . "',
            uses_customer = '" . (int)$data['uses_customer'] . "',
            status = '" . (int)$data['status'] . "',
            notify_mobile = '" . (int)$data['notify_mobile'] . "',
            details ='".   $this->db->escape(json_encode($data['details']))  ."',
            notification_status = '" . (int)$data['notification_status'] . "',
            automatic_apply = '" . (int)$data['automatic_apply'] . "',
            date_added = NOW()".
            $from_affiliate
        );

        $coupon_id = $this->db->getLastId();

        if(STORECODE == 'UPMXV084'){
            $arr= ['coupon'=>$coupon_id];
            $this->log("CouponEdit:" , json_encode($arr));
        }

        if (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $product_id) {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_product SET
                    coupon_id = '" . (int)$coupon_id . "',
                    product_id = '" . (int)$product_id . "'"
                );
            }
        }

        if (isset($data['coupon_category'])) {
            $data['coupon_category'] = array_unique($data['coupon_category']);

            foreach ($data['coupon_category'] as $category_id) {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_category SET
                    coupon_id = '" . (int)$coupon_id . "',
                    category_id = '" . (int)$category_id . "'"
                );
            }
        }

        if (isset($data['coupon_manufacturer'])) {
            foreach ($data['coupon_manufacturer'] as $manufacturer_id) {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_manufacturer SET
                    coupon_id = '" . (int)$coupon_id . "',
                    manufacturer_id = '" . (int)$manufacturer_id . "'"
                );
            }
        }

        if (isset($data['coupon_customer'])) {
            foreach ($data['coupon_customer'] as $customer_id) {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_customer SET
                    coupon_id = '" . (int)$coupon_id . "',
                    customer_id = '" . (int)$customer_id . "'"
                );
            }
        }

        if (isset($data['coupon_group'])) {
            foreach ($data['coupon_group'] as $group_id) {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_group SET
                    coupon_id = '" . (int)$coupon_id . "',
                    customer_group_id = '" . (int)$group_id . "'"
                );
            }
        }

        if (isset($data['coupon_product_excluded'])) {
            foreach ($data['coupon_product_excluded'] as $excluded_product_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$excluded_product_id . "' AND coupon_id = '" . (int)$coupon_id . "'");
                if($query->num_rows){
                    $updateCouponProduct = $this->db->query("UPDATE " . DB_PREFIX . "coupon_product SET product_id = '" . (int)$excluded_product_id . "' AND coupon_id = '" . (int)$coupon_id . "', product_excluded = '1' WHERE product_id = '" . (int)$excluded_product_id . "' AND coupon_id = '" . (int)$coupon_id . "' ");
                }else{
                    $updateCouponProduct = $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET product_id = '" . (int)$excluded_product_id . "', coupon_id = '" . (int)$coupon_id . "', product_excluded = '1' ");
                }
            }
        }

        if (isset($data['coupon_category_excluded'])) {
            foreach ($data['coupon_category_excluded'] as $excluded_category_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE category_id = '" . (int)$excluded_category_id . "' AND coupon_id = '" . (int)$coupon_id . "'");
                if($query->num_rows){
                    $updateCouponProduct = $this->db->query("UPDATE " . DB_PREFIX . "coupon_category SET category_id = '" . (int)$excluded_category_id . "' AND coupon_id = '" . (int)$coupon_id . "', category_excluded = '1' WHERE category_id = '" . (int)$excluded_category_id . "' AND coupon_id = '" . (int)$coupon_id . "' ");
                }else{
                    $updateCouponProduct = $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET category_id = '" . (int)$excluded_category_id . "', coupon_id = '" . (int)$coupon_id . "', category_excluded = '1' ");
                }
            }
        }

        if (isset($data['coupon_manufacturer_excluded'])) {
            foreach ($data['coupon_manufacturer_excluded'] as $excluded_manufacturer_id) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_manufacturer WHERE manufacturer_id = '" . (int)$excluded_manufacturer_id . "' AND coupon_id = '" . (int)$coupon_id . "'");
                if($query->num_rows){
                    $updateCouponProduct = $this->db->query("UPDATE " . DB_PREFIX . "coupon_manufacturer SET manufacturer_id = '" . (int)$excluded_manufacturer_id . "' AND coupon_id = '" . (int)$coupon_id . "', manufacturer_excluded = '1' WHERE manufacturer_id = '" . (int)$excluded_manufacturer_id . "' AND coupon_id = '" . (int)$coupon_id . "' ");
                }else{
                    $updateCouponProduct = $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_manufacturer SET manufacturer_id = '" . (int)$excluded_manufacturer_id . "', coupon_id = '" . (int)$coupon_id . "', manufacturer_excluded = '1' ");
                }
            }
        }

        if (\Extension::isinstalled('affiliate_promo') && isset($data['from_affiliate'])) {
            $this->db->query(
                "INSERT INTO " . DB_PREFIX . "coupon_to_affiliate SET
                    coupon_id = '" . (int)$coupon_id . "',
                    affiliate_id = '" . (int)$data['from_affiliate'] . "'"
            );
        }

        return $coupon_id;
    }

    public function editCoupon($coupon_id, $data)
    {
        if(STORECODE == 'UPMXV084'){
            $arr= ['coupon'=>$coupon_id];
            $this->log("CouponEdit:" , json_encode($arr));
        }

        $details='';
        $from_affiliate = '';
        if (\Extension::isinstalled('affiliate_promo')){
            //Affiliate promo App
            if($data['from_affiliate'])
                $from_affiliate = ", from_affiliate = '" . (int)$data['from_affiliate'] . "'";
            else
                $from_affiliate = ", from_affiliate = '0'";
        }

        //////////////////
        $couponDetails = "";
        if (isset($data['details'])){
            if (is_string($data['details']))
                $couponDetails =$data['details'];
            else
                $couponDetails =json_encode($data['details']);
            $details = "details ='". $this->db->escape($couponDetails) ."',";
        }
        $this->db->query(
            "UPDATE " . DB_PREFIX . "coupon SET
            name = '" . $this->db->escape($data['name']) . "',
            code = '" . $this->db->escape($data['code']) . "',
            discount = '" . (float)$data['discount'] . "',
            type = '" . $this->db->escape($data['type']) . "',
            total = '" . (float)$data['total'] . "',
            logged = '" . (int)$data['logged'] . "',
            shipping = '" . (int)$data['shipping'] . "',
            maximum_limit = '" . (float)$data['maximum_limit'] . "',
            minimum_to_apply = '" . (float)$data['minimum_to_apply'] . "',
            date_start = '" . $this->db->escape($data['date_start']) . "',
            date_end = '" . $this->db->escape($data['date_end']) . "',
            uses_total = '" . (int)$data['uses_total'] . "',
            uses_customer = '" . (int)$data['uses_customer'] . "',
            status = '" . (int)$data['status'] . "',
            $details
            notification_status = '" . (int)$data['notification_status'] . "',
            automatic_apply = '" . (int)$data['automatic_apply'] . "',
            notify_mobile = '" . (int)$data['notify_mobile'] . "'".
            $from_affiliate." 
            WHERE coupon_id = '" . (int)$coupon_id . "'"
        );

        if ( isset($data['coupon_product']) && ($data['details']["apply_item_from"] == "product" || $data['details']["buy_item_from"] == "product"))
        {
            if($this->config->get('wk_amazon_connector_status')){
                $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_fields WHERE product_id = '" . (int)$product_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_variation_map WHERE product_id = '" . (int)$product_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "amazon_product_map WHERE oc_product_id = '" . (int)$product_id . "'");
            }

            $data['coupon_product'] = array_unique($data['coupon_product']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id='{$coupon_id}' AND product_excluded='0' ");

            foreach ( $data['coupon_product'] as $product_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_product SET
                    product_id = '" . (int)$product_id . "',
                    coupon_id = '" . (int)$coupon_id . "'"
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id='{$coupon_id}' AND product_excluded='0' ");
        }

        if ( isset($data['coupon_category']) && ($data['details']["apply_item_from"] == "category" || $data['details']["buy_item_from"] == "category"))
        {
            $data['coupon_category'] = array_unique($data['coupon_category']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id='{$coupon_id}' AND category_excluded='0' ");

            foreach ( $data['coupon_category'] as $category_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_category SET
                    category_id = '" . (int)$category_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'"
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id='{$coupon_id}' AND category_excluded='0' ");
        }

        if ( isset($data['coupon_manufacturer']) && ($data['details']["apply_item_from"] == "manufacturer" || $data['details']["buy_item_from"] == "manufacturer"))
        {
            $data['coupon_manufacturer'] = array_unique($data['coupon_manufacturer']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id='{$coupon_id}' AND manufacturer_excluded='0' ");

            foreach ( $data['coupon_manufacturer'] as $manufacturer_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_manufacturer SET
                    manufacturer_id = '" . (int)$manufacturer_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'"
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id='{$coupon_id}' AND manufacturer_excluded='0' ");
        }

        if ( isset($data['coupon_customer']) )
        {
            $data['coupon_customer'] = array_unique($data['coupon_customer']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_customer WHERE coupon_id='{$coupon_id}'");

            foreach ( $data['coupon_customer'] as $customer_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_customer SET
                    customer_id = '" . (int)$customer_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'"
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_customer WHERE coupon_id='{$coupon_id}'");
        }

        if ( isset($data['coupon_group']) )
        {
            $data['coupon_group'] = array_unique($data['coupon_group']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_group WHERE coupon_id='{$coupon_id}'");

            foreach ( $data['coupon_group'] as $group_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_group SET
                    customer_group_id = '" . (int)$group_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'"
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_group WHERE coupon_id='{$coupon_id}'");
        }

        // PRODUCT EXCLUDED
        if ( isset($data['coupon_product_excluded'])  && $data['details']["exclude_item"] == "product" )
        {

            $data['coupon_product_excluded'] = array_unique($data['coupon_product_excluded']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id='{$coupon_id}' AND product_excluded='1' ");

            foreach ( $data['coupon_product_excluded'] as $product_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_product SET
                    product_id = '" . (int)$product_id . "',
                    coupon_id = '" . (int)$coupon_id . "'
                    ,product_excluded = '1' "
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id='{$coupon_id}' AND product_excluded='1' ");
        }

        // CATEGORY EXCLUDED
        if ( isset($data['coupon_category_excluded']) && $data['details']["exclude_item"] == "category" )
        {
            $data['coupon_category_excluded'] = array_unique($data['coupon_category_excluded']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id='{$coupon_id}' AND category_excluded='1' ");

            foreach ( $data['coupon_category_excluded'] as $category_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_category SET
                    category_id = '" . (int)$category_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'
                    , category_excluded = '1' "
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id='{$coupon_id}' AND category_excluded='1' ");
        }

        // MANUFACTURER EXCLUDED
        if ( isset($data['coupon_manufacturer_excluded']) && $data['details']["exclude_item"] == "manufacturer" )
        {
            $data['coupon_manufacturer_excluded'] = array_unique($data['coupon_manufacturer_excluded']);

            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id='{$coupon_id}' AND manufacturer_excluded='1' ");

            foreach ( $data['coupon_manufacturer_excluded'] as $manufacturer_id )
            {
                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "coupon_manufacturer SET
                    manufacturer_id = '" . (int)$manufacturer_id . "'
                    , coupon_id = '" . (int)$coupon_id . "'
                    , manufacturer_excluded = '1' "
                );
            }
        }
        else{
            $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id='{$coupon_id}' AND manufacturer_excluded='1' ");
        }

    }

    public function deleteCoupon($coupon_id)
    {
        if(STORECODE == 'UPMXV084'){
            $arr= ['coupon'=>$coupon_id];
            $this->log("CouponEdit:" , json_encode($arr));
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_manufacturer WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_group WHERE coupon_id = '" . (int)$coupon_id . "'");
    }

    public function copyCoupon($coupon_id)
    {

        $coupon = $this->getCoupon($coupon_id);

        if ($coupon) {
            $data = $coupon;

            $data['code'] = '';
            $data['status'] = '0';

            $couponCategories= $this->separateCouponExcludedAndIncluded($this->getCouponCategories($coupon_id , true),'category_excluded');
            $couponProducts = $this->separateCouponExcludedAndIncluded($this->getCouponProducts($coupon_id , true) ,'product_excluded');
            $data['coupon_product_excluded'] = array_column($couponProducts['excluded'],'product_id');
            $data['coupon_product'] = array_column($couponProducts['included'],'product_id');
            $data['coupon_category_excluded'] = array_column($couponCategories['excluded'],'category_id');
            $data['coupon_category'] = array_column($couponCategories['included'],'category_id');

            return $this->addCoupon($data);
        }
    }

    public function getCoupon($coupon_id)
    {
        $queryString = [];

        $queryString[] = "SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'";
        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }

    public function getCouponByCode($code)
    {
        $queryString = [];
        $queryString[] = 'SELECT DISTINCT * FROM ' . DB_PREFIX . 'coupon';
        $queryString[] = 'WHERE code = "' . $this->db->escape($code) . '"';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row;
    }

    /**
     * List the coupons for specific affiliate user
     *
     * @param $id
     * @return DB
     */
    public function getCouponByAffiliateUser($id)
    {
        return $this->db->query("SELECT DISTINCT * FROM {${DB_PREFIX}}coupon WHERE from_affiliate= '{$this->db->escape($id)}'");
    }

    public function getCoupons($data = array())
    {
        $queryString = [];

        $fields = 'coupon_id, name, code, discount, date_start, date_end, status';

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon";

        $sort_data = array(
            'name',
            'code',
            'discount',
            'date_start',
            'date_end',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function getCouponProducts($coupon_id,$fetchAllData = false)
    {
        $coupon_product_data = $queryString = [];

        $queryString[] = "SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'";

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_product_data[] = $result;
            }else{
                $coupon_product_data[] = $result['product_id'];
            }
        }

        return $coupon_product_data;
    }

    public function getCouponCategories($coupon_id,$fetchAllData = false)
    {
        $coupon_category_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_category';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';

        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_category_data[] = $result;
            }else{
                $coupon_category_data[] = $result['category_id'];
            }
        }

        return $coupon_category_data;
    }

    public function getCouponManufacturer($coupon_id,$fetchAllData = false)
    {
        $coupon_manufacturer_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_manufacturer';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';


        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_manufacturer_data[] = $result;
            }else{
                $coupon_product_data[] = $result['manufacturer_id'];
            }
        }
        return $coupon_manufacturer_data;
    }

    public function getCouponGroup($coupon_id,$fetchAllData = false)
    {
        $coupon_group_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_group';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';


        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_group_data[] = $result;
            }else{
                $coupon_group_data[] = $result['customer_group_id'];
            }
        }
        return $coupon_group_data;
    }

    public function getCouponCustomer($coupon_id,$fetchAllData = false): array
    {
        $coupon_customer_data = $queryString = [];

        $queryString[] = 'SELECT * FROM ' . DB_PREFIX . 'coupon_customer';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '"';


        $query = $this->db->query(implode(' ', $queryString));

        foreach ($query->rows as $result) {
            if($fetchAllData){
                $coupon_customer_data[] = $result;
            }else{
                $coupon_customer_data[] = $result['customer_id'];
            }
        }
        return $coupon_customer_data;
    }
    public function getTotalCoupons()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon");

        return $query->row['total'];
    }

    public function getCouponHistories($coupon_id, $start = 0, $limit = 10)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $fields = 'ch.order_id, CONCAT(c.firstname, " ", c.lastname) AS customer, ch.amount, ch.date_added';
        $queryString = [];
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon_history ch";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "customer c";
        $queryString[] = "ON (ch.customer_id = c.customer_id)";
        $queryString[] = "WHERE ch.coupon_id = '" . (int)$coupon_id . "'";
        $queryString[] = "ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit;

        $query = $this->db->query(implode(' ', $queryString));

        return $query->rows;
    }

    public function getTotalCouponHistories($coupon_id)
    {
        $queryString = [];
        $queryString[] = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'coupon_history ch';
        $queryString[] = 'LEFT JOIN `' . DB_PREFIX . 'order` o  ON ch.order_id= o.order_id ';
        $queryString[] = 'WHERE coupon_id = "' . (int)$coupon_id . '" AND o.order_status_id !=0';

        $query = $this->db->query(implode(' ', $queryString));

        return $query->row['total'];
    }

    public function dtHandler($data)
    {
        $affiliate_promo = \Extension::isInstalled('affiliate_promo') && $this->config->get('affiliate_promo')['status'];

        $queryString = [];
        $fields = ['c.coupon_id', 'c.name', 'c.code', 'c.discount', 'c.date_start', 'c.date_end', 'c.status','c.type','c.automatic_apply','c.shipping'];
        if($affiliate_promo)
            array_push($fields, 'af.affiliate_id', 'CONCAT(af.firstname, \' \', af.lastname) as affiliate');

        $fields = implode(', ', $fields);
        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon c";

        if($affiliate_promo){
            // note from_affiliate implemented as as a flag is promocode code added by affiliate
            //then from_affiliate = 1 if admin from_affiliate = null
            $queryString[] = "LEFT JOIN " . DB_PREFIX . "coupon_to_affiliate ctf ON c.coupon_id = ctf.coupon_id";
            $queryString[] = "LEFT JOIN " . DB_PREFIX . "affiliate af ON af.affiliate_id = ctf.affiliate_id";
        }

        $queryString[] = "WHERE 1";

        if (!empty($data['filter_name'])) {
            $queryString[] = "AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $total = $this->db->query(str_replace($fields, 'COUNT(*) as dc', implode(' ', $queryString)))->row['dc'];

        $sort_data = array(
            'name',
            'code',
            'discount',
            'date_start',
            'date_end',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $queryString[] = " ORDER BY " . $data['sort'];
        } else {
            $queryString[] = " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $queryString[] = " DESC";
        } else {
            $queryString[] = " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $queryString[] = " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $queryData->num_rows
        );

        return $data;
    }

    public function dtHistoryHandler($data)
    {
        $queryString = [];

        $fields = "ch.order_id, ch.amount, ch.date_added, CONCAT(customer.firstname, ' ', customer.lastname) as customer_name, o.total, o.commission, `order_product_names`.`order_products` order_products";

        $queryString[] = "SELECT {$fields} FROM " . DB_PREFIX . "coupon_history ch";
        $queryString[] = "LEFT JOIN " . DB_PREFIX . "customer ";
        $queryString[] = "ON ch.customer_id = customer.customer_id ";
        $queryString[] = "LEFT JOIN `" . DB_PREFIX . "order` o ON o.order_id = ch.order_id ";
        $queryString[] =  " JOIN (SELECT `" . DB_PREFIX . "order_product`.`order_id` , group_concat(`" . DB_PREFIX . "order_product`.`quantity`, ' * ', `" . DB_PREFIX . "order_product`.`name` SEPARATOR '<br/>') AS order_products FROM `" . DB_PREFIX . "order_product` GROUP BY `" . DB_PREFIX . "order_product`.`order_id`) `order_product_names` ON `order_product_names`.`order_id` = ch.order_id";
        $queryString[] = ($data['coupon_id'] > 0) ? " WHERE o.order_status_id !=0 AND `coupon_id` = " .(int) $data['coupon_id'] : '';
        $queryString[] = "GROUP BY ch.order_id";

        $total = $this->db->query("SELECT COUNT(coupon_id) as totalUses FROM " . DB_PREFIX . "coupon_history ch LEFT JOIN  `" . DB_PREFIX . "order` o
                                    ON ch.order_id= o.order_id  WHERE  o.order_status_id !=0 AND   ch.coupon_id = " . (int) $data['coupon_id'])->row['totalUses'];

        $sort_data = array(
            'order_id',
            'customer',
            'amount',
            'date_added'
        );

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['length'] < 1) {
                $data['length'] = time();
            }

            $queryString[] = "LIMIT " . (int)$data['start'] . "," . (int)$data['length'];
        }

        $queryData = $this->db->query(implode(' ', $queryString));

        $data = array(
            'data' => $queryData->rows,
            'total' => $total,
            'totalFiltered' => $total,
        );

        return $data;
    }

    function log($type, $contents , $fileName=false)
    {
        if (!$fileName || empty($fileName))
            $fileName='coupon.log';

        $log = new Log($fileName);
        $log->write('[' . strtoupper($type) . '] ' . $contents);
    }

    public function massEditCouponsStatus($data){
        if (!isset($data['couponIds']) || !is_array($data['couponIds']) || count($data['couponIds']) < 1 )
            return;

        if (!isset($data['discount']))
            $data['discount'] = 0;

        if (!isset($data['status']))
            $data['status'] = 0;
        if (!isset($data['affiliate_commission']))
            $data['affiliate_commission'] = 0;

        // update coupons
        $this->db->query(
            "UPDATE " . DB_PREFIX . "coupon SET
            discount = '" . (float)$data['discount'] . "',
            type = '" . $this->db->escape($data['type']) . "',
            status = '" . (int)$data['status'] . "'
            WHERE coupon_id IN (". implode(',',$data['couponIds']). ")"
        );

        // update affiliate commission
        if (isset($data['affiliateIds']) && is_array($data['affiliateIds']) && count($data['affiliateIds']) > 0){
            $this->db->query("UPDATE " . DB_PREFIX . "affiliate SET commission = '"
                . (float)$this->db->escape($data['affiliate_commission']) .
                "' WHERE affiliate_id IN (". implode(',',$data['affiliateIds']). ")");
        }

    }

    public  function separateCouponExcludedAndIncluded($rows,$excludedColumn){
        if (!$excludedColumn)
            return array();

        $result = array(
            'excluded'=>[] ,
            'included'=>[]
        );
        foreach ($rows as $row){
            if ($row[$excludedColumn])
                $result['excluded'][]=$row;
            else
                $result['included'][]=$row;
        }
        return $result;
    }

    public function disableCoupons(){

        $sql = "UPDATE " . DB_PREFIX . "coupon SET
            status = '" . 0 . "'".
            " WHERE coupon_id IN (
            SELECT coupon_id FROM (
            SELECT coupon_id FROM coupon 
            LIMIT 18446744073709551610 OFFSET ".(int) COUPONSLIMIT."
             ) tmp
        )";

        $this->db->query(
            $sql
        );
    }

    public function getLastCouponInLimitId(){

        $sql = "select coupon_id FROM " . DB_PREFIX . "coupon limit 1 offset ".(COUPONSLIMIT - 1);
        $query = $this->db->query($sql);
        return $query->row['coupon_id'];
    }

    public function isAutomaticCoupon($coupon){
        $result = null;
        $query = $this->db->query("SELECT automatic_apply FROM " . DB_PREFIX . "coupon WHERE  code = '" . $this->db->escape($coupon) . "'");
        if($query->num_rows)
            $result = $query->row['automatic_apply'];
        return $result;
    }
}
