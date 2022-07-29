<?php

class ModelShippingCatalyst extends Model
{
    public function addShipmentDetails($request_object, $status = 0)
    {
        $query = $fields = [];

        $query[] = 'INSERT INTO  ' . DB_PREFIX . 'shipments_details SET';
        $fields[] = 'order_id="' . $this->db->escape($request_object['number']) . '"';
        $fields[] = 'details=\'' . $this->db->escape(json_encode($request_object, JSON_UNESCAPED_UNICODE)) . '\'';
        $fields[] = 'shipment_status="' . $this->db->escape($status) . '"';
        $fields[] = 'shipment_operator="catalyst"';
        $query[] = implode(', ', $fields);
        $this->db->query(implode(' ', $query));
    }

    public function updateShipmentStatus($order_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "shipments_details SET `shipment_status` = '" . $this->db->escape($status) . "' WHERE `order_id` = '" . $this->db->escape($order_id) . "' AND `shipment_operator` = 'catalyst'");
    }

    public function getShipmentDetails($orderId)
    {
        $result = $this->db->query("SELECT * FROM  " . DB_PREFIX . "shipments_details where `order_id` = " . $this->db->escape($orderId) . " AND `shipment_operator` = 'catalyst' ")->row;
        if (!empty($result)) {
            $details = json_decode($result['details'], true);
            $details['shipment_status'] = $result['shipment_status'];
            return $details;
        }
        return [];
    }

    public function deleteShipment($orderId)
    {
        $this->db->query("DELETE FROM  " . DB_PREFIX . "shipments_details where `order_id` = " . $this->db->escape($orderId) . " AND `shipment_operator` = 'catalyst' ");
    }

    public function getPaymentMethods()
    {
        $this->language->load('shipping/catalyst');
        return [
            [
                'value' => 0,
                'text'  => $this->language->get('catalyst_payment_cash')
            ],
            [
                'value' => 1,
                'text'  => $this->language->get('catalyst_payment_prepaid')
            ]
        ];
    }

    public function getOrderStatus()
    {
        $this->language->load('shipping/catalyst');
        return [
            [
                'value' => 3,
                'text' => $this->language->get('catalyst_status_3')
            ],
            [
                'value' => 5,
                'text' => $this->language->get('catalyst_status_5')
            ],
            [
                'value' => 6,
                'text' => $this->language->get('catalyst_status_6')
            ]
        ];
    }
}
