<?php

class ModelAliexpressWarehouses extends Model
{
    public function checkForUpdate()
    {
        $status = false;

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."warehouse_aliexpress_product_option' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            $status = true;
        }

        if ($status) {
            $check_quantity = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '".DB_PREFIX."warehouse_aliexpress_product_variation' AND COLUMN_NAME = 'quantity'")->row;

            if (!$check_quantity) {
                $status = false;
            }
        }

        return $status;
    }

    public function getAliExpressOrder($id)
    {
        $sql = 'SELECT op.*, wlp.*, wlo.order_id as wl_order_id FROM order_product as op
        INNER JOIN warehouse_aliexpress_product AS wlp ON
        op.product_id = wlp.product_id
        LEFT JOIN warehouse_aliexpress_order AS wlo ON
        op.order_id = wlo.order_id
        WHERE op.order_id = %s';

        $result = $this->db->query(sprintf($sql, $id))->rows;

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getAliExpressOrders($data)
    {
        $sql = "SELECT GROUP_CONCAT(wap.ali_product_id) as ali_product_id, GROUP_CONCAT(op.order_product_id) as order_product_id, GROUP_CONCAT(op.quantity) as quantity, SUM(op.price*op.quantity) as sum_price, wap.product_url,op.order_id,CONCAT(o.firstname, ' ', o.lastname) AS customer, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, o.order_status_id, wao.status as aliexpress_order_status FROM ".DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX.'warehouse_aliexpress_product wap ON (wap.product_id=op.product_id) LEFT JOIN `'.DB_PREFIX.'order` o ON (o.order_id=op.order_id) LEFT JOIN '.DB_PREFIX.'warehouse_aliexpress_order wao ON (o.order_id = wao.order_id) WHERE op.product_id IN (SELECT wap.product_id FROM '.DB_PREFIX.'warehouse_aliexpress_product wap WHERE wap.product_id=op.product_id and o.order_status_id!=0 ) ';

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

        if (isset($data['filter_aliexpress_status'])) {
            if ('-1' == $data['filter_aliexpress_status']) {
                $sql .= ' AND wao.status IS NULL';
            } elseif (0 === $data['filter_aliexpress_status']) {
                $sql .= " AND wao.status = '0'";
            } elseif (1 == $data['filter_aliexpress_status']) {
                $sql .= " AND wao.status = '1'";
            }
        }

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

    public function getAliExpressOrdersTotal($data)
    {
        $sql = "SELECT GROUP_CONCAT(wap.ali_product_id) as ali_product_id,wap.product_url,op.order_id,CONCAT(o.firstname, ' ', o.lastname) AS customer,o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified, wao.status as aliexpress_order_status FROM ".DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX.'warehouse_aliexpress_product wap ON (wap.product_id=op.product_id) LEFT JOIN `'.DB_PREFIX.'order` o ON (o.order_id=op.order_id) LEFT JOIN '.DB_PREFIX.'warehouse_aliexpress_order wao ON (o.order_id = wao.order_id) WHERE op.product_id IN (SELECT wap.product_id FROM '.DB_PREFIX.'warehouse_aliexpress_product wap WHERE wap.product_id=op.product_id and o.order_status_id!=0 ) ';

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

        if (isset($data['filter_aliexpress_status'])) {
            if ('-1' == $data['filter_aliexpress_status']) {
                $sql .= ' AND wao.status IS NULL';
            } elseif (0 === $data['filter_aliexpress_status']) {
                $sql .= " AND wao.status = '0'";
            } elseif (1 == $data['filter_aliexpress_status']) {
                $sql .= " AND wao.status = '1'";
            }
        }

        $sql .= ' GROUP BY op.order_id  ';

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function getOrderOptions($order_id, $order_product_id)
    {
        // $query = $this->db->query("SELECT aliexpress_option_value_id FROM " . DB_PREFIX . "order_option oo LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN " . DB_PREFIX . "warehouse_aliexpress_option_value wapov ON (pov.option_value_id = wapov.option_value_id) WHERE oo.order_id = '" . (int)$order_id . "' AND oo.order_product_id = '" . (int)$order_product_id . "'")->rows;
        // $aliexress_product_options = array();
        // foreach ($query as $value) {
        //  if (isset($value['aliexpress_option_value_id']) && $value['aliexpress_option_value_id']) {
        //      $aliexress_product_options[] = $value['aliexpress_option_value_id'];
        //  } else {
        //      $aliexress_product_options = array();
        //      $query = $this->db->query("SELECT wapov.alix_option_value_id FROM " . DB_PREFIX . "order_option oo LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN " . DB_PREFIX . "warehouse_aliexpress_product_option_value wapov ON (pov.option_value_id = wapov.oc_option_value_id) WHERE oo.order_id = '" . (int)$order_id . "' AND oo.order_product_id = '" . (int)$order_product_id . "'")->rows;
        //      foreach ($query as $value) {
        //          if (isset($value['alix_option_value_id']) && $value['alix_option_value_id']) {
        //              $aliexress_product_options[] = $value['alix_option_value_id'];
        //          }
        //      }
        //  }
        // }

        $query = $this->db->query('SELECT wapov.* FROM '.DB_PREFIX.'order_option oo LEFT JOIN '.DB_PREFIX.'product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_product_option_value wapov ON (pov.option_value_id = wapov.oc_option_value_id) WHERE oo.order_id = '".(int) $order_id."' AND oo.order_product_id = '".(int) $order_product_id."'")->rows;
        $aliexress_product_options = array();
        foreach ($query as $value) {
            if (isset($value['alix_option_value_id']) && $value['alix_option_value_id']) {
                $aliexress_product_options[] = $value['alix_option_value_id'];
            }
        }

        return $aliexress_product_options;
    }

    public function getAlixProductId($order_id, $order_product_id)
    {
        $query = $this->db->query('SELECT wap.* FROM '.DB_PREFIX.'order_product op LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_product wap ON (op.product_id = wap.product_id) WHERE op.order_id = '".(int) $order_id."' AND op.order_product_id = '".(int) $order_product_id."'")->row;
        if ($query) {
            return $query['ali_product_id'];
        } else {
            false;
        }
    }

    public function checkProductVariationExist($order_product_id)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX.'product_option WHERE product_id = (SELECT product_id FROM '.DB_PREFIX."order_product WHERE order_product_id = '".(int) $order_product_id."') AND option_id = '".(int) $this->config->get('wk_dropship_aliexpress_variation_option_id')."'")->row;

        return $query;
    }

    public function getOrderVariations($order_id, $order_product_id)
    {
        $query = $this->db->query('SELECT aliexpress_variation FROM '.DB_PREFIX.'order_option oo LEFT JOIN '.DB_PREFIX.'product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) LEFT JOIN '.DB_PREFIX."warehouse_aliexpress_variation wav ON (pov.option_value_id = wav.option_value_id) WHERE oo.order_id = '".(int) $this->db->escape($order_id)."' AND oo.order_product_id = '".(int) $this->db->escape($order_product_id)."'")->row;
        $aliexress_product_options = array();

        return $query;
    }

    public function deletePriceRule($id)
    {
        $result = $this->db->query('DELETE FROM '.DB_PREFIX."price_rule WHERE rule_id = '".(int) $this->db->escape($id)."' ")->row;

        return true;
    }

    public function getRule($rule_id)
    {
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."price_rule WHERE rule_id = '".(int) $this->db->escape($rule_id)."' ")->row;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getPriceRules($data = array())
    {
        // $sql = "SELECT pr.*,cd.name as category_name FROM ".DB_PREFIX."price_rule pr LEFT JOIN ".DB_PREFIX."category_description cd ON (cd.category_id=pr.category_id) WHERE cd.language_id = '".(int)$this->config->get('config_language_id')."' ";

        $sql = 'SELECT * FROM '.DB_PREFIX.'price_rule pr WHERE 1=1 ';

        if (isset($data['filter']['filter_name']) && null != $data['filter']['filter_name']) {
            $sql .= " AND pr.name like '".$this->db->escape($data['filter']['filter_name'])."%' ";
        }

        if (isset($data['filter']['filter_channel']) && null != $data['filter']['filter_channel']) {
            $sql .= " AND pr.channel = '".$this->db->escape($data['filter']['filter_channel'])."' ";
        }

        if (isset($data['filter']['filter_category']) && null != $data['filter']['filter_category']) {
            $sql .= " AND cd.category_id = '".$this->db->escape($data['filter']['filter_category'])."' ";
        }

        if (isset($data['filter']['filter_price_from']) && null != $data['filter']['filter_price_from']) {
            $sql .= " AND pr.price_from = '".$this->db->escape($data['filter']['filter_price_from'])."' ";
        }

        if (isset($data['filter']['filter_price_to']) && null != $data['filter']['filter_price_to']) {
            $sql .= " AND pr.price_to = '".$this->db->escape($data['filter']['filter_price_to'])."' ";
        }

        if (isset($data['filter']['filter_amount_type']) && null != $data['filter']['filter_amount_type']) {
            $sql .= " AND pr.method_type = '".$this->db->escape($data['filter']['filter_amount_type'])."' ";
        }

        if (isset($data['filter']['filter_amount']) && null != $data['filter']['filter_amount']) {
            $sql .= " AND pr.amount = '".$this->db->escape($data['filter']['filter_amount'])."' ";
        }

        /*if (isset($data['sort'])) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY wp.warehouse_product_id';
        }

        if (isset($data['order']) && ('DESC' == $data['order'])) {
            $sql .= ' DESC';
        } else {
            $sql .= ' ASC';
        }*/

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

    public function getPriceRulesTotal($data = array())
    {
        // $sql = "SELECT pr.*,cd.name as category_name FROM ".DB_PREFIX."price_rule pr LEFT JOIN ".DB_PREFIX."category_description cd ON (cd.category_id=pr.category_id) WHERE cd.language_id = '".(int)$this->config->get('config_language_id')."' ";
        $sql = 'SELECT * FROM '.DB_PREFIX.'price_rule pr WHERE 1=1 ';

        if (isset($data['filter']['filter_name']) && null != $data['filter']['filter_name']) {
            $sql .= " AND pr.name like '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_name']))."%' ";
        }

        if (isset($data['filter']['filter_channel']) && null != $data['filter']['filter_channel']) {
            $sql .= " AND pr.channel = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_channel']))."' ";
        }

        if (isset($data['filter']['filter_category']) && null != $data['filter']['filter_category']) {
            $sql .= " AND cd.category_id = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_category']))."' ";
        }

        if (isset($data['filter']['filter_price_from']) && null != $data['filter']['filter_price_from']) {
            $sql .= " AND pr.price_from = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_price_from']))."' ";
        }

        if (isset($data['filter']['filter_price_to']) && null != $data['filter']['filter_price_to']) {
            $sql .= " AND pr.price_to = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_price_to']))."' ";
        }

        if (isset($data['filter']['filter_amount_type']) && null != $data['filter']['filter_amount_type']) {
            $sql .= " AND pr.method_type = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_amount_type']))."' ";
        }

        if (isset($data['filter']['filter_amount']) && null != $data['filter']['filter_amount']) {
            $sql .= " AND pr.amount = '".$this->db->escape(str_replace('+', ' ', $data['filter']['filter_amount']))."' ";
        }

        $result = $this->db->query($sql)->rows;

        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function addPriceRule($data)
    {
        // foreach ($data['pricerule_category'] as $key => $category) {
        // $sql = "INSERT INTO ".DB_PREFIX."price_rule SET name = '".$this->db->escape($data['pricerule_name'])."', channel = '".$this->db->escape($data['pricerule_channel'])."', category_id = '".$this->db->escape($category)."', category_relation = '".$this->db->escape($data['pricerule_category_relation'])."', price_from = '".$this->db->escape($data['pricerule_price_range_from'])."', price_to = '".$this->db->escape($data['pricerule_price_range_to'])."', operation_type = '".$this->db->escape($data['pricerule_operation_type'])."', method_type = '".$this->db->escape($data['pricerule_amount_type'])."', amount = '".$this->db->escape($data['pricerule_amount'])."'  ";
        $sql = 'INSERT INTO '.DB_PREFIX."price_rule SET name = '".$this->db->escape($data['pricerule_name'])."', channel = '".$this->db->escape($data['pricerule_channel'])."', category_id = '".(int) $this->db->escape($data['pricerule_category'])."', price_from = '".$this->db->escape($data['pricerule_price_range_from'])."', price_to = '".$this->db->escape($data['pricerule_price_range_to'])."', operation_type = '".$this->db->escape($data['pricerule_operation_type'])."', method_type = '".$this->db->escape($data['pricerule_amount_type'])."', amount = '".$this->db->escape($data['pricerule_amount'])."'  ";
        $this->db->query($sql);
        // }
    }

    public function editPriceRule($data)
    {
        if (1 == $data['pricerule_category']) {
            foreach ($data['pricerule_category'] as $key => $category) {
                $sql = 'UPDATE '.DB_PREFIX."price_rule SET name = '".$this->db->escape($data['pricerule_name'])."', channel = '".$this->db->escape($data['pricerule_channel'])."', category_id = '".$this->db->escape($category)."', price_from = '".$this->db->escape($data['pricerule_price_range_from'])."', price_to = '".$this->db->escape($data['pricerule_price_range_to'])."', operation_type = '".$this->db->escape($data['pricerule_operation_type'])."', method_type = '".$this->db->escape($data['pricerule_amount_type'])."', amount = '".$this->db->escape($data['pricerule_amount'])."' WHERE rule_id = '".(int) $this->db->escape($data['pricerule_rule_id'])."' ";
                $this->db->query($sql);
            }
        } else {
            $this->db->query('DELETE FROM '.DB_PREFIX."price_rule WHERE rule_id = '".$this->db->escape($data['pricerule_rule_id'])."' ");
            $this->addPriceRule($data);
        }
    }

    public function getExcludedPriceRulesRanges($id)
    {
        $sql_query = '';

        $sql_query = 'SELECT price_from as min , price_to as max FROM '.DB_PREFIX.'price_rule WHERE rule_id !='.(int) $id.'';

        $query = $this->db->query($sql_query);

        return $query->rows;
    }

    public function getPriceRulesRanges()
    {
        $sql_query = '';

        $sql_query = 'SELECT price_from as min , price_to as max FROM '.DB_PREFIX.'price_rule';

        $query = $this->db->query($sql_query);

        return $query->rows;
    }

    public function approveWarehouseProduct($id)
    {
        $this->db->query('UPDATE '.DB_PREFIX."warehouse_product SET approved = '1' WHERE warehouse_product_id = '".(int) $this->db->escape($id)."' ");

        return true;
    }

    public function disapproveWarehouseProduct($id)
    {
        $this->db->query('UPDATE '.DB_PREFIX."warehouse_product SET approved = '0' WHERE warehouse_product_id = '".(int) $this->db->escape($id)."' ");

        return true;
    }

    public function getWarehouseProducts($data)
    {
        $sql = "SELECT CONCAT(u.firstname,' ',u.lastname) as warehouseManager,wp.warehouse_product_id,wp.product_id,wp.quantity as warehouse_quantity,wp.approved,w.warehouse_code,w.title as warehouse_title,p.model,p.quantity,p.price,p.status,pd.name, (SELECT SUM(quantity) FROM ".DB_PREFIX.'warehouse_order wo WHERE (wo.product_id = wp.product_id && wo.warehouse_id=wp.warehouse_id ) ) as sold_quantity FROM '.DB_PREFIX.'warehouse_product wp LEFT JOIN '.DB_PREFIX.'warehouse w ON (w.warehouse_id=wp.warehouse_id) LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id=w.user_id) LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wp.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id=pd.product_id) WHERE pd.language_id = '".$this->config->get('config_language_id')."' ";

        if (isset($data['filter']) && isset($data['filter']['filter_product_name']) && null != $data['filter']['filter_product_name']) {
            $sql .= " AND pd.name like '".$this->db->escape($data['filter']['filter_product_name'])."%' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_model']) && null != $data['filter']['filter_product_model']) {
            $sql .= " AND p.model like '".$this->db->escape($data['filter']['filter_product_model'])."%' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_quantity']) && null != $data['filter']['filter_product_quantity']) {
            $sql .= " AND p.quantity >= '".$this->db->escape($data['filter']['filter_product_quantity'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehousr_manager']) && null != $data['filter']['filter_warehousr_manager']) {
            $sql .= " AND u.user_id = '".$this->db->escape($data['filter']['filter_warehousr_manager'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehouse']) && null != $data['filter']['filter_warehouse']) {
            $sql .= " AND w.warehouse_id = '".$this->db->escape($data['filter']['filter_warehouse'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehouse_quantity']) && null != $data['filter']['filter_warehouse_quantity']) {
            $sql .= " AND wp.quantity >= '".(int) $this->db->escape($data['filter']['filter_warehouse_quantity'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_price']) && null != $data['filter']['filter_product_price']) {
            $sql .= " AND p.price >= '".$this->db->escape($data['filter']['filter_product_price'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_status']) && '' != $data['filter']['filter_product_status']) {
            $sql .= " AND wp.approved = '".$this->db->escape($data['filter']['filter_product_status'])."' ";
        }

        $sort_data = array(
            'u.firstname',
            'wp.quantity',
            'wp.approved',
            'w.warehouse_code',
            'w.title',
            'p.model',
            'p.quantity',
            'p.price',
            'p.status',
            'pd.name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY wp.warehouse_product_id';
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

    public function getWarehouseProductsTotal($data)
    {
        $sql = "SELECT CONCAT(u.firstname,' ',u.lastname) as warehouseManager,wp.warehouse_product_id,wp.product_id,wp.quantity as warehouse_quantity,wp.approved,w.warehouse_code,w.title as warehouse_title,p.model,p.quantity,p.price,p.status,pd.name FROM ".DB_PREFIX.'warehouse_product wp LEFT JOIN '.DB_PREFIX.'warehouse w ON (w.warehouse_id=wp.warehouse_id) LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id=w.user_id) LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wp.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id=pd.product_id) WHERE pd.language_id = '".$this->config->get('config_language_id')."' ";

        if (isset($data['filter']) && isset($data['filter']['filter_product_name']) && null != $data['filter']['filter_product_name']) {
            $sql .= " AND pd.name like '".$this->db->escape($data['filter']['filter_product_name'])."%' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_model']) && null != $data['filter']['filter_product_model']) {
            $sql .= " AND p.model like '".$this->db->escape($data['filter']['filter_product_model'])."%' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_quantity']) && null != $data['filter']['filter_product_quantity']) {
            $sql .= " AND p.quantity >= '".$this->db->escape($data['filter']['filter_product_quantity'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehousr_manager']) && null != $data['filter']['filter_warehousr_manager']) {
            $sql .= " AND u.user_id = '".$this->db->escape($data['filter']['filter_warehousr_manager'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehouse']) && null != $data['filter']['filter_warehouse']) {
            $sql .= " AND w.warehouse_id = '".$this->db->escape($data['filter']['filter_warehouse'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_warehouse_quantity']) && null != $data['filter']['filter_warehouse_quantity']) {
            $sql .= " AND wp.quantity >= '".$this->db->escape($data['filter']['filter_warehouse_quantity'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_price']) && null != $data['filter']['filter_product_price']) {
            $sql .= " AND p.price >= '".$this->db->escape($data['filter']['filter_product_price'])."' ";
        }

        if (isset($data['filter']) && isset($data['filter']['filter_product_status']) && '' != $data['filter']['filter_product_status']) {
            $sql .= " AND wp.approved = '".$this->db->escape($data['filter']['filter_product_status'])."' ";
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function updateWarehouseQuantity($data)
    {
        $this->db->query('UPDATE '.DB_PREFIX."warehouse_product SET quantity = '".(int) $this->db->escape($data['warehouseQuantity'])."' WHERE warehouse_product_id = '".$this->db->escape($data['warehouseProductId'])."' ");

        $warehouse = $this->db->query('SELECT SUM(quantity) as quantity FROM '.DB_PREFIX."warehouse_product wp WHERE product_id = '".(int) $this->db->escape($data['productId'])."' ")->row;
        $this->db->query('UPDATE '.DB_PREFIX."product p SET quantity = '".(int) $warehouse['quantity']."' WHERE product_id = '".(int) $this->db->escape($data['productId'])."' ");

        return true;
        // $product = $this->db->query("SELECT quantity FROM ".DB_PREFIX."product WHERE product_id = '".(int)$data['productId']."' ")->row;
        // $warehouse = $this->db->query("SELECT SUM(quantity) as quantity FROM ".DB_PREFIX."warehouse_product WHERE product_id= '".(int)$data['productId']."' AND  warehouse_product_id != '".$data['warehouseProductId']."' ")->row;
        // $leftQuantity = 0;
        // if(isset($product['quantity']) && $product['quantity']) {
        //  $leftQuantity = $product['quantity'] - $warehouse['quantity'];
        //  if($leftQuantity && $leftQuantity > $data['warehouseQuantity']) {
        //      $this->db->query("UPDATE ".DB_PREFIX."warehouse_product SET quantity = '".(int)$data['warehouseQuantity']."' WHERE warehouse_product_id = '".$data['warehouseProductId']."' ");
        //      return true;
        //  }
        // } else {
        //  $this->db->query("UPDATE ".DB_PREFIX."warehouse_product SET quantity = '".(int)$data['warehouseQuantity']."' WHERE warehouse_product_id = '".$data['warehouseProductId']."' ");
        // }
        // return false;
    }

    public function deleteWarehouseProduct($warehouseProductId)
    {
        $this->db->query('DELETE FROM '.DB_PREFIX."warehouse_product WHERE warehouse_product_id = '".(int) $this->db->escape($warehouseProductId)."' ");

        return true;
    }

    /**
     * [checkAdmin check admin].
     *
     * @param [type] $user_id [user id]
     *
     * @return [type] [array]
     */
    public function checkAdmin($user_id)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse WHERE user_id = '".(int) $this->db->escape($user_id)."'");

        return $query->row;
    }

    /**
     * [checkWarehouseCode check whether warehouse code have already used or not].
     *
     * @param [type] $warehousecode [warehouse code]
     * @param bool   $warehouse_id  [warehouse id]
     *
     * @return [type] [array]
     */
    public function checkWarehouseCode($warehousecode, $warehouse_id = false)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse WHERE warehouse_code = '".$this->db->escape($warehousecode)."' AND warehouse_id != '".$this->db->escape($warehouse_id)."'");

        return $query->row;
    }

    /**
     * [checkWarehouseTitle check whether warehouse title have already used or not].
     *
     * @param [type] $title        [warehouse title]
     * @param bool   $warehouse_id [warehouse id]
     *
     * @return [type] [array]
     */
    public function checkWarehouseTitle($title, $warehouse_id = false)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse WHERE title = '".$this->db->escape($title)."' AND warehouse_id != '".$this->db->escape($warehouse_id)."'");

        return $query->row;
    }

    /**
     * [checkWarehousePostcode check whether warehouse posecode have already used or not].
     *
     * @param [type] $postalcode   [warehouse postcode]
     * @param bool   $warehouse_id [warehouse id]
     *
     * @return [type] [array]
     */
    public function checkWarehousePostcode($postalcode, $warehouse_id = false)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse WHERE postal_code = '".$this->db->escape($postalcode)."' AND warehouse_id != '".$this->db->escape($warehouse_id)."'");

        return $query->row;
    }

    /**
     * [getUserGroupId fetch warehouse group id].
     *
     * @return [type] [array]
     */
    public function getUserGroupId()
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."user_group WHERE name = 'Warehouse'");

        return $query->row;
    }

    /**
     * [getUser fetch user].
     *
     * @param [type] $data [user detail]
     *
     * @return [type] [array]
     */
    public function getUser($user_group_id)
    {
        $result = $this->db->query('SELECT * FROM '.DB_PREFIX."user WHERE user_group_id = '".(int) $this->db->escape($user_group_id)."'")->rows;

        return $result;
    }

    /**
     * [getWarehouseUser fetch warehouse users].
     *
     * @return [type] [array]
     */
    public function getWarehouseManagers()
    {
        $result = $this->db->query("SELECT u.user_id,CONCAT(u.firstname, ' ', u.lastname) as name FROM ".DB_PREFIX."user u WHERE u.user_group_id = '".$this->config->get('wk_dropship_user_group')."' ")->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * [getExtensions fetch extension].
     *
     * @param [type] $type [shipping]
     *
     * @return [type] [array]
     */
    public function getExtensions($type)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."extension WHERE `type` = '".$this->db->escape($type)."'");

        return $query->rows;
    }

    /**
     * [createWarehouseTable create warehouse tables at the time of installation].
     *
     * @return [type] [no]
     */
    public function createWarehouseTable()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse` (
            `warehouse_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `status` int(11) NOT NULL,
            `warehouse_code` varchar(200) NOT NULL,
            `title` varchar(200) NOT NULL,
            `description` varchar(500) NOT NULL,
            `country_id` int(50) NOT NULL,
            `zone_id` int(50) NOT NULL,
            `city` varchar(200) NOT NULL,
            `postal_code` varchar(200) NOT NULL,
            `street` varchar(200) NOT NULL,
            `longitude` varchar(200) NOT NULL,
            `latitude` varchar(200) NOT NULL,
            PRIMARY KEY (`warehouse_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_seller` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `seller_name` varchar(100) NOT NULL,
            `date_added` date NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_order` (
            `order_id` int(11) NOT NULL,
            `customer_name` varchar(100) NOT NULL,
            `status` tinyint(1) NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ali_product_id` double NOT NULL,
            `aliexpress_seller_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `product_url` varchar(1000) NOT NULL,
            `name` varchar(1000) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        if (!$this->checkForUpdate()) {
            $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `alix_option_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

            $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option_value` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `oc_option_id` int(11) NOT NULL,
                `alix_option_id` int(11) NOT NULL,
                `oc_option_value_id` int(11) NOT NULL,
                `alix_option_value_id` int(11) NOT NULL,
                `value` varchar(200) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

            $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `variation_text` varchar(400) NOT NULL,
                `variation_name` varchar(400) NOT NULL,
                `price` float NOT NULL,
                `price_prefix` varchar(10) NOT NULL,
                `quantity` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

            $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation_option` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `variation_id` int(11) NOT NULL,
                `option_value_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');
        }
        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_order` (
            `warehouse_order_id` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(100) NOT NULL,
            `warehouse_id` int(100) NOT NULL,
            `quantity` int(11) NOT NULL,
            `price` double NOT NULL,
            `warehouseAmount` double NOT NULL,
            `adminAmount` double NOT NULL,
            `product_id` int(11) NOT NULL,
            `total` double NOT NULL,
            `order_currency` varchar(50) NOT NULL,
            `paid_status` tinyint(4) NOT NULL,
            PRIMARY KEY (`warehouse_order_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_order_shipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `title` varchar(200) NOT NULL,
            `code` varchar(50) NOT NULL,
            `cost` double NOT NULL,
            `currency` varchar(50) NOT NULL,
            `paid` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_product` (
            `warehouse_product_id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(100) NULL,
            `user_id` int(11) NOT NULL,
            `product_id` int(100) NULL,
            `quantity` int(11) NOT NULL,
            `price` double NOT NULL,
            `price_diff` double NOT NULL,
            `approved` tinyint(1) NOT NULL,
            PRIMARY KEY (`warehouse_product_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_shipping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `warehouse_id` int(100) NOT NULL,
            `country_code` varchar(200) NOT NULL,
            `zip_from` varchar(100) NOT NULL,
            `zip_to` varchar(100) NOT NULL,
            `price` varchar(100) NOT NULL,
            `weight_from` varchar(100) NOT NULL,
            `weight_to` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_shippings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `code` varchar(100) NOT NULL,
            `status` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_transaction` (
            `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `total` double NOT NULL,
            `description` varchar(500) NOT NULL,
            `date_added` datetime NOT NULL,
            `status` tinyint(4) NOT NULL,
            PRIMARY KEY (`transaction_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_transaction_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` int(11) NOT NULL,
            `warehouse_order_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `warehouse_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `amount` double NOT NULL,
            `description` varchar(500) NOT NULL,
            `date_added` datetime NOT NULL,
            `status` tinyint(4) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'price_rule` (
            `rule_id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `channel` varchar(10) NOT NULL,
            `category_id` int(11) NOT NULL,
            `category_relation` varchar(10) NOT NULL,
            `price_from` double NOT NULL,
            `price_to` double NOT NULL,
            `operation_type` varchar(10) NOT NULL,
            `method_type` varchar(10) NOT NULL,
            `amount` double NOT NULL,
            PRIMARY KEY (`rule_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');
    }

    public function dropWarehouseTable()
    {
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_seller`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_order`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_option`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_option_value`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation_option`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_variation`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_order`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_order_shipping`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_product`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_shipping`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_shippings`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_transaction`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_transaction_details`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'price_rule`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option`');
        $this->db->query('DROP TABLE IF EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option_value`');
    }

    /**
     * [addWarehouse add warehouse details].
     *
     * @param [type] $data [warehouse details]
     */
    public function addWarehouse($data)
    {
        $this->db->query('INSERT INTO `'.DB_PREFIX."warehouse` SET user_id = '".(int) $this->db->escape($data['user'])."', warehouse_code = '".$this->db->escape($data['warehousecode'])."', title = '".$this->db->escape($data['title'])."', description = '".$this->db->escape($data['description'])."', country_id = '".(int) $this->db->escape($data['country_id'])."',zone_id = '".(int) $this->db->escape($data['zone_id'])."',city = '".$this->db->escape($data['origincity'])."',postal_code = '".$this->db->escape($data['postalcode'])."',street = '".$this->db->escape($data['originstreet'])."',longitude = '".$this->db->escape($data['longitude'])."',latitude = '".$this->db->escape($data['latitude'])."',status = '".(int) $this->db->escape($data['status'])."' ");

        $warehouse_id = $this->db->getLastId();

        if (isset($data['shippingmethods']) && $data['shippingmethods']) {
            foreach ($data['shippingmethods'] as $key => $shippingmethod) {
                $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_shippings SET user_id = '".(int) $this->db->escape($data['user'])."', warehouse_id = '".(int) $this->db->escape($warehouse_id)."', code = '".$this->db->escape($shippingmethod)."', status = '1' ");
            }
        }

        return $warehouse_id;
    }

    /**
     * [deleteWarehouse delete warehouse details].
     *
     * @param [type] $warehouse_id [warehouse id]
     * @param [type] $product_id   [product id]
     *
     * @return [type] [no]
     */
    public function deleteWarehouse($warehouse_id, $product_id)
    {
        $this->db->query('DELETE FROM `'.DB_PREFIX."warehouse_product` WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."' AND product_id='".$this->db->escape($product_id)."'");
    }

    public function deleteWarehousecompletely($warehouse_id)
    {
        $this->db->query('DELETE FROM `'.DB_PREFIX."warehouse` WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'");

        $this->db->query('DELETE FROM `'.DB_PREFIX."warehouse_product` WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'");

        $this->db->query('DELETE FROM `'.DB_PREFIX."warehouse_order` WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'");

        $this->db->query('DELETE FROM `'.DB_PREFIX."warehouse_shipping` WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'");
    }

    /**
     * [getTotalWarehouseNames fetch total warehouse names].
     *
     * @return [type] [array]
     */
    public function getTotalWarehouseNames()
    {
        $query = $this->db->query('SELECT * FROM `'.DB_PREFIX.'warehouse`');

        return $query->rows;
    }

    /**
     * [getWarehouseInfo fetch warehouse information].
     *
     * @param [type] $product_id [product id]
     *
     * @return [type] [array]
     */
    public function getWarehouseInfo($product_id)
    {
        $query = $this->db->query('SELECT warehouse_id FROM `'.DB_PREFIX."warehouse_product` WHERE product_id= '".(int) $this->db->escape($product_id)."'");

        return $query->row;
    }

    /**
     * [assignWarehouse assign product to the warehouse].
     *
     * @param [type] $data       [details]
     * @param [type] $product_id [product id]
     *
     * @return [type] [no]
     */
    public function assignWarehouse($data, $product_id)
    {
        $this->db->query('INSERT INTO `'.DB_PREFIX."warehouse_product` SET product_id= '".(int) $this->db->escape($product_id)."',warehouse_id = '".(int) $this->db->escape($data['warehouse'])."'");
    }

    /**
     * [updateAssignWarehouse update product quantity of warehouse].
     *
     * @param [type] $data       [warehouse details]
     * @param [type] $product_id [product id]
     *
     * @return [type] [no]
     */
    public function updateAssignWarehouse($data, $product_id)
    {
        $getid = $this->getWarehouseInfo($product_id);

        if ($getid) {
            $this->db->query('UPDATE `'.DB_PREFIX."warehouse_product` SET warehouse_id = '".(int) $this->db->escape($data['warehouse'])."' WHERE product_id= '".(int) $this->db->escape($product_id)."'");
        } else {
            $this->db->query('INSERT INTO `'.DB_PREFIX."warehouse_product` SET warehouse_id = '".(int) $this->db->escape($data['warehouse'])."', product_id= '".(int) $this->db->escape($product_id)."'");
        }
    }

    /**
     * [getWarehouses fetch warehouses].
     *
     * @param array $data [filter array]
     *
     * @return [type] [array]
     */
    public function getWarehouses($data = array())
    {
        $sql = 'SELECT * FROM `'.DB_PREFIX.'warehouse` w LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id = w.user_id) LEFT JOIN '.DB_PREFIX.'country c ON (c.country_id=w.country_id) WHERE 1 = 1 ';

        if (isset($data['filter_title']) && $data['filter_title']) {
            $sql .= " AND w.title LIKE '".$this->db->escape($data['filter_title'])."%'";
        }

        if (isset($data['filter_contactname']) && $data['filter_contactname']) {
            $sql .= " AND CONCAT(u.firstname,' ', u.lastname) LIKE '".$this->db->escape($data['filter_contactname'])."%'";
        }

        if (isset($data['filter_user']) && $data['filter_user']) {
            $sql .= " AND u.username LIKE '".$this->db->escape($data['filter_user'])."%' ";
        }

        if (isset($data['filter_description']) && $data['filter_description']) {
            $sql .= " AND w.description LIKE '".$this->db->escape($data['filter_description'])."%'";
        }

        if (isset($data['filter_origincountry']) && $data['filter_origincountry']) {
            $sql .= " AND w.country_id = '".$this->db->escape($data['filter_origincountry'])."'";
        }

        if (isset($data['filter_originstreet']) && $data['filter_originstreet']) {
            $sql .= " AND w.street LIKE '".$this->db->escape($data['filter_originstreet'])."%'";
        }

        if (isset($data['filter_zip']) && $data['filter_zip']) {
            $sql .= " AND w.postal_code = '".$this->db->escape($data['filter_zip'])."' ";
        }

        $sort_data = array(
            'w.title',
            'u.firstname',
            'u.username',
            'w.description',
            'w.street',
            'c.name',
            'w.postal_code',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY w.title';
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

    /**
     * [getWarehouse fetch warehouse details].
     *
     * @param [type] $warehouse_id [warehouse id]
     *
     * @return [type] [array]
     */
    public function getWarehouse($warehouse_id)
    {
        $result = $this->db->query('SELECT w.* FROM `'.DB_PREFIX."warehouse` w  WHERE w.warehouse_id = '".(int) $this->db->escape($warehouse_id)."' ")->row;
        $result['shipping_methods'] = $this->db->query('SELECT code FROM '.DB_PREFIX."warehouse_shippings WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'")->rows;

        return $result;
    }

    public function checkWarehouse($warehouse_id)
    {
        $result = $this->db->query('SELECT w.* FROM `'.DB_PREFIX."warehouse` w  WHERE w.warehouse_id = '".(int) $this->db->escape($warehouse_id)."' ")->row;

        return $result;
    }

    /**
     * [editWarehouse edit warehouse details].
     *
     * @param [type] $warehouse_id [warehouse id]
     * @param [type] $data         [warehouse details]
     *
     * @return [type] [no]
     */
    public function editWarehouse($warehouse_id, $data)
    {
        $this->db->query('UPDATE `'.DB_PREFIX."warehouse` SET user_id = '".(int) $this->db->escape($data['user'])."', warehouse_code = '".$this->db->escape($data['warehousecode'])."', title = '".$this->db->escape($data['title'])."', description = '".$this->db->escape($data['description'])."', country_id = '".(int) $this->db->escape($data['country_id'])."',zone_id = '".(int) $this->db->escape($data['zone_id'])."',city = '".$this->db->escape($data['origincity'])."',postal_code = '".$this->db->escape($data['postalcode'])."',street = '".$this->db->escape($data['originstreet'])."',longitude = '".$this->db->escape($data['longitude'])."',latitude = '".$this->db->escape($data['latitude'])."',status = '".(int) $this->db->escape($data['status'])."' WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."'");

        $this->db->query('DELETE FROM '.DB_PREFIX."warehouse_shippings WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."' ");

        if (isset($data['shippingmethods']) && $data['shippingmethods']) {
            foreach ($data['shippingmethods'] as $key => $shippingmethod) {
                $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_shippings SET user_id = '".(int) $this->db->escape($data['user'])."', warehouse_id = '".(int) $this->db->escape($warehouse_id)."', code = '".$this->db->escape($shippingmethod)."', status = '1' ");
            }
        }
    }

    /**
     * [getTotalWarehouses get total warehouses].
     *
     * @return [type] [integer]
     */
    public function getTotalWarehouses($data)
    {
        $sql = 'SELECT * FROM `'.DB_PREFIX.'warehouse` w LEFT JOIN '.DB_PREFIX.'user u ON (u.user_id = w.user_id) WHERE 1 = 1 ';

        if (isset($data['filter_title']) && $data['filter_title']) {
            $sql .= " AND w.title LIKE '".$this->db->escape($data['filter_title'])."%'";
        }

        if (isset($data['filter_contactname']) && $data['filter_contactname']) {
            $sql .= " AND CONCAT(u.firstname,' ', u.lastname) LIKE '".$this->db->escape($data['filter_contactname'])."%'";
        }

        if (isset($data['filter_user']) && $data['filter_user']) {
            $sql .= " AND w.warehouse_code LIKE '".$this->db->escape($data['filter_user'])."%' ";
        }

        if (isset($data['filter_description']) && $data['filter_description']) {
            $sql .= " AND w.description LIKE '".$this->db->escape($data['filter_description'])."%'";
        }

        if (isset($data['filter_origincountry']) && $data['filter_origincountry']) {
            $sql .= " AND w.country_id = '".$this->db->escape($data['filter_origincountry'])."'";
        }

        if (isset($data['filter_originstreet']) && $data['filter_originstreet']) {
            $sql .= " AND w.street LIKE '".$this->db->escape($data['filter_originstreet'])."%'";
        }

        if (isset($data['filter_zip']) && $data['filter_zip']) {
            $sql .= " AND w.postal_code = '".$this->db->escape($data['filter_zip'])."' ";
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    /**
     * [getProducts get product description].
     *
     * @param array  $data       [filtter array]
     * @param [type] $product_id [product id]
     *
     * @return [type] [array]
     */
    public function getProducts($data = array(), $product_id)
    {
        $sql = 'SELECT * FROM `'.DB_PREFIX.'product` cf LEFT JOIN `'.DB_PREFIX."product_description` cfd ON (cf.product_id = cfd.product_id) WHERE cf.product_id='".(int) $this->db->escape($product_id)."'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND cfd.name LIKE '".$this->db->escape($data['filter_name'])."%'";
        }

        $sort_data = array(
            'cfd.name',
            'cf.model',
            'cf.quantity',
            'cf.price',
            'cf.status',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY '.$data['sort'];
        } else {
            $sql .= ' ORDER BY cfd.name';
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
        }

        $query = $this->db->query($sql);

        return $query->row;
    }

    /**
     * [getTotalProduct get total product].
     *
     * @return [type] [integer]
     */
    public function getTotalProduct()
    {
        $query = $this->db->query('SELECT COUNT(*) AS total FROM `'.DB_PREFIX.'warehouse_product`');

        return $query->row['total'];
    }

    /**
     * [getTotalWarehouseProduct get total warehouse products].
     *
     * @return [type] [array]
     */
    public function getTotalWarehouseProduct()
    {
        $query = $this->db->query('SELECT product_id FROM `'.DB_PREFIX.'warehouse_product`');

        return $query->rows;
    }

    /**
     * [checkQuantity check warehouse products quantities].
     *
     * @param [type] $product_id [product id]
     *
     * @return [type] [array]
     */
    public function checkQuantity($product_id)
    {
        $query = $this->db->query('SELECT * FROM `'.DB_PREFIX."warehouse_product` WHERE product_id= '".(int) $this->db->escape($product_id)."'");

        return $query->rows;
    }

    /**
     * [getProductWarehouse fetch product of warehouse].
     *
     * @param array  $data       [filter array]
     * @param [type] $product_id [product id]
     *
     * @return [type] [array]
     */
    public function getProductWarehouse($data = array(), $product_id)
    {
        $sql = 'SELECT * FROM `'.DB_PREFIX.'warehouse_product` cf LEFT JOIN `'.DB_PREFIX."warehouse` cfd ON (cf.warehouse_id = cfd.warehouse_id) WHERE cf.product_id='".(int) $this->db->escape($product_id)."'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND cfd.title LIKE '".$this->db->escape($data['filter_name'])."%'";
        }

        $sort_data = array(
            'cfd.title',
            'cf.quantity',
            'cfd.status',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY cfd.title';
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
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * [updateWarehouseProduct update warehouse product quantity].
     *
     * @param [type] $quantity     [quantity]
     * @param [type] $warehouse_id [warehouse id]
     * @param [type] $product_id   [product id]
     *
     * @return [type] [array]
     */
    public function updateWarehouseProduct($quantity, $warehouse_id, $product_id)
    {
        $query = $this->db->query('SELECT quantity FROM `'.DB_PREFIX."warehouse_product` WHERE warehouse_id= '".(int) $this->db->escape($warehouse_id)."' AND product_id= '".(int) $this->db->escape($product_id)."'");

        $update_quantity = $quantity + $query->row['quantity'];

        $this->db->query('UPDATE `'.DB_PREFIX.'warehouse_product` SET quantity= '.(int) $this->db->escape($update_quantity)." WHERE warehouse_id = '".(int) $this->db->escape($warehouse_id)."' AND product_id= '".(int) $this->db->escape($product_id)."'");
    }

    /**
     * [checkProductQuantity check product quantity in warehouse].
     *
     * @param [type] $product_id   [product id]
     * @param [type] $warehouse_id [warehouse id]
     *
     * @return [type] [array]
     */
    public function checkProductQuantity($product_id, $warehouse_id)
    {
        $query = $this->db->query('SELECT quantity FROM `'.DB_PREFIX."warehouse_product` WHERE warehouse_id= '".(int) $this->db->escape($warehouse_id)."' AND product_id= '".(int) $this->db->escape($product_id)."'");

        return $query->row;
    }

    /**
     * [getTotalWarehouse get total warehouse].
     *
     * @param [type] $product_id [product id]
     *
     * @return [type] [integer]
     */
    public function getTotalWarehouse($product_id)
    {
        $query = $this->db->query('SELECT COUNT(*) AS total FROM `'.DB_PREFIX."warehouse_product` WHERE product_id='".(int) $this->db->escape($product_id)."'");

        return $query->row['total'];
    }

    /**
     * [addAlliexpressAttrGroup creates attribute group for aliexpress products ].
     *
     * @param [type] $language [aleexpress language id]
     *
     * @return [type] [integer]
     */
    public function addAlliexpressAttrGroup($language_id)
    {
        $this->db->query('INSERT INTO '.DB_PREFIX."attribute_group SET sort_order = '0'");

        $attribute_group_id = $this->db->getLastId();

        $this->db->query('INSERT INTO '.DB_PREFIX."attribute_group_description SET attribute_group_id = '".(int) $this->db->escape($attribute_group_id)."', language_id = '".(int) $this->db->escape($language_id)."', name = 'Aliexpress'");

        return $attribute_group_id;
    }

    /**
     * [checkAttributeGroupExists checks aliexpress attribute group exists  ].
     *
     * @param [type] $attribute_group_id [attribute group id]
     *
     * @return [type] [boolean]
     */
    public function checkAttributeGroupExists($attribute_group_id)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."attribute_group WHERE attribute_group_id = '".(int) $this->db->escape($attribute_group_id)."'");

        if ($query->row) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [addAlliexpressVariationOption creates option for aliexpress products variation ].
     *
     * @param [type] $language [aleexpress language id]
     *
     * @return [type] [integer]
     */
    public function addAlliexpressVariationOption($data = array())
    {
        $this->db->query('INSERT INTO `'.DB_PREFIX."option` SET type = 'select', sort_order = '0'");

        $option_id = $this->db->getLastId();

        foreach ($data as $language_id => $value) {
            $this->db->query('INSERT INTO '.DB_PREFIX."option_description SET option_id = '".(int) $option_id."', language_id = '".(int) $language_id."', name = '".$this->db->escape($value)."'");
        }

        return $option_id;
    }

    /**
     * [editAlliexpressVariationOption update option for aliexpress products variation ].
     *
     * @param [type] $language [aliexpress language id]
     *
     * @return [type] [integer]
     */
    public function editAlliexpressVariationOption($data = array(), $option_id)
    {
        $this->db->query('DELETE FROM '.DB_PREFIX."option_description WHERE option_id = '".(int) $option_id."'");

        foreach ($data as $language_id => $value) {
            $this->db->query('INSERT INTO '.DB_PREFIX."option_description SET option_id = '".(int) $option_id."', language_id = '".(int) $language_id."', name = '".$this->db->escape($value)."'");
        }
    }

    /**
     * [checkVariationOptionExists checks aliexpress variation option exists  ].
     *
     * @param [type] $option_id [option id]
     *
     * @return [type] [boolean]
     */
    public function checkVariationOptionExists($option_id)
    {
        $query = $this->db->query('SELECT * FROM `'.DB_PREFIX."option` WHERE option_id = '".(int) $this->db->escape($option_id)."'");

        if ($query->row) {
            return true;
        } else {
            return false;
        }
    }

    public function getAlliexpressVariationOption($option_id)
    {
        $option_data = array();

        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."option_description WHERE option_id = '".(int) $option_id."'");

        foreach ($query->rows as $result) {
            $option_data[$result['language_id']] = $result['name'];
        }

        return $option_data;
    }

    public function getTotalSellers($data = array())
    {
        $sql = 'SELECT COUNT(DISTINCT seller_name) AS total FROM '.DB_PREFIX.'warehouse_aliexpress_seller WHERE 1=1';

        if (!empty($data['filter_seller_name'])) {
            $sql .= " AND seller_name LIKE '".$this->db->escape($data['filter_seller_name'])."%'";
        }

        if (!empty($data['filter_import_from'])) {
            $sql .= " AND date_added >= '".$this->db->escape($data['filter_import_from'])."'";
        }

        if (!empty($data['filter_import_to'])) {
            $sql .= " AND date_added <= '".$this->db->escape($data['filter_import_to'])."'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getSellers($data = array())
    {
        $sql = 'SELECT was.*, count(wap.id) as products FROM '.DB_PREFIX.'warehouse_aliexpress_seller was LEFT JOIN '.DB_PREFIX.'warehouse_aliexpress_product wap ON (was.id = wap.aliexpress_seller_id) WHERE 1=1';

        if (!empty($data['filter_seller_name'])) {
            $sql .= " AND was.seller_name LIKE '".$this->db->escape($data['filter_seller_name'])."%'";
        }

        if (!empty($data['filter_import_from'])) {
            $sql .= " AND was.date_added >= '".$this->db->escape($data['filter_import_from'])."'";
        }

        if (!empty($data['filter_import_to'])) {
            $sql .= " AND was.date_added <= '".$this->db->escape($data['filter_import_to'])."'";
        }

        $sql .= ' GROUP BY was.id';

        $sort_data = array(
            'was.seller_name',
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= ' ORDER BY '.$this->db->escape($data['sort']);
        } else {
            $sql .= ' ORDER BY was.id';
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

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function deleteSeller($aliexpress_seller_id)
    {
        $getSellerProducts = $this->db->query('SELECT * FROM '.DB_PREFIX."warehouse_aliexpress_product WHERE aliexpress_seller_id = '".(int) $this->db->escape($aliexpress_seller_id)."'")->rows;

        $this->load->model('catalog/product');

        foreach ($getSellerProducts as $product) {
            $this->model_catalog_product->deleteProduct($product['product_id']);
        }

        $this->db->query('DELETE FROM '.DB_PREFIX."warehouse_aliexpress_seller WHERE id = '".(int) $this->db->escape($aliexpress_seller_id)."'");
    }

    public function extensionUpdate()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_order` (
        `order_id` int(11) NOT NULL,
        `customer_name` varchar(100) NOT NULL,
        `status` tinyint(1) NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_variation` (
        `option_id` int(11) NOT NULL,
        `option_value_id` int(11) NOT NULL,
        `aliexpress_variation` varchar(100) NOT NULL,
        `aliexpress_variation_text` text NOT NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `oc_option_id` int(11) NOT NULL,
            `alix_option_id` int(11) NOT NULL,
            `value` varchar(200) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_option_value` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `oc_option_id` int(11) NOT NULL,
            `alix_option_id` int(11) NOT NULL,
            `oc_option_value_id` int(11) NOT NULL,
            `alix_option_value_id` int(11) NOT NULL,
            `value` varchar(200) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `variation_text` varchar(400) NOT NULL,
            `variation_name` varchar(400) NOT NULL,
            `price` float NOT NULL,
            `price_prefix` varchar(10) NOT NULL,
            `quantity` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $this->db->query('CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'warehouse_aliexpress_product_variation_option` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `variation_id` int(11) NOT NULL,
            `option_value_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ');

        $check_quantity = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_DATABASE."' AND TABLE_NAME = '".DB_PREFIX."warehouse_aliexpress_product_variation' AND COLUMN_NAME = 'quantity'")->row;

        if (!$check_quantity) {
            $this->db->query('ALTER TABLE `'.DB_PREFIX.'warehouse_aliexpress_product_variation` ADD COLUMN `quantity` int(11) NOT NULL DEFAULT '.$this->config->get('wk_dropship_aliexpress_quantity'));
        }

        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."warehouse_aliexpress_option_value' ";

        $result = $this->db->query($sql)->row;
        if ($result) {
            $result = $this->db->query('SELECT * FROM '.DB_PREFIX.'warehouse_aliexpress_option ')->rows;
            if ($result) {
                foreach ($result as $key => $value) {
                    if (isset($value['alix_option_id'])) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option SET id = '".$value['id']."', oc_option_id = '".$value['oc_option_id']."', alix_option_id = '".$value['alix_option_id']."', value = '".$value['value']."' ");
                    }
                }
            }

            $result = $this->db->query('SELECT * FROM '.DB_PREFIX.'warehouse_aliexpress_option_value ')->rows;
            if ($result) {
                foreach ($result as $key => $value) {
                    if (isset($value['alix_option_id'])) {
                        $this->db->query('INSERT INTO '.DB_PREFIX."warehouse_aliexpress_product_option_value SET id = '".$value['id']."', oc_option_id = '".$value['oc_option_id']."', alix_option_id = '".$value['alix_option_id']."', oc_option_value_id = '".$value['oc_option_value_id']."', alix_option_value_id = '".$value['alix_option_value_id']."', value = '".$value['value']."' ");
                    }
                }
            }
        }
    }
}
