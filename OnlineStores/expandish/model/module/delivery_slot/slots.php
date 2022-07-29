<?php
class ModelModuleDeliverySlotSlots extends Model {
    // define table name
    private $table = "ds_delivery_slot";

    public function getSlots($data = array()) {
	
        $app_config_timezone = $this->config->get('config_timezone')?:'UTC';
        $now = (new DateTime('NOW', new DateTimeZone($app_config_timezone)))->format('Y-m-d H:i:s');

        $sql = "SELECT * FROM " . DB_PREFIX .$this->table . " ds ";
        $sql .= " WHERE ds.total_orders > (SELECT COUNT(o.order_id) FROM ds_delivery_slot_order o WHERE o.delivery_date = '".$data['dayValue']."' ) ";
        if($data['dayValue'] == date("m-d-Y")){
            $sql .= " AND ds.time_end > TIME( STR_TO_DATE( TIME_FORMAT('".$now."', '%h:%i %p'), '%h:%i %p' ) ) ";
        }
        $sql .= " AND ds.ds_day_id = ". $data['day_id'];

        if($data['seller_id'])
         {
            $sql .= " AND ds.seller_id = ". $data['seller_id'];
         }else{
            $sql .= " AND ( ds.seller_id IS NULL OR ds.seller_id = 0 )";
        }

        $sql .= " AND ds.status = 1";
        $query = $this->db->query($sql);

        return $query->rows;
    }


    public function getTotalSlots() {
        $query = $this->db->query("SELECT COUNT(ds_delivery_slot_id) AS total FROM ".DB_PREFIX.$this->table. " ");
        return $query->row['total'];
    }

    public function getOrderDeliverySlot($order_id) {
        $query = $this->db->query("SELECT *  FROM ".DB_PREFIX."ds_delivery_slot_order WHERE order_id = ".(int)$order_id." ");
        return $query->row;
    }

    public function getSlot($slot_id) {

        $query = $this->db->query("SELECT *, 
            TIME_FORMAT( `time_start`, '%H:%i' ) as time_start_formated, 
            TIME_FORMAT( `time_end`, '%H:%i' ) as time_end_formated 

            FROM " . DB_PREFIX.$this->table  . " WHERE ds_delivery_slot_id = '" . (int)$slot_id . "' ");

        return $query->row;
    }



}
?>
