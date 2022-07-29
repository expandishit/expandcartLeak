<?php

/**
 * ShipA Delivery Integration Model
 * 
 * @author MohamedHassanWD <hassan.mohamed.sf@gmail.com>
 * @copyright 2020 ExpandCart
 */
class ModelShippingShipa extends Model {

    /**
     * Insert new parcel/shipment into DB
     *
     * @param array $data Shipment Data as array
     * @return void
     */
    public function create_shipment($data) 
    {
        //create shipment in table
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $data['order_id'] . '"';
        $fields[] = 'details=\'' . json_encode($data['response'],JSON_UNESCAPED_UNICODE) . '\'';
        $fields[] = 'shipment_status="1"';
        $fields[] = 'shipment_operator="shipa"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    /**
     * Get parcels/shipments for order
     *
     * @param int $order_id Order ID   
     * @return array
     */
    public function getOrderShipments($order_id)
    {
        $query = $fields = [];

        $query[] = 'SELECT * FROM ' . DB_PREFIX . 'shipments_details';
        $fields[] = 'WHERE order_id="' . $order_id . '"';
        $fields[] = 'AND shipment_operator="shipa"';
        $query[] = implode(' ', $fields);
        $q = $this->db->query(implode(' ', $query));

        return $q->rows;
    }
}