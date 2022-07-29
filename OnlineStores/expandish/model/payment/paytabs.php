

<?php 
class ModelPaymentPaytabs extends Model {
	
	public function getMethod($address, $total) {
		$this->language->load_json('payment/paytabs');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('paytabs_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('paytabs_total') > 0 && $this->config->get('paytabs_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('paytabs_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	

		$method_data = array();

		if ($status) {

            $title = $this->getFieldName('paytabs');

            $method_data = array(
				'code'       => 'paytabs',
				'title'      => $title,
				'sort_order' => $this->config->get('paytabs_sort_order')
			);
		}

		return $method_data;
	}

    /**
     * @return mixed
     */
    protected function getFieldName($paymentMethodCode)
    {
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');

        $current_lang = $this->session->data['language'];
        $paymentMethodData = $this->model_setting_setting->getSetting($paymentMethodCode);
        $language = $this->model_localisation_language->getLanguageByCode($current_lang);
        $current_lang = $language['language_id'];

        if (!empty($paymentMethodData[$paymentMethodCode.'_field_name_' . $current_lang])) {
            $title = $paymentMethodData[$paymentMethodCode.'_field_name_' . $current_lang];
        } else {
            $title = $this->language->get('text_title');
        }
        return $title;
    }
}
?>