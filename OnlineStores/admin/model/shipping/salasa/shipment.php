<?php

class ModelShippingSalasaShipment extends Model
{

    /**
     * Add a new row to the shipment details table.
     *
     * @param int $orderId
     * @param mixed $details
     * @param int $status
     *
     * @return void
     */
    public function addShipmentDetails($orderId, $details, $status)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO `shipments_details` SET';
        $fields[] = 'order_id="' . $orderId . '"';
        $fields[] = 'details=\'' . json_encode($details,JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_status="' . $status . '"';
        $fields[] = 'shipment_operator="salasa"';
        $query[] = implode(', ', $fields);
//        $query[] = 'ON DUPLICATE KEY UPDATE details="' . json_encode($details) . '"';

        $this->db->query(implode(' ', $query));
    }

    /**
     * Retrieve a shipment details by order id.
     *
     * @param int $orderId
     *
     * @return array|bool
     */
    public function getShipmentDetails($orderId)
    {
        $query = [];

        $query[] = 'SELECT * FROM `shipments_details`';
        $query[] = 'WHERE order_id="' . $orderId . '"';

        $data = $this->db->query(implode(' ', $query));

        if ($data->num_rows > 0) {
            return $data->row;
        }

        return false;
    }

}
