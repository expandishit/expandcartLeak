<?php

class ModelModuleGoogleCaptchaSettings extends Model
{

    public function getSettings()
    {
        return $this->config->get('google_captcha');
    }
    /**
     * Get  reCaptcha app status
     *
     * @return boolean
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        if (isset($settings) && $settings['status'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get the reCaptcha site key.
     *
     * @return string
     */
    public function reCaptchaSiteKey()
    {
        return $this->getSettings()['site_key'];
    }

    public function reCaptchaSecreteKey()
    {
        return $this->getSettings()['secret_key'];
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
