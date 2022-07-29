<?php

use ExpandCart\Foundation\Providers\Extension;

class ModelModuleGoogleMap extends Model
{

    const APP_NAME = 'google_map';

    /**
     * The Expand api key
     */
    const DEFAULT_API_KEY = 'AIzaSyAyfxIdWNNR-STMNU7sFCQ6Q403TFOPdjI';

    public $defaultSettings = [
        'status' => 0,
        'api_key' => '',
        'lat' => 0,
        'lng' => 0,
    ];

    public function getSettings()
    {
        return $this->isActive() ? array_merge($this->defaultSettings, $this->config->get(static::APP_NAME) ?? []) : $this->defaultSettings;
    }

    public function getSystemSettings()
    {
        $lat = $lng = 0;
        $this->load->model('localisation/country');
        $countryId = $this->getCurrentCountryId();
        $config = $this->config->get(static::APP_NAME);
        if (!$config || $config['country_id'] != $countryId) {
            $country = $countryId ? $this->model_localisation_country->getCountry($countryId) : null;
            if ($country) {
                $address = urlencode($country['name']);
                $result = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=" . static::DEFAULT_API_KEY);
                if ($result) {
                    $result = json_decode($result);
                    if ($result && property_exists($result, 'status') && $result->status === "OK") {
                        $result = $result->results[0];
                        if ($result && property_exists($result, 'geometry')) {
                            $lat = $result->geometry->location->lat;
                            $lng = $result->geometry->location->lng;
                        }
                        
                        $this->load->model('setting/setting');
                        $this->model_setting_setting->insertUpdateSetting(
                            static::APP_NAME, 
                            [static::APP_NAME => ['country_id' => $countryId,'lat' => $lat,'lng' => $lng,]]
                        );
                    }
                }
            }
        } else {
            $lat = $config['lat'] ?: 0;
            $lng = $config['lng'] ?: 0;
        }
        return [
            'api_key' => static::DEFAULT_API_KEY,
            'country_id' => $countryId,
            'lat' => $lat,
            'lng' => $lng,
            'status' => 1,
        ];
    }

    private function getCurrentCountryId()
    {
        if (isset($this->session->data['shipping_country_id']) && !empty($this->session->data['shipping_country_id'])) return $this->session->data['shipping_country_id'];

        if (isset($this->session->data['payment_country_id']) && !empty($this->session->data['payment_country_id'])) return $this->session->data['payment_country_id'];

        return $this->config->get('config_country_id');
    }
    
    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled(static::APP_NAME);
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        $setting = array_merge($this->defaultSettings, $this->config->get(static::APP_NAME) ?? []);

        return $this->isInstalled() && (int) $setting['status'] === 1 && !empty($setting['api_key']);
    }
}
