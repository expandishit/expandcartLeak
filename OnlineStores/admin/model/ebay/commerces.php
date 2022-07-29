<?php

class ModelEbayCommerces extends Model
{

    public function getEbayOrders($data)
    {
        $sql = "SELECT GROUP_CONCAT(wap.ebay_product_id) as ebay_product_id, GROUP_CONCAT(op.order_product_id) as order_product_id, GROUP_CONCAT(op.quantity) as quantity, SUM(op.price*op.quantity) as sum_price, wap.product_url,op.order_id,CONCAT(o.firstname, ' ', o.lastname) AS customer, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, o.order_status_id FROM ".DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX.'commerce_ebay_product wap ON (wap.product_id=op.product_id) LEFT JOIN `'.DB_PREFIX.'order` o ON (o.order_id=op.order_id)  WHERE op.product_id IN (SELECT wap.product_id FROM '.DB_PREFIX.'commerce_ebay_product wap WHERE wap.product_id=op.product_id ) ';

        if (isset($data['filter_order_id']) && null != $data['filter_order_id']) {
            $sql .= " AND op.order_id = '".(int) $this->db->escape($data['filter_order_id'])."' ";
        }

        if (isset($data['filter_customer']) && null != $data['filter_customer']) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) like '".$this->db->escape($data['filter_customer'])."%' ";
        }

        if (isset($data['filter_date_added']) && null != $data['filter_date_added']) {
            $sql .= " AND o.date_added = '".$this->db->escape($data['filter_date_added'])."' ";
        }

        if (isset($data['filter_date_updated']) && null != $data['filter_date_updated']) {
            $sql .= " AND o.date_modified = '".$this->db->escape($data['filter_date_updated'])."' ";
        }

        if (isset($data['filter_total']) && null != $data['filter_total']) {
            $sql .= " AND o.total <= '".$this->db->escape($data['filter_total'])."' ";
        }

        $abandoned_status = 0;
        $sql .= " AND o.order_status_id != '".$abandoned_status."'";

        $sql .= ' GROUP BY op.order_id  ';

        if (isset($data['sort'])) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY op.order_id';
        }

        if (isset($data['order']) && ('DESC' == $data['order'])) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql .= ' LIMIT '.(int) $this->db->escape($data['start']).','.(int) $this->db->escape($data['limit']);
        }

        $result = $this->db->query($sql)->rows;

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getEbayOrdersTotal($data)
    {
        $sql = "SELECT GROUP_CONCAT(wap.ebay_product_id) as ebay_product_id,wap.product_url,op.order_id,CONCAT(o.firstname, ' ', o.lastname) AS customer,o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, COUNT(*) as total_count FROM ".DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX.'commerce_ebay_product wap ON (wap.product_id=op.product_id) LEFT JOIN `'.DB_PREFIX.'order` o ON (o.order_id=op.order_id)  WHERE op.product_id IN (SELECT wap.product_id FROM '.DB_PREFIX.'commerce_ebay_product wap WHERE wap.product_id=op.product_id ) ';

        if (isset($data['filter_order_id']) && null != $data['filter_order_id']) {
            $sql .= " AND op.order_id = '".(int) $this->db->escape($data['filter_order_id'])."' ";
        }

        if (isset($data['filter_customer']) && null != $data['filter_customer']) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) like '".$this->db->escape($data['filter_customer'])."%' ";
        }

        if (isset($data['filter_date_added']) && null != $data['filter_date_added']) {
            $sql .= " AND o.date_added = '".$this->db->escape($data['filter_date_added'])."' ";
        }

        if (isset($data['filter_date_updated']) && null != $data['filter_date_updated']) {
            $sql .= " AND o.date_modified = '".$this->db->escape($data['filter_date_updated'])."' ";
        }

        if (isset($data['filter_total']) && null != $data['filter_total']) {
            $sql .= " AND o.total <= '".$this->db->escape($data['filter_total'])."' ";
        }

        $sql .= ' GROUP BY op.order_id  ';

        $result = $this->db->query($sql);
        return $result->row['total_count'];
    }

    public function getEbayProductsId($order_id, $order_products_id=[])
    {
        if(empty($order_products_id))
            return [];
        $query = $this->db->query('SELECT wap.ebay_product_id FROM '.DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX."commerce_ebay_product wap ON (op.product_id = wap.product_id) WHERE op.order_id = '".(int) $order_id."' AND op.order_product_id  IN ('".implode(",", $order_products_id)."')" );
        return array_column($query->rows, 'ebay_product_id');
    }

    public function getEbayProductsUrl($products_id = []){
        if(empty($products_id))
            return [];
        $query = $this->db->query("SELECT product_url FROM `".DB_PREFIX."commerce_ebay_product` WHERE ebay_product_id  IN (".implode(",", $products_id).")");
        return array_column($query->rows, 'product_url');

    }
}
