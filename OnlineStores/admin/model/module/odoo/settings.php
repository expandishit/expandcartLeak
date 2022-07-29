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

    /**
     *   Install the required values for the application.
     *
     *   @return boolean whether successful or not.
     */
    public function install($store_id = 0)
    {
        try {
           
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public function odoo_integration_tables()
    {
        $settings = $this->getSettings();
        try {
            if($settings['customers_integrate']==1)
            {
            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "odoo_customers` (
                `customer_id` INT UNSIGNED NOT NULL,
                `odoo_customer_id` VARCHAR(255) NOT NULL
            )");
            }
            if($settings['products_integrate']==1)
            {
                $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "odoo_products` (
                    `product_id` INT UNSIGNED NOT NULL,
                    `odoo_product_id` VARCHAR(255) NOT NULL
                )");
            }
           
            if($settings['orders_integrate']==1)
            {
                $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "odoo_orders` (
                    `order_id` INT UNSIGNED NOT NULL,
                    `odoo_order_id` VARCHAR(255) NOT NULL
                )");
            }

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "odoo_sync` (
                `odoo_sync_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `module` VARCHAR(32) NOT NULL,
                `last_sync_date` VARCHAR(255) NOT NULL
            )");
          
          return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *   Remove the values from the database.
     *
     *   @return boolean whether successful or not.
     */
    public function uninstall($store_id = 0, $group = 'odoo')
    {
        try {

            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "odoo_customers`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "odoo_products`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "odoo_orders`");
            $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "odoo_sync`");

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    public function selectlastSync($module)
    {
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "odoo_sync WHERE module = '" . $module . "'");
      
        if (!$query->num_rows) {
            $last_sync_date= '1/1/1990';
            $this->db->query("INSERT INTO  " . DB_PREFIX . "odoo_sync SET module = '" . $module . "', 	last_sync_date = '" . $last_sync_date . "'");
        }
        return $query->num_rows ? $query->row : null;

    }

    public function updatelastSync($module,$last_sync_date)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "odoo_sync SET last_sync_date = '" . $last_sync_date . "' WHERE module = '" . $module . "'");
    }

   


    
}
