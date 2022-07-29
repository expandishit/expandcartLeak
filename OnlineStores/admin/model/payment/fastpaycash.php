<?php

class ModelPaymentFastpayCash extends Model
{
    protected $settingsGroup = 'fastpaycash';

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

}
