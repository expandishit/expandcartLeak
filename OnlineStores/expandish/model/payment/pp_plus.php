<?php

class ModelPaymentPPPlus extends Model
{

    /**
     * The settings key string.
     *
     * @var string
     */
    protected $settingsKey = 'pp_plus';


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
        $this->language->load_json('payment/pp_plus');

        $settings = $this->getSettings();

        $status = false;
        if ($this->validateSettings($settings)) {
            $status = true;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'pp_plus',
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

        if (!isset($settings['client_id']) || $settings['client_id'] == '') {
            return false;
        }

        if (!isset($settings['client_secret']) || $settings['client_secret'] == '') {
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
}
