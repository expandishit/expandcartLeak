<?php
class ModelShippingFds extends Model
{
    public function checkExists($oreder_id)
    {
        $query = $this->db->query("SELECT details FROM " . DB_PREFIX . "shipments_details WHERE order_id = '" . (int)$oreder_id . "' AND shipment_status = 1 limit 1");

        if ($query->num_rows) {
            return $query->rows[0]['details'];
        }

        return false;
    }
}