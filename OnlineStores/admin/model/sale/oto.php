<?php
class ModelSaleOto extends Model {
    public function getOtoId($order_id) {
        $result = $this->db->query("SELECT oto_id FROM " . DB_PREFIX . "oto_order WHERE `order_id` = " . (int) $order_id);
        if ($result->num_rows > 0) {
            return $result->row['oto_id'];
        }
        return false;
    }

    public function insertOtoId($order_id, $oto_id) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "oto_order (order_id, oto_id) VALUES ('".(int) $order_id."', '".(int) $oto_id."')");
    }

    public function deleteOrder($order_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "oto_order WHERE `order_id` = '" . (int) $order_id . "'");
    }
}