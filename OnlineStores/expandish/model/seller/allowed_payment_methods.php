<?php

class ModelSellerAllowedPaymentMethods extends Model
{
    public function getActiveMethods()
    {
        $this->load->model('setting/extension');
        $paymentMethods = $this->model_setting_extension->getExtensions('payment');
        $activePaymentMethods = [];
        foreach ($paymentMethods as $paymentMethod)
        {
            if( $paymentMethod['code'] == "pp_plus") {
				$this->load->model('setting/setting');
				$settings = $this->model_setting_setting->getSetting('payment')[$paymentMethod['code']];
			} else {
				$settings = $this->config->get($paymentMethod['code']);
			}

            if ($settings && is_array($settings) == true) {
                $status = $settings['status'];
            } else {
                $status = $this->config->get($paymentMethod['code'] . '_status');
            }

            if ($status) {
                $this->language->load_json('payment/' . $paymentMethod['code']);
                $paymentMethod['title'] = $this->language->get('text_title');
                $activePaymentMethods[] = $paymentMethod;
            }
        }
        return $activePaymentMethods;
    }

    public function getSellerAllowedPaymentMethods($sellerId)
    {
        $data = $this->db->query("SELECT data FROM " . DB_PREFIX . "ms_allowed_payment_methods WHERE `seller_id` = " . (int) $sellerId)->row['data'];
        if (!empty($data))
        {
            return unserialize($data);
        }
        return [];
    }

    public function save($sellerId, $allowedPaymentMethods)
    {
        $sellerCount = $this->db->query("SELECT COUNT(seller_id) AS seller_count FROM " . DB_PREFIX . "ms_allowed_payment_methods WHERE `seller_id` = " . (int) $sellerId)->row['seller_count'];
        if ($sellerCount > 0)
        {
            $this->db->query("UPDATE " . DB_PREFIX . "ms_allowed_payment_methods SET `data` = '" . serialize($allowedPaymentMethods) . "' WHERE `seller_id` = " . (int) $sellerId);
            return;
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "ms_allowed_payment_methods (`seller_id`, `data`) VALUES (".(int) $sellerId.", '".serialize($allowedPaymentMethods)."')");
    }
}