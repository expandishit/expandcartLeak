<?php

class ModelAliexpressManager extends Model
{
    public function getWarehouseByUserId($userId = false) {
        $sql = "SELECT warehouse_id,title FROM ".DB_PREFIX."warehouse WHERE user_id = '".$this->user->getId()."' ";
        $result = $this->db->query($sql)->rows;
        if($result) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * [getProducts get product description]
     * @param  array  $data    [filter data]
     * @return [type]          [array]
     */
    public function getProducts($data = array()){
        
        $sql = "SELECT wp.quantity,p.model,wp.approved,p.price,pd.name,w.warehouse_code,w.title, (SELECT SUM(quantity) FROM ".DB_PREFIX."warehouse_order wo WHERE (wo.product_id = wp.product_id && wo.warehouse_id=wp.warehouse_id ) ) as sold_quantity FROM " . DB_PREFIX . "warehouse_product wp LEFT JOIN ".DB_PREFIX."warehouse w ON (w.warehouse_id=wp.warehouse_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) WHERE w.user_id = '".$this->user->getId()."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['filter_warehouse_code']) && $data['filter_warehouse_code']) {
            $sql .= " AND w.warehouse_code LIKE '" . $this->db->escape($data['filter_warehouse_code']) . "%'";
        }

        if (isset($data['filter_warehouse_title']) && $data['filter_warehouse_title']) {
            $sql .= " AND w.title LIKE '" . $this->db->escape($data['filter_warehouse_title']) . "%'";
        }

        if (isset($data['filter_name']) && $data['filter_name']) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_model']) && $data['filter_model']) {
            $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (isset($data['filter_quantity']) && $data['filter_quantity']) {
            $sql .= " AND wp.quantity = '" . (int)$data['filter_quantity'] . "'";
        }

        if (isset($data['filter_qufilter_priceantity']) && $data['filter_price']) {
            $sql .= " AND p.price = '" . $this->db->escape($data['filter_price']) . "'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] != '' ) {
            $sql .= " AND wp.approved = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['sort']) && $data['sort']) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
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
        
        $result = $this->db->query($sql)->rows;
        
        if($result) {
            return $result;
        } else {
            return false;
        }

    }

    /**
     * [getTotalProducts get total products]
     * @param  [type] $warehouse_id [warehouse id]
     * @return [type]               [array]
     */
    public function getTotalProducts($data) {

        $sql = "SELECT wp.quantity,p.model,p.status,p.price,pd.name,w.warehouse_code,w.title FROM " . DB_PREFIX . "warehouse_product wp LEFT JOIN ".DB_PREFIX."warehouse w ON (w.warehouse_id=wp.warehouse_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = p.product_id) WHERE w.user_id = '".$this->user->getId()."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['filter_warehouse_code']) && $data['filter_warehouse_code']) {
            $sql .= " AND w.warehouse_code LIKE '" . $this->db->escape($data['filter_warehouse_code']) . "%'";
        }

        if (isset($data['filter_warehouse_title']) && $data['filter_warehouse_title']) {
            $sql .= " AND w.title LIKE '" . $this->db->escape($data['filter_warehouse_title']) . "%'";
        }

        if (isset($data['filter_name']) && $data['filter_name']) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_model']) && $data['filter_model']) {
            $sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (isset($data['filter_quantity']) && $data['filter_quantity']) {
            $sql .= " AND wp.quantity = '" . (int)$data['filter_quantity'] . "'";
        }

        if (isset($data['filter_qufilter_priceantity']) && $data['filter_price']) {
            $sql .= " AND p.price = '" . $this->db->escape($data['filter_price']) . "'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] != '' ) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        $result = $this->db->query($sql)->rows;
        
        if($result) {
            return count($result);
        } else {
            return false;
        }
    }

    /**
     * [editUser edit user]
     * @param  [type] $user_id [user id]
     * @param  [type] $data    [user details]
     * @return [type]          [array]
     */
    public function editUser($user_id, $data) {
        $this->db->query("UPDATE `" . DB_PREFIX . "user` SET username = '" . $this->db->escape($data['username']) . "',firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "' WHERE user_id = '" . (int)$user_id . "'");

        if ($data['password']) {
            $this->db->query("UPDATE `" . DB_PREFIX . "user` SET salt = '" . $this->db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE user_id = '" . (int)$user_id . "'");
        }
    }

    /**
     * [getUserByUsername get user]
     * @param  [type] $username [username]
     * @return [type]           [array]
     */
    public function getUserByUsername($username) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($username) . "'");

        return $query->row;
    }

    /**
     * [editPosition edit position of warehouse]
     * @param  [type] $user_id [user id]
     * @param  [type] $data    [data array]
     * @return [type]          [array]
     */
    public function editPosition($user_id,$data){

        $warehouse_id = $this->db->query("UPDATE `" . DB_PREFIX . "warehouse` SET longitude= '" . $this->db->escape($data['longitude']) . "', latitude= '" . $this->db->escape($data['latitude']) . "' WHERE user_id = '" . $this->db->escape($user_id) . "'");
    }

    /**
     * [getPosition get warehouse position]
     * @param  [type] $user_id [user id]
     * @return [type]          [array]
     */
    public function getPosition($user_id){
       
       $query=$this->db->query("SELECT * FROM " . DB_PREFIX . "warehouse WHERE user_id = '".$this->db->escape($user_id)."'");

        return $query->row;

    }
}
