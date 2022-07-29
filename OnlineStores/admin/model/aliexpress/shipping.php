<?php

class ModelAliexpressShipping extends Model
{
    
    /**
     * [getWarehouseId get warehouse id]
     * @param  [type] $id [user id]
     * @return [type]     [array]
     */
    public function getWarehouseId($id)
    {
        $warehouse_id=$this->db->query("SELECT warehouse_id FROM " . DB_PREFIX . "warehouse WHERE user_id = '".$id."'");

        if (!empty($warehouse_id->row)) {
            $this->session->data['warehouse_id']=$warehouse_id->row['warehouse_id'];
        } else {
            return 0;
        }
    }
    
    /**
     * [addShipping add shipping details]
     * @param [type] $data [shipping details]
     */
    public function addShipping($data)
    {
        $sql = $sqlchk = '';
        foreach ($data as $key => $value) {
            if ($key!='price') {
                $sqlchk .= $key." = '".$this->db->escape($value)."' AND ";
            }
            $sql .= $key." = '".$this->db->escape($value)."' ,";
        }
        $sqlchk = substr($sqlchk, 0, -5);
        $sql = substr($sql, 0, -1);
        $getId = $this->db->query("SELECT id FROM " .DB_PREFIX ."warehouse_shipping WHERE $sqlchk AND warehouse_id='".$this->session->data['warehouse_id']."'")->row;

        if ($getId) {
            $this->db->query("UPDATE " .DB_PREFIX ."warehouse_shipping SET $sql WHERE id = '".$getId['id']."'");
        } else {
            $this->db->query("INSERT INTO " .DB_PREFIX ."warehouse_shipping SET warehouse_id= '".$this->session->data['warehouse_id']."', $sql ");
        }

        if ($getId) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * [viewdata get shipping details]
     * @param  [type] $data [filter data]
     * @return [type]       [array]
     */
    public function viewdata($data)
    {
        $sql ="SELECT cs.* FROM " . DB_PREFIX . "warehouse_shipping cs WHERE warehouse_id ='".$this->session->data['warehouse_id']."' ";
    
        if (!empty($data['filter_country'])) {
            $sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
        }
        
        if (!empty($data['filter_zip_to'])) {
            $sql .= " AND cs.zip_to LIKE '" . $this->db->escape($data['filter_zip_to']) . "%'";
        }

        if (!empty($data['filter_zip_from'])) {
            $sql .= " AND cs.zip_from LIKE '" . $this->db->escape($data['filter_zip_from']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND cs.price = '" . (float)$this->db->escape($data['filter_price']) . "'";
        }
        
        if (!empty($data['filter_weight_to'])) {
            $sql .= " AND cs.weight_to = '" . (float)$this->db->escape($data['filter_weight_to']) . "'";
        }
        
        if (!empty($data['filter_weight_from'])) {
            $sql .= " AND cs.weight_from = '" . (float)$this->db->escape($data['filter_weight_from']) . "'";
        }

        $sql .= " GROUP BY cs.id";
                        
        $sort_data = array(
            'cs.country_code',
            'cs.price',
            'cs.zip_to',
            'cs.zip_from',
            'cs.weight_to',
            'cs.weight_from'
        );
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY cs.id";
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

        $result=$this->db->query($sql);

        return $result->rows;
    }
    
    /**
     * [viewtotalentry get total shipping detail entry]
     * @param  [type] $data [filter array]
     * @return [type]       [array]
     */
    public function viewtotalentry($data)
    {
        $sql ="SELECT cs.* FROM " . DB_PREFIX . "warehouse_shipping cs WHERE warehouse_id ='".$this->session->data['warehouse_id']."' ";
    
        if (!empty($data['filter_country'])) {
            $sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
        }
        
        if (!empty($data['filter_zip_to'])) {
            $sql .= " AND cs.zip_to LIKE '" . $this->db->escape($data['filter_zip_to']) . "%'";
        }

        if (!empty($data['filter_zip_from'])) {
            $sql .= " AND cs.zip_from LIKE '" . $this->db->escape($data['filter_zip_from']) . "%'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND cs.price = '" . (float)$this->db->escape($data['filter_price']) . "'";
        }
        
        if (!empty($data['filter_weight_to'])) {
            $sql .= " AND cs.weight_to = '" . (float)$this->db->escape($data['filter_weight_to']) . "'";
        }
        
        if (!empty($data['filter_weight_from'])) {
            $sql .= " AND cs.weight_from = '" . (float)$this->db->escape($data['filter_weight_from']) . "'";
        }

        $result = $this->db->query($sql);

        return count($result->rows);
    }
    
    /**
     * [deleteentry delete entry]
     * @param  [type] $id [warehouse shipping id]
     * @return [type]     [array]
     */
    public function deleteentry($id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "warehouse_shipping WHERE id='".(int)$id."'");
    }
}
