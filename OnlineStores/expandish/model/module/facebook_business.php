<?php
/*
 *	location: expandish/model/module/d_social_login.php
 */

class ModelModuleFacebookBusiness extends Model
{
    /**
     * @var string
     */
    private $name = 'facebook_business';

    public function getSettings()
    {
        return $this->config->get($this->name);
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
