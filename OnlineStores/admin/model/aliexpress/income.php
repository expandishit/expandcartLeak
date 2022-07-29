<?php

class ModelAliexpressincome extends Model
{
    public function getIncome($data = array())
    {
        $sql = 'SELECT w.*,u.firstname, u.lastname, z.name as zone, c.name as country, (SELECT SUM(wo.total) FROM '.DB_PREFIX.'warehouse_order wo LEFT JOIN `'.DB_PREFIX."order` o ON (wo.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wo.warehouse_id = w.warehouse_id ) as total_income, (SELECT SUM(wo.warehouseAmount) FROM ".DB_PREFIX.'warehouse_order wo LEFT JOIN `'.DB_PREFIX."order` o ON (wo.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wo.warehouse_id = w.warehouse_id AND wo.paid_status = 0) as payable, (SELECT SUM(wo.warehouseAmount) FROM ".DB_PREFIX.'warehouse_order wo  LEFT JOIN `'.DB_PREFIX."order` o ON (wo.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wo.warehouse_id = w.warehouse_id) as warehouseAmount, (SELECT SUM(wo.adminAmount) FROM ".DB_PREFIX.'warehouse_order wo LEFT JOIN `'.DB_PREFIX."order` o ON (wo.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wo.warehouse_id = w.warehouse_id ) as adminAmount, (SELECT SUM(wos.cost) FROM ".DB_PREFIX.'warehouse_order_shipping wos LEFT JOIN `'.DB_PREFIX."order` o ON (wos.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wos.paid = 0 AND wos.warehouse_id = w.warehouse_id) as shippingAmount, (SELECT SUM(wos.cost) FROM ".DB_PREFIX.'warehouse_order_shipping wos LEFT JOIN `'.DB_PREFIX."order` o ON (wos.order_id = o.order_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wos.paid = 1 AND wos.warehouse_id = w.warehouse_id) as paidShippingAmount FROM ".DB_PREFIX.'warehouse w LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id=w.user_id) LEFT JOIN '.DB_PREFIX.'zone z ON (z.zone_id=w.zone_id) LEFT JOIN '.DB_PREFIX.'country c ON (c.country_id=w.country_id) ';

        if ($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $sql .= ' WHERE w.user_id = '.(int) $this->user->getId().' ';
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getTransactionLogs($filterData)
    {
        $sql = "SELECT w.warehouse_id, w.title, w.warehouse_code, wt.date_added as date_added, wt.total, wt.description, CONCAT(u.firstname,' ', u.lastname) as name FROM ".DB_PREFIX.'warehouse_transaction wt LEFT JOIN '.DB_PREFIX.'warehouse w ON (w.warehouse_id=wt.warehouse_id) LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id=w.user_id) WHERE 1 = 1 ';

        if ($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $sql .= " AND u.user_id = '".$this->user->getId()."' ";
        }

        if (isset($filterData['filter_supplier_name']) && $filterData['filter_supplier_name']) {
            $sql .= " AND CONCAT(u.firstname,' ', u.lastname) like '".$this->db->escape($filterData['filter_supplier_name'])."%' ";
        }

        if (isset($filterData['filter_warehouse']) && $filterData['filter_warehouse']) {
            $sql .= " AND w.title like '".$this->db->escape($filterData['filter_warehouse'])."%' ";
        }

        if (isset($filterData['filter_order_total']) && $filterData['filter_order_total']) {
            $sql .= " AND wt.total = '".$this->db->escape($filterData['filter_order_total'])."' ";
        }

        if (isset($filterData['filter_transaction_date']) && $filterData['filter_transaction_date']) {
            $sql .= " AND wt.date_added like '".$this->db->escape($filterData['filter_transaction_date'])."%' ";
        }

        if (isset($filterData['sort']) && $filterData['sort']) {
            $sql .= ' ORDER BY '.$filterData['sort'];
        } else {
            $sql .= ' ORDER BY wt.transaction_id ';
        }

        if (isset($filterData['order']) && ('DESC' == $filterData['order'])) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }

        if (isset($filterData['start']) || isset($filterData['limit'])) {
            if ($filterData['start'] < 0) {
                $filterData['start'] = 0;
            }

            if ($filterData['limit'] < 1) {
                $filterData['limit'] = 20;
            }
            $sql .= ' LIMIT '.(int) $filterData['start'].','.(int) $filterData['limit'];
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getTransactionLogsTotal($filterData)
    {
        $sql = "SELECT w.warehouse_id, w.title, w.warehouse_code, wt.date_added as date_added, wt.total, wt.description, CONCAT(u.firstname,' ', u.lastname) as name FROM ".DB_PREFIX.'warehouse_transaction wt LEFT JOIN '.DB_PREFIX.'warehouse w ON (w.warehouse_id=wt.warehouse_id) LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id=w.user_id) WHERE 1 = 1 ';

        if ($this->user->getUserGroup() == $this->config->get('wk_dropship_user_group')) {
            $sql .= " AND u.user_id = '".$this->user->getId()."' ";
        }

        if (isset($filterData['filter_supplier_name']) && $filterData['filter_supplier_name']) {
            $sql .= " AND CONCAT(u.firstname,' ', u.lastname) like '".$this->db->escape($filterData['filter_supplier_name'])."%' ";
        }

        if (isset($filterData['filter_warehouse']) && $filterData['filter_warehouse']) {
            $sql .= " AND w.title like '".$this->db->escape($filterData['filter_warehouse'])."%' ";
        }

        if (isset($filterData['filter_order_total']) && $filterData['filter_order_total']) {
            $sql .= " AND wt.total = '".$this->db->escape($filterData['filter_order_total'])."' ";
        }

        if (isset($filterData['filter_transaction_date']) && $filterData['filter_transaction_date']) {
            $sql .= " AND wt.date_added like '".$this->db->escape($filterData['filter_transaction_date'])."%' ";
        }

        if (isset($filterData['sort']) && $filterData['sort']) {
            $sql .= ' ORDER BY '.$filterData['sort'];
        } else {
            $sql .= ' ORDER BY wt.transaction_id ';
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function addTransactionLog($ids, $detail)
    {
        $datetime = date('Y-m-d h:i:s');
        if ($ids) {
            $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_transaction SET user_id = '".(int) $detail['user_id']."', warehouse_id = '".(int) $detail['warehouse_id']."', description = '".$this->db->escape($detail['description'])."', date_added = '".$this->db->escape($datetime)."', status = '1' ");
            $transaction_id = $this->db->getLastId();
            $totalAmount = 0;
            foreach ($ids as $key => $id) {
                $result = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_order WHERE warehouse_order_id = $id ")->row;
                $sql = 'INSERT INTO '.DB_PREFIX."warehouse_transaction_details SET transaction_id = '".(int) $transaction_id."', warehouse_order_id = '".(int) $id."', order_id = '".(int) $result['order_id']."', warehouse_id = '".(int) $result['warehouse_id']."', product_id = '".(int) $result['product_id']."', amount = '".$this->db->escape($result['warehouseAmount'])."', description = '".$this->db->escape($detail['description'])."', date_added = '".$this->db->escape($datetime)."', status = '1'  ";
                $this->db->query($sql);

                $this->db->query('UPDATE '.DB_PREFIX."warehouse_order SET paid_status = 1 WHERE warehouse_order_id = '".(int) $id."' ");

                $all_paid = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_order WHERE warehouse_id = '".(int) $result['warehouse_id']."' AND order_id = '".(int) $result['order_id']."' AND paid_status = 0 ")->rows;
                if (!$all_paid) {
                    $this->db->query('UPDATE '.DB_PREFIX."warehouse_order_shipping SET paid = 1 WHERE warehouse_id = '".(int) $result['warehouse_id']."' AND order_id = '".(int) $result['order_id']."' ");
                    $shipping = $this->db->query('SELECT cost FROM '.DB_PREFIX."warehouse_order_shipping WHERE warehouse_id = '".(int) $result['warehouse_id']."' AND order_id = '".(int) $result['order_id']."' ")->row;
                    $totalAmount += $shipping['cost'];
                }
                $totalAmount += $result['warehouseAmount'];
            }
            $this->db->query('UPDATE '.DB_PREFIX."warehouse_transaction SET total = '".$totalAmount."' WHERE transaction_id = '".$transaction_id."' ");

            return true;
        } else {
            return false;
        }
    }

    public function getUnpaidOrderProductsByWarehouseId($data = array())
    {
        $sql = 'SELECT wo.*,p.model, pd.name, os.name as order_status, o.date_added FROM '.DB_PREFIX.'warehouse_order wo LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id = wo.product_id) LEFT JOIN '.DB_PREFIX.'product_description pd ON (p.product_id=pd.product_id) LEFT JOIN `'.DB_PREFIX.'order` o ON (o.order_id=wo.order_id) LEFT JOIN '.DB_PREFIX."order_status os ON (os.order_status_id=o.order_status_id) WHERE o.order_status_id = '".(int) $this->config->get('wk_dropship_complete_order_status')."' AND wo.warehouse_id = '".(int) $data['warehouse_id']."' AND wo.warehouse_id != '0' AND pd.language_id = '".$this->db->escape($this->config->get('config_language_id'))."' AND wo.product_id NOT IN (SELECT product_id FROM ".DB_PREFIX."warehouse_transaction_details wt WHERE wt.warehouse_id = wo.warehouse_id AND wt.product_id = wo.product_id AND wt.order_id = wo.order_id AND wt.status = '1' ) ";

        $result = $this->db->query($sql)->rows;

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getSupplierDetailsByWarehouseId($warehouse_id)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX.'warehouse w LEFT JOIN '.DB_PREFIX."user u ON (u.user_id=w.user_id) WHERE w.warehouse_id = '".(int) $warehouse_id."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
}
