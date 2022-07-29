<?php

class ModelAliexpressAssignwarehouse extends Model
{
    public function pushToStore($id)
    {
        $this->db->query('UPDATE '.DB_PREFIX."product SET status = 1 WHERE product_id = '".(int) $id."' ");

        return true;
    }

    public function getAliExpressProducts($data)
    {
        $sql = 'SELECT wap.*,p.status,p.image,p.date_added FROM '.DB_PREFIX.'warehouse_aliexpress_product wap LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wap.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '".(int) $this->config->get('config_language_id')."'";

        if (isset($data['filter_product_name']) && $data['filter_product_name']) {
            $sql .= " AND wap.name LIKE '%".$this->db->escape($data['filter_product_name'])."%' ";
        }

        if (isset($data['filter_ali_product_id']) && $data['filter_ali_product_id']) {
            $sql .= " AND wap.ali_product_id = '".(int) $data['filter_ali_product_id']."' ";
        }

        if (isset($data['filter_product_status'])) {
            $sql .= ' AND p.status = '.(int) $data['filter_product_status'].' ';
        }

        if (isset($data['filter_aliexpress_seller_id'])) {
            $sql .= ' AND wap.aliexpress_seller_id = '.(int) $data['filter_aliexpress_seller_id'].' ';
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

    public function getAliExpressProductsTotal($data)
    {
        $sql = 'SELECT wap.*,p.status,p.image FROM '.DB_PREFIX.'warehouse_aliexpress_product wap LEFT JOIN '.DB_PREFIX.'product p ON (p.product_id=wap.product_id) LEFT JOIN '.DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '".(int) $this->config->get('config_language_id')."'";

        if (isset($data['filter_product_name']) && $data['filter_product_name']) {
            $sql .= " AND wap.name LIKE '%".$this->db->escape($data['filter_product_name'])."%' ";
        }

        if (isset($data['filter_ali_product_id']) && $data['filter_ali_product_id']) {
            $sql .= " AND wap.ali_product_id = '".(int) $data['filter_ali_product_id']."' ";
        }

        if (isset($data['filter_product_status'])) {
            $sql .= ' AND p.status = '.(int) $data['filter_product_status'].' ';
        }

        if (isset($data['filter_aliexpress_seller_id'])) {
            $sql .= ' AND wap.aliexpress_seller_id = '.(int) $data['filter_aliexpress_seller_id'].' ';
        }

        $result = $this->db->query($sql)->rows;
        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function getProducts($data = array())
    {
        $user_id = $this->getUserIdByWarehouseId($data['selected_warehouse']);
        $sql = 'SELECT cf.product_id,cf.model,cf.image,cf.quantity,cf.price,cf.status,cfd.name FROM `'.DB_PREFIX.'product` cf LEFT JOIN '.DB_PREFIX."product_description cfd ON (cf.product_id = cfd.product_id) WHERE cfd.language_id = '".(int) $this->config->get('config_language_id')."' ";

        if (isset($data['selected_warehouse']) && $data['selected_warehouse']) {
            $sql .= ' AND cfd.product_id NOT IN (SELECT wp.product_id FROM '.DB_PREFIX.'warehouse_product wp LEFT JOIN '.DB_PREFIX."warehouse w ON (w.user_id=wp.user_id) WHERE w.user_id != '".(int) $user_id."' ) ";
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND cfd.name LIKE '".$this->db->escape($data['filter_name'])."%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND cf.model LIKE '".$this->db->escape($data['filter_model'])."%'";
        }

        if (isset($data['sort'])) {
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

            $sql .= ' LIMIT '.$data['start'].', '.$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getDropshipPrice($product_id)
    {
        $productInfo = $this->getProductInfoForDropshippingPrice($product_id);
        if ($productInfo) {
            foreach ($productInfo as $key => $value) {
                $result = $this->db->query('SELECT * FROM '.DB_PREFIX."price_rule WHERE price_from <= '".(int) $value['price']."' AND price_to >= '".(int) $value['price']."' ORDER BY rule_id DESC ")->row;
                if ($result) {
                    $price = $value['price'];
                    if ('p' == $result['method_type']) {
                        $added_price = $this->getPriceByPercentage($value['price'], $result['amount'], $result['operation_type']);
                    } else {
                        $added_price = $result['amount'];
                    }
                    if ($added_price) {
                        switch ($result['operation_type']) {
                            case '*':
                            $price = $price * $added_price;
                            break;
                            case '+':
                            $price = $price + $added_price;
                            break;
                            case '-':
                            $price = $price - $added_price;
                            break;
                        }
                    }
                    if ($price) {
                        return $price;
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }

    public function getPriceByPercentage($price, $value, $operation_type)
    {
        $toBeAdded = ($price * $value) / 100;

        return $toBeAdded;
    }

    public function getProductInfoForDropshippingPrice($product_id)
    {
        $sql = 'SELECT p.price FROM '.DB_PREFIX."product p WHERE p.product_id = '".(int) $product_id."' ";
        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * [getTotalWarehouseNames get total warehouse names].
     *
     * @return [type] [array]
     */
    public function getTotalWarehouseNames($status = false)
    {
        $sql = 'SELECT * FROM '.DB_PREFIX."warehouse w WHERE w.status = '".(int) $status."' ";
        $result = $this->db->query($sql)->rows;
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * [checkQuantity check quantity].
     *
     * @param [type] $product_id [product id]
     *
     * @return [type] [array]
     */
    public function checkQuantity($product_id, $warehouse_id)
    {
        $sql = 'SELECT quantity FROM `'.DB_PREFIX."warehouse_product` WHERE product_id= '".(int) $product_id."' AND warehouse_id = '".(int) $warehouse_id."' ";
        // if(isset($warehouse_id) && $warehouse_id) {
        //  $sql .= " AND warehouse_id = '".(int)$warehouse_id."' ";
        // }
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result['quantity'];
        } else {
            return false;
        }
    }

    /**
     * [checkWarehouseQuantity get warehouse product quantity].
     *
     * @param [type] $warehouse_id [warehouse id]
     * @param [type] $product_id   [product id]
     *
     * @return [type] [array]
     */
    public function checkWarehouseQuantity($quantity, $warehouse_id, $product_id)
    {
        $product = $this->db->query('SELECT quantity FROM '.DB_PREFIX."product WHERE product_id = '".(int) $product_id."' ")->row;
        $result = $this->db->query('SELECT SUM(quantity) as quantity FROM '.DB_PREFIX."warehouse_product WHERE product_id= '".(int) $product_id."' AND warehouse_id != '".(int) $warehouse_id."' ")->row;
        $leftQuantity = 0;
        if (isset($product['quantity']) && $product['quantity']) {
            $leftQuantity = $product['quantity'] - $warehouse['quantity'];
            if ($leftQuantity && $leftQuantity > $quantity) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function checkProductExistsInWarehouse($product_id)
    {
        $result = $this->db->query('SELECT product_id FROM '.DB_PREFIX."warehouse_product WHERE product_id = '".(int) $product_id."' ")->row;
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getProductPriceForWarehouse($product_id)
    {
        $result = $this->db->query('SELECT price FROM '.DB_PREFIX."warehouse_product WHERE product_id = '".(int) $product_id."' ")->row;
        if ($result) {
            return $result['price'];
        } else {
            $result = $this->db->query('SELECT price FROM '.DB_PREFIX."product WHERE product_id = '".(int) $product_id."' ")->row;
            if ($result) {
                return $result['price'];
            }
        }

        return false;
    }

    public function getProductPrice($product_id)
    {
        $result = $this->db->query('SELECT price FROM '.DB_PREFIX."product WHERE product_id = '".$product_id."' ")->row;
        if ($result) {
            return $result['price'];
        } else {
            return false;
        }
    }

    /**
     * [addProducts add products to warehouse].
     *
     * @param [type] $quantity     [quantity]
     * @param [type] $product_id   [product id]
     * @param [type] $warehouse_id [warehouse id]
     */
    public function addProducts($quantity, $product_id, $warehouse_id)
    {
        $user_id = 0;
        $diffPrice = 0;
        $status = 0;
        if ($this->config->get('wk_dropship_user_group') != $this->user->getUserGroup()) {
            $status = $this->config->get('wk_dropship_direct_to_store');
        } else {
            $status = 1;
        }

        $warehousePrice = $this->getProductPriceForWarehouse($product_id);

        if (!$this->checkProductExistsInWarehouse($product_id)) {
            $dropshipPrice = $this->getDropshipPrice($product_id);
            if ($dropshipPrice) {
                $this->db->query('UPDATE '.DB_PREFIX."product SET price = '".$this->db->escape($dropshipPrice)."' WHERE product_id = '".(int) $product_id."' ");
            }
        } else {
            $dropshipPrice = $this->getProductPrice($product_id);
        }

        if ($dropshipPrice && $warehousePrice) {
            $diffPrice = $dropshipPrice - $warehousePrice;
        }

        if ($warehouse_id) {
            $user_id = $this->getUserIdByWarehouseId($warehouse_id);
        }
        $query = $this->db->query('SELECT quantity FROM `'.DB_PREFIX."warehouse_product` WHERE warehouse_id= '".$warehouse_id."' AND product_id= '".$product_id."'");

        if (!empty($query->row)) {
            $update_quantity = $quantity + $query->row['quantity'];
            $this->db->query('UPDATE `'.DB_PREFIX.'warehouse_product` SET quantity= '.(int) $update_quantity." WHERE warehouse_id = '".(int) $warehouse_id."' AND product_id= '".(int) $product_id."' ");
        } else {
            $this->db->query('INSERT INTO `'.DB_PREFIX."warehouse_product` SET warehouse_id = '".(int) $warehouse_id."', product_id= '".(int) $product_id."', quantity= ".(int) $quantity.", user_id = '".(int) $user_id."', price = '".$this->db->escape($warehousePrice)."', price_diff = '".$this->db->escape($diffPrice)."', approved = '".(int) $status."' ");
        }
        $this->updateMainProductQuantity($product_id);
    }

    public function getUserIdByWarehouseId($warehouse_id)
    {
        $sql = 'SELECT user_id FROM '.DB_PREFIX."warehouse w WHERE warehouse_id = '".(int) $warehouse_id."' ";
        $result = $this->db->query($sql)->row;
        if ($result) {
            return $result['user_id'];
        } else {
            return false;
        }
    }

    public function updateMainProductQuantity($product_id)
    {
        $warehouse = $this->db->query('SELECT SUM(quantity) as quantity FROM '.DB_PREFIX."warehouse_product wp WHERE wp.product_id = '".(int) $product_id."' ")->row;
        $this->db->query('UPDATE '.DB_PREFIX."product p SET quantity = '".$warehouse['quantity']."' WHERE p.product_id = '".(int) $product_id."' ");
    }

    /**
     * [getTotalProduct get total products].
     *
     * @return [type] [integer]
     */
    public function getTotalProduct($data)
    {
        $user_id = $this->getUserIdByWarehouseId($data['selected_warehouse']);
        $sql = 'SELECT cf.product_id,cf.model,cf.image,cf.quantity,cf.price,cf.status,cfd.name FROM `'.DB_PREFIX.'product` cf LEFT JOIN '.DB_PREFIX."product_description cfd ON (cf.product_id = cfd.product_id) WHERE cfd.language_id = '".(int) $this->config->get('config_language_id')."'";

        if (isset($data['selected_warehouse']) && $data['selected_warehouse']) {
            $sql .= ' AND cfd.product_id NOT IN (SELECT wp.product_id FROM '.DB_PREFIX.'warehouse_product wp LEFT JOIN '.DB_PREFIX."warehouse w ON (w.user_id=wp.user_id) WHERE w.user_id != '".(int) $user_id."' ) ";
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND cfd.name LIKE '".$this->db->escape($data['filter_name'])."%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND cf.model LIKE '".$this->db->escape($data['filter_model'])."%'";
        }

        $result = $this->db->query($sql)->rows;

        if ($result) {
            return count($result);
        } else {
            return false;
        }
    }

    public function isAliexpressOption($option_id)
    {
        $query = $this->db->query('SELECT id FROM '.DB_PREFIX."warehouse_aliexpress_product_option WHERE oc_option_id='".(int) $option_id."'")->row;

        if ($query && isset($query['id'])) {
            return true;
        } else {
            return false;
        }
    }
}
