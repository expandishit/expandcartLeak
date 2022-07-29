<?php

class ModelModuleTopBannerAd extends Model
{
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

        $this->model_setting_setting->editSetting(
            'top_banner_ad', $inputs
        );

        return true;
    }

    public function install()
    {
    }

    public function uninstall()
    {
    }

    public function getSettings()
    {
        return $this->config->get('top_banner_ad');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }
}
