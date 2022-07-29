<?php

use ExpandCart\Foundation\Providers\Extension;

class ModelShippingBmDelivery extends Model
{
    /**
     * Curl Client Instance
     *
     * @var CurlClient
     */
    protected $curlClient;

    /**
     * Constructor
     *
     * @param Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->curlClient = $registry->get('curl_client');
    }


    /**
     * @const string BASE_API_URL
     */
    const BASE_API_URL = 'https://bms.olivery.app';

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return Extension::isInstalled('bm_delivery');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive(): bool
    {
        $settings = $this->getSettings();

        return $this->isInstalled()
            && (int) $settings['status'] === 1;
    }

    /**
     * Install bm delivery extension
     *
     * @return void
     */
    public function install()
    {
        $this->load->model('setting/setting');

        //Add settings default
        $this->model_setting_setting->insertUpdateSetting('bm_delivery', ['bm_delivery' => $this->getDefaultSettings()]);
    }

    /**
     * Default settings
     *
     * @return array
     */
    private function getDefaultSettings(): array
    {
        return [
            'status' => 0,
            // authentication infos
            'auth' => [
                'db' => '',
                'login' => '',
                'password' => '',
                'session_id' => '',
                'cookies' => '',
            ],
            'display_name' => '',
            'after_creation_status' => '',
            'tax_class_id' => '',
            'geo_zone_id' => '',
            'price' => [],
        ];
    }

    /**
     * Mix default settings & user settings
     *
     * @return array
     */
    public function getSettings(): array
    {
        return array_merge($this->getDefaultSettings(), $this->config->get('bm_delivery') ?? []);
    }

    /**
     * Update module settings.
     *
     * @param array $data
     *
     * @return bool
     */
    public function updateSettings(array $data): bool
    {
        $setting = $this->load->model('setting/setting', ['return' => true]);
        $setting->insertUpdateSetting('bm_delivery', ['bm_delivery' => $data]);

        return true;
    }

    /**
     * Create new shipment Order.
     * @param array $order data to be shipped.
     * @return HttpResponse
     */
    public function createShipment(array $data)
    {
        $settings = $this->getSettings();

        $results = $this->curlClient->request(
            'POST',
            $this->baseUrl() . '/create_order',
            [],
            [
                "jsonrpc" => "2.0",
                "params" => $data
            ],
            [
                'Content-Type' => 'application/json',
                'X-Oenerp' => $settings['auth']['session_id'],
                'Cookie' => $this->parseCookies($settings['auth']['cookies']),

            ]
        );

        return $results;
    }

    /**
     * Create authentication token
     *
     * @param string $db
     * @param string $username
     * @param string $pwd
     * @return HttpResponse
     */
    public function createAuthToken(string $db, string $username, string $pwd)
    {
        $result = $this->curlClient->request(
            'POST',
            $this->baseUrl() . '/web/session/authenticate',
            [],
            [
                "jsonrpc" => "2.0",
                "params" => [
                    "db" => $db,
                    "login" => $username,
                    "password" => $pwd
                ]
            ],
            [
                'Content-Type' => 'application/json'
            ]
        );

        return $result;
    }


    /**
     * Get BM Delivery areas
     *
     * @return array
     */
    public function getAreas(): array
    {
        $settings = $this->getSettings();
        $results = $this->curlClient->request(
            'POST',
            $this->baseUrl() . '/get_areas',
            [],
            [
                "jsonrpc" => "2.0",
                "params" => [
                    "db"        => $settings['auth']['db'], 
                    "login"     =>  $settings['auth']['login'], 
                    "password"  =>  $settings['auth']['password'] 
                ]
            ],
            [
                'Content-Type' => 'application/json',
                'X-Oenerp' => $settings['auth']['session_id'],
                'Cookie' => $this->parseCookies($settings['auth']['cookies']),
            ]
        );
        
        if ($results->ok()) {
            return $results->getContent()['result']['response'];
        }

        return [];
    }

    /**
     * The bm delivery resources base api url
     *
     * @return string
     */
    private function baseUrl(): string
    {
        return trim(self::BASE_API_URL, '/');
    }

    private function parseCookies(array $cookies = null)
    {
        if (!$cookies) {
            return '';
        }
        
        $cookieStr = '';

        foreach ($cookies as $key => $value) {
            $cookieStr .= "$key=$value; ";
        }

        if (count($cookieStr)) {
            $cookieStr = substr($cookieStr, 0, -2);
        }

        return $cookieStr;
    }
}
