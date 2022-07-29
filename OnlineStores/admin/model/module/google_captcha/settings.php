<?php

class ModelModuleGoogleCaptchaSettings extends Model
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
        $this->model_setting_setting->deleteSetting('google_captcha');

        $this->model_setting_setting->editSetting(
            'google_captcha', $inputs
        );

        return true;
    }


    public function getSettings()
    {
        return $this->config->get('google_captcha');
    }

    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    public function reCaptchaSiteKey()
    {
        return $this->getSettings()['site_key'];
    }

    public function getPageStatus($page)
    {
        $settings = $this->getSettings();
        if(count($settings) > 0 && isset($settings['fields']) && isset($settings['fields'][$page])){
            return ($settings['fields'][$page]['enabled'] == 1) ? true : false;
        }
        return false;
    }
}
