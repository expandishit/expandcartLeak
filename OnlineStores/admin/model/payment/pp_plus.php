<?php

class ModelPaymentPPPlus extends Model
{
    protected $settingsGroup = 'pp_plus';

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
        $this->load->model('setting/setting');

        if(isset($this->model_setting_setting->getSetting('payment')[$this->settingsGroup]))
            return $this->model_setting_setting->getSetting('payment')[$this->settingsGroup];
        else{
            return [];
        }
    }
}
