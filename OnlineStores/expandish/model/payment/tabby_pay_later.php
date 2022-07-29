<?php
class ModelPaymentTabbyPayLater extends Model
{
    /**
     * @var constant
     */
    const GATEWAY_NAME = 'tabby_pay_later';

    public function getMethod($address, $total)
    {
        //Disable pay later option
        return [];
        
        $settings = $this->config->get(self::GATEWAY_NAME);

        if (!$this->validate($settings, $address)) return [];

        $this->language->load_json("payment/" . self::GATEWAY_NAME);

        $method_data = [
            'code'         => self::GATEWAY_NAME,
            'title'        => $this->withPromoIcon($settings, $this->language->get('text_title')),
            'sort_order'   => 0
        ];

        return $method_data;
    }

    private function withPromoIcon(array $setting, string $title)
    {
        $locale = $this->config->get('config_language') == "ar" ? "ar" : "en";
        // if ((int)$setting['show_promo_image_in_checkout']) {
        $title = '<div style=" display: flex; align-items: center; "><img style="float: unset;margin: 0;max-width: 50px;" src="/image/tabby_' . $locale . '.png"/><span style="margin: 0 5px;">' . $title . '</span></div>';
        // }

        return $title;
    }

    private function validate($settings = [], $address = null): bool
    {
        return $this->countrySupported($settings, $address) && $this->zoneSupported($settings, $address) && $this->currencySupported($settings, $address);
    }


    private function countrySupported($settings = [], $address = null): bool
    {
        if (!$address) return !1;

        $this->load->model('extension/payment_method');

        if (!($extension = $this->model_extension_payment_method->selectByCode('tabby'))) {
            return !1;
        }

        if (!isset($address['iso_code_2'])) return !1;

        $tabbySupportedCountries = explode(',', $extension['supported_countries']);

        if (!empty($tabbySupportedCountries) && !array_key_exists(strtoupper($address['iso_code_2']), array_flip(array_map('strtoupper', $tabbySupportedCountries)))) {
            // not supported country!
            return !1;
        }

        return !!1;
    }

    private function zoneSupported($settings = [], $address = null): bool
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$settings['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        return !$settings['geo_zone_id'] || $query->num_rows ? true : false;
    }

    private function currencySupported($settings = [], $address = null): bool
    {
        return strtoupper($this->currency->getCode()) === strtoupper($this->currency($this->isoCode2($address['country_id'])));
    }

    private function isoCode2($country_id = null)
    {
        $this->load->model('localisation/country');
        return $this->model_localisation_country->getCountry($country_id)['iso_code_2'] ?: null;
    }

    private function currency($isoCode2 = null)
    {
        switch ($isoCode2) {
            case 'SA':
                return 'SAR';
            case 'AE':
                return 'AED';
            case 'KW':
                return 'KWD';
            case 'BH':
                return 'BHD';
            default:
                return null;
        }
    }
}
