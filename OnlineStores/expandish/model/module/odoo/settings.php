<?php

use ExpandCart\Foundation\Inventory\Inventory;
use ExpandCart\Foundation\Providers\Extension;
use ExpandCart\Foundation\Inventory\Clients\Odoo;

class ModelModuleOdooSettings extends Model
{

    private $inventory;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->setInventory(new Inventory(new Odoo, $this->getSettings()));
    }

    //************************************** app setup *********************************************

    /**
     * default app settings
     *
     * @return array
     */
    private function getDefaultSettings()
    {
        return [
            'status' => 0,
            'url' => null,
            'database' => null,
            'username' => null,
            'password' => null,
            'version' => Odoo::DEFAULT_VERSION,
        ];
    }

    /**
     * Get the value of inventory
     */
    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    /**
     * Set the value of inventory
     *
     * @return  self
     */
    public function setInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
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

        $this->model_setting_setting->insertUpdateSetting(
            'odoo',
            $inputs
        );
        $this->odoo_integration_tables();
        return true;
    }

    /**
     * app settings
     *
     * @return array app settings
     */
    public function getSettings()
    {
        return array_merge(
            $this->getDefaultSettings(),
            $this->config->get('odoo') ?? [],
            ['available_versions' => array_keys(Odoo::MODELS_MAP)]
        );
    }

    /**
     * Check if app installed
     *
     * @return boolean
     */
    public function isInstalled()
    {
        return Extension::isInstalled('odoo');
    }

    /**
     * Check is App Active
     *
     * @return boolean
     */
    public function isActive()
    {
        $settings = $this->getSettings();

        return $this->isInstalled()
            && (int) $settings['status'] === 1
            && $settings['url']
            && $settings['database']
            && $settings['username']
            && $settings['password'];
    }


    
}
