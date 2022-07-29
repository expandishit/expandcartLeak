<?php

class ModelShippingCatalyst extends Model {

    public function getQuote($address) 
    {
        $this->language->load_json('shipping/catalyst');

        $catalyst_rate = $this->config->get('catalyst_shipping_rate') ?? 0;

        if (
            !empty($this->config->get('catalyst_client_id')) &&
            !empty($this->config->get('catalyst_client_secret')) &&
            !empty($this->config->get('catalyst_branch_id')) &&
            !empty($this->config->get('catalyst_promise_time')) &&
            !empty($this->config->get('catalyst_preparation_time')) &&
            !empty($this->config->get('catalyst_google_api_key'))
        ) {
            $store_name = is_array($this->config->get('config_name')) ? $this->config->get('config_name')[$this->config->get('config_language_id')] : $this->config->get('config_name');
            $title      = $this->config->get('catalyst_title')[$this->config->get('config_language_id')] ?: sprintf($this->language->get('catalyst_title'), $store_name);
            return [
                'code' => 'catalyst',
                'title' => $store_name,
                'quote' => [
                    'catalyst' => [
                        'code' => 'catalyst.catalyst',
                        'title' => $title,
                        'cost' => $catalyst_rate,
                        'tax_class_id' => 0,
                        'text' => $this->currency->format($catalyst_rate, $this->currency->getCode())
                    ]
                ],
                'sort_order' => 2,
                'error' => false
            ];
        }

        return [];

    }

    public function updateOrderStatus($order_id, $status)
    {
        $result = $this->db->query("SELECT COUNT(order_id) AS order_count FROM `" . DB_PREFIX . "order` WHERE `order_id` = " . $this->db->escape($order_id));
        if ($result->row['order_count'] == 0) {
            return false;
        }
        $status = (int) $this->db->escape($status);
        $status = $this->config->get('catalyst_status_' . $status) ?? 0;
        if ($status != 0) {
            $this->db->query("UPDATE `" . DB_PREFIX . "shipments_details` SET `shipment_status` = " . $status . " WHERE `order_id` = " . $this->db->escape($order_id) . " AND `shipment_operator` = 'catalyst'");
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET `order_status_id` = " . $status . " WHERE `order_id` = " . $this->db->escape($order_id));
            return true;
        }
        return false;
    }
    
}
