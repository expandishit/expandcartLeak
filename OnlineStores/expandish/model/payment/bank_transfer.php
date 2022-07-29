<?php
class ModelPaymentBankTransfer extends Model {
  	public function getMethod($address, $total) {
		$this->language->load_json('payment/bank_transfer');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('bank_transfer_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		$this->load->model('module/minimum_deposit/settings');
		if ($this->model_module_minimum_deposit_settings->isActive() && !$this->config->get('minimum_deposit')['md_allow_cash_on_delivery'] ) {
			$status = false;

		}else{
			if ($this->config->get('bank_transfer_total') > 0 && $this->config->get('bank_transfer_total') > $total) {
				$status = false;
			} elseif (!$this->config->get('bank_transfer_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
		}
		$method_data = array();

		if ($status) {
			$bank_transfer_settings = $this->model_setting_setting->getSetting('bank_transfer');

            $current_lang = $this->session->data['language'];
			$this->load->model('localisation/language');
			$language = $this->model_localisation_language->getLanguageByCode($current_lang);
			$current_lang = $language['language_id'];

            if ( !empty($bank_transfer_settings['bank_transfer_field_name_' . $current_lang]) )
            {
                $title = $bank_transfer_settings['bank_transfer_field_name_' . $current_lang];
            }
            else
            {
                $title = $this->language->get('text_title');
            }

      		$method_data = array(
        		'code'       => 'bank_transfer',
        		'title'      => $title,
				'sort_order' => $this->config->get('bank_transfer_sort_order')
      		);
    	}

    	return $method_data;
  	}
}
?>
