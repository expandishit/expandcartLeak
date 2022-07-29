<?php

class ModelPaymentSofort extends Model
{

    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsKey = 'sofort';

    private $supportedCurrencies = ['EUR', 'GBP', 'CHF', 'PLN', 'HUF', 'CZK'];


    /**
     * Get method data array to parse it in checkout page.
     *
     * @param string $address
     * @param int $total
     *
     * @return array
     */
    public function getMethod($address, $total)
    {
        $this->language->load_json('payment/sofort');

        $settings = $this->getSettings();

        $currency = $settings['default_currency'];

        $status = true;
        if ($this->validateSettings($settings) == false) {
            $status = false;
        } else if ($this->validateCurrency($currency) == false) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'sofort',
                'title' => $this->language->get('text_title'),
                'sort_order' => $settings['sort_order']
            );
        }

        return $method_data;
    }

    /**
     * Validate payment method settings to check if client id and secret key is set or not.
     *
     * @param array $settings
     *
     * @return bool
     */
    public function validateSettings($settings)
    {
        if (is_array($settings) === false) {
            return false;
        }

        if ($settings['status'] != true) {
            return false;
        }

        if (!isset($settings['config_key']) || $settings['config_key'] == '') {
            return false;
        }

        return true;
    }

    public function validateCurrency($currency)
    {
        $supportedCurrencies = array_flip($this->supportedCurrencies);

        if (!isset($supportedCurrencies[$currency])) {
            return false;
        }

        return true;
    }

    /**
     * Return payment settings group using the key string.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return $this->config->get($this->settingsKey);
    }

    /**
     * Generate new token to validate the request.
     *
     * @param string $secret
     *
     * @return string
     */
    public function generateToken($secret)
    {
        return password_hash($secret, PASSWORD_DEFAULT);
    }

    /**
     * Validate the given token to check if the request is comming from the right source.
     *
     * @param string $token
     * @param string $hash
     *
     * @return bool
     */
    public function validateToken($token, $hash)
    {
        return password_verify($hash, $token);
    }

    /**
     * Create a simple secret to use it in the validation.
     *
     * @param int $orderId
     *
     * @return bool
     */
    public function getSecret($orderId)
    {
        return implode('-', [STORCODE, $orderId]);
    }

    public function getRealIp()
    {
        return file_get_contents('https://www.sofort.com/payment/status/ipList');
    }
}
