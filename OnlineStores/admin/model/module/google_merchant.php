<?php

use ExpandCart\Foundation\Providers\Extension;

class ModelModuleGoogleMerchant extends Model
{
    const APP_NAME = 'google_merchant';

    protected $curlClient;

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    /**
     * update the module settings.
     *
     * @param array $inputs
     *
     * @return bool
     */
    public function updateSettings($inputs)
    {
        $this->load->model('setting/setting');

        $this->model_setting_setting->insertUpdateSetting(static::APP_NAME, [static::APP_NAME => $inputs]);

        return true;
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled(static::APP_NAME);
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isInstalled();
    }

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        return true;
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = '')
    {
        return true;
    }

}
