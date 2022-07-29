<?php

class ModelEbayAssignCommerce extends Model
{
    public function pushToStore($id)
    {
        $this->db->query('UPDATE '.DB_PREFIX."product SET status = 1 WHERE product_id = '".(int) $id."' ");

        return true;
    }

    public function getEbayProducts($data)
    {
        $sql = 'SELECT wap.*,p.status,p.image,p.date_added FROM '.DB_PREFIX.'commerce_ebay_product wap LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wap.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '".(int) $this->config->get('config_language_id')."'";

        if (isset($data['filter_product_name']) && $data['filter_product_name']) {
            $sql .= " AND wap.name LIKE '%".$this->db->escape($data['filter_product_name'])."%' ";
        }

        if (isset($data['filter_ebay_product_id']) && $data['filter_ebay_product_id']) {
            $sql .= " AND wap.ebay_product_id = '".(int) $data['filter_ebay_product_id']."' ";
        }

        if (isset($data['filter_product_status'])) {
            $sql .= ' AND p.status = '.(int) $data['filter_product_status'].' ';
        }

        if (isset($data['sort'])) {
            $sql .= ' ORDER BY '.$data['sort'];
        } else {
            $sql .= ' ORDER BY wap.name';
        }

        if (isset($data['order']) && ('DESC' == $data['order'] || 'desc' == $data['order'])) {
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

            $sql .= ' LIMIT '.$data['start'].', '.$data['limit'];
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getEbayProductsTotal($data)
    {
        $sql = 'SELECT COUNT(*) as total FROM '.DB_PREFIX.'commerce_ebay_product wap LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wap.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '".(int) $this->config->get('config_language_id')."'";

        if (isset($data['filter_product_name']) && $data['filter_product_name']) {
            $sql .= " AND wap.name LIKE '%".$this->db->escape($data['filter_product_name'])."%' ";
        }

        if (isset($data['filter_ebay_product_id']) && $data['filter_ebay_product_id']) {
            $sql .= " AND wap.ebay_product_id = '".(int) $data['filter_ebay_product_id']."' ";
        }

        if (isset($data['filter_product_status'])) {
            $sql .= ' AND p.status = '.(int) $data['filter_product_status'].' ';
        }

        $result = $this->db->query($sql);
        return $result->row['total'];
    }

}
