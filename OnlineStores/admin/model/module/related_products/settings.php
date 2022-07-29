<?php

class ModelModuleRelatedProductsSettings extends Model
{
    /**
     * Application settings key.
     *
     * @var string
     */
    protected $settingsKey = 'related_products';


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
            'related_products', $inputs
        );

        return true;
    }

    public function install()
    {
        $inputs = [
            'rp_status' => 2,
//            'min_products' => 5,
//            'max_products' => 20,
            'products_count' => 10,
            'source' => [
                'categories',
                'manufacturers',
                'tags',
            ],
            'enable_random' => 2,
        ];
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting(
            'related_products', ['related_products' => $inputs]
        );
    }

    public function uninstall()
    {

    }

    public function validate($inputs)
    {
        return true;
    }

    /**
     * Gets the application settings.
     *
     * @return array
     */
    public function getSettings()
    {
        $settings = $this->config->get($this->settingsKey);
        return $settings;
    }


    /**
     * Checks if the application is active or not.
     *
     * @return bool
     */
    public function isActive()
    {
        $enabled=false;
        $settings = $this->getSettings();
        if (isset($settings) && $settings['rp_status'] == 1) {
            $enabled =  true;
        }
        return $enabled && \Extension::isInstalled('related_products') ;
    }
}
