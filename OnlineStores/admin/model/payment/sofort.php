<?php

class ModelPaymentSofort extends Model
{

    private $supportedCurrencies = ['EUR', 'GBP', 'CHF', 'PLN', 'HUF', 'CZK'];

    public $errors = [];

    protected $settingsGroup = 'sofort';

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool|\Exception
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(
            'payment', [$this->settingsGroup => $inputs]
        );

        return true;
    }

    /**
     * Get payment settings.
     *
     * @return array|bool
     */
    public function getSettings()
    {
        return ($this->config->get($this->settingsGroup) ?: []);
    }

    // TODO implement this
    // public function validate($inputs)
    // {

    //     if (is_array($inputs) === false) {
    //         $this->errors[] = $this->language->get('invalid_settings');
    //     }

    //     $configKeyPattern = '#^\d+\:\d+\:[0-9a-z]+$#';

    //     if (!preg_match($configKeyPattern, $inputs['config_key'])) {
    //         $this->errors[] = $this->language->get('invalid_config_key');
    //     }

    //     $supportedCurrencies = array_flip($this->supportedCurrencies);

    //     if (!isset($supportedCurrencies[$inputs['default_currency']])) {
    //         $this->errors[] = $this->language->get('invalid_currency');
    //     }

    //     if (count($this->errors) === 0) {
    //         return true;
    //     }

    //     return false;
    // }
}
