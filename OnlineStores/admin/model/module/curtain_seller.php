<?php

class ModelModuleCurtainSeller extends Model 
{
    protected $settings;
    protected $MODULE_NAME = 'curtain_seller';


    public function getSettings()
    {
        $this->load->model('setting/setting');

        return $this->model_setting_setting->getSetting($this->MODULE_NAME);
    }


    public function isEnabled()
    {
        $this->settings = $this->getSettings();
        return array_key_exists( 'curtain_seller_status', $this->settings ) && $this->settings['curtain_seller_status'] == '1';
    }

    public function isInForm()
    {
        $this->settings = $this->getSettings();
        return array_key_exists( 'curtain_seller_in_form', $this->settings ) && $this->settings['curtain_seller_in_form'] == '1';
    }
}
