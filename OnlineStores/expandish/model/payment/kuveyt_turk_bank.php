<?php

class ModelPaymentKuveytTurkBank extends Model {

    public function change_order_status($order_info, $order_status_id, $comment = '') {
        //$order_info = $this->getOrder($order_id);        
        if ($order_info) {
            $order_id = $order_info['order_id'];
            // Fraud Detection            
            if ($this->config->get('config_fraud_detection')) {
                $this->load->model('checkout/fraud');
                $risk_score = $this->model_checkout_fraud->getFraudScore($order_info);
                if ($risk_score > $this->config->get('config_fraud_score')) {
                    $order_status_id = $this->config->get('config_fraud_status_id');
                }
            }

            // Blacklist            
            $status = false;
            $this->load->model('account/customer');
            if ($order_info['customer_id']) {
                $results = $this->model_account_customer->getIps($order_info['customer_id']);
                foreach ($results as $result) {
                    if (method_exists($this->model_account_customer, 'isBanIp')) {
                        if ($this->model_account_customer->isBanIp($result['ip'])) {
                            $status = true;
                            break;
                        }
                    } else {
                        if ($this->model_account_customer->isBlacklisted($result['ip'])) {
                            $status = true;
                            break;
                        }
                    }
                }
            } else {
                if (method_exists($this->model_account_customer, 'isBanIp')) {
                    $status = $this->model_account_customer->isBanIp($order_info['ip']);
                } else {
                    $status = $this->model_account_customer->isBlacklisted($order_info['ip']);
                }
            }

            if ($status) {
                $order_status_id = $this->config->get('config_order_status_id');
            }

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" . (int) $order_status_id . "', notify = '0', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
        }
    }

    public function getMethod($address, $total) {
        $knet_settings = $this->model_setting_setting->getSetting('kuveyt_turk_bank');
        $current_lang = $this->config->get('config_language_id');
        $this->language->load_json('payment/kuveyt_turk_bank');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('kuveyt_turk_bank_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
        if (!empty($knet_settings['kuveyt_turk_bank_field_name_' . $current_lang])) {
            $title = $knet_settings['kuveyt_turk_bank_field_name_' . $current_lang];
        } else {
            $title = $this->language->get('text_title');
        }

        if (!$this->config->get('kuveyt_turk_bank_geo_zone_id')) {
            $status = true;
        } elseif ($query->row['country_id'] == $address['country_id']) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = [];

        if ($status) {
            $method_data = [
                'code' => 'kuveyt_turk_bank',
                'title' => $title,
                'sort_order' => $this->config->get('kuveyt_turk_bank_sort_order')
            ];
        }
        return $method_data;
    }

}

?>
