<?php
class ModelModuleDeliverySlotS extends Model {
    // define table name
    private $table = "ds_delivery_slot";

    public function addSlots(array $data) {

        $seller_id = $this->customer->getId();
        $status = 1;
        if(!isset($data['status'])){
            $status = 0;
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "ds_delivery_slot SET ds_day_id = '" . (int)$data['day_id'] . "', seller_id = '" . (int)$seller_id . "', delivery_slot = '" . $this->db->escape($data['delivery_slot']) . "',time_start =  TIME( STR_TO_DATE( '".$data['time_start']."', '%h:%i %p' ) ) ,time_end =  TIME( STR_TO_DATE( '".$data['time_end']."', '%h:%i %p' ) ) , total_orders = '" . (int)$data['total_orders'] . "', status = '".$status."'");
        return true;
    }

    public function editSlot($slot_id, array $data) {
        
        $seller_id = $this->customer->getId();
        $queryString = $fields = [];
        $queryString[] = "UPDATE " . DB_PREFIX.$this->table . " SET";

        $fields[] = "delivery_slot = '" . $data['delivery_slot'] . "'";
        $fields[] = "ds_day_id = '" . $data['day_id'] . "'";
        $fields[] = "time_start =  TIME( STR_TO_DATE( '".$data['time_start']."', '%h:%i %p' ) )";
        $fields[] = "time_end =  TIME( STR_TO_DATE( '".$data['time_end']."', '%h:%i %p' ) )";
        $fields[] = "total_orders = '" . (int)$data['total_orders'] . "'";
        $fields[] = "status = '" . (int)$data['status'] . "'";
        $fields[] = "seller_id = '" . (int)$seller_id . "'";

        $queryString[] = implode(',', $fields);
        $queryString[] = 'WHERE ds_delivery_slot_id="' . $slot_id . '"';

        $this->db->query(implode(' ', $queryString));

        return true;
    }

    public function deleteSlot($slot_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX.$this->table . " WHERE 	ds_delivery_slot_id = '" . (int)$slot_id . "'");

        return true;
    }

    public function getSlot($slot_id) {

        $query = $this->db->query("SELECT *, TIME_FORMAT( `time_start`, '%h:%i %p' ) as time_start_formated, TIME_FORMAT( `time_end`, '%h:%i %p' ) as time_end_formated FROM " . DB_PREFIX.$this->table  . " WHERE ds_delivery_slot_id = '" . (int)$slot_id . "' ");

        return $query->row;
    }

    public function getSlots($data = array()) {

        $seller_id = $this->customer->getId();

        $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ";

        $sql .= " WHERE ds_day_id = ".$data['day_id']." AND seller_id=".$seller_id;

        if (!empty($data['filter_name'])) {
            $sql .= " AND delivery_slot LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sort_data = array(
            'delivery_slot',
            'ds_delivery_slot_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY delivery_slot";
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

    public function getTotalSlots($data = array()) {
        $sql = "SELECT COUNT(ds_delivery_slot_id) AS total FROM ".DB_PREFIX.$this->table;
        $sql .= " WHERE ds_day_id = ".$data['day_id'];

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getOrderDeliverySlot($order_id) {
        $query = $this->db->query("SELECT *  FROM ".DB_PREFIX."ds_delivery_slot_order WHERE order_id = ".(int)$order_id." ");
        return $query->row;
    }
    
    public function slotsOrders(){

        $seller_id = $this->customer->getId();
        $sql="SELECT so.* ,ors.name AS status, ors.bk_color AS status_color
                FROM `" . DB_PREFIX . "ds_delivery_slot_order` so
                LEFT JOIN `" . DB_PREFIX . "ds_delivery_slot` ds
                ON ds.ds_delivery_slot_id  =  so.ds_delivery_slot_id 
                LEFT JOIN `" . DB_PREFIX . "order` o
                ON o.order_id =  so.order_id
                LEFT JOIN `" . DB_PREFIX . "order_status` ors
                ON ors.order_status_id =  o.order_status_id
                WHERE ors.language_id = " . (int)$this->config->get('config_language_id'). " AND ds.seller_id=".$seller_id."";

        $res = $this->db->query($sql);
        $total = $this->db->query("SELECT FOUND_ROWS() as total");
        if ($res->rows) $res->rows[0]['total_rows'] = $total->row['total'];

        return $res->rows;
        }

    
    public function getDaysLocalized()
    {
        $this->load->language('module/delivery_slot');
        return [
            [
                'id' => 1,
                'name' => $this->language->get('entry_saturday')
            ],
            [
                'id' => 2,
                'name' => $this->language->get('entry_sunday')
            ],
            [
                'id' => 3,
                'name' => $this->language->get('entry_monday')
            ],
            [
                'id' => 4,
                'name' => $this->language->get('entry_tuesday')
            ],
            [
                'id' => 5,
                'name' => $this->language->get('entry_wednesday')
            ],
            [
                'id' => 6,
                'name' => $this->language->get('entry_thursday')
            ],
            [
                'id' => 7,
                'name' => $this->language->get('entry_friday')
            ],
        ];
    }

    public function getSlotsTojson($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ds ";
        $sql .= " WHERE ds.total_orders > (SELECT COUNT(o.order_id) FROM ds_delivery_slot_order o WHERE o.delivery_date = '".$data['dayValue']."' ) ";
        if($data['dayValue'] == date("m-d-Y")){
            $sql .= " AND ds.time_end > TIME( STR_TO_DATE( TIME_FORMAT(NOW(), '%h:%i %p'), '%h:%i %p' ) ) ";
        }
        $sql .= " AND ds.ds_day_id = ". $data['day_id'];
        $sql .= " AND ds.status = 1";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Update order delivery slot data
     *
     * @param array $data
     * @return bool
     */
    public function updateOrderSlot($data = array()){
        // get slot data
        $query_slot_data = $this->db->query("SELECT  * FROM " . DB_PREFIX ."ds_delivery_slot WHERE ds_delivery_slot_id = '" . (int)$data['slot_id'] . "' ");
        $slotData = $query_slot_data->row;
        
        $querySlot = "UPDATE  ".DB_PREFIX."ds_delivery_slot_order SET ";
        $querySlot .= " ds_delivery_slot_id = ".(int)$data['slot_id'];
        $querySlot .= " , delivery_date = '".$data['slot_date']."'";
        $querySlot .= " , ds_day_id = ".(int)$slotData['ds_day_id'];
        $querySlot .= " , slot_description = '".$slotData['delivery_slot']."'";
        $querySlot .= " , day_name = '".$data['days'][$slotData['ds_day_id']]."'";
        $querySlot .= " WHERE order_id =".(int)$data['order_id']." AND ds_delivery_slot_order_id = ".(int)$data['slot_order_id'];

        try {
            $this->db->query($querySlot);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * delete order delivery slot
     *
     * @param array $data
     * @return bool
     */
  
    public function deleteOrderSlot($ds_delivery_slot_order_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX." ds_delivery_slot_order WHERE 	ds_delivery_slot_order_id = '" . (int)$ds_delivery_slot_order_id . "'");

        return true;
    }

    public function getBalance($data = array()) {
        $sql = "SELECT COUNT(ds_delivery_slot_id) AS total FROM ".DB_PREFIX."ds_delivery_slot_order ";
        $sql .= " WHERE ds_delivery_slot_id = ".$data['slot_id']." AND delivery_date = '".$data['balanceDate']."'";

        $query = $this->db->query($sql);
        return $query->row['total'];
    }
}
?>