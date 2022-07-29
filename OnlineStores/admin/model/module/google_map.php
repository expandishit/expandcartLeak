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

    protected $curlClient;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->curlClient = $registry->get('curl_client');
    }

    public function getSettings()
    {
        return $this->isInstalled() ? array_merge($this->defaultSettings, $this->config->get(static::APP_NAME) ?? []) : $this->defaultSettings;
    }

    private function getCurrentCountryId()
    {
        return $this->config->get('config_country_id');
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        if (empty($inputs['lat'])) $inputs['lat'] = $this->defaultSettings['lat'];
        if (empty($inputs['lng'])) $inputs['lng'] = $this->defaultSettings['lng'];

        $this->load->model('setting/setting');
        $this->model_setting_setting->insertUpdateSetting(static::APP_NAME, [static::APP_NAME => $inputs]);
        return true;
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

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        return true;
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = '')
    {
        return true;
    }

    public function isValidApiKey(string $api_key = '')
    {
        $setting = $this->getSettings();
        if (!empty($api_key) && $setting['api_key'] == $api_key) return true;

        $this->load->model('localisation/country');
        $countryId = $this->getCurrentCountryId();
        $country = $countryId ? $this->model_localisation_country->getCountry($countryId) : null;
        $country = $country ? $country['name'] : 'USA';

        $result = $this->curlClient->request(
            'GET',
            'https://maps.googleapis.com/maps/api/geocode/json',
            [
                'address' => urlencode($country),
                'key' => $api_key
            ]
        );

        $content = $result->getContent();

        if ($result->ok() && array_key_exists('status', $content) && $content['status'] === 'OK') {
            $result = $content['results'][0] ?: [];
            if ($result && array_key_exists('geometry', $result)) {
                $this->defaultSettings['lat'] = $result['geometry']['location']['lat'];
                $this->defaultSettings['lng'] = $result['geometry']['location']['lng'];
            }
            return true;
        }
        return !true;
    }
}
